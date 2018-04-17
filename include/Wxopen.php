<?php
include_once("Wxopen/wxBizMsgCrypt.php");
//微信开放平台
class Wxopen {
	//测试环境
    private $token;
	private $encodingAesKey;
	private $cryptmsg; //密文
	private $decryptmsg ; //明文
	private $MsgSignature ;
	private $component_appid ;
	private $component_appsecret;
	private $component_access_token ;//服务令牌
	private $version; //生成环境:sc 测试环境:test
	function __construct($type="strike"){
		//生产、测试环境
		if(SYS_RELEASE=='1'){
			$this->version = "test";
		}else{
			$this->version = "sc";
		}
		$componentconfig =  $GLOBALS["Component_Config"][$this->version];
		$this->token = $componentconfig["token"];
		$this->encodingAesKey = $componentconfig["encodingAesKey"];
		$this->component_appid = $componentconfig["component_appid"];
		$this->component_appsecret = $componentconfig["component_appsecret"];
		$pc = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->component_appid);
		$this->MsgSignature = $_GET["msg_signature"];
		$timeStamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$this->cryptmsg = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($this->cryptmsg)&&$type="strike"){
			$msg = '';
			$errCode = $pc->decryptMsg($this->MsgSignature,$timeStamp, $nonce, $this->cryptmsg, $msg);
			if ($errCode == 0) {
				$this->decryptmsg = $msg;
				$postObj = simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA);
				$this->api_component_token((array)$postObj); //处理服务令牌
			} else {
				//PubFun::save_log("errcode=>".$errCode,"wx_service");
				//print_r($errCode);
			}
		}
		$component_access_token = mc_get("component_access_token_".$this->component_appid);
	    if($component_access_token){
	    	$this->component_access_token = $component_access_token;
	    }else{
	    	$authorTicket = new AuthorizationVerifyTicket();
	    	$component_appid = $this->component_appid;
	    	$this->component_access_token = $authorTicket->scalar("component_access_token"," where appid='{$component_appid}'");
	        unset($authorTicket);
	    }
	    
	}
	public function __get($property_name){
		if (isset ( $this->$property_name )) {
			return ($this->$property_name);
		} else {
			return (NULL);
		}
	}
    //对消息进行加密
	public function get_cryptmsg($replyMsg){
		$pc = new WXBizMsgCrypt($this->token,$this->encodingAesKey,$this->component_appid);
		$timeStamp = time();
		$nonce = $this->createNoncestr(32);
		$encryptMsg='';
		$errCode = $pc->encryptMsg($replyMsg, $timeStamp,$nonce, $encryptMsg);
		if ($errCode == 0) {
			return $encryptMsg;
		}else{
			return false;
		}
	}
	//获取服务令牌
	public function api_component_token($postObj){
		$InfoType = $postObj["InfoType"];
		if($InfoType == "component_verify_ticket"){
			$data["component_appid"] = $this->component_appid;
			$data["component_appsecret"] = $this->component_appsecret;
			$data["component_verify_ticket"] = $postObj["ComponentVerifyTicket"];
			$ret = $this->oAuthRequest("https://api.weixin.qq.com/cgi-bin/component/api_component_token",$data,true);
			$retdata = json_decode($ret,true);
		    $ticket = new AuthorizationVerifyTicket();
		    if(!empty($retdata["component_access_token"])){
		    	$appid = $this->component_appid;
		    	$authdata["component_verify_ticket"] = $postObj["ComponentVerifyTicket"];
		    	$authdata["component_access_token"] = $retdata["component_access_token"];
		    	$ticket->update_data($authdata, " where appid='{$appid}'");
		    	mc_set("component_access_token_".$this->component_appid,$retdata["component_access_token"],3600);
		    	mc_set("component_verify_ticket_".$this->component_appid,$postObj["ComponentVerifyTicket"],3600);
		    }
		}
		//处理取消授权
		if($InfoType == "unauthorized"){
			$wxuseraccount = new WxUserAccount();
			$accountdata["status"] = -1;
			$accountdata["is_band"] = 0;
			$accountdata["is_auth"] = 0;
			$AuthorizerAppid =  trim($postObj["AuthorizerAppid"]);
			$wxuseraccount->update($accountdata," where `appid`='$AuthorizerAppid'");
		}
	}
	//获取预授权码
	public function api_create_preauthcode($pagename="bingdingindex"){
		$redirect_uri = "http://t.sxzx.dodoca.com/auth/".$pagename;
		if($this->version == "sc"){
			$redirect_uri = "http://www.sxzx.com/auth/".$pagename;
		}
		if(!empty($this->component_access_token)){
			$data["component_appid"] = $this->component_appid;
			$apiurl ="https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=";
			$ret = $this->oAuthRequest($apiurl.$this->component_access_token,$data,true);
			$retdata = json_decode($ret,true);
			if(!empty($retdata["pre_auth_code"])){
				return "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=".$this->component_appid."&pre_auth_code=".$retdata["pre_auth_code"]."&redirect_uri=".$redirect_uri;
			}else{
				return false;
			}
		}
		return false;
	}
	//获取授权公众号的信息
	public function api_query_auth($authorization_code){
		if(!empty($this->component_access_token)){
			$data["component_appid"] = $this->component_appid;
			$data["authorization_code"] = $authorization_code;
			$apiurl = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=";
			$ret = $this->oAuthRequest($apiurl.$this->component_access_token,$data,true);
			$retdata = json_decode($ret,true);
			if(!empty($retdata["authorization_info"]["authorizer_appid"])){
				return $retdata;
			}else{
				return false;
			}
		}
		return false;
	}
	//获取授权方的账户信息
	public function api_get_authorizer_info($authorizer_appid){
		if(!empty($this->component_access_token)){
			$data["component_appid"] = $this->component_appid;
			$data["authorizer_appid"] = $authorizer_appid;
			$apiurl = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=";
			$ret = $this->oAuthRequest($apiurl.$this->component_access_token,$data,true);
			$retdata = json_decode($ret,true);
			if(!empty($retdata["authorizer_info"]["nick_name"])){
				return $retdata;
			}else{
				return false;
			}
		}
		return false;
	}
	/*
	 * 刷新授权方令牌
	 */
	
	public function api_authorizer_token($authorizer_appid,$authorizer_refresh_token){
		if(!empty($this->component_access_token)){
			$data["component_appid"] = $this->component_appid;
			$data["authorizer_appid"] = $authorizer_appid;
			$data["authorizer_refresh_token"] = $authorizer_refresh_token;
			$apiurl = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=";
			$ret = $this->oAuthRequest($apiurl.$this->component_access_token,$data,true);
			$retdata = json_decode($ret,true);
			if(!empty($retdata["authorizer_access_token"])){
				return $retdata;
			}else{
				return $retdata;
			}
		}
		return false;
	}
	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr( $length = 32 )
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		}
		return $str;
	}
	public function oAuthRequest($url,$data,$is_post=false,$data_type="json"){
		$ch = curl_init();
		if($is_post==false){  //get 请求
			$url =  $url.'?'.http_build_query($data);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $is_post);
		if($is_post){
		    if($data_type=="json"){
				$this_header = array("Content-Type:application/json;encoding=utf-8");
				curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
				$data = json_encode($data);
			}else{
				$data = http_build_query($data);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		$info = curl_exec($ch);
		curl_close($ch);
		return $info;
	}
	
}
