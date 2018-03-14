<?php
// +----------------------------------------------------------------------
// | IndexController.class.php
// +----------------------------------------------------------------------
// | Author: polo <gao.bo168@gmail.com>
// +----------------------------------------------------------------------
// | Data: 2015-3-4下午5:05:32
// +----------------------------------------------------------------------
// | Version: 2015-3-4下午5:05:32
// +----------------------------------------------------------------------
// | Copyright: ShowMore
// +----------------------------------------------------------------------
namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class IndexController extends PluginBaseController{
    function index(){
		return $this->fetch("/index");
		//return false;
	}
		
	/**
     * 前台用户注册
     */
    public function register()
    {
        return $this->fetch("/register");
    }

    /**
     * 前台用户注册提交
     */
    public function doRegister()
    {
        if ($this->request->isPost()) {
            $rules = [
                'captcha'  => 'require',
                'code'     => 'require',
                'password' => 'require|min:6|max:32',

            ];

            $isOpenRegistration=cmf_is_open_registration();

            if ($isOpenRegistration) {
                unset($rules['code']);
            }

            $validate = new Validate($rules);
            $validate->message([
                'code.require'     => '验证码不能为空',
                'password.require' => '密码不能为空',
                'password.max'     => '密码不能超过32个字符',
                'password.min'     => '密码不能小于6个字符',
                'captcha.require'  => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if (!cmf_captcha_check($data['captcha'])) {
                $this->error('验证码错误');
            }

            if(!$isOpenRegistration){
                $errMsg = cmf_check_verification_code($data['mobile'], $data['code']);
                if (!empty($errMsg)) {
                    $this->error($errMsg);
                }
            }

            $register          = new PluginWechatModel();
            $user['password'] = $data['password'];
			if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['mobile'])) {
                $user['mobile'] = $data['mobile'];
                $log            = $register->registerMobile($user);
            } else {
                $log = 2;
            }
            
            $redirect = cmf_plugin_url('Wechat://Index/login') . '/';
            switch ($log) {
                case 0:
                    $this->success('注册成功', $redirect);
                    break;
                case 1:
                    $this->error("您的账户已注册过");
                    break;
                case 2:
                    $this->error("您输入的账号格式错误");
                    break;
                default :
                    $this->error('未受理的请求');
            }

        } else {
            $this->error("请求错误");
        }

    }

	
	/**
     * 插件登陆界面
     */
    public function login()
    {
        $user_id = session('PLUGIN_WECHAT_USER_ID');
        if (!empty($user_id)) {//已经登录
            redirect(url("admin/Index/index"));
        } else {
            return $this->fetch(":login");
        }
    }

    /**
     * 登录验证
     */
    public function doLogin()
    {

        $captcha = $this->request->param('captcha');
        if (empty($captcha)) {
            $this->error(lang('CAPTCHA_REQUIRED'));
        }
        //验证码
        if (!cmf_captcha_check($captcha)) {
            $this->error(lang('CAPTCHA_NOT_RIGHT'));
        }

        $name = $this->request->param("mobile");
        if (empty($name)) {
            $this->error(lang('USERNAME_REQUIRED'));
        }
        $pass = $this->request->param("password");
        if (empty($pass)) {
            $this->error(lang('PASSWORD_REQUIRED'));
        }
        
        $where['mobile'] = $name;

        $result = Db::name('PluginWechatUser')->where($where)->find();

        if (!empty($result)) {
            if (cmf_compare_password($pass, $result['password'])) {
                
                //登入成功页面跳转
                session('PLUGIN_WECHAT_USER_ID', $result["id"]);
                session('name', $result["mobile"]);
                $token                     = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                Db::name('user')->update($result);
                cookie("plugin_wechat_username", $name, 3600 * 24 * 30);
                $this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
            } else {
                $this->error(lang('PASSWORD_NOT_RIGHT'));
            }
        } else {
            $this->error(lang('USERNAME_NOT_EXIST'));
        }
    }

    /**
     * 微信插件用户退出
     */
    public function logout()
    {
        session('PLUGIN_WECHAT_USER_ID', null);
        return redirect(url('/', [], false, true));
    }
	
	public function send()
    {
        $validate = new Validate([
            'username' => 'require',
        ]);

        $validate->message([
            'username.require' => '请输入手机号!',
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $accountType = '';

        if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
            $accountType = 'mobile';
        } else {
            $this->error(session('a','','think')/*"请输入正确的手机格式!"*/);
        }

        //TODO 限制 每个ip 的发送次数

        $code = cmf_get_verification_code($data['username']);
        if (empty($code)) {
            $this->error("验证码发送过多,请明天再试!");
        }

        if ($accountType == 'mobile') {

            $param  = ['mobile' => $data['username'], 'code' => $code];
            $result = hook_one("send_mobile_verification_code", $param);

            if ($result !== false && !empty($result['error'])) {
                $this->error($result['message']);
            }

            if ($result === false) {
                $this->error('未安装验证码发送插件,请联系管理员!');
            }

            cmf_verification_code_log($data['username'], $code);

            if (!empty($result['message'])) {
                $this->success($result['message']);
            } else {
                $this->success('验证码已经发送成功!');
            }
        }
    }

}