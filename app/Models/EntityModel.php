<?php
namespace App\Models;

use PDO;

class EntityModel {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get an entity definition (row from `entities` table) by its slug.
     * Useful for resolving things like 'client', 'order', etc.
     */
    public function getEntityBySlug($slug, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM entities 
            WHERE slug = :slug AND company_id = :company_id 
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug, 'company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records for a specific entity.
     * Only returns non-deleted records for the current company.
     */
    public function getRecordsByEntityId($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM records 
            WHERE entity_id = :entity_id 
              AND company_id = :company_id 
              AND is_deleted = 0
        ");
        $stmt->execute([
            'entity_id' => $entity_id,
            'company_id' => $company_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get relation options (used for rendering dropdowns for relation-type fields).
     * Finds the related entity based on field slug and relationship_entities table.
     * Then fetches records from the related entity to populate a <select>.
     */
    public function getRelationOptions($current_entity_id, $slug, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT e.id, e.name, e.slug
            FROM custom_fields cf
            JOIN relationship_entities er ON er.child_entity_id = cf.entity_id
            JOIN entities e ON e.id = er.parent_entity_id
            WHERE cf.entity_id = :current_entity_id
              AND cf.slug = :slug
              AND cf.company_id = :company_id
        ");
        $stmt->execute([
            'current_entity_id' => $current_entity_id,
            'slug' => $slug,
            'company_id' => $company_id
        ]);

        $related = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$related) return [];

        // Get records from the parent entity
        $records = $this->getRecordsByEntityId($related['id'], $company_id);

        $options = [];
        foreach ($records as $record) {
            // Load fields to generate a label
            $fields = $this->getFieldDefinitionsWithValues($related['id'], $record['id'], $company_id);

            $label = null;
            foreach ($fields as $f) {
                if (!empty($f['is_primary_label']) && $f['is_primary_label']) {
                    $label = $f['value'];
                    break;
                }
            }

            $options[] = [
                'id' => $record['id'],
                'label' => $label ?: ('#' . $record['id'])
            ];
        }

        return $options;
    }

    /**
     * Get all custom fields of a specific N:M relationship
     */
    public function getRelationshipFieldsByRelationshipId($relationship_id) {
        $stmt = $this->pdo->prepare("
            SELECT id, relationship_id, meta_key, field_label, field_type, is_required
            FROM relationship_fields
            WHERE relationship_id = :id
            ORDER BY sort_order ASC
        ");
        $stmt->execute(['id' => $relationship_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Save N:M or 1:N relationships and their meta values.
     *
     * @param int $parent_record_id The record being edited (e.g. an Order)
     * @param int $company_id The company that owns the records
     * @param array $data The submitted relationship data from the form
     */
    public function saveRelationshipsWithMeta($parent_record_id, $company_id, $data) {
        foreach ($data as $relationship_id => $items) {
            // Load relationship config by ID (NOT by slug)
            $rel = $this->getEntityRelationshipById((int)$relationship_id, $company_id);
            if (!$rel) continue;

            // Remove previous relationships (soft replacement pattern)
            $stmt = $this->pdo->prepare("
                DELETE rr, rm
                FROM relationship_records rr
                LEFT JOIN relationship_meta rm ON rm.relationship_id = rr.id
                WHERE rr.left_record_id = :parent_id
                  AND rr.relationship_id = :relationship_id
                  AND rr.company_id = :company_id
            ");
            $stmt->execute([
                'parent_id' => $parent_record_id,
                'relationship_id' => $relationship_id,
                'company_id' => $company_id
            ]);

            // Load meta field definitions (like quantity, unit_price)
            $field_defs = $this->getRelationshipFieldsByRelationshipId($relationship_id);
            $field_map = [];
            foreach ($field_defs as $f) {
                $field_map[$f['meta_key']] = $f['id']; // meta_key => field_id
            }

            // Loop over each linked record in the form
            foreach ($items as $item) {
                if (empty($item['record_id'])) continue;

                // Insert the core relationship link
                $stmt = $this->pdo->prepare("
                    INSERT INTO relationship_records 
                    (company_id, left_record_id, right_record_id, relationship_id)
                    VALUES (:company_id, :left_id, :right_id, :relationship_id)
                ");
                $stmt->execute([
                    'company_id' => $company_id,
                    'left_id' => $parent_record_id,
                    'right_id' => $item['record_id'],
                    'relationship_id' => $relationship_id
                ]);

                $new_rel_id = $this->pdo->lastInsertId();

                // Insert meta field values (if any)
                foreach ($item as $key => $value) {
                    if ($key === 'record_id' || !isset($field_map[$key])) continue;

                    $stmt = $this->pdo->prepare("
                        INSERT INTO relationship_meta 
                        (relationship_id, relationship_field_id, meta_value)
                        VALUES (:relationship_id, :field_id, :value)
                    ");
                    $stmt->execute([
                        'relationship_id' => $new_rel_id,
                        'field_id' => $field_map[$key],
                        'value' => $value
                    ]);
                }
            }
        }
    }



    /**
     * Fetch all custom field definitions and values for a given record.
     * Uses LEFT JOIN to ensure fields with no value are also returned (useful for forms).
     */
    public function getFieldDefinitionsWithValues($entity_id, $record_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT cf.id AS field_id, cf.slug, cf.field_name, cf.field_type, cf.is_required, cfv.value, cf.is_primary_label
            FROM custom_fields cf
            LEFT JOIN custom_field_values cfv 
                ON cf.id = cfv.custom_field_id AND cfv.record_id = :record_id
            WHERE cf.entity_id = :entity_id 
              AND cf.company_id = :company_id
        ");
        $stmt->execute([
            'record_id' => $record_id,
            'entity_id' => $entity_id,
            'company_id' => $company_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save or update a field value in the EAV model.
     * If a value already exists for this record + field, it updates.
     * If not, it inserts a new row.
     */
    public function updateFieldValue($record_id, $field_id, $value) {
        // First check if this record+field already has a value
        $stmt = $this->pdo->prepare("
            SELECT id FROM custom_field_values 
            WHERE record_id = :record_id AND custom_field_id = :field_id
        ");
        $stmt->execute(['record_id' => $record_id, 'field_id' => $field_id]);

        if ($stmt->fetch()) {
            // Update existing value
            $stmt = $this->pdo->prepare("
                UPDATE custom_field_values 
                SET value = :value 
                WHERE record_id = :record_id AND custom_field_id = :field_id
            ");
        } else {
            // Insert new value
            $stmt = $this->pdo->prepare("
                INSERT INTO custom_field_values (record_id, custom_field_id, value) 
                VALUES (:record_id, :field_id, :value)
            ");
        }

        return $stmt->execute([
            'record_id' => $record_id,
            'field_id'  => $field_id,
            'value'     => $value
        ]);
    }

    /**
     * Get all child relationships (1:N) connected to a parent entity.
     * Returns rows from relationship_entities.
     *
     * @param int $entity_id - the parent entity ID.
     * @return array - Each row contains keys like child_entity_id, child_slug, child_name, and now the unique relationship ID.
     */
    public function getChildRelationships($entity_id) {
        $stmt = $this->pdo->prepare("
            SELECT er.child_entity_id, e.slug AS child_slug, e.name AS child_name, er.id
            FROM relationship_entities er
            JOIN entities e ON e.id = er.child_entity_id
            WHERE er.parent_entity_id = :entity_id
        ");
        $stmt->execute(['entity_id' => $entity_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**

     */
    public function getFormRelationships($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM relationship_entities
            WHERE parent_entity_id = :entity_id AND company_id = :company_id
        ");
        $stmt->execute([
            'entity_id' => $entity_id,
            'company_id' => $company_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    /**
     * Get all records linked to a given record via an N:M relationship,
     * including any relationship-specific metadata (like quantity, unit_price, etc.).
     *
     * Example use case: get all products linked to a specific order.
     *
     * @param int $parent_id         - ID of the parent record (e.g., order ID)
     * @param string $slug           - Relationship slug (e.g., 'order_product')
     * @param int $child_entity_id   - ID of the child entity (e.g., product entity ID)
     * @param int $company_id        - Current company ID
     * @return array - Each record with metadata fields (grouped by relationship)
     */
     public function getLinkedRecordsWithMeta($parent_id, $relationship_id, $child_entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                rr.id AS relationship_record_id,
                rr.right_record_id AS child_id,
                r.entity_id,
                rf.meta_key,
                rm.meta_value
            FROM relationship_records rr
            JOIN records r ON r.id = rr.right_record_id
            LEFT JOIN relationship_meta rm ON rm.relationship_id = rr.id
            LEFT JOIN relationship_fields rf ON rf.id = rm.relationship_field_id
            WHERE rr.left_record_id = :parent_id
              AND rr.relationship_id = :relationship_id
              AND rr.company_id = :company_id
              AND r.is_deleted = 0
        ");
        $stmt->execute([
            'parent_id' => $parent_id,
            'relationship_id' => $relationship_id,
            'company_id' => $company_id
        ]);

        $grouped = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rid = $row['relationship_record_id'];

            if (!isset($grouped[$rid])) {
                $grouped[$rid] = [
                    'id' => $row['child_id'],
                    'entity_id' => $row['entity_id'],
                    'summary' => $this->formatRecordLabel($row['entity_id'], $row['child_id'], $company_id),
                    'meta' => []
                ];
            }

            if (!empty($row['meta_key'])) {
                $grouped[$rid]['meta'][$row['meta_key']] = $row['meta_value'];
            }
        }

        return array_values($grouped);
    }




     /**
     * Get all records from an entity that have a custom field value matching the given value.
     * This function was originally used for reverse lookups (e.g. all orders where a field equals X).
     * Now it does not require a field slug; you can extend it later if needed for more complex lookups.
     *
     * @param int $entity_id
     * @param mixed $value - the value to match (e.g., the parent's record ID in a relation)
     * @param int $company_id
     * @return array
     */
    public function getRecordsByFieldValue($entity_id, $value, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT r.id
            FROM records r
            JOIN custom_field_values cfv ON cfv.record_id = r.id
            JOIN custom_fields cf ON cf.id = cfv.custom_field_id
            WHERE r.entity_id = :entity_id
              AND r.company_id = :company_id
              AND cfv.value = :value
              AND r.is_deleted = 0
        ");
        $stmt->execute([
            'entity_id' => $entity_id,
            'company_id' => $company_id,
            'value' => $value
        ]);

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate a human-friendly summary for each record based on the primary label.
        foreach ($records as &$r) {
            $fields = $this->getFieldDefinitionsWithValues($entity_id, $r['id'], $company_id);
            foreach ($fields as $f) {
                if (!empty($f['is_primary_label']) && $f['is_primary_label']) {
                    $r['summary'] = $f['value'];
                    break;
                }
            }
            if (!isset($r['summary'])) {
                $r['summary'] = '#' . $r['id'];
            }
        }
        return $records;
    }



    /**
     * Get all linked child records for a given parent record and relationship.
     *
     * This method fetches rows from the 'relationship_records' table that link a parent record 
     * to its child records under a specific relationship (identified by its unique ID). Then, 
     * for each linked child record, it computes a summary using the primary label field.
     *
     * @param int $parent_id The parent record ID (e.g., an order ID).
     * @param int $relationship_id The unique ID from relationship_entities.
     * @param int $company_id The current company ID.
     * @return array List of linked records, each with an added 'summary' key.
     */
    public function getLinkedRecordsByRelationshipId($parent_id, $relationship_id, $company_id) {

        $stmt = $this->pdo->prepare("
            SELECT r.id, r.entity_id
            FROM relationship_records rr
            JOIN records r ON r.id = rr.right_record_id
            WHERE rr.left_record_id = :parent_id
              AND rr.relationship_id = :relationship_id
              AND rr.company_id = :company_id
              AND r.is_deleted = 0
        ");
        $stmt->execute([
            'parent_id' => $parent_id,
            'relationship_id' => $relationship_id,
            'company_id' => $company_id
        ]);

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For each linked record, compute a friendly summary label
        foreach ($records as &$r) {
            $r['summary'] = $this->formatRecordLabel($r['entity_id'], $r['id'], $company_id);
        }
        unset($r);

        return $records;
    }


    public function getParentRelationships($entity_id) {
        $stmt = $this->pdo->prepare("
            SELECT re.id, e.id AS parent_entity_id, e.name AS parent_name, e.slug AS parent_slug
            FROM relationship_entities re
            JOIN entities e ON e.id = re.parent_entity_id
            WHERE re.child_entity_id = :entity_id
        ");
        $stmt->execute(['entity_id' => $entity_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getParentRecordByRelationship($child_record_id, $relationship_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT r.id
            FROM relationship_records rr
            JOIN records r ON r.id = rr.left_record_id
            WHERE rr.right_record_id = :child_id
              AND rr.relationship_id = :relationship_id
              AND rr.company_id = :company_id
              AND r.is_deleted = 0
            LIMIT 1
        ");
        $stmt->execute([
            'child_id' => $child_record_id,
            'relationship_id' => $relationship_id,
            'company_id' => $company_id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $entity_id = $this->getEntityIdForRecord($row['id'], $company_id);
        $summary = $this->formatRecordLabel($entity_id, $row['id'], $company_id);
        return ['id' => $row['id'], 'summary' => $summary];
    }


    /**
     * Fetch a relationship definition from relationship_entities by its unique ID.
     *
     * @param int $id - The unique ID of the relationship.
     * @param int $company_id - The company ID for scoping.
     * @return array|null - Returns the relationship row, or null if not found.
     */
    public function getEntityRelationshipById($id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM relationship_entities
            WHERE id = :id AND company_id = :company_id
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $id,
            'company_id' => $company_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    /**
     * INTERNAL: Formats a single record's display label using common slugs.
     * Used by getLabelForRecord() and other methods.
     */
    private function formatRecordLabel($entity_id, $record_id, $company_id) {
        $fields = $this->getFieldDefinitionsWithValues($entity_id, $record_id, $company_id);

        // Try to get the primary label field
        foreach ($fields as $f) {
            if (!empty($f['is_primary_label']) && $f['is_primary_label']) {
                return $f['value'];
            }
        }

        // Fallback: show record ID
        return '#' . $record_id;
    }


    /**
     * Public label lookup for relation fields.
     * Given a record ID, returns the preferred human-readable label.
     */
    public function getLabelForRecord($record_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT entity_id FROM records 
            WHERE id = :id AND company_id = :company_id AND is_deleted = 0 
            LIMIT 1
        ");
        $stmt->execute(['id' => $record_id, 'company_id' => $company_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return '#' . $record_id;
        }

        return $this->formatRecordLabel($record['entity_id'], $record_id, $company_id);
    }

    /**
     * Given a record ID, return its entity ID.
     * Useful for resolving relationships.
     */
    public function getEntityIdForRecord($record_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT entity_id FROM records 
            WHERE id = :id AND company_id = :company_id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $record_id, 'company_id' => $company_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['entity_id'] ?? null;
    }

    /**
     * Fetch a full entity row from its ID.
     * Used when resolving linked relationships dynamically.
     */
    public function getEntityById($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM entities 
            WHERE id = :id AND company_id = :company_id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $entity_id, 'company_id' => $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
