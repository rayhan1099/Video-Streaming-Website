<?php

class App
{
    public Database $database;
    public Request $request;
    public SFiles $files;
    public FileDelivery $fileDelivery;
    public Response $response;
    public Transcoder $transcoder;
    public $currentUser = false;
    public $upload_path;
    public string $app_name;
    public User $user;

    public function __construct($config)
    {
        try {
            $this->database = new Database(
                $config['database']['username'],
                $config['database']['password'],
                $config['database']['dbname']
            );
            $this->request = new Request();
            $this->response = new Response();
            $this->user = new User($this);
            $this->files = new SFiles($this);
            $this->fileDelivery = new FileDelivery($this);
            $this->transcoder = new  Transcoder($this);
            $this->app_name = APPNAME;
            $this->upload_path = $config['upload_path'];
        }catch (Exception $e){
            die("Application database initialization error. Error message: <b>".$e->getMessage()."</b>");
        }
    }



}