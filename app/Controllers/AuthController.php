<?php

use App\Database\Database;

class AuthController {
    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // login() method remains unchanged...
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id'         => $user['id'],
                    'email'      => $user['email'],
                    'role_id'    => $user['role_id'],
                    'company_id' => $user['company_id']
                ];

                if (!empty($_POST['remember'])) {
                    setcookie('remember_token', $user['id'] . ':' . hash('sha256', $user['password']), time() + (86400 * 30), '/');
                }

                redirect('index.php?route=entitydef&action=list');
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

    /**
     * Handles registration of a NEW company and its first admin user.
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->pdo->beginTransaction(); // Start a database transaction

            try {
                $email = trim($_POST['email'] ?? '');
                $cui = trim($_POST['cui'] ?? '');

                // 1. Validate form data
                if (empty($email) || empty($cui) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception(__('error_invalid_data'));
                }

                // 2. Check if a company with this CUI already exists
                $stmt = $this->pdo->prepare("SELECT id FROM companies WHERE cui = :cui");
                $stmt->execute(['cui' => $cui]);
                if ($stmt->fetch()) {
                    throw new \Exception(__('registration_error_cui_exists'));
                }

                // 3. Check if a user with this email already exists
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    throw new \Exception(__('registration_error_email_exists'));
                }

                // 4. Mock API call to get company name from CUI
                // In a real app, you would replace this with a cURL request.
                $companyName = "Compania " . preg_replace("/[^0-9]/", "", $cui); // e.g., "Compania 12345678"

                // 5. Create the new company
                $stmt = $this->pdo->prepare("INSERT INTO companies (name, cui) VALUES (:name, :cui)");
                $stmt->execute(['name' => $companyName, 'cui' => $cui]);
                $companyId = $this->pdo->lastInsertId();

                // 6. Create the default 'Admin' role for this new company
                $stmt = $this->pdo->prepare("INSERT INTO roles (company_id, name) VALUES (:company_id, 'Admin')");
                $stmt->execute(['company_id' => $companyId]);
                $roleId = $this->pdo->lastInsertId();

                // 7. Generate a secure password and create the admin user
                $password = bin2hex(random_bytes(8)); // Creates a 16-character hexadecimal password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $this->pdo->prepare("INSERT INTO users (company_id, role_id, email, password) VALUES (:company_id, :role_id, :email, :password)");
                $stmt->execute([
                    'company_id' => $companyId,
                    'role_id'    => $roleId,
                    'email'      => $email,
                    'password'   => $hashedPassword
                ]);

                // If all database queries were successful, commit the transaction
                $this->pdo->commit();

                // 8. Send the generated password to the new user's email
                $subject = __('password_email_subject');
                $body = sprintf(__('password_email_body'), $password);
                $headers = 'From: no-reply@yourcrm.com' . "\r\n";
                @mail($email, $subject, $body, $headers); // Use @ to suppress errors if mail() isn't configured

                // 9. Redirect to login page with a success message
                set_flash('success', __('registration_success_new_company'));
                redirect('index.php?route=login');
                exit;

            } catch (\Exception $e) {
                // If any step failed, roll back all database changes
                $this->pdo->rollBack();
                set_flash('error', $e->getMessage());
                redirect('index.php?route=register');
                exit;
            }
        }

        // --- Render the registration form view ---
        $pageTitle = __('register_title');
        $backgroundImageUrl = 'https://images.pexels.com/photos/6627539/pexels-photo-6627539.jpeg';
        $formView = __DIR__ . '/../Views/auth/register.php';

        require_once __DIR__ . '/../Views/layouts/auth.php';
    }

    // logout() and logFailedLogin() methods remain unchanged...
    public function logout() {
        session_destroy();
        redirect('index.php?route=login');
        exit;
    }

    private function logFailedLogin($email) {
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)");
        $stmt->execute(['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
    }
}
