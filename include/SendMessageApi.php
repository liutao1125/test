<?php
/* *
 * @author zhangyong
 * 发送短信API
 */
class SendMessageApi{
	  //$accept_mobile		接收方
	  //$content 					发送内容
	  public static function send_sms($mobile,$content){	
	  
// 	  			$postData = "account=weihuoban&pswd=32@PlYjFrMbGy@10&mobile=".urlencode($mobile)."&msg=".urlencode($content)."&needstatus=true&extno=2013";
	  			$postData = "account=3348305788@qq.com&pswd=ddk123&mobile=".urlencode($mobile)."&msg=".urlencode($content)."&needstatus=true&extno=2013";			
				$ua = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11 AlexaToolbar/alxg-3.1";
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://175.102.15.131/msg/HttpSendSM");
				curl_setopt($ch, CURLOPT_REFERER, "http://mp.weixin.qq.com/cgi-bin/loginpage?t=wxm-login&lang=zh_CN");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, $ua);
				$response = curl_exec($ch);
				return $response;
	  }
}
?>
