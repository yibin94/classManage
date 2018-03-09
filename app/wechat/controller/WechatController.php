<?php
// +----------------------------------------------------------------------
// | WechatController.class.php
// +----------------------------------------------------------------------
// | Author: polo <gao.bo168@gmail.com>
// +----------------------------------------------------------------------
// | Data: 2015-3-3下午2:48:20
// +----------------------------------------------------------------------
// | Version: 2015-3-3下午2:48:20
// +----------------------------------------------------------------------
// | Copyright: ShowMore
// +----------------------------------------------------------------------
namespace app\wechat\controller;
use think\Controller;

//use Think\Controller;

class WechatController extends Controller{
    /**
     * [index 微信公众号对接]
     * 
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-3 下午3:29:22
     * @copyright Show More
     */
    public function index(){
        //hook('wechat',array('type'=>'connect'));Cannot pass parameter 2 by reference
		//hook('wechat','connect');
		//hook('wechat',array('connect'));
		$arr = ['a'=>1,'b'=>2];
		hook('wechat',$arr);
    }
}