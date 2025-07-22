<?php

function __($key, ...$args) {
    $lang = $GLOBALS['lang'] ?? [];
    $text = $lang[$key] ?? $key;

    // Use vsprintf only if arguments are provided
    return $args ? vsprintf($text, $args) : $text;
}
