<?php

namespace Mydom\Tp6Uploads;

use \think\facade\Filesystem;

class UploadFile
{
    //上传文件类型
    public $filetype;
    //默认上传图片允许后缀
    public $imageExt = 'jpg,png,jpeg,PNG,JPG,JPEG,webp';
    //默认上传视频允许后缀
    public $videoExt = 'MP4,mp4,ogg,avi,wmv,mov';
    //图片上传默认大小
    public $imageSize = 100 * 1024;//kb
    //视频上传默认大小
    public $videoSize = 1024 * 1024 * 5;//mb
    //指定上传后缀
    public $defaultExt = '';
    //指定上传大小
    public $defaultSize = 1024;
    //业务ID
    public $business_id = 1;

    public function __construct($fileType = 'image' ,$appointExt = '',$appointSize = 0)
    {
        $this->filetype = $fileType;
        switch ($this->filetype){
            case 'image':
                if($appointExt){
                    $this->imageExt.= ',' . $appointExt;
                }
                if($appointSize > 0){
                    $this->imageSize = $appointSize;
                }
                break;
            case 'video':
                if($appointExt){
                    $this->videoExt.= ',' . $appointExt;
                }
                if($appointSize > 0){
                    $this->videoSize = $appointSize;
                }
                break;
            default:
                if(!$appointExt){
                    throw new \Exception('请指定文件后缀');
                }
                $this->defaultExt = $appointExt;
                if($appointSize > 0){
                    $this->defaultSize = $appointSize;
                }
        }
    }

    public function normalUpload($filename,$disk = 'uploads',$path = 'default')
    {
        $files = request()->file($filename);

        if (!is_array($files)) {
            $files = [$files];
            $isOne = true;
        }
        try {
            foreach ($files as $index => $file){
                switch ($this->filetype){
                    case 'image':
                        validate([$filename => "fileSize:{$this->imageSize}|fileExt:{$this->imageExt}"])->check([$filename => $file]);
                        break;
                    case 'video':
                        validate([$filename => "fileSize:{$this->videoSize}|fileExt:{$this->videoExt}"])->check([$filename => $file]);
                        break;
                    default:
                        validate([$filename => "fileSize:{$this->defaultSize}|fileExt:{$this->defaultExt}"])->check([$filename => $file]);
                }
            }
        } catch (\think\exception\ValidateException $e) {
            $key = $index + 1;
            throw new \Exception("第{$key}个".$e->getMessage(),10500);
        }

        try{
            $savename = [];
            foreach($files as $file) {
                $filePath = Filesystem::disk($disk)->putFile( $path, $file);
                $savename[] = str_replace('\\','/',$filePath);
            }

            if(isset($isOne)){
                $savename = $savename[0];
            }
            return $savename;
        }catch (\think\exception\ValidateException $e){
            throw new \Exception($e->getMessage(),10500);
        }
    }
}