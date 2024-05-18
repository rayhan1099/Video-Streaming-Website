<?php

class Request
{
    public function __construct(
        private $gets = null,
        private $posts = null,
        private $request = null
    )
    {
        $this->request = $_REQUEST;
        $this->gets = $_GET;
        $this->posts = $_POST;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->gets)) {
            return $this->gets[$key];
        }
        return null;
    }

    public function post($key)
    {
        if (array_key_exists($key, $this->posts)) {
            return $this->posts[$key];
        }
        return null;
    }

    public function request($key)
    {
        if (
            array_key_exists($key, $this->gets)
        ) {
            return $this->gets[$key];
        }
        if (
            array_key_exists($key, $this->posts)
        ) {
            return $this->posts[$key];
        }
        if (
            array_key_exists($key, $this->request)
        ) {
            return $this->request[$key];
        }
        return null;
    }
}