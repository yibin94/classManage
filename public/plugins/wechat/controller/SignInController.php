<?php

namespace plugins\wechat\controller; //插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use think\Validate;
use think\Db;
use think\Request;

class SignInController extends PluginBaseController{
	
    public function index(){
		$action = request()->param('act');echo $action;die;
        if($action == "add"){
            echo '绑定学号操作';
        }elseif($action == "modify"){
            echo '更换学号操作';
        }
        //$data = Db::name('PluginWechatUser')->where('openid',$openid);
        //$this->fetch();	
		return false;
	}
		
         
	
}