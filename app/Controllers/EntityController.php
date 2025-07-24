<?php

use App\Database\Database;
use App\Controllers\Traits\EntityDataProvider;
use App\Controllers\Traits\EntityFormProcessor;

// Make sure your autoloader can find the traits, or include them manually
require_once __DIR__ . '/Traits/EntityDataProvider.php';
require_once __DIR__ . '/Traits/EntityFormProcessor.php';

class EntityController {
    use EntityDataProvider, EntityFormProcessor;

    protected $pdo;
    protected $type;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->type = $_GET['type'] ?? 'clienti';
    }

    public function list() {
        require_auth();
        $company_id = current_company_id();
        $entityModel = entity_model(); // Use your helper to get the model instance

        $entity = $entityModel->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', sprintf(__('entity_not_found'), $this->type));
            redirect("index.php?route=entity&type=clienti&action=list");
        }
        
        $entity_id = $entity['id'];

        // ✅ NEW: Get only the fields that should be displayed on the list
        $list_fields = $entityModel->getFieldsForListView($entity_id, $company_id);

        // ✅ NEW: Get all records with their values efficiently
        $records = $entityModel->getRecordsWithValues($entity_id, $company_id, $list_fields);

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/list.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }


    public function view() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;
        if (!$record_id) {
            set_flash('error', __('missing_record_id'));
            redirect("index.php?route=entity&type={$this->type}&action=list");
        }

        $entity = entity_model()->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=clienti&action=list");
        }
        
        // Use the trait to get all view data
        $viewData = $this->getViewData($entity['id'], $record_id, $company_id);
        extract($viewData); // Extracts $layout_rows, $relationships, etc.

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/view.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    public function form() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;

        $entity = entity_model()->getEntityBySlug($this->type, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=clienti&action=list");
        }
        $entity_id = $entity['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Use the trait to handle the entire submission process
            $this->handleFormSubmission($record_id, $entity_id, $company_id, $entity);
        }
        
        // Use the trait to get all form data
        $formData = $this->getFormData($entity_id, $record_id, $company_id);
        extract($formData); // Extracts $layout_rows, $relationship_form_data, etc.

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entity/form.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    public function delete() {
        require_auth();
        $company_id = current_company_id();
        $record_id = $_GET['id'] ?? null;
        if (!$record_id) {
            set_flash('error', __('missing_record_id'));
            redirect("index.php?route=entity&type={$this->type}&action=list");
        }

        // ... (rest of delete logic is fine as it's short)
        $stmt = $this->pdo->prepare("UPDATE records SET is_deleted = 1, deleted_at = NOW() WHERE id = :id AND company_id = :company_id");
        $stmt->execute(['id' => $record_id, 'company_id' => $company_id]);
        
        set_flash('success', 'Record deleted successfully.');
        redirect("index.php?route=entity&type={$this->type}&action=list");
    }
}
