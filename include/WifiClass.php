<?php
error_reporting(0);
class  WifiClass
{
	static public function getcodeAction($openid,$wifiVerifyCode,$uid)
	{

		$msgId = sprintf("%u",crc32(self::create_uuid()));
		$platId = $GLOBALS['WIFI_CONFIG']['PLATID'];
		$key = $GLOBALS['WIFI_CONFIG']['DES_KEY'];

		//微信号：premier-capitalSH
		//$guid = "gh_1679f677066c";
		//通过 uid 去获得 guid
		
		$guidObj = new WxWifiInfo();
		$guid = $guidObj->m_get_data($uid);
		$guid = $guid["guid"];
		
		//PubFun::save_log('uid ->'.$uid.',guid->'.$guid,'wx_wifi');
		$ArrayData["guid"] = $guid;
		$ArrayData["wifiVerifyCode"] = $wifiVerifyCode;
		$ArrayData["openid"] = $openid;

		$urlData = json_encode($ArrayData);
		$urlData = self::mencrypt($urlData,$key);
		
		$urlData = rawurlencode($urlData);
		$postData = "param=$urlData&msgId=$msgId&platId=$platId";
		
		$url = $GLOBALS['WIFI_CONFIG']['GET_WIFI_VERIFY_URL'];

		$response =self::wifi_exec($url,$postData);

		PubFun::save_log('response ->'.$response.',postData->'.$postData,'wx_wifi');

		$res = json_decode($response,true);
		if($res["msgId"] = $msgId)
		{
			$res = $res["result"];
			$res = self::mdecrypt($res,$key);
			$flag = json_decode($res,true);
			PubFun::save_log('result ->'.$flag,'wx_wifi');
			$flagCode = $flag["resultCode"];
			if ($flagCode != 1)
			{
				$contentStr = "上网失败了哦..再次发送试试吧";
				return $contentStr;
			} else {
				$contentStr = "上网成功";
				return $contentStr;
			}
		}

	}

	/**
	 * [mencrypt 获取Des加密结果]
	 * @param  [string] $input [加密字符串]
	 * @param  [string] $key   [加密key]
	 * @return [string]        [加密结果]
	 */
	static private function mencrypt($encrypt,$key="") {
		$encrypt =self::pkcs5_pad($encrypt);

    $iv = mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_DES, MCRYPT_MODE_ECB ), MCRYPT_RAND );
    $passcrypt = mcrypt_encrypt ( MCRYPT_DES, $key, $encrypt, MCRYPT_MODE_ECB, $iv );
    $encode = base64_encode ( $passcrypt );
    //return bin2hex($encode);
    return $encode;
    }
   
  /**
   * 
	 * [mencrypt 获取Des解密结果]
	 * @param  [string] $input [解密字符串]
	 * @param  [string] $key   [解密key]
	 * @return [string]        [解密结果]
	 */
  static private function mdecrypt($decrypt,$key="")
  {
    $decoded = base64_decode ( $decrypt );
    $iv = mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_DES, MCRYPT_MODE_ECB ), MCRYPT_RAND );
    $decrypted = mcrypt_decrypt ( MCRYPT_DES, $key, $decoded, MCRYPT_MODE_ECB, $iv );
    return self::pkcs5_unpad($decrypted);
	}

	
	/**
	 * [pkcs5_pad 获取Pkcs5_padding填充结果]
	 * @param  [string] $text [要填充的字符串]
	 * @return [string]       [填充后的字符串]
	 * @author [Primo.chu] <[zhukejin@dodoca.com]>
	 */
	static private function pkcs5_pad($text)
	{
		$len = strlen($text);
		$mod = $len % 8;
		$pad = 8 - $mod;
		return $text.str_repeat(chr($pad),$pad);
	}

	/**
	 * [pkcs5_pad 获取Pkcs5_padding填充结果]
	 * @param  [string] $text [要填充的字符串]
	 * @return [string]       [填充后的字符串]
	 */
	static private function pkcs5_unpad($text)
	{
		$pad = ord($text{strlen($text)-1});

		if ($pad > strlen($text)) return $text;
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return $text;
		return substr($text, 0, -1 * $pad);
	}

   /**
    * [create_uuid 获取UUID]
    * @param  string $prefix [自定义前缀]
    * @return [string]         [uuid结果]
    */
   static private function create_uuid($prefix = "")
   {
    $str = md5(uniqid(mt_rand(), true));   
    $uuid  = substr($str,0,8) . '-';   
    $uuid .= substr($str,8,4) . '-';   
    $uuid .= substr($str,12,4) . '-';   
    $uuid .= substr($str,16,4) . '-';   
    $uuid .= substr($str,20,12);   
    return $prefix . $uuid;
	}

	/**
	 * [wifi_exec 通过CURL模拟HTTP请求]
	 * @param  [string]  $url    [要请求的地址]
	 * @param  [string]  $data   [要请求的数据]
	 * @param  boolean $isPost [是否模拟post请求]
	 * @return [返回结果]
	 */
	static private function wifi_exec($url, $data, $isPost = true)
	{
		$curl = curl_init (); // 启动一个CURL会话
		curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
		curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
		curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 ); // 自动设置Referer
		                                            
		// $oldReferer = "https://mp.weixin.qq.com/";
		// curl_setopt($curl, CURLOPT_HTTPHEADER, array("Referer:$referer"));
		
		if ($isPost) {
			curl_setopt ( $curl, CURLOPT_POST, 0 ); // 发送一个常规的Post请求
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data ); // Post提交的数据包
		}
		
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
		curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
		
		$tmpInfo = curl_exec ( $curl ); // 执行操作
		                             // print_r($tmpInfo);exit;
		if (curl_errno ( $curl )) {
			echo 'Errno' . curl_error ( $curl ); // 捕抓异常
			
			return;
		}
		
		curl_close ( $curl ); // 关闭CURL会话
		                   // 解析HTTP数据流
		return ($tmpInfo);
	}
}