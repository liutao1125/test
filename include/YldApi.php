<?php
/**
 * 印立得API
 */
class YldApi {
	
	/* private static $login_url = "http://portal.weixinprint.cn/singlelogin?";
	private static $sync_user_url = "http://203.195.135.229:8099/sync/syncuser";
	private static $sync_publicinfo_url = "http://203.195.135.229:8099/sync/syncpublicinfo";
	//正式环境http://inleaderdev.weixinprint.com/weixin?third_id=
	private static $yld_url = "http://inleader.weixinprint.cn/weixin?third_id=";
	private static $appid = "ddk";
	private static $token = "123456789"; */
	
    private static $login_url = "http://print.dodoca.com/singlelogin?";
	//private static $sync_user_url = "http://203.195.140.140:8099/sync/syncuser";
	//private static $sync_publicinfo_url = "http://203.195.140.140:8099/sync/syncpublicinfo";
	
	private static $sync_user_url = "http://open.weixinprint.com/sync/syncuser";
	private static $sync_publicinfo_url = "http://open.weixinprint.com/sync/syncpublicinfo";
	//正式环境http://inleaderdev.weixinprint.com/weixin?third_id=
	private static $yld_url = "http://inleaderdev.weixinprint.com/weixin?third_id=";
	private static $appid = "ddk";
	private static $token = "95wubk7b7a";
	
	/**
	 * 构造单点登录url
	 */
	public function sysoLogin($username){
		if(empty($username)){
			return false;
			exit();
		}
		$param=array();
		$param["timestamp"]=number_format(microtime(true),3,'','');
		$param["nonce"]=$this->create_noncestr();
		$signature=$this->generalSignature($param);
		$param["signature"]=$signature;
		$param["AppID"]=self::$appid;
		$param["loginName"]=$username;
		return self::$login_url.http_build_query($param);
	}
	
	/**
	 * 单点登录同步接口
	 * string $usernames 登录名(aaa,bbb,ccc)用逗号分开 限制30个
	 * string $appid 应用Id
	 * 成功返回true
	 * 失败返回错误消息
	 */
	public function ssoSysc($usernames){
		if(empty($usernames)){
			return false;
			exit();
		}
		$param=array();
		$param["timestamp"]=number_format(microtime(true),3,'','');
		$param["nonce"]=$this->create_noncestr();
		$signature=$this->generalSignature($param);
		$param["signature"]=$signature;
		$param["AppID"]=self::$appid;
		$param["usernames"]=$usernames;
		$ret = $this->getHttpResponsePOST(self::$sync_user_url,http_build_query($param));
		$jsondata = json_decode($ret,true);
		//s($param);
		//s($jsondata);
		if($jsondata["result"]=="vertifysuccuss"||$jsondata["result"]=="usesenameexist"){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 
	 * @param $username 用户名称
	 * @param $publicusername 微信名称
	 * @param $weixin_token 微信token
	 * @param $weixin_code_img 二维码图片
	 * @return boolean|mixed
	 */
	public function syncPublicInfo($username,$publicusername,$weixin_token,$weixin_code_img,$weixin_id){
		$param=array();
		$param["timestamp"]=number_format(microtime(true),3,'','');
		$param["nonce"]=$this->create_noncestr();
		$signature=$this->generalSignature($param);
		$param["signature"]=$signature;
		$param["AppID"]=self::$appid;
		$param["weixin_token"]=$weixin_token;
		$param["publicusername"]=$publicusername;
		$param["username"]=$username;
		$param["weixin_code_img"]=$weixin_code_img;
		$param["weixin_id"]=$weixin_id."_".self::$appid;
		$ret = $this->getHttpResponsePOST(self::$sync_publicinfo_url,http_build_query($param));
		$jsondata = json_decode($ret,true);
		//s($param);
		//s($jsondata);
		 if($jsondata["result"]=="success"){
			return self::$yld_url.$param["weixin_id"];
		}else{
			return false;
		}
	}
	/**
	 * sha1加密参数
	 * @param array $param
	 * @return string
	 */
    public function generalSignature($param){
		$buff = "";
		$timestamp = $param["timestamp"];
		$token=self::$token;
		$nonce = $param["nonce"]; 
		$tmpArr = array($token, $timestamp, $nonce);
		// use SORT_STRING rule
	    sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		//s($tmpStr);
		$signature = strtolower(sha1( $tmpStr ));
		return $signature;
	}
	protected function create_noncestr( $length = 8 ) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
			//$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}
		return $str;
	}
	/**
	 * 远程获取数据，POST模式
	 * 注意：
	 * @param $url 指定URL完整路径地址
	 * @param $para 请求的数据
	 * return 远程输出的数据
	 */
	function getHttpResponsePOST($url, $para) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		return $responseText;
	}
	
	/**
	 * 远程获取数据，GET模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * return 远程输出的数据
	 */
	function getHttpResponseGET($url,$cacert_url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
	
		return $responseText;
	}
	
	 public function getcode($userid){
		$param["timestamp"]=number_format(microtime(true),3,'','');
		$param["nonce"]=$this->create_noncestr();
		$wxuser = new WxUser();
    	$wxuserinfo = $wxuser->get_row_byid($userid);
    	$userkey = $wxuserinfo["userkey"];
		$tmpArr = array($userkey, $param["timestamp"], $param["nonce"]);
	    sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$signature = strtolower(sha1( $tmpStr ));
		$param["signature"]=$signature;
		return $param;
	}
}