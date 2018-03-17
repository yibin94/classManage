<?php
// +----------------------------------------------------------------------
// | WechatPlugin.class.php
// +----------------------------------------------------------------------
// | Author: polo <gao.bo168@gmail.com>
// +----------------------------------------------------------------------
// | Data: 2015-3-3下午2:33:43
// +----------------------------------------------------------------------
// | Version: 2015-3-3下午2:33:43
// +----------------------------------------------------------------------
// | Copyright: ShowMore
// +----------------------------------------------------------------------
namespace plugins\wechat;
use cmf\lib\Plugin;
use think\Db;
//use app\wechat\api\TpWechat\TpWechat;
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
        $db_prefix = config('database.prefix');//C('DB_PREFIX');
        $sql1=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_user` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `uid` int(20) NOT NULL COMMENT '绑定本站uid',
  `subscribe` tinyint(2) NOT NULL COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息',
  `openid` varchar(40) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `nickname` varchar(255) NOT NULL COMMENT '用户的昵称',
  `sex` tinyint(2) NOT NULL COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` varchar(50) NOT NULL COMMENT '用户所在城市',
  `country` varchar(50) NOT NULL COMMENT '用户所在国家',
  `province` varchar(50) NOT NULL COMMENT '用户所在省份',
  `language` varchar(50) NOT NULL COMMENT '用户的语言，简体中文为zh_CN',
  `headimgurl` varchar(255) NOT NULL COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `subscribe_time` int(10) NOT NULL COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  `unionid` varchar(255) NOT NULL COMMENT '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段',
  `latitude` varchar(20) NOT NULL COMMENT '地理位置纬度',
  `longitude` varchar(20) NOT NULL COMMENT '地理位置经度',
  `labelname` varchar(255) NOT NULL COMMENT '微信反馈的地理位置信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $sql2=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_autoreply` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '关键字回复功能名称',
  `rule` varchar(255) NOT NULL COMMENT '正则规则',
  `function` varchar(50) NOT NULL COMMENT '回复调用方法',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '功能是否启用,0为不启用,1为启用,默认为1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SQL;
        $sql3=<<<INSERT
INSERT INTO `{$db_prefix}plugin_wechat_autoreply` VALUES
('1', '使用帮助说明', '/^(帮助|bz|help)$/i', 'replyHelp', '1'),
('2', '天气预报', '/^(.+)天气$/i', 'replyWeather', '1'),
('3', '快递', '/^快递(.+)$/i', 'replyExpress', '1'),
('4', '彩票种类和查询码', '/^(彩票|caipiao|cp)$/i', 'replyLotteryList', '1'),
('5', '彩票开奖结果', '/^cp(.+)$/i', 'replyLotteryRes', '1'),
('6', '找周边', '/^找(.+)$/i', 'replyFind', '1'),
('7', '热门文章', '/^(热门|remen|rm)$/i', 'replyHot', '1'),
('8', '热门分类列表', '/^rm(.+)$/i', 'replyHotList', '1');
INSERT;

       $sql4=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名称',
  `password` varchar(64) NOT NULL COMMENT '用户密码',
  `mobile` varchar(20) NOT NULL COMMENT '用户手机号码用于找回密码',
  `openid` varchar(40) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号用户表';
SQL;
      
	  $sql5=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '游戏名称',
  `url` varchar(30) NOT NULL COMMENT '游戏地址',
  `clicks` varchar(20) NOT NULL DEFAULT 0 COMMENT '游戏被点击（被玩）次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号游戏表';
SQL;
     $sql6=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_user_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(11) NOT NULL COMMENT '玩家id',
  `gameId` varchar(11) NOT NULL COMMENT '游戏id',
  `score` varchar(20) NOT NULL DEFAULT 0 COMMENT '分数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号玩家和游戏表';
SQL;
     $sql7=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_courseware` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '课件名字',
  `url` varchar(30) NOT NULL COMMENT '课件地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号课件表';
SQL;
     $sql8=<<<SQL
CREATE TABLE `{$db_prefix}plugin_wechat_signin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(40) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `signInTime` int(10) NOT NULL DEFAULT 0 COMMENT '签到时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微信公众号签到表';
SQL;

        //D()->     
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_autoreply;");
        Db::execute($sql1);
        Db::execute($sql2);
        Db::execute($sql3);
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall(){//卸载方法必须实现
        $db_prefix = config('database.prefix');//C('DB_PREFIX');
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_user;");
        Db::execute("DROP TABLE IF EXISTS {$db_prefix}plugin_wechat_autoreply;");
        return true;//卸载成功返回true，失败false
    }

    //实现的wechat钩子方法
    public function wechat($param){
        $config=$this->getConfig();
        switch ($param['type']) {
            case 'connect':
                $options = array(
                    			'token'=>$config['Token'], //填写你设定的key
                    			'encodingaeskey'=>$config['EncodingAESKey'], //填写加密用的EncodingAESKey
                    			'appid'=>$config['AppID'], //填写高级调用功能的app id
                    			'appsecret'=>$config['AppSecret'] //填写高级调用功能的密钥
                    		);
                $weObj = new TpWechat($options);
                $weObj->valid();		
					
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
                case TpWechat::MSGTYPE_LOCATION:
                    /* 收到用户主动回复地理位置 */
                	$location = $weObj->getRev()->getRevGeo();
                	$judge = model('PluginWechatUser')->where(array('openid'=>$openid))->find();
                	if($judge){
                		model('PluginWechatUser')->where(array('id' => $judge['id']))->setField(array('latitude'=>$location['x'],'longitude'=>$location['y'],'labelname'=>$location['label']));
                	}else{
                		if($config['IsAuth'] == 0){
                			$user_data = array(
                					'subscribe' => 1,
                					'openid' => $openid,
                					'subscribe_time' => time(),
                					'latitude' => $location['x'],
                					'longitude' => $location['y'],
                					'labelname' => $location['label']
                			);
                		}else if($config['IsAuth'] == 1){
                			$user_data = $weObj->getUserInfo($openid);
                			$user_data['latitude'] = $location['x'];
                			$user_data['longitude'] = $location['y'];
                			$user_data['labelname'] = $location['label'];
                		}
                		model('PluginWechatUser')->add($user_data);
                	}
                    $weObj->text("您的最新位置已经更新,查询周边信息可回复'找xxx',比如'找ATM','找银行','找酒店','找厕所'等等,下次查询前记得先发位置再查询哟O(∩_∩)O~")->reply();
                    break;
           		case TpWechat::MSGTYPE_EVENT:
           		    $rev_event = $weObj->getRevEvent();
           		    /* 检测事件类型 */
           		    switch ($rev_event['event']){
           		        case TpWechat::EVENT_MENU_CLICK:
						$callback = 'http://www.shibin.tech/classManage/public/plugin/wechat/Index/index.html';
						$code = $weObj->getOauthRedirect($callback,'','snsapi_userinfo');
						$weObj->getOauthAccessToken();
						$weObj->text($code)->reply();
           		            //TODO:CLICK事件
           		            break;
           		        case TpWechat::EVENT_SUBSCRIBE:
           		            /* 如果公众号没有认证,则不能拉取用户信息 */
           		            if($config['IsAuth'] == 0){
           		                $user_data = array(
           		                    'subscribe' => 1,
           		                    'openid' => $openid,
           		                    'subscribe_time' => time()
           		                );
           		            }else if($config['IsAuth'] == 1){
           		                $user_data = $weObj->getUserInfo($openid);
								$user_data['uid'] = 0;
								$user_data['latitude'] = 0;
								$user_data['longitude'] = 0;
								$user_data['labelname'] = '';  
           		            }
           		            $judge = Db::name('PluginWechatUser')->where('openid',$openid)->find();
							
           		            if($judge){
           		                Db::name('PluginWechatUser')->where('id',$judge['id'])->save($user_data);
           		            }else{
           		                Db::name('PluginWechatUser')->insert($user_data);
           		            }
           		            /* 下推关注欢迎语 */
           		            $weObj->text($config['Welcome'])->reply();
           		            break;
       		            case TpWechat::EVENT_UNSUBSCRIBE:
       		                $judge = model('PluginWechatUser')->where(array('openid'=>$openid))->find();
       		                if($judge){
       		                    model('PluginWechatUser')->where(array('id' => $judge['id']))->setField(array('subscribe'=>0));
       		                }
       		                break;
       		            case TpWechat::EVENT_LOCATION:
       		                /* 认证号才有的功能 */
       		                $location = $weObj->getRev()->getRevEventGeo(); //获取上报地理位置
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