<?php

namespace plugins\wechat\controller; //插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use think\Validate;
use think\Db;
use think\Request;

class SignInController extends PluginBaseController{
	
    public function index(){
        if(empty(session('userInfo'))){
            if(!isset($weObj)){
                $loginObj = new LoginValidationController();
                $weObj = $loginObj->getWeObj();
            }
            $loginObj->authLogin();//授权验证登录获取code
            //通过code换取网页授权access_token
            $res = $weObj->getOauthAccessToken();var_dump($res);die;
            if($res){
                //刷新access_token（如果需要）
                $refreshRes = $weObj->getOauthRefreshToken($res['refresh_token']);
                //拉取用户信息(需scope为 snsapi_userinfo)
                $userInfo = $weObj->getOauthUserinfo($refreshRes['access_token'],$refreshRes['openid']);
                session('userInfo',$userInfo);
            }
        }else{
            $userInfo = session('userInfo');
        }
        /* 绑定学号或者更换已绑定学号提交表单操作 */
        if(request()->isPost()){
            $openid = $userInfo['openid'];
            //var_dump(request()->post());
            $saveStudentId = request()->post('saveStudentId');
            $modifyStudentId = request()->post('modifyStudentId');
            if($saveStudentId!=null){
                Db::name('PluginWechatUser')->where('openid', $openid)->setField('studentId',$saveStudentId);
                $this->success('学号绑定成功！',url('wechat/wechat/index'));
            }elseif($modifyStudentId!=null){
                if(trim($modifyStudentId)!=''){
                   $originStudentId = Db::name('PluginWechatUser')->where('openid', $openid)->value('studentId');
                   if($originStudentId != trim($modifyStudentId)){
                       $res = Db::name('PluginWechatUser')->where('openid', $openid)->update(['studentId' => $modifyStudentId]);
                       if($res){
                          //修改学号后得将该账号下的所有签到记录清空
                          Db::name('PluginWechatSignin')->where('openid',$openid)->delete();
                          $this->success('学号修改成功！','http://www.shibin.tech/classManage/public/index.php/wechat/wechat/index');
                       }else{
                          $this->error('学号修改失败！');
                       }
                   }
                   $this->success('学号跟之前绑定的学号一致，操作成功！','http://www.shibin.tech/classManage/public/index.php/wechat/wechat/index');
                }
            }else{
                $this->error('无效操作！',url('wechat/wechat/index'));
            }
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
	
    //查看签到记录  
    public function signin_record(){
        $user = request()->param('user');
        $where = [];
        if($user=='me'){
           $where = ['openid' => $openid];
        }
        //$signin_record = Db::name('pluginWechatSignin')->where($where)->select();
        //foreach ($signin_record as $key => $value) {
        //使用左连接查询对应 openid 的nickname
        $signin_record = Db::view('PluginWechatSignin','id,studentId,signInTime')
                ->view('PluginWechatUser','nickname,sex','PluginWechatSignin.openid=PluginWechatUser.openid','LEFT')
                ->where($where)
                ->select();
        //}
        $this->assign('signin_record', $signin_record);
        return $this->fetch('/signIn/signin_record');
    }
/*
    //查看我的签到记录	
    public function my_signin_record(){
        $my_signin_record = Db::name('PluginWechatSignin')->where('openid',$openid)->select();
        $this->assign('signin_record', $my_signin_record);
        return $this->display('signin_record');
    }     
	
    //查看所有人的签到记录  
    public function all_signin_record(){
        $all_signin_record = Db::name('PluginWechatSignin')->select();
        $this->assign('signin_record', $all_signin_record);
        return $this->display('signin_record');
    }  
    */
}