<?php

class Response
{
    static function send401($message = null)
    {
        self::response(401, ['message' => $message ?? 'Unauthorized']);
    }

    static function response($code, $data)
    {
        header(http_response_code($code), true, $code);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Content-Type: application/json");
        echo json_encode($data);
    }
}