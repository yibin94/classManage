<?php

namespace plugins\wechat\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\wechat\model\PluginWechatModel;
use think\Validate;
use think\Db;

class UploadCoursewareController extends PluginBaseController{
    function index(){
		echo request()->post('media_id').'--'.request()->param('media_id');
	    $media_id = request()->param('media_id');
		$obj = new CommonValidationController();
		$weObj = $obj->getWeObj();
		//通过code换取网页授权access_token
		$res = $weObj->getOauthAccessToken();
	    //根据微信JS接口上传了图片,会返回上面写的images.serverId（即media_id），填在下面即可  
		 $getDataUrl = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$res['refresh_token']."&media_id=$media_id";  
		 //获取微信"获取临时素材"接口返回来的内容（即刚上传的图片数据）  
		 $data = file_get_contents($getDataUrl);  
		 //__DIR__指向当前执行的PHP脚本所在的目录  
		 echo __DIR__;
		 //以读写方式打开一个文件，若没有，则自动创建  
		 $resource = fopen(__DIR__."/".time().".jpg" , 'w+');  
		 //将图片内容写入上述新建的文件  
		 fwrite($resource, $data);  
		 //关闭资源  
		 fclose($resource);
		
		return ;
	}
}	