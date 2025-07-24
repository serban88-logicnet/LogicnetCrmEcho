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

/**
 * A helper function to create a URL-friendly slug from a string.
 * It handles special characters and Romanian diacritics.
 */
function slugify($text) {
    // Basic replacements for Romanian diacritics
    $text = str_replace(
        ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
        ['a', 'a', 'i', 's', 't', 'A', 'A', 'I', 'S', 'T'],
        $text
    );
    
    // Transliterate to ASCII
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    
    // Replace non-alphanumeric characters with underscores
    $text = preg_replace('/[^a-zA-Z0-9]+/', '_', $text);
    
    // Trim underscores from the beginning and end
    $text = trim($text, '_');
    
    // Convert to lowercase
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}