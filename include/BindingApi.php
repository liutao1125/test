<?php
/* *
 * @author zhangyong
 * 微信绑定API
 */
class BindingApi{
	
	  public static function login($account_username,$account_password,$username,$imgcode) {
		  
		  $token=md5($username);
		  $key1=md5($account_username).'cookie';						//定义cookie缓存
		  $key2=md5($account_username).'_token';						//定义_token缓存
		  //mc_unset($key1);
		  //mc_unset($key2);
		  if(mc_get($key1))
		  {
			  $cookie=mc_get($key1);
		  }
		  if(mc_get($key2))
		  {
			  $_token=mc_get($key2);
		  }
		  if(empty($_token))
		  {
			  $url = "https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
			  $referer="https://mp.weixin.qq.com/";
			  $data["username"]=$account_username;
			  $data["pwd"]=md5($account_password);
			  $data["f"]="json";
			  if(!empty($imgcode))
			  {
			  	 $data["imgcode"]=$imgcode;
				 $key4=md5($account_username).'imgcode_cookie';
				 if(mc_get($key4))
		 		 {
					$imgcode_cookie_arr=explode(';',mc_get($key4));
			  		$imgcode_cookie=$imgcode_cookie_arr[0];
		  			mc_unset($key4);
		  		 }
				 $re=self::curl_submit($url,$data,$referer,$cookie=false,$isPost=true,$imgcode_cookie);
			  }else{
			 	 $re=self::curl_submit($url,$data,$referer,$cookie=false);
			  }
			  $arr=json_decode($re['body'],true);
			  if(isset($arr['redirect_url'])&&!empty($arr['redirect_url']))
			  {
			  	$_arr1=explode("&token=", $arr['redirect_url']);
			  	$_arr2=explode("&phone=", $arr['redirect_url']);
			  }
			  if(isset($_arr2[1])&&!empty($_arr2[1]))
			  {
				  $rs['msg']='-1';
				  return $rs;
				  
			  }elseif( $arr['base_resp']['ret']=="0" && $_arr1[1]!=''){
				  $cookie = $re['cookie'];
				  $_token=$_arr1[1];
				  mc_set(md5($account_username).'cookie',$cookie,72000);	
				  mc_set(md5($account_username).'_token',$_token,72000);
			  }elseif( $arr['base_resp']['ret']=="-8"){
			  	 self::getVerifyCode($account_username);
				 $rs['msg']='-8';				//公众平台登录需要验证码
				 return $rs;
			  }elseif( $arr['base_resp']['ret']=="-27"){
			  	 self::getVerifyCode($account_username);
				 $rs['msg']='-27';				//公众平台登录验证码错误
				 return $rs;
			  }elseif( $arr['base_resp']['ret']=="-23"){
				 $rs['msg']='-23';				//公众平台登录验证码错误
				 mc_unset($key1);
		  		 mc_unset($key2);
				 return $rs;
			  }else{
				  $rs['msg']='1';				//公众平台登录用户名或密码错误
				  mc_unset($key1);
		  		  mc_unset($key2);
				  return $rs;
			  }
			
		  }
		  if(!empty($cookie)&&!empty($token)&&!empty($_token))
	      {
				  $rs=self::tobedev($cookie,$token,$_token,$account_username,$username);
				  return $rs;
		  }
	  }

	 /* *
	  * author zhangyong
	  * 绑定微信平台数据
	  */
	public static function tobedev($cookie,$token,$_token,$account_username,$username)
	 {
		  //$token=md5("yandandan");
		  //$data['url']="http://t.data.dodoca.com/wx/1/".$token."/";
		  //$data['url']="http://weixin.dodoca.com/main/index.php/Index/autocall/apiid/".$token."2013";
		  $data['callback_encrypt_mode']=0;
		  $data['callback_token']=$token;
		  $data['encoding_aeskey']=self::getencodingaeskey($cookie,$_token);
		  $data['operation_seq']='';
		  $data['url']=API_DOMAIN."/wx/1/".$token."/";
		  $referer="https://mp.weixin.qq.com/advanced/advanced?action=interface&t=advanced/interface&token=".$_token."&lang=zh_CN";
		  $url="https://mp.weixin.qq.com/advanced/callbackprofile?t=ajax-response&token=".$_token."&lang=zh_CN";
		  $re=self::curl_submit($url,$data,$referer,$cookie);
		  $arr=json_decode($re['body'],true);
		  
		  //绑定日志
		  $bandingmsg="cookie:".$cookie;
		  $bandingmsg.="======JSON:". json_encode($re);
		  $bandingmsg.="======msg:".$arr['ret'];
		  $bandingmsg.="======account_username:".$account_username;
		  $bandingmsg.="======username:".$username;
		  $bandingmsg.="======date:".date('Y-m-d H:i:s');
		  
		  PubFun::save_log($bandingmsg,'banding.log');
		  $arr['ret']=$arr['base_resp']['ret'];
		  if(isset($arr['ret']) && $arr['ret']=="0"){
			  $response=self::changedev($cookie,$token,$_token);		//提交绑定数据
			  //$rs["msg"]="恭喜你，你的微信公共账号已经成功绑定到我们的平台";
			  $rs["msg"]='2';
		  }else if($arr['ret']=="-204"){
			  //$rs["msg"]="资料不全，无法继续 你需要添加公众号头像、描述和运营地区后才能继续下一步。";
			  $rs["msg"]='-204';
		  }else if($arr['ret']=="-301"){
			  //$rs["msg"]="连接服务器超时，请稍后重试";
			  $rs["msg"]='-301';
		  }else if($arr['ret']=="-302"){
			  //$rs["msg"]="你的服务器没有正确响应Token验证，请阅读使用指南";
			  $rs["msg"]='-302';
		  }else if($arr['ret']=="-206"){
			  $rs["msg"]='-206';
		  }else if($arr['ret']=="-1"){
			  $rs["msg"]='-1';
		  }else{
			  //$rs["msg"]="你的公共账号信息还未通过审核，暂时不能申请绑定";
			  $key1=md5($account_username).'cookie';						//定义cookie缓存
			  $key2=md5($account_username).'_token';						//定义_token缓存
			  mc_unset($key1);
		  	  mc_unset($key2);
		  	  $rs["msg"]='3';
		  }
		  return $rs;
	  }

	/* *
	 *  author zhangyong
	 *  开启开发模式，提交绑定数据
	 */
	 public static function changedev($cookie,$token,$_token){
			$data['flag']="1";
			$data['type']="2";
			$data['token']=$_token;
			$referer="https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=".$_token."&lang=zh_CN";
			$url="https://mp.weixin.qq.com/misc/skeyform?form=advancedswitchform&lang=zh_CN";
			$re=self::curl_submit($url,$data,$referer,$cookie);
			$response=json_decode($re['body'],true);
			return $response;
	 }

	 /* *
	  * author zhangyong
	  * 获取用户信息（fakeid、姓名、二维码）
	  */
	  public static function getuserinfo($account_username,$username){
		    $userinfo=array();	
			$key1=md5($account_username).'cookie';						//定义cookie缓存
		    $key2=md5($account_username).'_token';						//定义_token缓存
			$token=md5($username);
			$cookie=mc_get($key1);
			$_token=mc_get($key2);
			$referer="https://mp.weixin.qq.com/cgi-bin/home?t=home/index&lang=zh_CN&token={$_token}";
			$url="https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&token={$_token}&lang=zh_CN";
			$re=self::curl_submit($url,false,$referer,$cookie,false);
			$fakeid=self::getTextArea($re['ALL'],'uin:"', '",' );
			$nickname=self::getTextArea($re['ALL'],'class="nickname">','</a>' );
			$qrcode=self::getimg($fakeid,$cookie,$token,$_token);
			$headimg=self::getheadimg($fakeid,$cookie,$token,$_token);
			$appinfor=self::getAppidAppSecret($cookie,$token,$_token);
			if($fakeid&&$username&&$qrcode&&$headimg)
			{
				$rs["msg"]='4';
				$rs['fakeid']=$fakeid;
				$rs['nickname']=$nickname;
				$rs['erweima']=$qrcode['id'];
				$rs['logo_img']=$headimg['org'];
				$rs['appid']=$appinfor['appid'];
				$rs['appsecret']=$appinfor['appsecret'];
				return $rs;
			}else{
				$rs["msg"]='5';
				return $rs;
			}
		}

	   /* *
		* author zhangyong
		* 获取二维码图片并保存在本地
		*/
		public static function getimg($fakeid,$cookie,$token,$_token){
			$referer="https://mp.weixin.qq.com/cgi-bin/settingpage?t=setting/index&action=index&token={$_token}&lang=zh_CN";
			$url="https://mp.weixin.qq.com/misc/getqrcode?fakeid={$fakeid}&token={$_token}&style=1&action=download";
			$re=self::curl_submit($url,false,$referer,$cookie,false);
			$match=self::getTextArea($re['cookie'],'slave_user=', ';' );
			if($match) {
			  	$file_name="qrcode_for_".$match."_430.jpg";
			  	//$file_url=$_SERVER["SINASRV_UPLOAD"].'/'.$file_name;
				$file_url=$_SERVER["SINASRV_DATA_TMP"].'/'.$file_name;
			  	file_put_contents($file_url,$re['body']);
			  	if(is_file($file_url))
              	{
            	 	$pic=new PicData();
            	 	//$pic_info=$pic->upload_file($file_url,1,430,430);
					$pic_info=$pic->post_file($file_url);
			  	}
			  	return $pic_info;
			}
		}
		
			
	 /* *
	  * author zhangyong
	  * 获取用户头像
	  */
        public static function getheadimg($fakeid,$cookie,$token,$_token){
			$referer="https://mp.weixin.qq.com/cgi-bin/settingpage?t=setting/index&action=index&token={$_token}&lang=zh_CN";
			$url="https://mp.weixin.qq.com/misc/getheadimg?fakeid={$fakeid}&token={$_token}";
			$re=self::curl_submit($url,false,$referer,$cookie,false);
			$match=self::getTextArea($re['cookie'],'slave_user=', ';' );
			if($match) {
			  	$file_name="headimg_for_".$match."_430.jpg";
			  	//$file_url=$_SERVER["SINASRV_UPLOAD"].'/'.$file_name;
				$file_url=$_SERVER["SINASRV_DATA_TMP"].'/'.$file_name;
			  	file_put_contents($file_url,$re['body']);
			  	if(is_file($file_url))
              	{
            	 	$pic=new PicData();
            	 	//$pic_info=$pic->upload_file($file_url,1,430,430);
					$pic_info=$pic->post_file($file_url);
			  	}
			  	return $pic_info;
			}
		}
		
		/* *
		* author zhangyong
		* 获取用户信息
		*/
		public static function getTextArea($text,$str_start,$str_end){
			if(empty($text)||empty($str_start))
			{
				return false;
			}
			$start_pos=@strpos($text,$str_start);
			if($start_pos===false){
				return false;
			}
			$end_pos=strpos($text,$str_end, $start_pos);
			if($end_pos>$start_pos && $end_pos!==false)
			{
				$begin_pos=$start_pos+strlen($str_start);
				return substr($text, $begin_pos,$end_pos-$begin_pos);
			}
			else
			{
				return false;
			}
		}
		
	 /* *
	  * author zhangyong
	  * 获取App、AppSecret
	  */
        public static function getAppidAppSecret($cookie,$token,$_token){
			$referer="https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=".$_token."&lang=zh_CN";
			$url="https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=".$_token."&lang=zh_CN&f=json";
			$re=self::curl_submit($url, false, $referer,$cookie,false);
			$AppId = self::getTextArea($re['body'],'"app_id":"', '",' );
			$AppSecret = self::getTextArea($re['body'],'"app_key":"', '",' );
			$appinfor=array("appid" => trim($AppId), "appsecret" => trim($AppSecret));
			$bandingmsg.="======AppId:".$AppId;
			$bandingmsg.="======AppSecret:".$AppSecret;
			$bandingmsg.="======Date:".date('Y-m-d H:i:s');
		    PubFun::save_log($bandingmsg,'bandingapp.log');
			return $appinfor;
		}

		 /* *
	  * author zhangyong
	  * 获取验证码
	  */
        public static function getVerifyCode($account_username){
			 $time = explode ( " ", microtime () );  
			 $time = $time [1] . ($time [0] * 1000);  
			 $time2 = explode ( ".", $time );  
			 $time = $time2 [0];  
			 $referer="https://mp.weixin.qq.com/";
			 $url = "https://mp.weixin.qq.com/cgi-bin/verifycode?username=".$account_username."&r=".$time."";
			 $re2= self::curl_submit($url,false,$referer,$cookie=false);
			 
			 $key4=md5($account_username).'imgcode_cookie';
			 $imgcode_cookie=$re2['cookie'];
			 mc_set($key4,$imgcode_cookie,72000);
				
			 $file_url=$_SERVER["SINASRV_DATA_TMP"].'/'.md5($account_username).'verifycode.jpg';
			 file_put_contents($file_url,$re2['body']);
			 if(is_file($file_url))
	      	 {
	    	 	$pic=new PicData();
	    	 	//$pic_info=$pic->upload_file($file_url,1,430,430);
				$pic_info=$pic->post_file($file_url);
				$key3=md5($account_username).'imgcode';
				mc_set($key3,$pic_info['org'],72000);
		  	 }
		}
		
		
		 /* *
	  * author zhangyong
	  * 获取EncodingAESKey
	  */
		public static function getencodingaeskey($cookie,$_token){
			$url = "https://mp.weixin.qq.com/advanced/advanced?action=randomkey&token=".$_token."&lang=zh_CN&f=json&ajax=1";
			$referer="https://mp.weixin.qq.com/advanced/advanced?action=interface&t=advanced/interface&token=".$_token."&lang=zh_CN";
			$re= self::curl_submit($url,$data,$referer,$cookie,$isPost=false);
			$re=json_decode($re['body'],true);
			return $re['encoding_aeskey'];
		}
   /* *
	* author zhangyong
	* 模拟数据
	*/
	public static function curl_submit($url,$data,$referer,$cookie=false,$isPost=true,$imgcode_cookie=false) {
		$dataStr = "";
		if($data && is_array($data)) {
			foreach($data as $key => $value) {
				$dataStr .= "$key=$value&";
			}
		}else $dataStr.=$data;
		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		// curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		
		// $oldReferer = "https://mp.weixin.qq.com/";
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Referer:$referer"));
		
		if($isPost) {
			curl_setopt($curl, CURLOPT_POST, 0); // 发送一个常规的Post请求
			curl_setopt($curl, CURLOPT_POSTFIELDS, $dataStr); // Post提交的数据包
		}
		
		curl_setopt($curl, CURLOPT_TIMEOUT, 120); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		// curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie.txt');
		// curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
		// curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
		// curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
		
		if($cookie) {
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		}
		
		if($imgcode_cookie) {
			curl_setopt($curl, CURLOPT_COOKIE, $imgcode_cookie);
		}
		
		$tmpInfo = curl_exec($curl); // 执行操作
		//print_r($tmpInfo);exit;
		if (curl_errno($curl)) {
			echo 'Errno'. curl_error($curl);//捕抓异常
			return;
		}
		
		curl_close($curl); // 关闭CURL会话
		// 解析HTTP数据流
		list($header, $body) = explode("\r\n\r\n", $tmpInfo);
		
		if(!$cookie) {
			// 解析COOKIE
			$cookie = "";
			preg_match_all("/Set\-cookie: (.*)/i", $header, $matches);
			if(count($matches == 2)) {
				foreach($matches[1] as $each) {
					$cookie .= trim($each). ";";
				}
			}
		}
		$rs=array("cookie" => $cookie, "body" => trim($body),"ALL"=>trim($tmpInfo));
		return $rs;
	}

}
?>
