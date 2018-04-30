<?php
namespace app\wechat\controller;
use think\Controller;

class WechatController extends Controller{
    /**
     * [index 微信公众号对接]
     */
    public function index(){
        //hook('wechat',array('type'=>'connect'));Cannot pass parameter 2 by reference
	
		$arr = ['type'=>'connect'];
		hook('wechat',$arr);//钩子触发 wechat 执行
    }
}