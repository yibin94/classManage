<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    function index(){var_dump($_REQUEST);die;
		 $fileName = $_REQUEST['file']['name'];echo $fileName;
		 $fileData = $_REQUEST['file']['tmp_name'];echo $fileData;die;
         $saveUrl = "/webdata/classManage/public/upload";
		 file_put_contents($saveUrl.'/'.$fileName, $fileData);
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