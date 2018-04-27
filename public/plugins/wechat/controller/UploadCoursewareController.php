<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    function index(){
		 $fileName = $_FILES['file']['name'];
         $saveUrl = "/webdata/classManage/public/upload";
		 if($fileName){
			 move_uploaded_file($_FILES["file"]["tmp_name"],
      $saveUrl.'/'.$fileName);
	         $this->error($_FILES["file"]["error"]);
		 }else{
			 $this->error("fail to upload file!");
		 }
		 //$fileData = $_FILES['file']['tmp_name'];
		 //file_put_contents($saveUrl.'/'.$fileName, $fileData);
	}

	public function upload(){
		return $this->fetch("/uploadCourseware/upload");
	}
	
    public function download(){
		$filename = "resume.pdf";
		$url = "/webdata/classManage/public/resume.pdf";
		$file=fopen($url,"r");//打开文件
		//输入文件标签
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($url));
		header("Content-Disposition: attachment; filename=$filename");
		//输出文件内容
		echo fread($file,filesize($url));
		fclose($file);

	}	
}	