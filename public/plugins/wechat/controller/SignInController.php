<?php

namespace plugins\wechat\controller; //插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use think\Validate;
use think\Db;
use think\Request;

class SignInController extends PluginBaseController{
	
    public function index(){
        if(request()->isPost()){echo session('openid');
            //var_dump(request()->post());
            $studentId = request()->post('studentId');
        }else{
    		$action = request()->param('act');
            if(strcmp($action, "add") == 0){
                //echo '绑定学号操作';
                $this->assign('act', 'add');
                return $this->fetch('signIn/index');
            }
            if(strcmp($action, "modify") == 0){
                //echo '更换学号操作';
                $this->assign('act', 'modify');
                return $this->fetch('signIn/index');
            }
        }
	}
		
         
	
}