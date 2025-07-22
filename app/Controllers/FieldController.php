<?php

use App\Models\EntityModel;
use App\Models\FieldModel;
use App\Database\Database;

class FieldController {
    protected $pdo;
    protected $entityModel;
    protected $fieldModel;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->entityModel = new EntityModel($this->pdo);
        $this->fieldModel = new FieldModel($this->pdo);
    }

    /**
     * List all custom fields for a given entity.
     * Route: ?route=field&type=order&action=list
     */
    public function list() {
        require_auth();

        $company_id = current_company_id();
        $entity_slug = $_GET['type'] ?? null;

        if (!$entity_slug) {
            set_flash('error', __('entity_type_missing'));
            redirect("index.php?route=entity&type=client&action=list");
        }

        $entity = $this->entityModel->getEntityBySlug($entity_slug, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entity&type=client&action=list");
        }

        $fields = $this->fieldModel->getCustomFieldsByEntityId($entity['id'], $company_id);

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/field/list.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Show form and create a new field for the given entity.
     * Route: ?route=field&type=order&action=add
     */
    public function add() {
        require_auth();

        $company_id = current_company_id();
        $entity_slug = $_GET['type'] ?? null;

        $entity = $this->entityModel->getEntityBySlug($entity_slug, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=field&type=client&action=list");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->fieldModel->createField($entity['id'], $company_id, $_POST);

            if ($success) {
                set_flash('success', __('field_created_success'));
                redirect("index.php?route=field&type={$entity['slug']}&action=list");
            } else {
                set_flash('error', __('field_create_error'));
            }
        }

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/field/form.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Show form and update an existing field.
     * Route: ?route=field&type=order&action=edit&id=7
     */
    public function edit() {
        require_auth();

        $company_id = current_company_id();
        $field_id = $_GET['id'] ?? null;
        $entity_slug = $_GET['type'] ?? null;

        if (!$field_id) {
            set_flash('error', __('field_id_missing'));
            redirect("index.php?route=field&type=client&action=list");
        }

        $entity = $this->entityModel->getEntityBySlug($entity_slug, $company_id);
        if (!$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=field&type=client&action=list");
        }

        $field = $this->fieldModel->getFieldById($field_id, $company_id);
        if (!$field) {
            set_flash('error', __('field_not_found'));
            redirect("index.php?route=field&type={$entity_slug}&action=list");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->fieldModel->updateField($field_id, $company_id, $_POST);

            if ($success) {
                set_flash('success', __('field_updated_success'));
                redirect("index.php?route=field&type={$entity_slug}&action=list");
            } else {
                set_flash('error', __('field_update_error'));
            }
        }

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/field/form.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Delete a custom field.
     * Route: ?route=field&type=order&action=delete&id=7
     */
    public function delete() {
        require_auth();

        $company_id = current_company_id();
        $field_id = $_GET['id'] ?? null;
        $entity_slug = $_GET['type'] ?? null;

        if (!$field_id) {
            set_flash('error', __('field_id_missing'));
            redirect("index.php?route=field&type=client&action=list");
        }

        $success = $this->fieldModel->deleteField($field_id, $company_id);

        if ($success) {
            set_flash('success', __('field_deleted_success'));
        } else {
            set_flash('error', __('field_delete_error'));
        }

        redirect("index.php?route=field&type={$entity_slug}&action=list");
    }
}
