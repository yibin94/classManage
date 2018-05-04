<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class GameController extends PluginBaseController{
    /*游戏列表*/
	public function index(){
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
            $data = Db::name('pluginWechatAccessToken')->where('openid', $openid)->find();
            $res = [];
            if(!empty($data) && time() < $data['expire_time']){//未过期
              $res = [
                'access_token' => $data['access_token'],
                'openid' => $openid
              ];
            }else{//access_token过期了就重新获取
                $res = $weObj->getOauthAccessToken();echo 5555555555;die;
            }
        }
        var_dump($res);die;
        if(!empty($res)){
            //刷新access_token（如果需要）
            //$refreshRes = $weObj->getOauthRefreshToken($res['refresh_token']);
            //拉取用户信息(需scope为 snsapi_userinfo)
            //$userInfo = $weObj->getOauthUserinfo($refreshRes['access_token'],$refreshRes['openid']);
            $userInfo = $weObj->getOauthUserinfo($res['access_token'],$res['openid']);
            session('userInfo',$userInfo);
            session('openid',$userInfo['openid']);
            $data = Db::name('pluginWechatAccessToken')->where('openid', $userInfo['openid'])->find();
            var_dump($data);die;
            if(!empty($data)&&time()>=$data['expire_time']){//原来有但过期就更新。
                Db::name('pluginWechatAccessToken')->where('openid', $userInfo['openid'])->update(['access_token'=>$res['access_token'],'expire_time'=>time()+7000]);
            }elseif(empty($data)){//原来没有就新增。
                $data = [
                   'openid' => $userInfo['openid'],
                   'access_token' => $res['access_token'],
                   'expire_time' => time()+7000
                ];var_dump($data);die;
                Db::name('pluginWechatAccessToken')->insert($data);
            }
        }
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
}	