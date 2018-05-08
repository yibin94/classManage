<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use plugins\wechat\controller\LoginValidationController;
use think\Validate;
use think\Db;

class GameController extends PluginBaseController{
    /*游戏列表*/
	public function index(){
        $this->verifyLogin();
		return $this->fetch("/game/gameList");
	}
	/*选择并进入游戏*/
	public function chooseGame($name){
        
		return $this->fetch("/game/$name/index");
	}

    /* 游戏排行表 */
    public function ranking(){
        echo "游戏排行榜";
    }

    public function verifyLogin(){
        if(!isset($weObj)){
            $loginObj = new LoginValidationController();
            $weObj = $loginObj->getWeObj();
        }
        $loginObj->authLogin();//授权验证登录获取code
        $openid = session('openid');
        if(empty($openid)){
            //通过code换取网页授权access_token
            $res = $weObj->getOauthAccessToken();
        }else{
            $data = Db::name('pluginWechatAccessToken')->where('id',1)->find();
            $res = [];
            if(!empty($data) && time() < $data['expire_time']){//未过期
              $res = [
                'access_token' => $data['access_token'],
                'openid' => $openid
              ];
            }else{//access_token过期了或者原来没有就重新获取
                $res = $weObj->getOauthAccessToken();
            }
        }
        
        if(!empty($res)){
            $userInfo = $weObj->getOauthUserinfo($res['access_token'],$res['openid']);
            session('openid',$userInfo['openid']);
            $data = Db::name('pluginWechatAccessToken')->where('id',1)->find();
            
            if(!empty($data)&&time()>=$data['expire_time']){//原来有但过期就更新。
                Db::name('pluginWechatAccessToken')->where('id', 1)->update(['access_token'=>$res['access_token'],'expire_time'=>time()+7000]);
            }elseif(empty($data)){//原来没有就新增。
                $data = [
                   'access_token' => $res['access_token'],
                   'expire_time' => time()+7000
                ];
                Db::name('pluginWechatAccessToken')->insert($data);
            }
        }
    }
}	