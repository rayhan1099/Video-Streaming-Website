<?php

use MiladRahimi\Jwt\Exceptions\InvalidKeyException;
use MiladRahimi\Jwt\Exceptions\InvalidSignatureException;
use MiladRahimi\Jwt\Exceptions\InvalidTokenException;
use MiladRahimi\Jwt\Exceptions\JsonDecodingException;
use MiladRahimi\Jwt\Exceptions\JsonEncodingException;
use MiladRahimi\Jwt\Exceptions\SigningException;
use MiladRahimi\Jwt\Exceptions\ValidationException;
use MiladRahimi\Jwt\Generator;
use MiladRahimi\Jwt\Parser;
use MiladRahimi\Jwt\Cryptography\Algorithms\Hmac\HS256;

class Auth
{
    private HS256 $signer;
    private string $key;
    private Generator $generator;
    private Parser $parser;
    private App $app;

    public function __construct($key, $app)
    {
        $this->key = $key;
        $this->app = $app;
        try {
            $this->signer = new HS256($this->key);
            $this->generator = new Generator($this->signer);
            $this->parser = new Parser($this->signer);

        } catch (InvalidKeyException $e) {
            die("Failed to initialize the signer...");
        }

    }

    function login($email, $password)
    {
        $user = $this->app->database->get('users', ['*'], ['email' => $email]);
        if (empty($user)) {
            $this->app->response::response(401, ['message' => 'Invalid email or password']);
            return;
        }
        if (!password_verify($password, $user['password'])) {
            $this->app->response::response(401, ['message' => 'Invalid email or password']);
            return;
        }
        unset($user['password']);

        try {
            $jwt = $this->generator->generate(['g_time' => time(), 'id_token' => $user]);
            $this->app->response::response(200, ['jwtToken' => $jwt]);
        } catch (JsonEncodingException|SigningException $e) {
            $this->app->response::response(401, ['message' => 'Authorization failed']);
        }
    }

    function validate($jwt)
    {
        try {
            $claims = $this->parser->parse($jwt);
            return $claims;
        } catch (InvalidSignatureException|InvalidTokenException|JsonDecodingException|SigningException|ValidationException $e) {
            $this->app->response::response(401, ['message' => 'Unauthorized' . $e->getMessage()]);
        }
        return false;
    }

    function signup($email, $password, $c_password, $bdate)
    {
        $error = [];
        if (empty($email)) {
            $error[] = 'Email field is required';
        }

        if (empty($password)) {
            $error[] = 'Password field is required';
        }

        if (empty($c_password)) {
            $error[] = 'Confirm password field is required';
        }

        if (empty($bdate)) {
            $error[] = 'Birth date field is required';
        }
        if (count($error) > 0) {
            $this->app->response::response(401, ['message' => $error]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = 'Invalid email address';

        }
        if ((new DateTime())->diff(new DateTime($bdate))->y < 14) {
            $error[] = 'You are younger than 14 years';
        }
        if ($password !== $c_password) {
            $error[] = 'Passwords don\'t match';
        }
        if (strlen($password) < 6) {
            $error[] = 'Password too short';
        }
        if (count($error) > 0) {
            $this->app->response::response(401, ['message' => $error]);
            return;
        }

        $insert = $this->app->database->insert('users', [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
//            'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);
        if ($insert === '23000') {
            $this->app->response::response(401, ['message' => 'User with this email already exist']);
            return;
        }
        if ($insert) {
            $this->app->response::response(200, ['message' => 'Account created successfully!']);
            return;
        }

        $this->app->response::response(401, ['message' => 'Something went wrong. Account creation failed. ']);
    }
}