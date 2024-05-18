<?php
/*
 * User class
 */

class User{
    private App $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function user_profile(){
        $video_count = $this->app->database->get('videos', ['count(id) as videoCount'], ['user_id'=>$this->app->currentUser['id_token']['id']]);
        $profile_data = $this->app->currentUser['id_token'];
        unset($profile_data['is_admin']);
        $message = [
            'videoCount'=>$video_count['videoCount'],
            'profile'=>$profile_data,
        ];
        $this->app->response::response(200, $message);
    }
}