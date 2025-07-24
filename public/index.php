<?php
session_start();

// ✅ Load global config
require_once __DIR__ . '/../config/config.php';

// ✅ Load composer autoloader (for dependencies)
require_once __DIR__ . '/../vendor/autoload.php';

// ✅ Load custom helper functions
require_once __DIR__ . '/../app/Helpers/flash.php';   // Flash messaging
require_once __DIR__ . '/../app/Helpers/debug.php';   // dd(), dump()
require_once __DIR__ . '/../app/Helpers/system.php';  // current_user(), label_for(), etc.

// ✅ Load translation system
$lang = require_once __DIR__ . '/../lang/ro.php';
$GLOBALS['lang'] = $lang;
require_once __DIR__ . '/../app/Helpers/lang.php'; // __() function

// ✅ AUTO-LOGIN via "Remember Me" cookie
if (empty($_SESSION['user']) && !empty($_COOKIE['remember_token'])) {
    list($userId, $hash) = explode(':', $_COOKIE['remember_token']);

    // Get user from DB
    $pdo = \App\Database\Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Validate hash matches user's password hash
    if ($user && hash('sha256', $user['password']) === $hash) {
        $_SESSION['user'] = [
            'id'         => $user['id'],
            'email'      => $user['email'],
            'role_id'    => $user['role_id'],
            'company_id' => $user['company_id']
        ];
    }
}

// ✅ ROUTING
$route = $_GET['route'] ?? 'login';



switch ($route) {
    case 'login':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->login();
        break;

    // In index.php, add this to your switch statement:
    case 'register':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->register();
        break;

    case 'logout':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->logout();
        break;

    case 'entity':
        require_once __DIR__ . '/../app/Controllers/EntityController.php';
        $entity = new EntityController();
        $action = $_GET['action'] ?? 'list';
        $entity->$action(); // list(), form(), view(), delete()
        break;

    case 'entity-layout':
        require_once __DIR__ . '/../app/Controllers/EntityLayoutController.php';
        $c = new EntityLayoutController();
        $action = $_GET['action'] ?? 'editor';
        // Check if the action method exists in the controller
        if (method_exists($c, $action)) {
            $c->$action();
        } else {
            // Handle error for unknown action
            echo "Unknown action.";
        }
        break;

    case 'field':
        require_once __DIR__ . '/../app/Controllers/FieldController.php';
        $field = new FieldController();
        $action = $_GET['action'] ?? 'list';
        $field->$action();
        break;

    case 'entitydef':
        require_once __DIR__ . '/../app/Controllers/EntityDefinitionController.php';
        $c = new EntityDefinitionController();
        $action = $_GET['action'] ?? 'list';
        $c->$action();
        break;




    default:
        echo "Page not found.";
        break;
}
