<?php

use App\Models\EntityDefinitionModel;
use App\Models\RelationshipModel;
use App\Database\Database;

class EntityDefinitionController {
    protected $pdo;
    protected $entityDefModel;
    protected $relationshipModel;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->entityDefModel = new EntityDefinitionModel($this->pdo);
        $this->relationshipModel = new RelationshipModel($this->pdo);
    }

    public function list() {
        require_auth();
        $company_id = current_company_id();
        $entities = $this->entityDefModel->getAllEntities($company_id);
        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entitydef/list.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    public function form() {
        require_auth();
        $company_id = current_company_id();
        $id = $_GET['id'] ?? null;

        $is_edit = $id !== null;
        $entity = $is_edit ? $this->entityDefModel->getEntityById($id, $company_id) : null;

        if ($is_edit && !$entity) {
            set_flash('error', __('entity_not_found_generic'));
            redirect("index.php?route=entitydef&action=list");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ✨ CHANGE: Automatically generate the slug from the entity name
            if (isset($_POST['name'])) {
                $_POST['slug'] = slugify($_POST['name']);
            }

            if ($is_edit) {
                $success = $this->entityDefModel->updateEntity($id, $company_id, $_POST);
                if ($success) {
                    $this->relationshipModel->saveEntityRelationships($id, $company_id, $_POST['relationships'] ?? []);
                    set_flash('success', __('entity_updated_success'));
                    redirect("index.php?route=entitydef&action=list");
                } else {
                    set_flash('error', __('entity_update_error'));
                }
            } else {
                $success = $this->entityDefModel->createEntity($company_id, $_POST);
                if ($success) {
                    $new_id = $this->pdo->lastInsertId();
                    $fieldStmt = $this->pdo->prepare("INSERT INTO custom_fields (entity_id, company_id, field_name, slug, field_type, is_required, is_primary_label) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $fieldStmt->execute([$new_id, $company_id, 'Nume', 'nume', 'text', 1, 1]);
                    
                    if (!empty($_POST['relationships'])) {
                        $this->relationshipModel->saveEntityRelationships($new_id, $company_id, $_POST['relationships']);
                    }
                    set_flash('success', __('entity_created_success'));
                    redirect("index.php?route=entitydef&action=list");
                } else {
                    set_flash('error', __('entity_create_error'));
                }
            }
        }

        $allEntities = $this->relationshipModel->getAllEntities($company_id);
        $relationships = $is_edit ? $this->relationshipModel->getRelationshipsForEntity($id, $company_id) : [];

        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/entitydef/form.php';
        require_once __DIR__ . '/../Views/partials/footer.php';
    }

    public function delete() {
        require_auth();
        $company_id = current_company_id();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            set_flash('error', __('entity_id_missing'));
            redirect("index.php?route=entitydef&action=list");
        }
        
        $entity = $this->entityDefModel->getEntityById($id, $company_id);
        if ($entity && $entity['is_system']) {
            set_flash('error', 'Entitățile de sistem (ex: Clienți, Facturi) nu pot fi șterse.');
            redirect("index.php?route=entitydef&action=list");
            return;
        }

        $success = $this->entityDefModel->deleteEntity($id, $company_id);

        if ($success) {
            set_flash('success', __('entity_deleted_success'));
        } else {
            set_flash('error', __('entity_delete_error'));
        }

        redirect("index.php?route=entitydef&action=list");
    }
}
