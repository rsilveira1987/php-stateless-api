<?php

    require 'vendor/autoload.php';

    // Configuracoes de DEBUG
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // CONSTANTES
    define('WWWROOT',__DIR__);
    define('DATABASE_PATH', WWWROOT . '/database');
    define('VIEWS_PATH', WWWROOT . '/views');
    define('JWT_SECRET_KEY','IdMd');

    // Timezone
    date_default_timezone_set('America/Sao_Paulo');

    //-------------------------------------------------------------------------
    // Iniciar sessao
    //-------------------------------------------------------------------------
    // session_save_path( WWWROOT . '/php_session');  // define this path to show the 'session issue'
    
    new \App\Utils\Session;

    function guidv4() {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    