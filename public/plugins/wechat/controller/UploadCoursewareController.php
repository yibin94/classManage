<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    /* 上传文件处理 */
    function index(){
		 $fileName = $_FILES['file']['name'];
         $saveUrl = UPLOADFILE_SAVE_PATH;
         if($saveUrl && !file_exists($saveUrl)){
            mkdir($saveUrl,0777,true);
         }
		 if($fileName){
			 move_uploaded_file($_FILES["file"]["tmp_name"],
      $saveUrl.'/'.$fileName);
	         $this->error($_FILES["file"]["error"]);
		 }else{
			 $this->error("fail to upload file!");
		 }
	}

    /* 上传文件页面 */
	public function upload(){
		return $this->fetch("/uploadCourseware/upload");
	}
	
    public function viewOrDownload(){
        $courseware = Db::name('PluginWechatCourseware')->select();
        $this->assign('courseware',$courseware);
        $this->display();
        //echo '查看或下载文件处理';
    }

    /* 查看或下载文件处理 */
    public function download($filename){
		//$filename = "resume.pdf";
		$url = UPLOADFILE_SAVE_PATH;//"/webdata/classManage/public/";
        if(!file_exists($url.$filename)){
            $this->error('文件不存在！');
            exit();
        }
		$file=fopen($url.$filename,"r");//打开文件
		//输入文件标签
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($url));
		header("Content-Disposition: attachment; filename=$filename");
		//输出文件内容
        //读取文件内容并直接输出到浏览器
		echo fread($file,filesize($url));
		fclose($file);

	}	
}	