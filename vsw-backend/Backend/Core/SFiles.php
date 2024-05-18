<?php

class SFiles
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /*
     * $user = null -> homepage
     * $user = int -> my videos
     * $user = int+admin -> all videos on site
     */
    function video_list($user = null, $is_admin = false, $unapproved = false)
    {
        if ($is_admin) {
            if ($unapproved == true){
                $list = $this->app->database->select('videos', ['video_title as title', 'id as videoID', 'video_thumbnail as thumb', 'duration', 'created_at as uploadTime', 'permission_level as approvalState'], ['permission_level'=>'H']);
            }else{
                $list = $this->app->database->select('videos', ['video_title as title', 'id as videoID', 'video_thumbnail as thumb', 'duration', 'created_at as uploadTime', 'permission_level as approvalState'], []);
            }

        } else if ($user == null) {
            $list = $this->app->database->select('videos', ['video_title as title', 'id as videoID', 'video_thumbnail as thumb', 'duration', 'created_at as uploadTime', 'permission_level as approvalState'], ['permission_level' => 'A']);
        } else {
            $list = $this->app->database->select('videos', ['video_title as title', 'id as videoID', 'video_thumbnail as thumb', 'duration', 'created_at as uploadTime', 'permission_level as approvalState'], ['user_id' => $user]);
        }
        $this->app->response::response(200, $list);
    }
    function playback($id, $user = false, $is_admin = false){
        $list = $this->app->database->get('videos', ['video_title as title', 'id as videoID', 'video_thumbnail as thumb', 'duration', 'created_at as uploadTime', 'permission_level as approvalState', 'video_location', 'user_id', 'video_description as description'], ['id'=>$id]);
        if (empty($list)){
            $this->app->response::response(404, ['message' => 'The video not found or inaccessible']);
            return;
        }
        $src = ['src'=>'http://127.0.0.1:9001/'.$list['video_location'], 'type'=>'video/mp4'];
        $list['playbackUrlList'] = [$src];
        unset($list['video_location']);
        if ($user){
            if ($user['id_token']['id'] != $list['user_id'] && $list['approvalState'] == 'H'){
                $this->app->response::response(403, ['message' => 'The video is inaccessible']);
                return;
            }
            $this->app->response::response(200, $list);
            return;
        }
        if ($is_admin){
            $this->app->response::response(200, $list);
        }
    }
    function upload($files, $post)
    {
        $error = [];
        if (count($files) < 2) {
            $error[] = "Please select both files";
        }
        if (empty($post['title']) || strlen($post['title']) < 10) {
            $error[] = 'Please enter a valid video title';
        }
        if (empty($post['desc']) || strlen($post['desc']) < 30) {
            $error[] = 'Please enter a valid video description';
        }
        if (empty($post['cat'])) {
            $error[] = 'Please select a category';
        }

        if (count($error) > 0) {
            $this->app->response::response(400, ['message' => $error]);
            return;
        }

        $folderPath = $this->app->upload_path;
        $thumb = $_FILES['thumb']['tmp_name'];
        $video = $_FILES['video']['tmp_name'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $v_mime = finfo_file($finfo, $video);
        $t_mime = finfo_file($finfo, $thumb);
        $error = [];
        if (!stristr($v_mime, 'video/')) {
            $error[] = 'Need a video file in the video field';
        }
        if (!stristr($t_mime, 'image/')) {
            $error[] = 'Need an image file in thumbnail field';
        }
        if (count($error) > 0) {
            $this->app->response::response(400, ['message' => $error]);
            return;
        }

        //storing info
        $uniqid = uniqid("vplay");
        $thumb_loc = $folderPath . "/raw/" . $uniqid . '.thumb.jpg';
        $thumb_loc_final = "uploads/thumb/" . $uniqid . '.thumb.webp';

        $video_loc = $folderPath . "/raw/" . $uniqid . '.video.mkv';
        $serve_template = "uploads/processed/" . $uniqid . "_720p.mp4";

        move_uploaded_file($thumb, $thumb_loc);
        move_uploaded_file($video, $video_loc);
        $vduration = $this->app->transcoder->duration($video_loc);

        $arr = [
            'video_title' => $post['title'],
            'video_description' => $post['desc'],
            'video_thumbnail' => $thumb_loc_final,
            'video_location' => $serve_template,
            'user_id' => intval($this->app->currentUser['id_token']['id']),
            'category_id' => intval($post['cat']),
            'duration'=>$vduration,
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ];
        if ($vid = $this->app->database->insert('videos', $arr)) {
            $r = $this->app->database->insert('video_processing_queue', [
                'source_path' => $video_loc,
                'video_id' => $vid,
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'finished_at' => '0000-00-00 00:00:00',
            ]);

            /*
             * execute transcoder immediately
             */
            $this->app->response::response(200, ['message' => 'Video uploaded successfully! Waiting for approval']);
            $this->app->transcoder->resize_image($thumb_loc, $thumb_loc_final);
            unlink($thumb_loc);
            $this->app->transcoder->process_video($video_loc, $serve_template);
            unlink($video_loc);
            $this->app->database->update('video_processing_queue', ['finished_at' => (new DateTime())->format('Y-m-d H:i:s'),], ['id' => $r]);
            return;
        }
        $this->app->response::response(400, ['message' => 'An error occurred! Upload failed']);
        return;
    }

}