<?php
/**
 *
 * 处理微信数据
 */
class WeiXinV2
{
	private $Token;
	private $AppId;
	private $AppSecret;
	private $uid;
	private $from_type;
	private $openid;
	private $dev_user_name;
	private $fans_id;
	private $userinfo;
	private $wxopen;
	private $is_auth=false; //是否是授权公众号
	private $authorizer_access_token; //是否是授权公众号
	private $authorizer_refresh_token;//刷新令牌
	public function __construct($AppId){
		$this->AppId = $AppId;
		$this->wxopen = new Wxopen("justdecrypt");
		$this->is_auth = true;
	}
	/**
	 * 微信请求响应地址
	 */
	public function responseMsg()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
			$log=new WxMsg();
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = trim($postObj->FromUserName);
            $data["fromusername"]=str_replace("'","",$fromUsername);
            $data["tousername"] = trim($postObj->ToUserName);
            $keyword = $postObj->Content?$postObj->Content:'';
            $data["content"]=trim($keyword);
            $data["msgtype"]=trim($postObj->MsgType);
            $data["picurl"]=trim($postObj->PicUrl );
            $data["event"] = trim($postObj->Event?$postObj->Event:'');
            $data["eventkey"] = trim($postObj->EventKey?$postObj->EventKey:'');
            $data["createtime"]=trim($postObj->CreateTime);
            $data["msgid"]=trim($postObj->MsgId);
            $call_msg='';
            $msgid=$data["msgid"];
			
            $this->openid=$data["fromusername"];
            $this->dev_user_name=$data["tousername"];
            switch($data["msgtype"])
            {
            	case "text"://推送文本消息
            		if("TESTCOMPONENT_MSG_TYPE_TEXT"==$data["content"]){
            			$this->call_msg("TESTCOMPONENT_MSG_TYPE_TEXT_callback");
            		}
            		elseif(strstr($data["content"],"QUERY_AUTH_CODE")){
            			$query_auth_code = str_replace("QUERY_AUTH_CODE:","",$data["content"]);
            			$authinfo = $this->wxopen->api_query_auth($query_auth_code);
            		    $authorizer_access_token = $authinfo["authorization_info"]["authorizer_access_token"];
            		    $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$authorizer_access_token;
            		    $kfmsg = $this->get_reply_msg($this->openid,$query_auth_code."_from_api");
            		    $this->sub_curl($url,$kfmsg);
            		}
            		break;
            	case "image":
            		break;
            	case "event"://事件推送
            		$this->call_msg($postObj->Event."from_callback");
            		break;
            	case "location":
            		break;
            	case "voice":
            		break;
            }  
            exit;
		}else {
			echo "no post data!";
			exit;
		}
	}
	/**
	 * 返回文本信息
	 */
	public function call_msg( $keyword='', $msgType='text')
	{
		
		if($keyword)
		{
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>
						</xml>";
			$time = time();
			$msgType = "text";
			$resultStr = sprintf($textTpl, $this->openid, $this->dev_user_name, $time, $msgType, $keyword);
			if($this->is_auth){
				echo $this->wxopen->get_cryptmsg($resultStr);
			}else{
				echo $resultStr;
			}
		}
	}
	/**
	 * 客服回复
	 */
	public function get_reply_msg($openid,$msg,$msg_type='text')
	{
		$textTpl = '{
					    "touser":"%s",
					    "msgtype":"%s",
					    "%s":
					    {
					         "content":"%s"
					    }
					}';
		$textTpl = sprintf($textTpl, $openid, $msg_type, $msg_type, $msg);
		return $textTpl;
	}
	public function sub_curl($url,$data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$info = curl_exec($ch);
		$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		//PubFun::save_log('$url->'.$url.',uid->'.$this->uid.',code->'.$code,'wx_log');
		curl_close($ch);
		return $info;
	}
}
?>
