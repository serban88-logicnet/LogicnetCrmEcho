<?php

namespace App\Controllers\Traits;

use App\Models\EntityModel;

trait EntityFormProcessor
{
    /**
     * Handles the submission of the entity form (create/update).
     */
    public function handleFormSubmission(&$record_id, $entity_id, $company_id, $entity)
    {
        $entityModel = new EntityModel($this->pdo);
        $is_new_record = !$record_id;

        if ($is_new_record) {
            $stmt = $this->pdo->prepare("INSERT INTO records (company_id, entity_id) VALUES (?, ?)");
            $stmt->execute([$company_id, $entity_id]);
            $record_id = $this->pdo->lastInsertId();
        }

        foreach ($_POST['fields'] as $field_id => $value) {
            $entityModel->updateFieldValue($record_id, $field_id, $value);
        }
        
        try {
            $submitted_rels = $_POST['relationships'] ?? [];
            $entityModel->syncRelationships($record_id, $company_id, $submitted_rels);
            
            if ($entity['slug'] === 'facturi') {
                // You would call your invoice recalculation logic here
            }

            $success_message_key = $is_new_record ? 'success_create' : 'success_update';
            set_flash('success', sprintf(__($success_message_key), $entity['name']));

        } catch (\Exception $e) {
            set_flash('error', $e->getMessage());
        }

        // Handle redirection after save
        if ($is_new_record) {
            redirect("index.php?route=entity&type={$this->type}&action=view&id={$record_id}");
        } else {
            redirect("index.php?route=entity&type={$this->type}&action=form&id={$record_id}");
        }
        exit;
    }
}
