<?php

function dd($var) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
    die();
}

function dump($var) {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}
