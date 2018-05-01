<?php

namespace plugins\wechat\controller; //插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Request;

class LoginValidationController extends PluginBaseController{
	public function getWeObj(){
		if(!isset($weObj)){
			$config = $this->getPlugin()->getConfig();
			$options = array(
			                //填写你设定的key
							'token'=>$config['Token'],
							//填写加密用的EncodingAESKey
							'encodingaeskey'=>$config['EncodingAESKey'],
							'appid'=>$config['AppID'], //填写高级调用功能的appid
							'appsecret'=>$config['AppSecret'] //填写高级调用功能的密钥
					   );
			$weObj = new TpWechat($options);
		}
		return $weObj;
	}
    /* 登录验证控制器初始化验证是否已经登录. */
	public function authLogin()
    {
		$code = isset($_GET['code'])?$_GET['code']:'';//code不为空则表明允许授权登录。
		if (!$code){
			$weObj = $this->getWeObj();
			//用户同意授权后跳转的回调地址，snsapi_userinfo获取用户信息
			$callback = request()->url(true);//获取当前请求(包含域名)的完整URL地址
			//echo $callback;die;
			return $this->redirect($weObj->getOauthRedirect($callback,'STATE','snsapi_userinfo'));
		}
    }

}