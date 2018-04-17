<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use plugins\wechat\controller\CommonValidationController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class GameController extends CommonValidationController{
    /*游戏列表*/
	function index(){
		$obj = new CommonValidationController();
		
		$code = isset($_GET['code'])?$_GET['code']:'';//code不为空则表明允许授权登录。
		if (!$code){
			//$weObj = $this->getWeObj();
			$callback = 'http://www.shibin.tech/classManage/public/plugin/wechat/Index/index';
			header("Location: $callback");
		}
		
		$weObj = $obj->getWeObj();
		//通过code换取网页授权access_token
		$res = $weObj->getOauthAccessToken();
		if($res){
			//刷新access_token（如果需要）
			$refreshRes = $weObj->getOauthRefreshToken($res['refresh_token']);
			//拉取用户信息(需scope为 snsapi_userinfo)
			$userInfo = $weObj->getOauthUserinfo($refreshRes['access_token'],$refreshRes['openid']);
			$this->assign(
			   array(
			      'openid'=>$userInfo['openid'],
				  'nickname'=>$userInfo['nickname'],
				  'sex'=>$userInfo['sex'],
				  'headimgurl'=>$userInfo['headimgurl']
			   )
			);
			return $this->fetch("/game/gameList");
		}
			
		return false;
	}
	
	function chooseGame($name){
		return $this->fetch("/game/$name/index");
	}
}	