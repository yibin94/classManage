<?php

namespace plugins\wechat\controller; //插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use think\Validate;
use think\Db;
use think\Request;

class SignInController extends PluginBaseController{
	
    public function index(){
		$action = request()->param('act');
        if(strcmp($action, "add") == 0){
            //echo '绑定学号操作';
            $this->fetch('signIn/index?act=add');
        }
        if(strcmp($action, "modify") == 0){
            //echo '更换学号操作';
            $this->fetch('signIn/index?act=modify');
        }
        //$data = Db::name('PluginWechatUser')->where('openid',$openid);
        //$this->fetch();	
		//return false;
	}
		
         
	
}