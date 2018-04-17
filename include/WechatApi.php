<?php
// 微信接口SDK
class WechatApi {
	// 接口网关地址
	const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
	const MEDIA_GET_URL = '/media/get?';
	const GROUP_GET_URL = '/groups/get?';
	const USER_GROUP_URL = '/groups/getid?';
	const GROUP_CREATE_URL = '/groups/create?';
	const GROUP_UPDATE_URL = '/groups/update?';
	const GROUP_MEMBER_UPDATE_URL = '/groups/members/update?';
	const CUSTOM_SEND_URL = '/message/custom/send?';
	const MEDIA_UPLOADNEWS_URL = '/media/uploadnews?';
	const MASS_SEND_URL = '/message/mass/send?';
	const MASS_PREVIEW_URL = '/message/mass/preview?';
	const TEMPLATE_SEND_URL = '/message/template/send?';
	const MASS_SEND_GROUP_URL = '/message/mass/sendall?';
	const MASS_DELETE_URL = '/message/mass/delete?';
	const UPLOAD_MEDIA_URL = 'http://file.api.weixin.qq.com/cgi-bin';
	const MEDIA_UPLOAD = '/media/upload?';
	const SEMANTIC_API_URL = 'https://api.weixin.qq.com/semantic/semproxy/search?';
	// appid
	private $appid;
	// appsecret
	private $appsecret;
	// 授权token
	private $access_token;
	// 当前登陆用户uid
	private $uid;
	function __construct($uid = false, $access_token = true) {
		if ($uid) {
			$this->uid = $uid;
		} else {
			$this->uid = get_uid ();
		}
		$useraccount = new WxUserAccount ();
		$userinfo = $useraccount->scalar ( "appid,appsecret", " where userid=" . $this->uid );
		if ($userinfo) {
			$this->appid = $userinfo ["appid"];
			$this->appsecret = $userinfo ["appsecret"];
			if ($access_token) {
				$tokendata = $this->get_access_token ();
				if (is_array ( $tokendata )) {
					return $tokendata ["errmsg"];
				} else {
					$this->access_token = $tokendata;
				}
			}
		} else {
			return false;
		}
	}
	 /**
      * 获取access_token
      * @return mixed
      */
     private  function get_access_token(){
     	$weixin = new WeiXin($this->uid,1);
     	$access_token = $weixin->get_access_token();
     	return $access_token;
     }
	/**
	 * 获取用户分组列表
	 * @return boolean|array
	 */
	public function getGroup(){
		$groups = mc_get ( $this->uid . "_mc_groups");
		if (!$groups) {
			if (!$this->access_token) return false;
			$result = $this->http_get(self::API_URL_PREFIX.self::GROUP_GET_URL.'access_token='.$this->access_token);
			if ($result)
			{
				$json = json_decode($result,true);
				if(!isset ( $json ['errcode'] )){
					mc_set ( $this->uid."_mc_groups",$result,30);
					return $json;
				}else{
					return $json;
				}
			}
			return false;
		}else{
			return json_decode($groups,true);
		}
	}
	
	/**
	 * 上传多媒体文件
	 * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
	 * @param array $data {"media":'@Path\filename.jpg'}
	 * @param type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
	 * @return boolean|array
	 */
	public function uploadMedia($data, $type){
		if (!$this->access_token) return false;
		$result = $this->http_post(self::UPLOAD_MEDIA_URL.self::MEDIA_UPLOAD.'access_token='.$this->access_token.'&type='.$type,$data,true);
		if ($result)
		{
			$json = json_decode($result,true);
			return $json;
		}
		return false;
	}
	/**
	 * 根据媒体文件ID获取媒体文件
	 * 
	 * @param string $media_id
	 *        	媒体文件id
	 * @return raw data
	 */
	public function getMedia($media_id) {
		if (! $this->access_token)
			return false;
		$result = $this->http_get ( self::UPLOAD_MEDIA_URL . self::MEDIA_GET_URL . 'access_token=' . $this->access_token . '&media_id=' . $media_id);
		if ($result) {
			$json = json_decode ( $result, true );
			return $result;
		}
		return false;
	}
	/**
	 * 上传图文消息素材
	 * 
	 * @param array $data
	 *        	消息结构{"articles":[{...}]}
	 * @return boolean array
	 */
	public function uploadArticles($pk_id,$type=1) {
		if (! $this->access_token)
			return array("errcode"=>-1,"errmsg"=>"access_token未找到！");
		$data =array();
		if($type==1){
			$data = $this->reqSingleArticles($pk_id);
			if(isset($data['errcode'])){ return $data; }
		}
		else{
			$data = $this->reqMoreArticles($pk_id);
			if(isset($data['errcode'])) { return $data;}
		}
		$result = $this->http_post ( self::API_URL_PREFIX . self::MEDIA_UPLOADNEWS_URL . 'access_token=' . $this->access_token, self::json_encode ( $data ) );
		if ($result) {
			$json = json_decode ( $result, true );
			return $json;
		}
		return array("errcode"=>-1,"errmsg"=>"系统繁忙，请稍后再试！");
	}
	/**
	 * 构造单图文素材json结构体
	 */
	public function reqSingleArticles($pk_id){
		$data["articles"]=array();
		$wxpic = new WxReplyPic();
		$articleinfo = $wxpicdata = $wxpic->scalar("title,img_url,summary,cont_type,link_url,hid_link_url,content"," where id=".$pk_id);
		$fail_count=0;
		$img_url=trim($articleinfo["img_url"]);
		$pic=new PicData();
		$img_url=$pic->local_img($img_url);
		$pic->close();
		$pic_type=strrchr($img_url,'.');
		if($pic_type!='.jpg')
		{
			$new_img=str_replace($pic_type,'.jpg',$img_url);//强制改扩展名称
			copy($img_url,$new_img);
			$img_url=$new_img;
		}
		$mediadata = array("media"=>'@'.$img_url);
		$media_id=$this->uploadMedia($mediadata,"image");
		if(!isset($media_id['errcode']))
		{
			$t["thumb_media_id"]=$media_id["media_id"];
			//$t["author"]='作者';
			$t["title"]=$articleinfo["title"];
			if(!empty($articleinfo["hid_link_url"])){
			   $t["content_source_url"]=$articleinfo["hid_link_url"];
			}
			$t["content"]=$articleinfo["content"].'<br/><img src="http://www.dodoca.com/www/images/fan_group/readmore.gif" alt="" />';
			$t["digest"]=$articleinfo["summary"];
			$t["show_cover_pic"] = 1;
			array_push($data["articles"], $t);
			unset($t);
			return $data;
		}else{
			return $media_id;
		}
	}
	/**
	 * 构造多图文素材json结构体
	 */
	public function reqMoreArticles($pk_id){
		$data["articles"]=array();
		$obj=new WxReplyPicMores();
		$fail_count=0;
		$articles=$obj->get_row_byid($pk_id);
		foreach((array)$articles as $k=>$v)
		{
			$img_url=trim($v["img_url"]);
			if($img_url)
			{
				$pic=new PicData();
				$img_url=$pic->local_img($img_url);
				$pic->close();
				$pic_type=strrchr($img_url,'.');
				if($pic_type!='.jpg')
				{
					$new_img=str_replace($pic_type,'.jpg',$img_url);//强制改扩展名称
					copy($img_url,$new_img);
					$img_url=$new_img;
				}
				$mediadata = array("media"=>'@'.$img_url);
				$media_id=$this->uploadMedia($mediadata,"image");
				if($media_id)
				{
					$t["thumb_media_id"]=$media_id["media_id"];
					//$t["author"]='作者';
					$t["title"]=$v["title"];
					if(!empty($v["hid_link_url"])){
			   			$t["content_source_url"]=$v["hid_link_url"];
					}
					$t["content"]=$t["content"].'<br/><img src="http://www.dodoca.com/www/images/fan_group/readmore.gif" alt="" />';
					$t["digest"]=$v["summary"];
					$t["show_cover_pic"] = 1;
					array_push($data["articles"], $t);
					unset($t);
				}
				else
				{
					$fail_count++;
				}
			}
		}
		if($fail_count)
		{
			return array("errcode"=>40001,"有".$fail_count."图片上传失败！");
		}else{
			return $data;
		}
	}
	/**
	 * 高级群发消息, 根据OpenID列表群发图文消息
	 * 
	 * @param array $data
	 *        	消息结构{ 
	 *        "touser":[ "OPENID1", "OPENID2" ], 
	 *        "mpnews":{ "media_id":"123dsdajkasd231jhksad" },
	 *        "msgtype":"mpnews"
	 *        }
	 * @return boolean array
	 */
	public function sendMassMessage($data) {
		if (! $this->access_token)
			return false;
		$result = $this->http_post ( self::API_URL_PREFIX . self::MASS_SEND_URL . 'access_token=' . $this->access_token, self::json_encode ( $data ) );
		if ($result) {
			$json = json_decode ( $result, true );
			return $json;
		}
		return false;
	}
/**
	 * 高级群发消息, 根据OpenID列表预览图文消息
	 * 
	 * @param array $data
	 *        	消息结构{ 
	 *        "touser":OPENID, 
	 *        "mpnews":{ "media_id":"123dsdajkasd231jhksad" },
	 *        "msgtype":"mpnews"
	 *        }
	 * @return boolean array
	 */
	public function previewMassMessage($data) {
		if (! $this->access_token)
			return false;
		$result = $this->http_post ( self::API_URL_PREFIX . self::MASS_PREVIEW_URL . 'access_token=' . $this->access_token, self::json_encode ( $data ) );
		if ($result) {
			$json = json_decode ( $result, true );
			return $json;
		}
		return false;
	}
	/**
	 * 高级群发消息, 根据群组id群发图文消息
	 * @param array $data 消息结构{ "filter":[ "group_id": "2" ], "mpnews":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"mpnews" }
	 * @return boolean|array
	 */
	public function sendGroupMassMessage($data){
		if (!$this->access_token) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MASS_SEND_GROUP_URL.'access_token='.$this->access_token,self::json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);
			return $json;
		}
		return false;
	}
	/**
	 * 高级群发消息, 删除群发图文消息
	 * @param int $msg_id 消息id
	 * @return boolean|array
	 */
	public function deleteMassMessage($msg_id){
		if (!$this->access_token) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MASS_DELETE_URL.'access_token='.$this->access_token,self::json_encode(array('msg_id'=>$msg_id)));
		if ($result)
		{
			$json = json_decode($result,true);
			return true;
		}
		return false;
	}
	/*
	 * 群发操作
	 * $id wx_mass_send表中的群发id
	 */
	public function excuMassSend($id){
		$userid = $this->uid;
		$WxMassSend =  new WxMassSend();
		$massinfo = $WxMassSend->scalar("*"," where send_status=0 and userid={$userid} and id=".$id);
		$retmsg = array();
		if(!$massinfo) {
			$retmsg = array("errcode"=>-1,"errmsg"=>"该群发记录未找到或已执行过！");
			return $retmsg;
		}
		$msgtype = $massinfo["msgtype"];
		$msgpkid = $massinfo["pk_id"];
		$masstype = $massinfo["masstype"];
		switch($msgtype){
			case 1: //单图文
				$wxreplypic = new WxReplyPic();
				$media_id = array();
				$media_id["media_id"] = $wxreplypic->scalar("media_id", " where id=".$msgpkid);
				if(empty($media_id["media_id"])){
					$media_id = $this->uploadArticles($msgpkid,1);
				} 
				if(!isset($media_id['errcode'])){ //获取到了文章id
					$wxreplypic->update(array("media_id"=>$media_id["media_id"])," where id=".$msgpkid);
					if($masstype==1){
						$reqdata["touser"] = json_decode($massinfo["openids"],true);
						$reqdata["mpnews"]["media_id"] = $media_id["media_id"];
						$reqdata["msgtype"] = "mpnews";
						$retmsg = $this->sendMassMessage($reqdata);
					}else{
						if($massinfo["group_id"]==10000){ //组id为10000时，为全体群发
							$reqdata["filter"]["is_to_all"] = true;
						}else{
							$reqdata["filter"]["group_id"] = $massinfo["group_id"];
						}
						$reqdata["mpnews"]["media_id"] = $media_id["media_id"];
						$reqdata["msgtype"] = "mpnews";
						$retmsg = $this->sendGroupMassMessage($reqdata);
					}
					if(!empty($retmsg["msg_id"])){
						$data["msgid"] = $retmsg["msg_id"];
						$data["send_status"] = 1;
						$retmsg = array("errcode"=>0,"errmsg"=>"群发成功！");
					}else{
						$data["send_status"] = -1;
						$data["send_err"] = $retmsg["errmsg"];
						$retmsg = array("errcode"=>-1,"errmsg"=>"群发失败！原因：".$retmsg["errmsg"]);
					}
					
				}else{
					$data["send_status"] = -1;
					$data["send_err"] = $media_id["errmsg"];
					$retmsg = array("errcode"=>-1,"errmsg"=>"群发失败！原因：".$media_id["errmsg"]);
				}
				$WxMassSend->update_data($data, " where userid={$userid} and id=".$id);
				break;
			case 2: //多图文
				$wxreplypicmore = new WxReplyPicMore();
				$media_id = array();
				$media_id["media_id"] = $wxreplypicmore->scalar("media_id", " where id=".$msgpkid);
				if(empty($media_id["media_id"])){
					$media_id = $this->uploadArticles($msgpkid,2);
				}
				if(!isset($media_id['errcode'])){ //获取到了文章id
					$wxreplypicmore->update(array("media_id"=>$media_id["media_id"])," where id=".$msgpkid);
					if($masstype==1){
						$reqdata["touser"] = json_decode($massinfo["openids"],true);
						$reqdata["mpnews"]["media_id"] = $media_id["media_id"];
						$reqdata["msgtype"] = "mpnews";
						$retmsg = $this->sendMassMessage($reqdata);
						//$retmsg = $this->previewMassMessage($reqdata); //预览图文消息
					}else{
					    if($massinfo["group_id"]==10000){ //组id为10000时，为全体群发
							$reqdata["filter"]["is_to_all"] = true;
						}else{
							$reqdata["filter"]["group_id"] = $massinfo["group_id"];
						}
						$reqdata["mpnews"]["media_id"] = $media_id["media_id"];
						$reqdata["msgtype"] = "mpnews";
						$retmsg = $this->sendGroupMassMessage($reqdata);
					}
					if(!empty($retmsg["msg_id"])){
						$data["msgid"] = $retmsg["msg_id"];
						$data["send_status"] = 1;
						$retmsg = array("errcode"=>0,"errmsg"=>"群发成功！");
					}else{
						$data["send_status"] = -1;
						$data["send_err"] = $retmsg["errmsg"];
						$retmsg = array("errcode"=>-1,"errmsg"=>"群发失败！原因：".$retmsg["errmsg"]);
					}
				}else{
					$data["send_status"] = -1;
					$data["send_err"] = $media_id["errmsg"];
					$retmsg = array("errcode"=>-1,"errmsg"=>"群发失败！原因：".$media_id["errmsg"]);
				}
				$WxMassSend->update_data($data, " where userid={$userid} and id=".$id);
				break;
			default:  //文本
				$wxReplyMsg = new WxReplyMsg();
				$content = $wxReplyMsg->scalar("reply_msg", " where userid={$userid} and id=".$msgpkid);
				$reqdata = $data = array();
				if($masstype==1){
					$reqdata["touser"] = json_decode($massinfo["openids"],true);
					$reqdata["text"]["content"] = $content;
					$reqdata["msgtype"] = "text";
					$retmsg = $this->sendMassMessage($reqdata);
					/* s($masstype);
					s($reqdata);
					s($retmsg); */
				}else{  
				    if($massinfo["group_id"]==10000){ //组id为10000时，为全体群发
						$reqdata["filter"]["is_to_all"] = true;
					}else{
						$reqdata["filter"]["group_id"] = $massinfo["group_id"];
					}
					$reqdata["text"]["content"] = $content;
					$reqdata["msgtype"] = "text";
					$retmsg = $this->sendGroupMassMessage($reqdata);
					/* s($masstype);
					s($reqdata);
					s($retmsg); */
				}
				 
				if(!empty($retmsg["msg_id"])){
					$data["msgid"] = $retmsg["msg_id"];
					$data["send_status"] = 1;
					$retmsg = array("errcode"=>0,"errmsg"=>"群发成功！");  
				}else{
					$data["send_status"] = -1;
					$data["send_err"] = $retmsg["errmsg"];
					$retmsg = array("errcode"=>-1,"errmsg"=>"群发失败！原因是：".$retmsg["errmsg"]);
				}
				$WxMassSend->update_data($data, " where userid={$userid} and id=".$id);
				break;
		}
		return $retmsg;
	}
	/**
	 * GET 请求
	 * 
	 * @param string $url        	
	 */
	private function http_get($url, $data=false) {
		if($data)
		$url = $url . '?' . http_build_query ( $data );
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSLVERSION, 1 ); // CURL_SSLVERSION_TLSv1
		}
		curl_setopt ( $oCurl, CURLOPT_URL, $url );
		curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec ( $oCurl );
		$aStatus = curl_getinfo ( $oCurl );
		curl_close ( $oCurl );
		if (intval ( $aStatus ["http_code"] ) == 200) {
			return $sContent;
		} else {
			return false;
		}
	}
	
	/**
	 * POST 请求
	 * 
	 * @param string $url        	
	 * @param array $param        	
	 * @param boolean $post_file
	 *        	是否文件上传
	 * @return string content
	 */
	private function http_post($url, $param, $post_file = false) {
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt ( $oCurl, CURLOPT_SSLVERSION, 1 ); // CURL_SSLVERSION_TLSv1
		}
		if (is_string ( $param ) || $post_file) {
			$strPOST = $param;
		} else {
			$aPOST = array ();
			foreach ( $param as $key => $val ) {
				$aPOST [] = $key . "=" . urlencode ( $val );
			}
			$strPOST = join ( "&", $aPOST );
		}
		curl_setopt ( $oCurl, CURLOPT_URL, $url );
		curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $oCurl, CURLOPT_POST, true );
		curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
		$sContent = curl_exec ( $oCurl );
		$aStatus = curl_getinfo ( $oCurl );
		curl_close ( $oCurl );
		if (intval ( $aStatus ["http_code"] ) == 200) {
			return $sContent;
		} else {
			return false;
		}
	}
	/**
	 * 微信api不支持中文转义的json结构
	 * @param array $arr
	 */
	static function json_encode($arr) {
		$parts = array ();
		$is_list = false;
		//Find out if the given array is a numerical array
		$keys = array_keys ( $arr );
		$max_length = count ( $arr ) - 1;
		if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
			$is_list = true;
			for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
				if ($i != $keys [$i]) { //A key fails at position check.
					$is_list = false; //It is an associative array.
					break;
				}
			}
		}
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) { //Custom handling for arrays
				if ($is_list)
					$parts [] = self::json_encode ( $value ); /* :RECURSION: */
				else
					$parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
			} else {
				$str = '';
				if (! $is_list)
					$str = '"' . $key . '":';
				//Custom handling for multiple data types
				if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
					$str .= $value; //Numbers
				elseif ($value === false)
				$str .= 'false'; //The booleans
				elseif ($value === true)
				$str .= 'true';
				else
					$str .= '"' . addslashes ( $value ) . '"'; //All other things
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts [] = $str;
			}
		}
		$json = implode ( ',', $parts );
		if ($is_list)
			return '[' . $json . ']'; //Return numerical JSON
		return '{' . $json . '}'; //Return associative JSON
	}
}