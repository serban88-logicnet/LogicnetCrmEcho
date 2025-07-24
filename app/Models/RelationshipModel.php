<?php
namespace App\Models;

use PDO;

class RelationshipModel {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getRelationshipsForEntity($entity_id, $company_id) {
        $stmt = $this->pdo->prepare("
            SELECT re.*, 
                   e1.name AS entity_one_name, e1.slug AS entity_one_slug,
                   e2.name AS entity_two_name, e2.slug AS entity_two_slug
            FROM relationship_entities re
            JOIN entities e1 ON e1.id = re.entity_one_id
            JOIN entities e2 ON e2.id = re.entity_two_id
            WHERE re.company_id = :company_id 
              AND (re.entity_one_id = :entity_id OR re.entity_two_id = :entity_id)
        ");
        $stmt->execute([
            'company_id' => $company_id,
            'entity_id' => $entity_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
     * ✨ CHANGE: Replaced the destructive DELETE/INSERT with a safer sync/diff algorithm.
     * This prevents relationships defined on other pages from being accidentally deleted.
     */
    public function saveEntityRelationships($entity_id, $company_id, $relationships) {
        // 1. Get existing relationships from DB for the current entity
        $stmt = $this->pdo->prepare("
            SELECT * FROM relationship_entities 
            WHERE company_id = ? AND (entity_one_id = ? OR entity_two_id = ?)
        ");
        $stmt->execute([$company_id, $entity_id, $entity_id]);
        $db_rels = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db_rel_map = [];
        foreach ($db_rels as $db_rel) {
            $key_part1 = min($db_rel['entity_one_id'], $db_rel['entity_two_id']);
            $key_part2 = max($db_rel['entity_one_id'], $db_rel['entity_two_id']);
            $db_rel_map["$key_part1-$key_part2"] = $db_rel;
        }

        $form_rel_keys = [];
        $relationships = $relationships ?? []; // Ensure it's an array to prevent errors

        // 2. Loop through submitted form relationships to INSERT or UPDATE
        foreach ($relationships as $rel) {
            if (empty($rel['entity_one_id']) || empty($rel['entity_two_id']) || empty($rel['type'])) {
                continue;
            }

            $key_part1 = min($rel['entity_one_id'], $rel['entity_two_id']);
            $key_part2 = max($rel['entity_one_id'], $rel['entity_two_id']);
            $key = "$key_part1-$key_part2";
            $form_rel_keys[$key] = true;

            if (isset($db_rel_map[$key])) {
                // UPDATE if the type or parent/child orientation has changed
                if ($db_rel_map[$key]['entity_one_id'] != $rel['entity_one_id'] || $db_rel_map[$key]['relationship_type'] != $rel['type']) {
                    $update_stmt = $this->pdo->prepare("
                        UPDATE relationship_entities 
                        SET entity_one_id = ?, entity_two_id = ?, relationship_type = ?, entity_one_label = ?, entity_two_label = ?
                        WHERE id = ? AND company_id = ?
                    ");
                    $update_stmt->execute([
                        $rel['entity_one_id'], $rel['entity_two_id'], $rel['type'],
                        $rel['entity_one_label'], $rel['entity_two_label'],
                        $db_rel_map[$key]['id'], $company_id
                    ]);
                }
            } else {
                // INSERT new relationship
                $insert_stmt = $this->pdo->prepare("
                    INSERT INTO relationship_entities (company_id, entity_one_id, entity_two_id, relationship_type, entity_one_label, entity_two_label)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert_stmt->execute([
                    $company_id, $rel['entity_one_id'], $rel['entity_two_id'],
                    $rel['type'], $rel['entity_one_label'], $rel['entity_two_label']
                ]);
            }
        }

        // 3. Loop through DB relationships to DELETE any that were removed from the form
        foreach ($db_rel_map as $key => $db_rel) {
            if (!isset($form_rel_keys[$key])) {
                $delete_stmt = $this->pdo->prepare("DELETE FROM relationship_entities WHERE id = ? AND company_id = ?");
                $delete_stmt->execute([$db_rel['id'], $company_id]);
            }
        }
    }
}
