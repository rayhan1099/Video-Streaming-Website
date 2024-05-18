<?php
ini_set('post_max_size', '1000M');
ini_set('upload_max_filesize', '1000M');
/*
 * Application config
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/Request.php';
require_once __DIR__ . '/Core/Response.php';
require_once __DIR__ . '/Core/Transcoder.php';
require_once __DIR__ . '/Core/SFiles.php';
require_once __DIR__ . '/Core/FileDelivery.php';
require_once __DIR__ . '/../Driver/User.php';
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/App.php';


const APPNAME = "VPlay";

$config = [
    'database' => [
        'username' => 'ananta',
        'password' => 'password',
        'dbname' => 'video_streaming'
    ],
    'signer_key' => '12345678901234567890123456789012',
    'upload_path' => __DIR__ . '/uploads',
];