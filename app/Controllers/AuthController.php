<?php

use App\Database\Database;

class AuthController {
    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ... login, register, logout, logFailedLogin methods are unchanged ...
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = ['id' => $user['id'], 'email' => $user['email'], 'role_id' => $user['role_id'], 'company_id' => $user['company_id']];
                if (!empty($_POST['remember'])) {
                    setcookie('remember_token', $user['id'] . ':' . hash('sha256', $user['password']), time() + (86400 * 30), '/');
                }
                redirect('index.php?route=entity&type=clienti&action=list');
                exit;
            } else {
                $this->logFailedLogin($email);
                set_flash('error', __('login_invalid_credentials'));
            }
        }
        $pageTitle = __('login_title');
        $backgroundImageUrl = 'https://images.pexels.com/photos/6627539/pexels-photo-6627539.jpeg';
        $formView = __DIR__ . '/../Views/auth/login.php';
        require_once __DIR__ . '/../Views/layouts/auth.php';
    }
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->pdo->beginTransaction();
            try {
                $email = trim($_POST['email'] ?? '');
                $cui = trim($_POST['cui'] ?? '');
                if (empty($email) || empty($cui) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception(__('error_invalid_data'));
                }
                $stmt = $this->pdo->prepare("SELECT id FROM companies WHERE cui = :cui");
                $stmt->execute(['cui' => $cui]);
                if ($stmt->fetch()) {
                    throw new \Exception(__('registration_error_cui_exists'));
                }
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    throw new \Exception(__('registration_error_email_exists'));
                }
                $companyName = "Compania " . preg_replace("/[^0-9]/", "", $cui); 
                $stmt = $this->pdo->prepare("INSERT INTO companies (name, cui) VALUES (:name, :cui)");
                $stmt->execute(['name' => $companyName, 'cui' => $cui]);
                $companyId = $this->pdo->lastInsertId();
                $stmt = $this->pdo->prepare("INSERT INTO roles (company_id, name) VALUES (:company_id, 'Admin')");
                $stmt->execute(['company_id' => $companyId]);
                $roleId = $this->pdo->lastInsertId();
                $password = bin2hex(random_bytes(8));
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("INSERT INTO users (company_id, role_id, email, password) VALUES (:company_id, :role_id, :email, :password)");
                $stmt->execute(['company_id' => $companyId, 'role_id'    => $roleId, 'email'      => $email, 'password'   => $hashedPassword]);
                $this->createDefaultEntitiesForCompany($companyId);
                $this->pdo->commit();
                $subject = __('password_email_subject');
                $body = sprintf(__('password_email_body'), $password);
                $headers = 'From: no-reply@yourcrm.com' . "\r\n";
                @mail($email, $subject, $body, $headers);
                set_flash('success', __('registration_success_new_company'));
                redirect('index.php?route=login');
                exit;
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                set_flash('error', $e->getMessage());
                redirect('index.php?route=register');
                exit;
            }
        }
        $pageTitle = __('register_title');
        $backgroundImageUrl = 'https://images.pexels.com/photos/6627539/pexels-photo-6627539.jpeg';
        $formView = __DIR__ . '/../Views/auth/register.php';
        require_once __DIR__ . '/../Views/layouts/auth.php';
    }
    public function logout() {
        session_destroy();
        redirect('index.php?route=login');
        exit;
    }
    private function logFailedLogin($email) {
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)");
        $stmt->execute(['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
    }

    private function createDefaultEntitiesForCompany($companyId) {
        $entityStmt = $this->pdo->prepare("INSERT INTO entities (company_id, name, slug, description, is_system) VALUES (?, ?, ?, ?, ?)");
        $fieldStmt = $this->pdo->prepare("INSERT INTO custom_fields (entity_id, company_id, field_name, slug, field_type, is_required, is_primary_label, is_system) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $relStmt = $this->pdo->prepare("INSERT INTO relationship_entities (company_id, entity_one_id, entity_two_id, entity_one_label, entity_two_label, relationship_type) VALUES (?, ?, ?, ?, ?, ?)");
        $relFieldStmt = $this->pdo->prepare("INSERT INTO relationship_fields (relationship_id, meta_key, field_label, field_type, is_required, company_id) VALUES (?, ?, ?, ?, ?, ?)");

        $entityStmt->execute([$companyId, 'Clienți', 'clienti', 'Lista de clienți ai companiei', 1]);
        $clientId = $this->pdo->lastInsertId();
        $clientFields = [['Nume Client', 'nume_client', 'text', 1, 1, 1], ['CUI', 'cui', 'text', 1, 0, 1], ['Email', 'email', 'text', 0, 0, 1]];
        foreach ($clientFields as $field) {
            $fieldStmt->execute(array_merge([$clientId, $companyId], $field));
        }

        $entityStmt->execute([$companyId, 'Facturi', 'facturi', 'Lista de facturi emise', 1]);
        $facturiId = $this->pdo->lastInsertId();
        $facturiFields = [
            ['Număr Factură', 'numar_factura', 'text', 1, 1, 1],
            ['Data Emiterii', 'data_emiterii', 'date', 1, 0, 1],
            // ✨ CHANGE: Added "Valoare Totala" back as a system field
            ['Valoare Totală', 'valoare_totala', 'number', 1, 0, 1]
        ];
        foreach ($facturiFields as $field) {
            $fieldStmt->execute(array_merge([$facturiId, $companyId], $field));
        }

        $entityStmt->execute([$companyId, 'Produse', 'produse', 'Catalogul de produse sau servicii', 1]);
        $produseId = $this->pdo->lastInsertId();
        $produseFields = [['Nume Produs', 'nume_produs', 'text', 1, 1, 1], ['Preț Unitar', 'pret_unitar', 'number', 1, 0, 1]];
        foreach ($produseFields as $field) {
            $fieldStmt->execute(array_merge([$produseId, $companyId], $field));
        }

        $relStmt->execute([$companyId, $clientId, $facturiId, 'Client', 'Facturi', 'one_many']);
        $relStmt->execute([$companyId, $facturiId, $produseId, 'Factură', 'Produse', 'many_many']);
        $facturaProdusRelId = $this->pdo->lastInsertId();

        // ✨ CHANGE: Add "Cantitate" as a required field on the Factura-Produs relationship
        $relFieldStmt->execute([$facturaProdusRelId, 'cantitate', 'Cantitate', 'number', 1, $companyId]);
    }
}
