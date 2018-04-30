<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use plugins\wechat\controller\CommonValidationController;
use cmf\controller\PluginBaseController;
use think\Validate;
use think\Db;
use think\Request;

class IndexController extends CommonValidationController{
	
    function index(){
			
		return false;
	}
		
	/**
     * 微信插件用户注册页面
     */
    public function register()
    {
        return $this->fetch("index/register");
    }

    /**
     * 微信插件用户注册提交
     */
    public function doRegister()
    {
        if ($this->request->isPost()) {
            $rules = [
                'captcha'  => 'require',
                'code'     => 'require',
                'password' => 'require|min:6|max:32',
            ];

            $validate = new Validate($rules);
            $validate->message([
                'code.require'     => '验证码不能为空',
                'password.require' => '密码不能为空',
                'password.max'     => '密码不能超过32个字符',
                'password.min'     => '密码不能小于6个字符',
                'captcha.require'  => '验证码不能为空',
            ]);

            $data = $this->request->post();//获取post数据.
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if (!cmf_captcha_check($data['captcha'])) {
                $this->error('验证码错误');
            }

            //手机验证码检查.
			$errMsg = cmf_check_verification_code($data['username'], $data['code']);
			if (!empty($errMsg)) {
				$this->error($errMsg);
			}

            $register          = new PluginWechatModel();
            $user['password'] = $data['password'];
			if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
                $user['mobile'] = $data['username'];
                $log            = $register->registerMobile($user);
            } else {
                $log = 2;
            }
            
            $redirect = cmf_plugin_url('Wechat://Index/login') . '/';
            switch ($log) {
                case 0:
                    $this->success('注册成功', $redirect);//注册成功跳到登录界面.
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
     * 微信插件用户登陆界面
     */
    public function login()
    {
        $user_id = session('PLUGIN_WECHAT_USER_ID');
        if (!empty($user_id)) {//已经登录
            redirect(url("admin/Index/index"));
        } else {
            return $this->fetch("index/login");
        }
    }

    /**
     * 微信插件用户登录验证
     */
    public function doLogin()
    {
		//验证码
        $captcha = $this->request->param('captcha');
        if (empty($captcha)) {
            $this->error(lang('CAPTCHA_REQUIRED'));
        }
        
        if (!cmf_captcha_check($captcha)) {
            $this->error(lang('CAPTCHA_NOT_RIGHT'));
        }
        //手机号或用户名
        $name = $this->request->param("username");
        if (empty($name)) {
            $this->error('用户名不能为空！');
        }
		//密码
        $pass = $this->request->param("password");
        if (empty($pass)) {
            $this->error(lang('PASSWORD_REQUIRED'));
        }
        
        $where['mobile'] = $name;

        $result = Db::name('PluginWechatUser')->where($where)->find();

        if (!empty($result)) {
			//当前表单填写密码与数据库相应记录的密码比较.
            if (cmf_compare_password($pass, $result['password'])) { 
                //登入成功页面跳转
                session('PLUGIN_WECHAT_USER_ID', $result["id"]);
                session('name', $result["mobile"]);
				/*
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
				*/
                
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
	/* 发送手机验证码 */
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

        if (!preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
            $this->error("请输入正确的手机格式!");
        }

        //TODO 限制 每个ip 的发送次数

        $code = cmf_get_verification_code($data['username']);//生成手机数字验证码.
        if (empty($code)) {
            $this->error("验证码发送过多,请明天再试!");
        }

		$param  = ['mobile' => $data['username'], 'code' => $code];
		$result = hook_one("send_mobile_verification_code", $param);/*调用手机验证码发送插件*/

		if ($result !== false && !empty($result['error'])) {
			$this->error($result['message']);
		}

		if ($result === false) {
			$this->error('未安装验证码发送插件,请联系管理员!');/*直接在后台插件界面安装即可*/
		}

		cmf_verification_code_log($data['username'], $code);

		if (!empty($result['message'])) {
			$this->success($result['message']);
		} else {
			$this->success('验证码已经发送成功!');
		}
    }

	/**
     * 找回密码
     */
    public function findPassword()
    {
        return $this->fetch('index/find_password');
    }

    /**
     * 用户密码重置
     */
    public function passwordReset()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'captcha'           => 'require',
                'verification_code' => 'require',
                'password'          => 'require|min:6|max:32',
            ]);
            $validate->message([
                'verification_code.require' => '验证码不能为空',
                'password.require'          => '密码不能为空',
                'password.max'              => '密码不能超过32个字符',
                'password.min'              => '密码不能小于6个字符',
                'captcha.require'           => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            if (!cmf_captcha_check($data['captcha'])) {
                $this->error('验证码错误');
            }
            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }

            $userModel = new PluginWechatModel();
            if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
                $user['mobile'] = $data['username'];
                $log            = $userModel->mobilePasswordReset($data['username'], $data['password']);
            } else {
                $log = 2;
            }
            switch ($log) {
                case 0:
                    $this->success('密码重置成功', $this->request->root());
                    break;
                case 1:
                    $this->error("您的账户尚未注册");
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
	
}