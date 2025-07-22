<?php

use App\Database\Database;
use App\Models\EntityModel;

class EntityController {
    protected $pdo;
    protected $type;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->type = $_GET['type'] ?? 'client';
    }

    public function list() {
        require_auth();
        $current_company = current_company_id();
        $entity = entity_model()->getEntityBySlug($this->type, $current_company);
        if (!$entity) {
            set_flash('error', sprintf(__('entity_not_found'), $this->type));
            redirect("index.php?route=entity&type=client&action=list");
            exit;
        }

        $entity_id = $entity['id'];
        $records = entity_model()->getRecordsByEntityId($entity_id, $current_company);

        foreach ($records as &$record) {
            $record['fields'] = entity_model()->getFieldDefinitionsWithValues($entity_id, $record['id'], $current_company);
        }
        unset($record);


        // echo "<pre>"; print_r($records);

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/list.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }


    public function form() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;

        // Load current entity (e.g. 'order', 'client')
        $entity = entity_model()->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=client&action=list");
            exit;
        }

        $entity_id = $entity['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CREATE
            if (!$record_id) {
                $stmt = $this->pdo->prepare("INSERT INTO records (company_id, entity_id) VALUES (:company_id, :entity_id)");
                $stmt->execute([
                    'company_id' => $company_id,
                    'entity_id' => $entity_id
                ]);
                $record_id = $this->pdo->lastInsertId();
            }

            // Save main entity fields (works for both create & update)
            foreach ($_POST['fields'] as $field_id => $value) {
                entity_model()->updateFieldValue($record_id, $field_id, $value);
            }

            // If creating via &relationship=X&parent_id=Y, link this to parent
            if (isset($_GET['relationship'], $_GET['parent_id'])) {
                $relationship_id = (int) $_GET['relationship'];
                $parent_id = (int) $_GET['parent_id'];

                $rel = entity_model()->getEntityRelationshipById($relationship_id, $company_id);
                if ($rel && $rel['child_entity_id'] == $entity_id) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO relationship_records (company_id, relationship_id, left_record_id, right_record_id)
                        VALUES (:company_id, :relationship_id, :left_record_id, :right_record_id)
                    ");
                    $stmt->execute([
                        'company_id' => $company_id,
                        'relationship_id' => $relationship_id,
                        'left_record_id' => $parent_id,
                        'right_record_id' => $record_id
                    ]);
                }
            }


            // Save N:M relationship links and meta (always)
            if (!empty($_POST['relationships'])) {
                entity_model()->saveRelationshipsWithMeta(
                    $record_id,
                    $company_id,
                    $_POST['relationships']
                );
            }

            // Flash success message
            set_flash('success', sprintf(
                $record_id == ($_GET['id'] ?? null) 
                    ? __('success_update') 
                    : __('success_create'), 
                $entity['name']
            ));

            redirect("index.php?route=entity&type={$this->type}&action=list");
            exit;
        }

        // Load field definitions and values for the form
        $fields = entity_model()->getFieldDefinitionsWithValues($entity_id, $record_id ?? 0, $company_id);

        // Pre-fill parent relation field when accessed from "Add Related" link
        if (isset($_GET['relationship'], $_GET['parent_id'])) {
            $relationship_id = (int) $_GET['relationship'];
            $parent_id = (int) $_GET['parent_id'];

            $rel = entity_model()->getEntityRelationshipById($relationship_id, $company_id);
            if ($rel && $rel['child_entity_id'] == $entity_id) {
                foreach ($fields as &$field) {
                    if ($field['field_type'] === 'relation') {
                        $related_options = entity_model()->getRelationOptions($entity_id, $field['slug'], $company_id);
                        $match = array_filter($related_options, fn($opt) => $opt['id'] == $parent_id);
                        if (!empty($match)) {
                            $field['value'] = $parent_id;
                            $field['prefilled'] = true;
                            break;
                        }
                    }
                }
                unset($field);
            }
        }

        // Load N:M relationship blocks (products in order, tags in post, etc.)
        $form_relationships = entity_model()->getFormRelationships($entity_id, $company_id);
        $form_data = [];

        foreach ($form_relationships as $rel) {
            $child_records = entity_model()->getRecordsByEntityId($rel['child_entity_id'], $company_id);

            $existing = [];
            if ($record_id) {
                $existing = entity_model()->getLinkedRecordsWithMeta(
                    $record_id,
                    $rel['id'],
                    $rel['child_entity_id'],
                    $company_id
                );
            }

            $form_data[] = [
                'relationship' => $rel,
                'options' => $child_records,
                'existing' => $existing,
                'fields' => ($rel['relationship_type'] === 'N:M')
                    ? entity_model()->getRelationshipFieldsByRelationshipId($rel['id'])
                    : [] // no fields for 1:N
            ];
        }


        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/form.php';
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
            redirect("index.php?route=entity&type=client&action=list");
            exit;
        }

        $entity_id = $entity['id'];

        // Load current record's fields
        $fields = entity_model()->getFieldDefinitionsWithValues($entity_id, $record_id, $company_id);

        // Get both child and parent relationship definitions
        $relationships = entity_model()->getChildRelationships($entity_id);
        $parentRelationships = entity_model()->getParentRelationships($entity_id);


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
            redirect("index.php?route=entity&type=client&action=list");
            exit;
        }

        $entity_id = $entity['id'];

        $stmt = $this->pdo->prepare("SELECT * FROM records WHERE id = :id AND entity_id = :entity_id AND company_id = :company_id");
        $stmt->execute([
            'id' => $record_id,
            'entity_id' => $entity_id,
            'company_id' => $company_id
        ]);
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
}
