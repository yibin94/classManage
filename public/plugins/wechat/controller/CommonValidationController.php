<?php
// +----------------------------------------------------------------------
// | IndexController.class.php
// +----------------------------------------------------------------------
// | Author: polo <gao.bo168@gmail.com>
// +----------------------------------------------------------------------
// | Data: 2018-3-15下午5:05:32
// +----------------------------------------------------------------------
// | Version: 2015-3-4下午5:05:32
// +----------------------------------------------------------------------
// | Copyright: ShowMore
// +----------------------------------------------------------------------
namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\api\TpWechat\TpWechat;
use think\Request;

class CommonValidationController extends PluginBaseController{
	
	protected function getWeObj(){
		$config = $this->getPlugin()->getConfig();
		$options = array(
						'token'=>$config['Token'], //填写你设定的key
						'encodingaeskey'=>$config['EncodingAESKey'],//填写加密用的EncodingAESKey
						'appid'=>$config['AppID'], //填写高级调用功能的appid
						'appsecret'=>$config['AppSecret'] //填写高级调用功能的密钥
				   );
		return new TpWechat($options);
	}
    /* 公共验证控制器初始化验证是否已经登录. */
	protected function _initialize()
    {
		parent::_initialize();
		$code = isset($_GET['code'])?$_GET['code']:'';//code不为空则表明允许授权登录。
		if (!$code){
			$weObj = $this->getWeObj();
			//用户同意授权后跳转的回调地址，snsapi_userinfo获取用户信息
			//$callback = 'http://www.shibin.tech/classManage/public/plugin/wechat/'.request()->param('_controller').'/'.request()->param('_action');
			$callback = request()->url(true);// 获取当前请求的包含域名的完整URL地址
			echo $weObj->getOauthRedirect($callback,'STATE','snsapi_userinfo');die;
			return $this->redirect($weObj->getOauthRedirect($callback,'STATE','snsapi_userinfo'));
		}
		
		/*
		
		
		return true;
		//获取sesion中的用户id，可判断是否登录.
        $userId = session('PLUGIN_WECHAT_USER_ID');
        if (!$userId) {//无用户id则跳回登录界面.
            return $this->redirect(cmf_plugin_url("Wechat://Index/login"));
        }*/
    }

}