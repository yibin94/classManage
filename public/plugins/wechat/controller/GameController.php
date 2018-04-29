<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use plugins\wechat\controller\CommonValidationController;
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class GameController extends CommonValidationController{
    /*游戏列表*/
	function index(){
        /*$obj = new CommonValidationController();
        $weObj = $obj->getWeObj();*/
        $weObj = CommonValidationController::getWeObj();
        //通过code换取网页授权access_token
        $res = $weObj->getOauthAccessToken();
        if($res){
            //刷新access_token（如果需要）
            $refreshRes = $weObj->getOauthRefreshToken($res['refresh_token']);
            //拉取用户信息(需scope为 snsapi_userinfo)
            $userInfo = $weObj->getOauthUserinfo($refreshRes['access_token'],$refreshRes['openid']);
            //$userInfo = $weObj->getOauthUserinfo($res['access_token'],$res['openid']);
            var_dump($userInfo);
            //return $this->fetch("/index/index");
        }
die;
		return $this->fetch("/game/gameList");
	}
	
	function chooseGame($name){
		return $this->fetch("/game/$name/index");
	}
}	