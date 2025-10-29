<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Load default parameters (can be overridden by set_params.php)
require_once __DIR__ . '/defaults.php';
