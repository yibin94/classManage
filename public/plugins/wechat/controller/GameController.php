<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class GameController extends PluginBaseController{
    /*游戏列表*/
	function index(){
		return $this->fetch("/game/gameList");
	}
	
	function chooseGame($name){echo '<script>alert('.$name.')</script>';
	echo '<script>alert('.$this->request->param().')</script>';
		return $this->fetch("/game/".$name."/index");
	}
}	