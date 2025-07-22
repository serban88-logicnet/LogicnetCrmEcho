<?php
namespace App\Models;

use PDO;

class EntityDefinitionModel {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all entities for a company.
     */
    public function getAllEntities($company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM entities
            WHERE company_id = :company_id
            ORDER BY name ASC
        ");
        $stmt->execute(['company_id' => $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get one entity by its ID and company.
     */
    public function getEntityById($id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM entities
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
     * Create a new entity definition.
     */
    public function createEntity($company_id, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO entities (company_id, name, slug, description)
            VALUES (:company_id, :name, :slug, :description)
        ");
        return $stmt->execute([
            'company_id'  => $company_id,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? ''
        ]);
    }

    /**
     * Update an existing entity definition.
     */
    public function updateEntity($id, $company_id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE entities
            SET name = :name,
                slug = :slug,
                description = :description
            WHERE id = :id AND company_id = :company_id
        ");
        return $stmt->execute([
            'id'          => $id,
            'company_id'  => $company_id,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? ''
        ]);
    }

    /**
     * Delete an entity.
     */
    public function deleteEntity($id, $company_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM entities
            WHERE id = :id AND company_id = :company_id
        ");
        return $stmt->execute([
            'id' => $id,
            'company_id' => $company_id
        ]);
    }
}
