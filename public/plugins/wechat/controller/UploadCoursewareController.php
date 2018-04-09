<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    function index(){
		echo "<script>alert(233);</script>";
		var_dump($_FILES);//die;
		$filename = $_FILES['file']['name'];
$key = $_POST['key'];
$key2 = $_POST['key2'];
if ($filename) {
    move_uploaded_file($_FILES["file"]["tmp_name"],
      "/webdata/classManage/public/upload/" . $filename);
}
echo $key;
echo $key2;
die;


		//echo request()->post('media_id').'--'.request()->param('media_id');
	    $media_id = request()->param('media_id');
		$config = $this->getPlugin()->getConfig();
		$options = array(
						'token'=>$config['Token'], //填写你设定的key
						'encodingaeskey'=>$config['EncodingAESKey'],//填写加密用的EncodingAESKey
						'appid'=>$config['AppID'], //填写高级调用功能的appid
						'appsecret'=>$config['AppSecret'] //填写高级调用功能的密钥
				   );
		$weObj = new TpWechat($options);
		//通过code换取网页授权access_token
		$res = $weObj->getOauthAccessToken();
	    //根据微信JS接口上传了图片,会返回上面写的images.serverId（即media_id），填在下面即可  
		 $getDataUrl = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$res['refresh_token']."&media_id=$media_id";  
		$ranfilename=time().rand().".jpg";
		$data = file_get_contents($getDataUrl);
		$path = __DIR__;
        $filename=$path.'/'.date('Y_m_d').'/'.$ranfilename;
		file_put_contents($filename,$data);
		return ;
	}
}	