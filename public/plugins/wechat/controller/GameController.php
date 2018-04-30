<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class GameController extends PluginBaseController{
    /*游戏列表*/
	function index(){
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
            session('userInfo',$userInfo);
        }
		return $this->fetch("/game/gameList");
	}
	/*选择并进入游戏*/
	function chooseGame($name){
        var_export(session('userInfo'));die;
		return $this->fetch("/game/$name/index");
	}
}	