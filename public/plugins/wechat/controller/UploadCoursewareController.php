<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\controller\LoginValidationController;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    /* 上传文件处理 */
    function index(){
		 $fileName = $_FILES['file']['name'];
         $saveUrl = UPLOADFILE_SAVE_PATH;
         if(!in_array(substr(strrchr($fileName, '.'), 1),['jpg','jpeg','png','pdf','word','doc'])){
            @unlink($saveUrl.'/'.$fileName);//删除已上传的该文件
            $this->error('不允许上传此类型文件！');
         }
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
        $this->verifyLogin();
		return $this->fetch("/uploadCourseware/upload");
	}
	
    public function viewOrDownload(){
        $this->verifyLogin();
        //使用左连接查询对应 openid 的nickname
        $courseware = Db::view('PluginWechatCourseware','id,name,url,views,downloads')
                ->view('PluginWechatUserCourseware','userId','PluginWechatCourseware.id=PluginWechatUserCourseware.coursewareId','LEFT')
                ->view('PluginWechatUser','headimgurl,nickname,studentId','PluginWechatUserCourseware.userId=PluginWechatUser.id')
                ->select();
        //$courseware = Db::name('pluginWechatCourseware')->select();
        $this->assign(['courseware'=>$courseware, 'url'=>'/classManage/public/upload']);
        return $this->fetch('/uploadCourseware/viewOrDownload');
        //echo '查看或下载文件处理';
    }

    /* 查看或下载文件处理 */
    public function download($filename){
        $filename = urldecode($filename);//避免找不到文件.
		$url = UPLOADFILE_SAVE_PATH;//"/webdata/classManage/public/";
        $con = file_get_contents($url.'/'.$filename);
        if($con==false){
            $this->error('文件不存在！');
            exit();
        }
		$file = fopen($url.'/'.$filename,"r");//打开文件
		//输入文件标签
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($url.'/'.$filename));
		header("Content-Disposition: attachment; filename=$filename");
		//输出文件内容
        //读取文件内容并直接输出到浏览器
		echo fread($file,filesize($url.'/'.$filename));
		fclose($file);
	}	

    public function verifyLogin(){
        if(!isset($weObj)){
            $loginObj = new LoginValidationController();
            $weObj = $loginObj->getWeObj();
        }
        $loginObj->authLogin();//授权验证登录获取code
        $openid = session('openid');
        if(empty($openid)){
            //通过code换取网页授权access_token
            $res = $weObj->getOauthAccessToken();
        }else{
            $data = Db::name('pluginWechatAccessToken')->where('id',1)->find();
            $res = [];
            if(!empty($data) && time() < $data['expire_time']){//未过期
              $res = [
                'access_token' => $data['access_token'],
                'openid' => $openid
              ];
            }else{//access_token过期了或者原来没有就重新获取
                $res = $weObj->getOauthAccessToken();
            }
        }
        
        if(!empty($res)){
            $userInfo = $weObj->getOauthUserinfo($res['access_token'],$res['openid']);
            session('openid',$userInfo['openid']);
            $data = Db::name('pluginWechatAccessToken')->where('id',1)->find();
            
            if(!empty($data)&&time()>=$data['expire_time']){//原来有但过期就更新。
                Db::name('pluginWechatAccessToken')->where('id', 1)->update(['access_token'=>$res['access_token'],'expire_time'=>time()+7000]);
            }elseif(empty($data)){//原来没有就新增。
                $data = [
                   'access_token' => $res['access_token'],
                   'expire_time' => time()+7000
                ];
                Db::name('pluginWechatAccessToken')->insert($data);
            }
        }
    }
}	