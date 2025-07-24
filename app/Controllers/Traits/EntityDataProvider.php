<?php

namespace App\Controllers\Traits;

use App\Models\EntityLayoutModel;
use App\Models\EntityModel;

trait EntityDataProvider
{
    /**
     * Fetches and processes the layout and values for a record.
     * This version correctly preserves the row order from the editor.
     */
    private function getProcessedLayout($entity_id, $record_id, $company_id)
    {
        $layoutModel = new EntityLayoutModel();
        $entityModel = new EntityModel($this->pdo);

        // 1. Fetch the visual layout structure, already sorted correctly by the model
        $layout = $layoutModel->getLayout($company_id, $entity_id);

        // 2. Fetch all field values for the current record
        $fieldValues = $entityModel->getAllFieldValuesForRecord($record_id, $company_id);
        $valuesMap = array_column($fieldValues, 'value', 'custom_field_id');

        // 3. Group fields by row name while preserving the order
        $rows = [];
        foreach ($layout as $field) {
            $rowName = $field['group_name'] ?: 'Details';
            if (!isset($rows[$rowName])) {
                $rows[$rowName] = [
                    'layoutType' => $field['col_span'] ?: '1',
                    'columns' => []
                ];
            }

            $colIndex = $field['col_order'] ?: 0;
            $rowIndex = $field['row_order'] ?: 0;

            // Inject the value
            $field['value'] = $valuesMap[$field['custom_field_id']] ?? '';
            
            $rows[$rowName]['columns'][$colIndex][$rowIndex] = $field;
        }

        // 4. Sort the fields within each column by their original row_order index
        foreach ($rows as &$row) {
            foreach ($row['columns'] as &$column) {
                ksort($column);
            }
        }

        return $rows;
    }

    /**
     * Prepares all necessary data for the entity view page.
     */
    private function getViewData($entity_id, $record_id, $company_id)
    {
        $entityModel = new EntityModel($this->pdo);
        return [
            'layout_rows' => $this->getProcessedLayout($entity_id, $record_id, $company_id),
            'relationships' => $entityModel->getChildRelationships($entity_id),
            'parentRelationships' => $entityModel->getParentRelationships($entity_id)
        ];
    }

    /**
     * Prepares all necessary data for the entity form page.
     */
    private function getFormData($entity_id, $record_id, $company_id)
    {
        $entityModel = new EntityModel($this->pdo);
        // This is where you would put your complex relationship data fetching logic
        $relationship_form_data = []; 
        
        return [
            'layout_rows' => $this->getProcessedLayout($entity_id, $record_id, $company_id),
            'relationship_form_data' => $relationship_form_data
        ];
    }
}