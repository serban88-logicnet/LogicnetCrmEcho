<?php
namespace App\Models;

use PDO;

class EntityModel {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function syncRelationships($current_record_id, $company_id, $submitted_rels) {
        $current_entity_id = $this->getEntityIdForRecord($current_record_id, $company_id);
        $all_defined_rels = $this->getRelationshipsForEntity($current_entity_id, $company_id);

        foreach ($all_defined_rels as $rel) {
            $rel_id = $rel['id'];
            $is_parent_view = ($rel['entity_one_id'] == $current_entity_id);
            $form_data = $submitted_rels[$rel_id] ?? [];

            $db_links_stmt = $this->pdo->prepare(
                "SELECT * FROM relationship_records WHERE relationship_id = ? AND " . ($is_parent_view ? "parent_record_id" : "child_record_id") . " = ?"
            );
            $db_links_stmt->execute([$rel_id, $current_record_id]);
            $db_links = $db_links_stmt->fetchAll(PDO::FETCH_ASSOC);
            $db_other_side_ids = array_column($db_links, $is_parent_view ? 'child_record_id' : 'parent_record_id');

            $form_other_side_ids = [];
            $form_meta_map = [];
            if (isset($form_data['record_id'])) {
                if(!empty($form_data['record_id'])) $form_other_side_ids[] = $form_data['record_id'];
            } else {
                if(is_array($form_data)) {
                    foreach($form_data as $item) {
                        if(!empty($item['record_id'])) {
                            $form_other_side_ids[] = $item['record_id'];
                            $form_meta_map[$item['record_id']] = $item;
                        }
                    }
                }
            }
            $form_other_side_ids = array_filter($form_other_side_ids, 'is_numeric');

            $ids_to_delete = array_diff($db_other_side_ids, $form_other_side_ids);
            $ids_to_add = array_diff($form_other_side_ids, $db_other_side_ids);
            $ids_to_update = array_intersect($db_other_side_ids, $form_other_side_ids);

            if (!empty($ids_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
                $col_to_check = $is_parent_view ? "child_record_id" : "parent_record_id";
                $col_for_current = $is_parent_view ? "parent_record_id" : "child_record_id";
                $delete_stmt = $this->pdo->prepare(
                    "DELETE FROM relationship_records WHERE relationship_id = ? AND {$col_for_current} = ? AND {$col_to_check} IN ($placeholders)"
                );
                $delete_stmt->execute(array_merge([$rel_id, $current_record_id], $ids_to_delete));
            }

            if (!empty($ids_to_add)) {
                foreach ($ids_to_add as $other_id) {
                    if ($rel['relationship_type'] === 'one_one') {
                        $check_stmt = $this->pdo->prepare("DELETE FROM relationship_records WHERE relationship_id = ? AND (parent_record_id = ? OR child_record_id = ?)");
                        $check_stmt->execute([$rel_id, $other_id, $other_id]);
                    }
                    $parent_id = $is_parent_view ? $current_record_id : $other_id;
                    $child_id = $is_parent_view ? $other_id : $current_record_id;
                    $add_stmt = $this->pdo->prepare("INSERT INTO relationship_records (company_id, relationship_id, parent_record_id, child_record_id) VALUES (?, ?, ?, ?)");
                    $add_stmt->execute([$company_id, $rel_id, $parent_id, $child_id]);
                    $new_rel_record_id = $this->pdo->lastInsertId();
                    $this->updateRelationshipMeta($new_rel_record_id, $rel_id, $form_meta_map[$other_id] ?? []);
                }
            }
            
            if (!empty($ids_to_update)) {
                foreach ($ids_to_update as $other_id) {
                    $db_link = array_values(array_filter($db_links, fn($link) => $link[$is_parent_view ? 'child_record_id' : 'parent_record_id'] == $other_id))[0];
                    $this->updateRelationshipMeta($db_link['id'], $rel_id, $form_meta_map[$other_id] ?? []);
                }
            }
        }
    }

    private function updateRelationshipMeta($rel_record_id, $rel_id, $meta_data) {
        $field_defs = $this->getRelationshipFieldsByRelationshipId($rel_id);
        if (empty($field_defs)) return;

        $this->pdo->prepare("DELETE FROM relationship_meta WHERE relationship_record_id = ?")->execute([$rel_record_id]);
        
        $insert_meta_stmt = $this->pdo->prepare("INSERT INTO relationship_meta (relationship_record_id, relationship_field_id, meta_value) VALUES (?, ?, ?)");
        foreach ($field_defs as $field) {
            if (isset($meta_data[$field['meta_key']])) {
                $insert_meta_stmt->execute([$rel_record_id, $field['id'], $meta_data[$field['meta_key']]]);
            }
        }
    }

    /**
     * ✨ FIX: This is now the single, robust function for getting related records.
     * It works from any perspective and fetches metadata when available.
     */
    public function getLinkedRecordsByRelationshipId($record_id, $relationship_id, $company_id) {
        $rel = $this->getEntityRelationshipById($relationship_id, $company_id);
        if (!$rel) return [];

        $current_entity_id = $this->getEntityIdForRecord($record_id, $company_id);
        $is_parent_view = ($rel['entity_one_id'] == $current_entity_id);

        $other_record_col = $is_parent_view ? 'child_record_id' : 'parent_record_id';
        $current_record_col = $is_parent_view ? 'parent_record_id' : 'child_record_id';

        $stmt = $this->pdo->prepare("
            SELECT 
                r.id,
                r.entity_id,
                rf.meta_key,
                rm.meta_value
            FROM relationship_records rr
            JOIN records r ON r.id = rr.{$other_record_col}
            LEFT JOIN relationship_meta rm ON rm.relationship_record_id = rr.id
            LEFT JOIN relationship_fields rf ON rf.id = rm.relationship_field_id
            WHERE rr.{$current_record_col} = :record_id
              AND rr.relationship_id = :relationship_id
              AND rr.company_id = :company_id
              AND r.is_deleted = 0
        ");
        $stmt->execute([
            'record_id' => $record_id,
            'relationship_id' => $relationship_id,
            'company_id' => $company_id
        ]);

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $other_id = $row['id'];
            if (!isset($grouped[$other_id])) {
                $grouped[$other_id] = [
                    'id' => $row['id'],
                    'entity_id' => $row['entity_id'],
                    'summary' => $this->formatRecordLabel($row['entity_id'], $row['id'], $company_id),
                    'meta' => []
                ];
            }
            if (!empty($row['meta_key'])) {
                $grouped[$other_id]['meta'][$row['meta_key']] = $row['meta_value'];
            }
        }
        return array_values($grouped);
    }

    public function getRelationshipBySlug($slug1, $slug2, $company_id) {
        $stmt = $this->pdo->prepare("SELECT re.* FROM relationship_entities re JOIN entities e1 ON re.entity_one_id = e1.id JOIN entities e2 ON re.entity_two_id = e2.id WHERE re.company_id = :company_id AND ((e1.slug = :slug1 AND e2.slug = :slug2) OR (e1.slug = :slug2 AND e2.slug = :slug1)) LIMIT 1");
        $stmt->execute(['company_id' => $company_id, 'slug1' => $slug1, 'slug2' => $slug2]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getFieldBySlug($slug, $entity_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM custom_fields WHERE slug = :slug AND entity_id = :entity_id AND company_id = :company_id LIMIT 1");
        $stmt->execute(['slug' => $slug, 'entity_id' => $entity_id, 'company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getFieldValue($record_id, $field_id) {
        $stmt = $this->pdo->prepare("SELECT value FROM custom_field_values WHERE record_id = :record_id AND custom_field_id = :field_id LIMIT 1");
        $stmt->execute(['record_id' => $record_id, 'field_id' => $field_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getEntityBySlug($slug, $company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM entities WHERE slug = :slug AND company_id = :company_id LIMIT 1");
        $stmt->execute(['slug' => $slug, 'company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getRecordsByEntityId($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM records WHERE entity_id = :entity_id AND company_id = :company_id AND is_deleted = 0");
        $stmt->execute(['entity_id' => $entity_id, 'company_id' => $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRelationshipFieldsByRelationshipId($relationship_id) {
        $stmt = $this->pdo->prepare("SELECT id, relationship_id, meta_key, field_label, field_type, is_required FROM relationship_fields WHERE relationship_id = :id ORDER BY sort_order ASC");
        $stmt->execute(['id' => $relationship_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getFieldDefinitionsWithValues($entity_id, $record_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT cf.id AS field_id, cf.slug, cf.field_name, cf.field_type, cf.is_required, cfv.value, cf.is_primary_label FROM custom_fields cf LEFT JOIN custom_field_values cfv ON cf.id = cfv.custom_field_id AND cfv.record_id = :record_id WHERE cf.entity_id = :entity_id AND cf.company_id = :company_id");
        $stmt->execute(['record_id' => $record_id, 'entity_id' => $entity_id, 'company_id' => $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateFieldValue($record_id, $field_id, $value) {
        $stmt = $this->pdo->prepare("SELECT id FROM custom_field_values WHERE record_id = :record_id AND custom_field_id = :field_id");
        $stmt->execute(['record_id' => $record_id, 'field_id' => $field_id]);
        if ($stmt->fetch()) {
            $stmt = $this->pdo->prepare("UPDATE custom_field_values SET value = :value WHERE record_id = :record_id AND custom_field_id = :field_id");
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO custom_field_values (record_id, custom_field_id, value) VALUES (:record_id, :field_id, :value)");
        }
        return $stmt->execute(['record_id' => $record_id, 'field_id'  => $field_id, 'value'     => $value]);
    }
    public function getChildRelationships($entity_id) {
        $all_rels = $this->getRelationshipsForEntity($entity_id, current_company_id());
        $child_rels = [];
        foreach ($all_rels as $rel) {
            if (($rel['entity_one_id'] == $entity_id && in_array($rel['relationship_type'], ['one_many', 'many_many', 'one_one'])) || ($rel['entity_two_id'] == $entity_id && in_array($rel['relationship_type'], ['many_one', 'many_many', 'one_one']))) {
                 $child_rels[] = $rel;
            }
        }
        return $child_rels;
    }
    public function getFormRelationships($entity_id, $company_id) {
        return $this->getRelationshipsForEntity($entity_id, $company_id);
    }
    public function getRelationshipsForEntity($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT re.*, e1.name AS entity_one_name, e1.slug AS entity_one_slug, e2.name AS entity_two_name, e2.slug AS entity_two_slug FROM relationship_entities re JOIN entities e1 ON e1.id = re.entity_one_id JOIN entities e2 ON e2.id = re.entity_two_id WHERE re.company_id = :company_id AND (re.entity_one_id = :entity_id OR re.entity_two_id = :entity_id)");
        $stmt->execute(['company_id' => $company_id, 'entity_id' => $entity_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getParentRelationships($entity_id) {
        $all_rels = $this->getRelationshipsForEntity($entity_id, current_company_id());
        $parent_rels = [];
        foreach ($all_rels as $rel) {
            if (($rel['entity_two_id'] == $entity_id && $rel['relationship_type'] === 'one_many') || ($rel['entity_one_id'] == $entity_id && $rel['relationship_type'] === 'many_one')) {
                 $parent_rels[] = $rel;
            }
        }
        return $parent_rels;
    }
    public function getParentRecordByRelationship($child_record_id, $relationship_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT r.id FROM relationship_records rr JOIN records r ON r.id = rr.parent_record_id WHERE rr.child_record_id = :child_id AND rr.relationship_id = :relationship_id AND rr.company_id = :company_id AND r.is_deleted = 0 LIMIT 1");
        $stmt->execute(['child_id' => $child_record_id, 'relationship_id' => $relationship_id, 'company_id' => $company_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $entity_id = $this->getEntityIdForRecord($row['id'], $company_id);
        $summary = $this->formatRecordLabel($entity_id, $row['id'], $company_id);
        return ['id' => $row['id'], 'summary' => $summary];
    }
    public function getEntityRelationshipById($id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM relationship_entities WHERE id = :id AND company_id = :company_id LIMIT 1");
        $stmt->execute(['id' => $id, 'company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    private function formatRecordLabel($entity_id, $record_id, $company_id) {
        $fields = $this->getFieldDefinitionsWithValues($entity_id, $record_id, $company_id);
        foreach ($fields as $f) {
            if (!empty($f['is_primary_label']) && $f['is_primary_label']) {
                return $f['value'];
            }
        }
        return '#' . $record_id;
    }
    public function getLabelForRecord($record_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT entity_id FROM records WHERE id = :id AND company_id = :company_id AND is_deleted = 0 LIMIT 1");
        $stmt->execute(['id' => $record_id, 'company_id' => $company_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) return '#' . $record_id;
        return $this->formatRecordLabel($record['entity_id'], $record_id, $company_id);
    }
    public function getEntityIdForRecord($record_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT entity_id FROM records WHERE id = :id AND company_id = :company_id LIMIT 1");
        $stmt->execute(['id' => $record_id, 'company_id' => $company_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['entity_id'] ?? null;
    }
    public function getEntityById($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM entities WHERE id = :id AND company_id = :company_id LIMIT 1");
        $stmt->execute(['id' => $entity_id, 'company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
