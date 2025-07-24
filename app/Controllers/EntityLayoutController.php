<?php

// No namespace declaration at the top

// Use statements to import the models we need
use App\Models\EntityLayoutModel;
use App\Models\EntityModel; // Correct model to use
use App\Database\Database; // Import the Database class

/**
 * Handles the logic for the drag-and-drop entity layout editor.
 */
class EntityLayoutController
{
    /**
     * Displays the visual layout editor for a specific entity.
     */
    public function editor()
    {
        // Use your global helper functions for authentication and getting company ID
        require_auth();
        $company_id = current_company_id();

        // Get the entity ID from the URL query string
        if (!isset($_GET['entity_id']) || !is_numeric($_GET['entity_id'])) {
            set_flash('error', 'Entity ID is missing or invalid.');
            redirect('index.php?route=entitydef&action=list');
            exit;
        }
        $entity_id = (int)$_GET['entity_id'];

        // Get the database connection instance first
        $pdo = Database::getInstance()->getConnection();

        // --- FIX IS HERE ---
        // Instantiate the correct models
        $layoutModel = new EntityLayoutModel(); // This one is self-contained, so it's fine.
        $entityModel = new EntityModel($pdo);   // Use your main EntityModel and pass the connection.

        // Fetch the entity's details using the correct model and method
        $entity = $entityModel->getEntityById($entity_id, $company_id);
        if (!$entity) { // Simplified check
            set_flash('error', 'Entity not found or you do not have permission to edit it.');
            redirect('index.php?route=entitydef&action=list');
            exit;
        }

        // Fetch the current layout
        $layout = $layoutModel->getLayout($company_id, $entity_id);

        // Fetch ALL field definitions for this entity using the correct method from EntityModel
        // We pass 0 for record_id to get just the definitions.
        $allFields = $entityModel->getFieldDefinitionsWithValues($entity_id, 0, $company_id);

        // Determine which fields are available to be added to the layout
        $layoutFieldIds = array_column($layout, 'custom_field_id');
        $availableFields = array_filter($allFields, function ($field) use ($layoutFieldIds) {
            // Use 'field_id' which is the alias in your getFieldDefinitionsWithValues method
            return !in_array($field['field_id'], $layoutFieldIds);
        });

        // Render the view using the same pattern as your other controllers
        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/layout/editor.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Handles the AJAX request to save the new layout configuration.
     */
    public function save()
    {
        // Ensure this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            return;
        }
        
        // Use your global helpers
        require_auth();
        $company_id = current_company_id();

        // Get the raw POST data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Basic validation of the received data
        if (empty($data['entity_id']) || !isset($data['layout'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Missing entity_id or layout data.']);
            return;
        }

        $entity_id = (int)$data['entity_id'];
        $layoutData = $data['layout'];

        // Instantiate the model and save the layout
        $layoutModel = new EntityLayoutModel();
        $success = $layoutModel->saveLayout($company_id, $entity_id, $layoutData);

        // Send a JSON response back to the frontend
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Layout saved successfully!']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['status' => 'error', 'message' => 'Failed to save layout.']);
        }
        // Important to exit after an API response
        exit();
    }

    /**
     * Handles AJAX request to create a new custom field.
     */
    public function createField()
    {
        $this->ensureAjaxPost();
        $company_id = current_company_id();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['entity_id']) || empty($data['field_name']) || empty($data['field_type'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Missing required field data.'], 400);
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $data['field_name'])));

        try {
            $pdo = \App\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO custom_fields (company_id, entity_id, field_name, field_type, slug, is_required, is_primary_label, show_on_list) 
                 VALUES (:company_id, :entity_id, :field_name, :field_type, :slug, :is_required, :is_primary_label, :show_on_list)"
            );
            $stmt->execute([
                ':company_id' => $company_id,
                ':entity_id' => (int)$data['entity_id'],
                ':field_name' => trim($data['field_name']),
                ':field_type' => $data['field_type'],
                ':slug' => $slug,
                ':is_required' => !empty($data['is_required']) ? 1 : 0,
                ':is_primary_label' => !empty($data['is_primary_label']) ? 1 : 0,
                ':show_on_list' => !empty($data['show_on_list']) ? 1 : 0 // ✅ ADDED
            ]);
            $newFieldId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT id as field_id, field_name, field_type, is_required, is_primary_label, show_on_list FROM custom_fields WHERE id = ?");
            $stmt->execute([$newFieldId]);
            $newField = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->jsonResponse(['status' => 'success', 'field' => $newField]);

        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handles AJAX request to update an existing custom field.
     */
    public function updateField()
    {
        $this->ensureAjaxPost();
        $company_id = current_company_id();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['field_id']) || empty($data['field_name']) || empty($data['field_type'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Missing required field data.'], 400);
        }

        try {
            $pdo = \App\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "UPDATE custom_fields SET field_name = :field_name, field_type = :field_type, 
                 is_required = :is_required, is_primary_label = :is_primary_label, show_on_list = :show_on_list
                 WHERE id = :field_id AND company_id = :company_id"
            );
            $stmt->execute([
                ':field_name' => trim($data['field_name']),
                ':field_type' => $data['field_type'],
                ':is_required' => !empty($data['is_required']) ? 1 : 0,
                ':is_primary_label' => !empty($data['is_primary_label']) ? 1 : 0,
                ':show_on_list' => !empty($data['show_on_list']) ? 1 : 0, // ✅ ADDED
                ':field_id' => (int)$data['field_id'],
                ':company_id' => $company_id
            ]);

            $this->jsonResponse(['status' => 'success', 'message' => 'Field updated.']);

        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handles AJAX request to delete a custom field.
     */
    public function deleteField()
    {
        $this->ensureAjaxPost();
        $company_id = current_company_id();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['field_id'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Field ID is missing.'], 400);
        }

        try {
            $pdo = \App\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM custom_fields WHERE id = :field_id AND company_id = :company_id");
            $stmt->execute([
                ':field_id' => (int)$data['field_id'],
                ':company_id' => $company_id
            ]);

            $this->jsonResponse(['status' => 'success', 'message' => 'Field deleted.']);

        } catch (\Exception $e) {
            // Handle potential foreign key constraint errors
            if (strpos($e->getMessage(), 'constraint fails') !== false) {
                 $this->jsonResponse(['status' => 'error', 'message' => 'Cannot delete this field because it has existing data. Please remove the data first.'], 409);
            }
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to send a JSON response and exit.
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Helper to ensure the request is a POST request and the user is authenticated.
     */
    private function ensureAjaxPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method.'], 405);
        }
        require_auth();
    }
}
