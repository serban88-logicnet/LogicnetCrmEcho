<?php

namespace App\Models;

use App\Database\Database;
use PDO;
 
/**
 * Manages the layout of fields for entities on a per-company basis.
 * This model interacts with the `entity_layouts` table.
 */
class EntityLayoutModel
{
    /**
     * @var PDO The database connection object.
     */
    private $db;

    /**
     * EntityLayoutModel constructor.
     * Establishes the database connection.
     */
    public function __construct()
    {
        // Get the singleton database connection instance
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retrieves the visual layout for a given entity and company.
     *
     * If a custom layout has been saved in `entity_layouts`, it fetches that.
     * If not, it generates a default, single-column layout based on all available
     * fields for that entity.
     *
     * @param int $company_id The ID of the company.
     * @param int $entity_id The ID of the entity.
     * @return array An array representing the layout, with each item containing field and layout details.
     */
    public function getLayout(int $company_id, int $entity_id): array
    {
        // SQL to get the custom layout, joining with custom_fields to get field details
        $sql = "
            SELECT
                el.custom_field_id,
                el.group_name,
                el.row_order,
                el.col_order,
                el.col_span,
                cf.field_name,
                cf.field_type,
                cf.slug,
                cf.is_required
            FROM
                entity_layouts el
            JOIN
                custom_fields cf ON el.custom_field_id = cf.id
            WHERE
                el.company_id = :company_id AND el.entity_id = :entity_id
            ORDER BY
                el.row_order ASC, el.col_order ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':entity_id', $entity_id, PDO::PARAM_INT);
        $stmt->execute();
        $customLayout = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If a custom layout exists, return it
        if (!empty($customLayout)) {
            return $customLayout;
        }

        // --- Fallback to Default Layout ---
        // If no custom layout is found, generate a default one.
        return $this->generateDefaultLayout($company_id, $entity_id);
    }

    /**
     * Saves a new layout configuration for an entity and company.
     *
     * This method performs a transaction: it first deletes the old layout
     * and then inserts all the new layout rows.
     *
     * @param int $company_id The ID of the company.
     * @param int $entity_id The ID of the entity.
     * @param array $layoutData An array of layout items from the frontend. Each item
     * should be an associative array with keys like 'custom_field_id',
     * 'row_order', 'col_order', 'col_span', 'group_name'.
     * @return bool True on success, false on failure.
     */
    public function saveLayout(int $company_id, int $entity_id, array $layoutData): bool
    {
        // Start a database transaction
        $this->db->beginTransaction();

        try {
            // Step 1: Delete the existing layout for this company and entity
            $deleteSql = "DELETE FROM entity_layouts WHERE company_id = :company_id AND entity_id = :entity_id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $deleteStmt->bindParam(':entity_id', $entity_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Step 2: Insert the new layout rows
            $insertSql = "
                INSERT INTO entity_layouts
                    (company_id, entity_id, custom_field_id, group_name, row_order, col_order, col_span)
                VALUES
                    (:company_id, :entity_id, :custom_field_id, :group_name, :row_order, :col_order, :col_span)
            ";
            $insertStmt = $this->db->prepare($insertSql);

            foreach ($layoutData as $item) {
                $insertStmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
                $insertStmt->bindParam(':entity_id', $entity_id, PDO::PARAM_INT);
                $insertStmt->bindParam(':custom_field_id', $item['custom_field_id'], PDO::PARAM_INT);
                $insertStmt->bindParam(':group_name', $item['group_name']);
                $insertStmt->bindParam(':row_order', $item['row_order'], PDO::PARAM_INT);
                $insertStmt->bindParam(':col_order', $item['col_order'], PDO::PARAM_INT);
                $insertStmt->bindParam(':col_span', $item['col_span'], PDO::PARAM_INT);
                $insertStmt->execute();
            }

            // If everything was successful, commit the transaction
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            // If any error occurred, roll back the transaction
            $this->db->rollBack();
            // Optional: log the error message
            // error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Generates a default, single-column layout for an entity.
     * This is used as a fallback when no custom layout has been saved.
     *
     * @param int $company_id The ID of the company.
     * @param int $entity_id The ID of the entity.
     * @return array A layout array.
     */
    private function generateDefaultLayout(int $company_id, int $entity_id): array
    {
        // Get all fields for the entity to build the default layout
        $fieldSql = "
            SELECT id, field_name, field_type, slug, is_required
            FROM custom_fields
            WHERE company_id = :company_id AND entity_id = :entity_id
            ORDER BY id ASC
        ";

        $fieldStmt = $this->db->prepare($fieldSql);
        $fieldStmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $fieldStmt->bindParam(':entity_id', $entity_id, PDO::PARAM_INT);
        $fieldStmt->execute();
        $fields = $fieldStmt->fetchAll(PDO::FETCH_ASSOC);

        $defaultLayout = [];
        $rowIndex = 0;
        foreach ($fields as $field) {
            $defaultLayout[] = [
                'custom_field_id' => $field['id'],
                'group_name' => 'Details', // Default group name
                'row_order' => $rowIndex++,
                'col_order' => 0,
                'col_span' => 12, // Default to full width
                'field_name' => $field['field_name'],
                'field_type' => $field['field_type'],
                'slug' => $field['slug'],
                'is_required' => $field['is_required'],
            ];
        }

        return $defaultLayout;
    }
}
