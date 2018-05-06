<?php

namespace plugins\wechat;
use cmf\lib\Plugin;
use think\Db;
use plugins\wechat\api\TpWechat\TpWechat;
use plugins\wechat\model\PluginWechatModel;
class WechatPlugin extends Plugin{
    public $info = array(
        'name'=>'Wechat',
        'title'=>'微信公众号',
        'description'=>'微信公众号接入',
        'status'=>1,
        'author'=>'yibin',
        'version'=>'1.0'
    );
	
    public $hasAdmin = 1;//插件是否有后台管理界面

    // 插件安装
    public function install(){//安装方法必须实现
        $db_prefix = config('database.prefix');
        $sql1=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_user` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `subscribe` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用户是否订阅该公众号标识，1是0否',
  `openid` varchar(40) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `studentId` varchar(20) NOT NULL COMMENT '学号',
  `nickname` varchar(255) NOT NULL COMMENT '用户的昵称',
  `sex` tinyint(2) NOT NULL COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `headimgurl` varchar(255) NOT NULL COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `subscribe_time` int(10) NOT NULL COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        /*$sql2=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_autoreply` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '关键字回复功能名称',
  `rule` varchar(255) NOT NULL COMMENT '正则规则',
  `function` varchar(50) NOT NULL COMMENT '回复调用方法',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '功能是否启用,0为不启用,1为启用,默认为1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SQL;*/
        /*$sql3=<<<INSERT
INSERT INTO `{$db_prefix}plugin_wechat_autoreply` VALUES
('1', '使用帮助说明', '/^(帮助|bz|help)$/i', 'replyHelp', '1'),
('2', '天气预报', '/^(.+)天气$/i', 'replyWeather', '1'),
('3', '快递', '/^快递(.+)$/i', 'replyExpress', '1'),
('4', '彩票种类和查询码', '/^(彩票|caipiao|cp)$/i', 'replyLotteryList', '1'),
('5', '彩票开奖结果', '/^cp(.+)$/i', 'replyLotteryRes', '1'),
('6', '找周边', '/^找(.+)$/i', 'replyFind', '1'),
('7', '热门文章', '/^(热门|remen|rm)$/i', 'replyHot', '1'),
('8', '热门分类列表', '/^rm(.+)$/i', 'replyHotList', '1');
INSERT;*/
      
	  $sql4=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '游戏名称',
  `url` varchar(255) NOT NULL COMMENT '游戏地址',
  `clicks` int(11) NOT NULL DEFAULT 0 COMMENT '游戏被点击（被玩）次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号游戏表';
SQL;
     $sql5=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_user_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '玩家id',
  `gameId` int(11) NOT NULL COMMENT '游戏id',
  `record` int(11) NOT NULL DEFAULT 0 COMMENT '最高记录(分数)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号玩家-游戏表';
SQL;
     $sql6=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_courseware` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '课件名字',
  `url` varchar(255) NOT NULL COMMENT '课件地址目录',
  `views` int(11) NOT NULL DEFAULT 0 COMMENT '浏览量',
  `downloads` int(11) NOT NULL DEFAULT 0 COMMENT '下载量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号课件表';
SQL;
     $sql7=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_user_courseware` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(120) NOT NULL COMMENT '用户 id',
  `coursewareId` varchar(255) NOT NULL COMMENT '课件 id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号用户-课件表';
SQL;
     $sql8=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_signin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(40) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `studentId` varchar(20) NOT NULL COMMENT '学号',
  `signInTime` int(10) NOT NULL DEFAULT 0 COMMENT '签到时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号签到表';
SQL;
     $sql9=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_access_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_token` varchar(512) NOT NULL COMMENT 'access_token',
  `expire_time` int(10) NOT NULL DEFAULT 0 COMMENT 'access_token过期时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号access_token缓存表';
SQL;
     
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user;");
        //Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_autoreply;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_games;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user_games;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_courseware;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user_courseware;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_signin;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_access_token;");
        Db::execute($sql1);
        //Db::execute($sql2);
        //Db::execute($sql3);
        Db::execute($sql4);
        Db::execute($sql5);
        Db::execute($sql6);
        Db::execute($sql7);
        Db::execute($sql8);
        Db::execute($sql9);
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall(){//卸载方法必须实现
        $db_prefix = config('database.prefix');
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user;");
        //Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_autoreply;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_games;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user_games;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_courseware;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user_courseware;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_signin;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_access_token;");
        return true;//卸载成功返回true，失败false
    }
  
    public static $options;

    //实现的wechat钩子方法
    public function wechat($param){
        $config=$this->getConfig();
        switch ($param['type']) {
            case 'connect':
                self::$options = array(
                    			'token'=>$config['Token'], //填写你设定的key
                    			'encodingaeskey'=>$config['EncodingAESKey'], //填写加密用的EncodingAESKey
                    			'appid'=>$config['AppID'], //填写高级调用功能的app id
                    			'appsecret'=>$config['AppSecret'] //填写高级调用功能的密钥
                    		);
                $weObj = new TpWechat(self::$options);
                $weObj->valid();		
					
  
       //设置菜单     
       $newmenu = array(
          "button" => array(
             array(
                "name" => "签到",
                "sub_button" => array(
                   array ('type'=>'view','name'=>'绑定学号','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/SignIn/index.html?act=add'),
                   array ('type'=>'view','name'=>'换绑学号','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/SignIn/index.html?act=modify'),
                   array ('type'=>'click','name'=>'签到','key'=>'MENU_KEY_SIGNIN'),
                   array ('type'=>'view','name'=>'我的记录','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/SignIn/signin_record.html?record=me'),
                   array ('type'=>'view','name'=>'所有记录','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/SignIn/signin_record.html?record=all')
                 )
             ),
             array(
                "name" => "课间娱乐",
                "sub_button" => array(
                     array ('type'=>'view','name'=>'小游戏','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/Game/index.html'),
                     array ('type'=>'view','name'=>'游戏排行榜','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/Game/ranking.html')
                 )
             ),
             array(
                "name" => "学习资料",
                "sub_button" => array(
                     array ('type'=>'view','name'=>'上传课件','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/UploadCourseware/upload.html'),
                     array ('type'=>'view','name'=>'查看或下载课件','url'=>'http://www.shibin.tech/classManage/public/index.php/plugin/wechat/UploadCourseware/viewOrDownload.html')
                 )
             )
          )
      );     
       $result = $weObj->createMenu($newmenu);

                //用户openid:
                $openid = $weObj->getRev()->getRevFrom();
                $type = $weObj->getRev()->getRevType();
                switch($type) {
                case TpWechat::MSGTYPE_TEXT:
                    /* 收到用户主动回复消息处理 */
                    $content = $weObj->getRev()->getRevContent(); 
					          $wechatModel = new PluginWechatModel;
					          //获取消息内容
                    /* 将消息内容与已有关键字进行匹配,对相应关键字进行相关响应 */
                    $reply = $wechatModel->reply($openid,$content,$weObj,$config);
            		    exit;
                    break;    
           		  case TpWechat::MSGTYPE_EVENT:
           		    $rev_event = $weObj->getRevEvent();
           		    /* 检测事件类型 */
           		    switch ($rev_event['event']){
           		        case TpWechat::EVENT_MENU_CLICK:
           		            //TODO:CLICK事件
                          $receiveData = $weObj->getRevData();
                          switch ($receiveData['EventKey']) {//获取设置的key值。
                            case 'MENU_KEY_SIGNIN'://签到操作
                              //先查看用户是否已绑定学号，若无则不能签到。
                              $studentId = Db::name('pluginWechatUser')->where('openid',$openid)->value('studentId');//取某个字段值
                              if(empty($studentId)){
                                 $revCont = '请先绑定学号后再签到！';
                                 break;
                              }/*
                              $record = Db::name('pluginWechatSignin')->where('openid',$openid)->order('id DESC')->limit(1)->find();
                              if(!empty($record)){
                                $studentId = $record['studentId'];
                              }*/
                              $data = [
                                 'openid' => $openid,
                                 'studentId' => $studentId,
                                 'signInTime' => time()
                              ];
                              $res = Db::name('pluginWechatSignin')->insert($data);
                              if($res){
                                 $revCont = '签到成功！';
                              }else{
                                 $revCont = '签到失败！';
                              }
                              break;
                            default:
                              $revCont = '点我干嘛！';
                              break;
                          }
                          $weObj->text($revCont)->reply();
           		            break;
           		        case TpWechat::EVENT_SUBSCRIBE:
           		            /* 如果公众号没有认证,则不能拉取用户信息 */
           		            if($config['IsAuth'] == 0){
                              //创建基本用户数据
           		                $user_data = [
                                  'openid' => $openid,
                                  'sex' => 0,
                                  'headimgurl' => '',
                                  'nickname' => '匿名'
                              ];
           		            }else if($config['IsAuth'] == 1){
                              //直接根据 openid 获取用户信息
           		                $user_data = $weObj->getUserInfo($openid);
           		            }
                          $user_data['subscribe'] = 1;
                          $user_data['studentId'] = '';
                          $user_data['subscribe_time'] = time();

           		            $judge = Db::name('pluginWechatUser')->where('openid',$openid)->find();
           		            if($judge){
           		                Db::name('PluginWechatUser')->where('id',$judge['id'])->update($user_data);
           		            }else{//当插入数据多于表字段时为了避免出错可将database.php中'fields_strict' => false。
                              $res = Db::name('PluginWechatUser')->insert($user_data);
           		            }
           		            /* 下推关注欢迎语 */
           		            $weObj->text($config['Welcome'])->reply();
           		            break;
       		            case TpWechat::EVENT_UNSUBSCRIBE:
       		                $judge = Db::name('PluginWechatUser')->where('openid',$openid)->find();
       		                if($judge){
       		                    Db::name('PluginWechatUser')->where(array('id' => $judge['id']))->setField('subscribe',0);
       		                }
       		                break;
       		            default:
       		                break;
           		    }
           			break;
           		case TpWechat::MSGTYPE_IMAGE:
           			break;
           		default:
           			$weObj->text("help info")->reply();
           			break;
               }
                break;/* connect end */
            default:
                break;
        }
    }
}