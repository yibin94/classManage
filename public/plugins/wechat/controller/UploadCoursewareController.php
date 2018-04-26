<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    function index(){var_dump($_REQUEST);var_dump($_FILES);die;
		 // 获取表单上传文件 例如上传了001.jpg    
		 $file = request()->file('file');    
		 // 移动到框架应用根目录/public/upload/ 目录下    
		 $info = $file->move(ROOT_PATH . 'public/upload');
		 if($info){
			 // 成功上传后 获取上传信息
			 // 输出 jpg       
			 echo $info->getExtension();
			 // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
			 echo $info->getSaveName();
			 // 输出 42a79759f284b767dfcb2a0197904287.jpg
			 echo $info->getFilename(); 
		 }else{
			 // 上传失败获取错误信息
			 echo $file->getError();
		 }
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