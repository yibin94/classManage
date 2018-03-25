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
use plugins\wechat\controller\CommonValidationController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;
use think\Request;

class IndexController extends CommonValidationController{
    function index(){
		$obj = new CommonValidationController();
		$weObj = $obj->getWeObj();
		//通过code换取网页授权access_token
		$res = $weObj->getOauthAccessToken();
		if($res){
			//刷新access_token（如果需要）
			$refreshRes = $weObj->getOauthRefreshToken($res['refresh_token']);
			//拉取用户信息(需scope为 snsapi_userinfo)
			$userInfo = $weObj->getOauthUserinfo($refreshRes['access_token'],$refreshRes['openid']);
			$this->assign(
			   array(
			      'openid'=>$userInfo['openid'],
				  'nickname'=>$userInfo['nickname'],
				  'sex'=>$userInfo['sex'],
				  'headimgurl'=>$userInfo['headimgurl']
			   )
			);
			return $this->fetch("/index/index");
		}
			
		return false;
				
		/*
		 // 获取表单上传文件 例如上传了001.jpg    
		 $file = request()->file('file');    
		 // 移动到框架应用根目录/public/upload/ 目录下    
		 $info = $file->move(ROOT_PATH . 'public/upload');
		 if($info){
			 // 成功上传后 获取上传信息
			 // 输出 jpg       
			 echo $info->getExtension();
			 // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
			 echo $info->getSaveName();
			 // 输出 42a79759f284b767dfcb2a0197904287.jpg
			 echo $info->getFilename(); 
		 }else{
			 // 上传失败获取错误信息
			 echo $file->getError();
		 }
		*/
		
	}
		
	public function upload(){
		/*var_dump(request()->param(true));
		var_dump($_FILES);
		if ($_FILES["file"]["error"] > 0)
{
    echo "错误：" . $_FILES["file"]["error"] . "<br>";
}
else
{
    echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
    echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
    echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"];
}*/
	   
	   echo '<script>alert(request()->param('media_id'))</script>';
	    $obj = new CommonValidationController();
		$weObj = $obj->getWeObj();
		//通过code换取网页授权access_token
		$res = $weObj->getOauthAccessToken();
	    //根据微信JS接口上传了图片,会返回上面写的images.serverId（即media_id），填在下面即可  
		 $str = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$res['refresh_token']."&media_id=".request()->param('media_id');  
		 //获取微信“获取临时素材”接口返回来的内容（即刚上传的图片）  
		 $a = file_get_contents($str);  
		//__DIR__指向当前执行的PHP脚本所在的目录  
		 echo __DIR__;
		 //以读写方式打开一个文件，若没有，则自动创建  
		 $resource = fopen(__DIR__."/1.jpg" , 'w+');  
		 //将图片内容写入上述新建的文件  
		 fwrite($resource, $a);  
		 //关闭资源  
		 fclose($resource);  
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