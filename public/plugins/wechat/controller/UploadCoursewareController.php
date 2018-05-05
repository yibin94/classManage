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
             $fileInfo = [
                 'name' => $fileName,
                 'url' => $saveUrl,
                 'views' => 0,
                 'downloads' => 0,
             ];
             $coursewareId = Db::name('pluginWechatCourseware')->insertGetId($fileInfo);
             $userId = Db::name('pluginWechatUser')->where('openid',session('openid'))->value('id');
             Db::name('pluginWechatUserCourseware')->insert(['userId'=>$userId,'coursewareId'=>$coursewareId]);
		 }else{
            $this->error($_FILES["file"]["error"]);
			 //$this->error("fail to upload file!");
		 }
	}

    /* 上传文件页面 */
	public function upload(){
        if(empty(session('openid'))){
            if(!isset($weObj)){
                $loginObj = new LoginValidationController();
                $weObj = $loginObj->getWeObj();
            }
            $loginObj->authLogin();//授权验证登录获取code
            //通过code换取网页授权access_token
            $res = $weObj->getOauthAccessToken();
            if($res){
                //刷新access_token（如果需要）
                $refreshRes = $weObj->getOauthRefreshToken($res['refresh_token']);
                //拉取用户信息(需scope为 snsapi_userinfo)
                $userInfo = $weObj->getOauthUserinfo($refreshRes['access_token'],$refreshRes['openid']);
                $openid = $userInfo['openid'];
                session('openid',$openid);
            }
        }else{
            $openid = session('openid');
        }
		return $this->fetch("/uploadCourseware/upload");
	}
	
    public function viewOrDownload(){
        //使用左连接查询对应 openid 的nickname
        $courseware = Db::view('PluginWechatCourseware','id,name,url,views,downloads')
                ->view('PluginWechatUserCourseware','userId','PluginWechatCourseware.id=PluginWechatUserCourseware.coursewareId','LEFT')
                ->view('PluginWechatUser','headimgurl,nickname,studentId','PluginWechatUserCourseware.userId=PluginWechatUser.id')
                ->select();
        //$courseware = Db::name('pluginWechatCourseware')->select();
        $this->assign('courseware',$courseware);
        return $this->fetch('/uploadCourseware/viewOrDownload');
        //echo '查看或下载文件处理';
    }

    /* 查看或下载文件处理 */
    public function download($filename){
		//$filename = "resume.pdf";
		$url = UPLOADFILE_SAVE_PATH;//"/webdata/classManage/public/";
        if(!file_exists($url.'/'.$filename)){
            $this->error('文件不存在！');
            exit();
        }
		$file=fopen($url.'/'.$filename,"r");//打开文件
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