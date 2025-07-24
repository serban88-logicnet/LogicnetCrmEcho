<?php

use App\Database\Database;
use App\Models\EntityModel;

class EntityController {
    protected $pdo;
    protected $type;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->type = $_GET['type'] ?? 'clienti';
    }

    public function list() {
        require_auth();
        $current_company = current_company_id();
        $entity = entity_model()->getEntityBySlug($this->type, $current_company);
        if (!$entity) {
            set_flash('error', sprintf(__('entity_not_found'), $this->type));
            redirect("index.php?route=entity&type=clienti&action=list");
            exit;
        }
        $entity_id = $entity['id'];
        $records = entity_model()->getRecordsByEntityId($entity_id, $current_company);
        foreach ($records as &$record) {
            $record['fields'] = entity_model()->getFieldDefinitionsWithValues($entity_id, $record['id'], $current_company);
        }
        unset($record);
        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/list.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    public function view() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;
        if (!$record_id) {
            set_flash('error', __('missing_record_id'));
            redirect("index.php?route=entity&type={$this->type}&action=list");
            exit;
        }
        $entity = entity_model()->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=clienti&action=list");
            exit;
        }
        $entity_id = $entity['id'];
        $fields = entity_model()->getFieldDefinitionsWithValues($entity_id, $record_id, $company_id);
        $relationships = entity_model()->getChildRelationships($entity_id);
        $parentRelationships = entity_model()->getParentRelationships($entity_id);

        // ✨ FIX: This logic now correctly fetches all necessary data for the view page
        if ($entity['slug'] === 'facturi') {
            foreach ($relationships as &$rel) {
                $other_entity_slug = ($rel['entity_one_id'] == $entity_id) ? $rel['entity_two_slug'] : $rel['entity_one_slug'];
                if ($other_entity_slug === 'produse') {
                    $other_entity_id = ($rel['entity_one_id'] == $entity_id) ? $rel['entity_two_id'] : $rel['entity_one_id'];
                    $pretUnitarField = entity_model()->getFieldBySlug('pret_unitar', $other_entity_id, $company_id);
                    if ($pretUnitarField) {
                        // We must call getLinkedRecordsWithMeta to get the quantity
                        $linked_products = entity_model()->getLinkedRecordsByRelationshipId($record_id, $rel['id'], $company_id);
                        foreach ($linked_products as &$link) {
                            $price_value = entity_model()->getFieldValue($link['id'], $pretUnitarField['id']);
                            $link['price'] = $price_value['value'] ?? 0;
                        }
                        unset($link);
                        // Store this enriched data back into the main relationships array
                        $rel['linked_records'] = $linked_products;
                    }
                }
            }
            unset($rel);
        }

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/view.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    public function delete() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;
        if (!$record_id) {
            set_flash('error', __('missing_record_id'));
            redirect("index.php?route=entity&type={$this->type}&action=list");
            exit;
        }
        $entity = entity_model()->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=clienti&action=list");
            exit;
        }
        $entity_id = $entity['id'];
        $stmt = $this->pdo->prepare("SELECT * FROM records WHERE id = :id AND entity_id = :entity_id AND company_id = :company_id");
        $stmt->execute(['id' => $record_id, 'entity_id' => $entity_id, 'company_id' => $company_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            set_flash('error', __('record_not_found_or_unauthorized'));
            redirect("index.php?route=entity&type={$this->type}&action=list");
            exit;
        }
        $stmt = $this->pdo->prepare("UPDATE records SET is_deleted = 1, deleted_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $record_id]);
        set_flash('success', sprintf(__('success_delete'), $entity['name']));
        redirect("index.php?route=entity&type={$this->type}&action=list");
        exit;
    }

    public function form() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;

        $entity = entity_model()->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=clienti&action=list");
            exit;
        }
        $entity_id = $entity['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $is_new_record = !$record_id;

            if ($is_new_record) {
                $stmt = $this->pdo->prepare("INSERT INTO records (company_id, entity_id) VALUES (?, ?)");
                $stmt->execute([$company_id, $entity_id]);
                $record_id = $this->pdo->lastInsertId();
            }

            foreach ($_POST['fields'] as $field_id => $value) {
                entity_model()->updateFieldValue($record_id, $field_id, $value);
            }
            
            try {
                $submitted_rels = $_POST['relationships'] ?? [];
                entity_model()->syncRelationships($record_id, $company_id, $submitted_rels);
                
                if ($entity['slug'] === 'facturi') {
                    $this->recalculateInvoiceTotal($record_id, $company_id);
                }

                $success_message_key = $is_new_record ? 'success_create' : 'success_update';
                set_flash('success', sprintf(__($success_message_key), $entity['name']));

            } catch (\Exception $e) {
                set_flash('error', $e->getMessage());
            }

            if ($is_new_record && isset($_GET['parent_id']) && $_GET['parent_id'] > 0) {
                $parent_id = (int)$_GET['parent_id'];
                $parent_entity_id = entity_model()->getEntityIdForRecord($parent_id, $company_id);
                $parent_entity = entity_model()->getEntityById($parent_entity_id, $company_id);
                redirect("index.php?route=entity&type={$parent_entity['slug']}&action=form&id={$parent_id}");
            } elseif ($is_new_record) {
                redirect("index.php?route=entity&type={$this->type}&action=view&id={$record_id}");
            } else {
                redirect("index.php?route=entity&type={$this->type}&action=form&id={$record_id}");
            }
            exit;
        }

        $fields = entity_model()->getFieldDefinitionsWithValues($entity_id, $record_id ?? 0, $company_id);
        
        $preselected_parent_info = null;
        if (!$record_id && isset($_GET['relationship'], $_GET['parent_id'])) {
            $preselected_parent_info = [
                'rel_id' => (int)$_GET['relationship'],
                'parent_id' => (int)$_GET['parent_id']
            ];
        }
        
        $relationship_form_data = [];
        $all_relationships = entity_model()->getRelationshipsForEntity($entity_id, $company_id);

        foreach ($all_relationships as $rel) {
            $is_parent_view = ($rel['entity_one_id'] == $entity_id);
            $render_type = 'single';
            if ($rel['relationship_type'] === 'many_many' || ($rel['relationship_type'] === 'one_many' && $is_parent_view)) {
                $render_type = 'multiple';
            }

            $other_entity_id = $is_parent_view ? $rel['entity_two_id'] : $rel['entity_one_id'];
            $other_entity = entity_model()->getEntityById($other_entity_id, $company_id);
            
            $all_options = entity_model()->getRecordsByEntityId($other_entity_id, $company_id);
            
            $pretUnitarField = entity_model()->getFieldBySlug('pret_unitar', $other_entity_id, $company_id);
            if ($pretUnitarField) {
                foreach($all_options as &$opt) {
                    $price_value = entity_model()->getFieldValue($opt['id'], $pretUnitarField['id']);
                    $opt['price'] = $price_value['value'] ?? 0;
                }
                unset($opt);
            }
            
            foreach($all_options as &$opt) {
                $opt['summary'] = entity_model()->getLabelForRecord($opt['id'], $company_id);
                if ($rel['relationship_type'] === 'one_one') {
                    $existing_link = entity_model()->getLinkedRecordsByRelationshipId($opt['id'], $rel['id'], $company_id);
                    $opt['is_linked'] = !empty($existing_link);
                }
            }
            unset($opt);

            $existing_links = $record_id ? entity_model()->getLinkedRecordsByRelationshipId($record_id, $rel['id'], $company_id) : [];
            
            if ($other_entity['slug'] === 'produse' && $pretUnitarField) {
                foreach ($existing_links as &$link) {
                    $price_value = entity_model()->getFieldValue($link['id'], $pretUnitarField['id']);
                    $link['price'] = $price_value['value'] ?? 0;
                }
                unset($link);
            }

            $current_link = $existing_links[0] ?? null;
            
            if ($preselected_parent_info && $rel['id'] == $preselected_parent_info['rel_id']) {
                $parent_summary = entity_model()->getLabelForRecord($preselected_parent_info['parent_id'], $company_id);
                $current_link = ['id' => $preselected_parent_info['parent_id'], 'summary' => $parent_summary];
            }

            $existing_ids = array_column($existing_links, 'id');
            $available_options = array_filter($all_options, fn($opt) => !in_array($opt['id'], $existing_ids));

            $relationship_form_data[] = [
                'relationship' => $rel,
                'other_entity' => $other_entity,
                'render_type' => $render_type,
                'options' => $available_options,
                'all_options_for_js' => $all_options,
                'existing' => $existing_links,
                'current_link' => $current_link,
                'fields' => ($rel['relationship_type'] === 'many_many')
                    ? entity_model()->getRelationshipFieldsByRelationshipId($rel['id'])
                    : []
            ];
        }

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/form.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    private function recalculateInvoiceTotal($invoice_id, $company_id) {
        $facturaProdusRel = entity_model()->getRelationshipBySlug('facturi', 'produse', $company_id);
        if (!$facturaProdusRel) return;

        $linked_products_with_meta = entity_model()->getLinkedRecordsByRelationshipId($invoice_id, $facturaProdusRel['id'], $company_id);
        
        $total = 0;
        foreach ($linked_products_with_meta as $product) {
            $quantity = (float)($product['meta']['cantitate'] ?? 0);
            
            $pretUnitarField = entity_model()->getFieldBySlug('pret_unitar', $product['entity_id'], $company_id);
            $price = 0;
            if ($pretUnitarField) {
                $price_value = entity_model()->getFieldValue($product['id'], $pretUnitarField['id']);
                $price = (float)($price_value['value'] ?? 0);
            }
            $total += $quantity * $price;
        }

        $valoareTotalaField = entity_model()->getFieldBySlug('valoare_totala', entity_model()->getEntityIdForRecord($invoice_id, $company_id), $company_id);
        if ($valoareTotalaField) {
            entity_model()->updateFieldValue($invoice_id, $valoareTotalaField['id'], $total);
        }
    }
}
