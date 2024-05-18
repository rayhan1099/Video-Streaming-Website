<?php

class Transcoder
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;

    }

    function execute(){
    }
    function resize_image($source_path, $dest){
        $cmd = "ffmpeg -i '$source_path' -s 500:300 '$dest'";
        shell_exec($cmd);
    }
    function process_video($source, $dest, $res_list = ['360']){
        $cmd = "ffmpeg -i '$source' -movflags +faststart -pix_fmt yuv420p -vf 'scale=1280:-1' -vsync 1 -threads 0 -vcodec libx264 -r 29.970 -g 60 -sc_threshold 0 -b:v 1024k -bufsize 1216k -maxrate 1280k -preset ultrafast -profile:v main -tune film -acodec aac -b:a 128k -ac 2 -ar 48000 -af 'aresample=async=1:min_hard_comp=0.100000:first_pts=0' -f mp4 -y '$dest'";
        shell_exec($cmd);
    }
    function duration($source){
        $cmd = "ffprobe '$source' -show_format 2>&1|sed -n 's/duration=//p'";
        return shell_exec($cmd);
    }
}