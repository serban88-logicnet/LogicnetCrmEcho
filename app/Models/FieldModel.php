<?php
namespace App\Models;

use PDO;

class FieldModel {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all fields for a given entity and company.
     */
    public function getCustomFieldsByEntityId($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM custom_fields
            WHERE entity_id = :entity_id AND company_id = :company_id
            ORDER BY id ASC
        ");
        $stmt->execute([
            'entity_id' => $entity_id,
            'company_id' => $company_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single field by ID and company (for edit).
     */
    public function getFieldById($id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM custom_fields
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
     * Create a new custom field.
     */
    public function createField($entity_id, $company_id, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO custom_fields 
            (entity_id, company_id, field_name, slug, field_type, is_required, is_primary_label)
            VALUES 
            (:entity_id, :company_id, :field_name, :slug, :field_type, :is_required, :is_primary_label)
        ");
        return $stmt->execute([
            'entity_id'        => $entity_id,
            'company_id'       => $company_id,
            'field_name'       => $data['field_name'],
            'slug'             => $data['slug'],
            'field_type'       => $data['field_type'],
            'is_required'      => !empty($data['is_required']) ? 1 : 0,
            'is_primary_label' => !empty($data['is_primary_label']) ? 1 : 0
        ]);
    }

    /**
     * Update an existing field.
     */
    public function updateField($id, $company_id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE custom_fields 
            SET field_name = :field_name,
                slug = :slug,
                field_type = :field_type,
                is_required = :is_required,
                is_primary_label = :is_primary_label
            WHERE id = :id AND company_id = :company_id
        ");
        return $stmt->execute([
            'id'               => $id,
            'company_id'       => $company_id,
            'field_name'       => $data['field_name'],
            'slug'             => $data['slug'],
            'field_type'       => $data['field_type'],
            'is_required'      => !empty($data['is_required']) ? 1 : 0,
            'is_primary_label' => !empty($data['is_primary_label']) ? 1 : 0
        ]);
    }

    /**
     * Delete a field.
     */
    public function deleteField($id, $company_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM custom_fields
            WHERE id = :id AND company_id = :company_id
        ");
        return $stmt->execute([
            'id' => $id,
            'company_id' => $company_id
        ]);
    }
}
