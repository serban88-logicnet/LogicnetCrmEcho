<?php
namespace App\Models;

use PDO;

class RelationshipModel {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all relationships where the given entity is either parent or child.
     */
    public function getRelationshipsForEntity($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT re.*, 
                   p.name AS parent_name, p.slug AS parent_slug,
                   c.name AS child_name, c.slug AS child_slug
            FROM relationship_entities re
            JOIN entities p ON p.id = re.parent_entity_id
            JOIN entities c ON c.id = re.child_entity_id
            WHERE re.company_id = :company_id 
              AND (re.parent_entity_id = :entity_id OR re.child_entity_id = :entity_id)
        ");
        $stmt->execute([
            'company_id' => $company_id,
            'entity_id' => $entity_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all entities for dropdowns when building relationships.
     */
    public function getAllEntities($company_id) {
        $stmt = $this->pdo->prepare("
            SELECT id, name, slug 
            FROM entities 
            WHERE company_id = :company_id
            ORDER BY name ASC
        ");
        $stmt->execute(['company_id' => $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save submitted relationships for an entity.
     * Replaces all relationships for this entity.
     */
    public function saveEntityRelationships($entity_id, $company_id, $relationships) {
	    // Clear all relationships where this entity is involved
	    $stmt = $this->pdo->prepare("
	        DELETE FROM relationship_entities
	        WHERE company_id = :company_id AND (parent_entity_id = :id OR child_entity_id = :id)
	    ");
	    $stmt->execute([
	        'company_id' => $company_id,
	        'id' => $entity_id
	    ]);

	    // Insert each submitted relationship
	    foreach ($relationships as $rel) {
	        if (
	            !isset($rel['parent_id'], $rel['child_id'], $rel['type']) ||
	            !$rel['parent_id'] || !$rel['child_id'] || !$rel['type']
	        ) {
	            continue; // skip incomplete entries
	        }

	        $stmt = $this->pdo->prepare("
	            INSERT INTO relationship_entities 
	                (company_id, parent_entity_id, child_entity_id, parent_label, child_label, relationship_type)
	            VALUES 
	                (:company_id, :parent_id, :child_id, :parent_label, :child_label, :type)
	        ");
	        $stmt->execute([
	            'company_id'   => $company_id,
	            'parent_id'    => $rel['parent_id'],
	            'child_id'     => $rel['child_id'],
	            'parent_label' => $rel['parent_label'] ?? '',
	            'child_label'  => $rel['child_label'] ?? '',
	            'type'         => $rel['type']
	        ]);
	    }
	}

}
