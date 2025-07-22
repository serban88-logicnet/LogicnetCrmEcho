<?php

use App\Database\Database;
use App\Models\EntityModel;

function current_company_id(): int {
    return $_SESSION['user']['company_id'] ?? 1;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function entity_model(): \App\Models\EntityModel {
    static $instance = null;

    if (!$instance) {
        $pdo = Database::getInstance()->getConnection();
        $instance = new EntityModel($pdo);
    }

    return $instance;
}

function label_for(int $record_id): string {
    return entity_model()->getLabelForRecord($record_id, current_company_id());
}


function redirect($url) {
    header("Location: $url");
    exit;
}

function require_auth() {
    if (empty($_SESSION['user'])) {
        set_flash('error', __('auth_required'));
        redirect('index.php?route=login');
    }
}

