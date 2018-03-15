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

class CommonValidationController extends PluginBaseController{
    /* 公共验证控制器初始化验证是否已经登录. */
	protected function _initialize()
    {
		parent::_initialize();
		$openid = session('openid','','thinkcmf');
		$this->assign('openid',$openid);
		return $this->fetch("/index/index");
		//获取sesion中的用户id，可判断是否登录.
        $userId = session('PLUGIN_WECHAT_USER_ID');
        if (!$userId) {//无用户id则跳回登录界面.
            return $this->redirect(cmf_plugin_url("Wechat://Index/login"));
        }
    }

}