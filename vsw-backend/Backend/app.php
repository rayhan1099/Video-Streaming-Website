<?php
/*
 * Application entry point
 */
require_once __DIR__ . '/config.php';
global $config;

$app = new App($config);
$current_uri = $_SERVER['REQUEST_URI'];
$route = substr($current_uri, 8);
$parsed = parse_url($route);
$route = $parsed['path'];
if (array_key_exists('query', $parsed)){
    parse_str($parsed['query'], $qs);
}

$post_data = file_get_contents("php://input");
$auth = new Auth($config['signer_key'], $app);
$data = json_decode($post_data);
$auth_data = null;
$isAuthorized = false;
$isAdmin = false;

/*
 * To satisfy preflight request
 */
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    $app->response::response(200, []);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    //Try to check user authorization
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? false;
    if ($auth_header){
        $jwtToken = substr($auth_header, 7);

        if ($auth_data = $auth->validate($jwtToken)){
            $isAdmin = $auth_data['id_token']['is_admin'] === "Y";
            $isAuthorized = true;
            $app->currentUser = $auth_data;
        }
    }
}
switch ($route) {
    case '/login':
        $auth->login($data->email, $data->password);
        break;
    case '/signup':
        $auth->signup($data->email, $data->password, $data->c_password, $data->birth_date);
        break;
    case '/upload':
        if (!$isAuthorized){
            $app->response::send401();
            exit();
        }
        $app->files->upload($_FILES, $_POST);
        //upload operation
        break;
    case '/profile':
        if (!$isAuthorized){
            $app->response::send401();
            exit();
        }
        $app->user->user_profile();
        break;
    case '/my-videos':
        if (!$isAuthorized){
            $app->response::send401();
            exit();
        }
        $app->files->video_list($auth_data['id_token']['id']);
        break;
    case '/home':
        $app->files->video_list();
        break;
    case '/catlist':
        $list = $app->database->select('categories', ['*'], []);
        $app->response::response(200, $list);
        break;

    case '/playback':
        if (!$isAdmin && $isAuthorized){
            $app->files->playback($data->videoID, $auth_data);
        }elseif ($isAdmin){
            $app->files->playback($data->videoID, false, true);
        }else{
            $app->files->playback($data->videoID);
        }

        break;
    case '/user-list':
        if (!$isAuthorized || !$isAdmin){
            $app->response::send401();
            exit();
        }
        $list = $app->database->select('users', ['id', 'email', 'created_at'], []);
        $app->response::response(200,$list);
        break;
    case '/video-list':
        if (!$isAuthorized || !$isAdmin){
            $app->response::send401();
            exit();
        }
        $app->files->video_list(null, true);
        break;
    case '/approval':
        if (!$isAuthorized || !$isAdmin){
            $app->response::send401();
            exit();
        }
        $app->files->video_list(null, true, true);
        break;
    case '/approve-video':
        if (!$isAuthorized || !$isAdmin){
            $app->response::send401();
            exit();
        }
        $app->database->update('videos', ['permission_level'=>'A',], ['id'=>$data->videoID]);
        $app->response::response(200, ['message'=>'Approved']);
        break;
    case '/delete-video':
        if (!$isAuthorized || !$isAdmin){
            $app->response::send401();
            exit();
        }
        $app->database->delete('video_processing_queue', ['video_id'=>$data->videoID]);
        $app->database->delete('videos', ['id'=>$data->videoID]);
        $app->response::response(200, ['message'=>'Deleted!']);
        break;
    default:
        $app->response::response(404, ['message'=>"I'm clueless. Sorry :("]);
        break;
}