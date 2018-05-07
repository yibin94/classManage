<?php

namespace plugins\wechat\controller; //插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use think\Validate;
use think\Db;
use think\Request;

class SignInController extends PluginBaseController{
	
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

        /* 绑定学号或者更换已绑定学号提交表单操作 */
        if(request()->isPost()){
            //var_dump(request()->post());
            $saveStudentId = request()->post('saveStudentId');
            $modifyStudentId = request()->post('modifyStudentId');
            if($saveStudentId!=null){
                Db::name('PluginWechatUser')->where('openid', $openid)->setField('studentId',$saveStudentId);
                $this->success('学号绑定成功！');
            }elseif($modifyStudentId!=null){
                if(trim($modifyStudentId)!=''){
                   $originStudentId = Db::name('PluginWechatUser')->where('openid', $openid)->value('studentId');
                   if($originStudentId != trim($modifyStudentId)){
                       $res = Db::name('PluginWechatUser')->where('openid', $openid)->update(['studentId' => $modifyStudentId]);
                       if($res){
                          //修改学号后得将该账号下的所有签到记录清空
                          Db::name('PluginWechatSignin')->where('openid',$openid)->delete();
                          $this->success('学号修改成功！');
                       }else{
                          $this->error('学号修改失败！');
                       }
                   }
                   $this->success('学号跟之前绑定的学号一致，操作成功！');
                }
            }else{
                $this->error('无效操作！');
            }
        }else{
    		$action = request()->param('act');
            if(strcmp($action, "add") == 0){
                $originalStudentId = Db::name('PluginWechatUser')->where('openid',$openid)->value('studentId');
                if($originalStudentId != ''){
                    $this->error('你已经绑定过学号！不能重复绑定！');
                }
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
        $user = request()->param('record');
        $where = [];
        if($user=='me'){
           $where = ['PluginWechatSignin.openid' => session('openid')];
        }
        //$signin_record = Db::name('pluginWechatSignin')->where($where)->select();
        //foreach ($signin_record as $key => $value) {
        //使用左连接查询对应 openid 的nickname
        $signin_record = Db::view('PluginWechatSignin','id,studentId,signInTime')
                ->view('PluginWechatUser','headimgurl,nickname,sex','PluginWechatSignin.openid=PluginWechatUser.openid','LEFT')
                ->where($where)
                ->order('id DESC')
                ->select();
        //}
        $this->assign('signin_record', $signin_record);
        return $this->fetch('/signIn/signin_record');
    }
}