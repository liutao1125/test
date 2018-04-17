<?php
/**
 *	支付宝服务窗
 *  @author dengjianjun@dodoca.com
 *  @link 
 *  @version 1.0
 *  usage:
 *  发文本消息：
 *  
 */
class AliFwc {
	// 网关地址
	private $gateurl = 'https://openapi.alipay.com/gateway.do';
	// 应用ID
	private $appid;
	// 商户私钥
	private $rsaPrivateKey;
	// 商户公钥
	private $rsaPubkey;
	// 服务器端编码格式
	private $charset = "UTF-8";
	// 签名类型
	protected $signType = "RSA";
	// 返回数据格式
	public $format = "json";
	// 接受到的xml数据包
	public $_biz_content;
	// 回复的xml消息包
	private $_msg;
	// 用户id
	private $uid;
	// 来源
	private $from_type;
	// 用户的支付宝用户号
	private $openid;
	// 微伙伴用户资料
	private $userinfo;
	// 粉丝Id
	private $fans_id;
	// 服务窗信息
	private $fwcinfo;
	// 用户回复的话
	private $keyword;
	public function __construct($uid, $from_type = '3', $token = '') {
		$this->uid = $uid;
		$this->from_type = $from_type;
		$info = User::get_user_info ( $uid );
		if ($info ["status"] == '-1' || $info ["over_time"] < date ( "Y-m-d" )) 		// 过期或者被删除
		{
			// PubFun::save_log ( 'uid->' . $this->uid . ',info_uid->' . $info ["uid"], 'wx_over_time' );
			exit ();
		}
		$this->userinfo = $info;
		$this->init_account ( $uid, $from_type );
	}
	// 初始化服务窗用户信息
	public function init_account($uid, $from_type) {
		$wx = new WxUserFwc ();
		$data = $wx->get_fwc_info ( $uid );
		if ($data) {
			$this->appid = $data ["appid"];
			$this->rsaPrivateKey = $data ["prikey"];
			$this->rsaPubkey = $data ["pubkey"];
		} else {
		}
	}
	/**
	 * 初始粉丝数据
	 */
	public function init_fans() {
		$wei = new WxUserFans ();
		$rs = $wei->get_row_byusername ( $this->uid, $this->openid );
		if ($rs) {
			$this->fans_id = $rs ["id"];
		} else {
			$this->fans_id = 0;
		}
		$wei->close ();
		unset ( $wei );
	}
	/**
	 * 绑定验证
	 */
	public function valid($publicrsa) {
		$response = array (
				"success" => "true",
				"biz_content" => $publicrsa 
		);
		// $signString = $this->sign($this->getSignContent($response));
		$msg = array (
				'response' => $response,
				// 'sign'=>$signString,
				'sign_type' => $this->signType 
		);
		header ( 'Content-Type: text/xml' );
		$XML = ArrayToXML::parse ( $msg, "alipay" );
		echo $XML;
	}
	/**
	 * 告诉支付宝已收到消息
	 */
	public function response_act(){
		$response["XML"]["ToUserId"]=$this->openid;
		$response["XML"]["AppId"]=$this->uid;
		$response["XML"]["CreateTime"]=number_format(microtime(true),3,'','');
		$response["XML"]["MsgType"]="ack";
		//$signString = $this->sign($this->getSignContent($response));
		$msg = array (
				'response' => $response,
				// 'sign'=>$signString,
				'sign_type' => $this->signType
		);
		header ( 'Content-Type: text/xml' );
		$XML = ArrayToXML::parse ( $msg, "alipay" );
		echo $XML;
	}
	
	/**
	 * 临时写订阅数据
	 */
	public function tmp_subscribe() {
		if (! $this->openid || ! $this->uid)
			return;
		$fans = new WxUserFans ();
		$ext = $fans->scalar ( "id", " where userid=" . $this->uid . " and weixin_user='" . $this->openid . "'" );
		if (! $ext) {
			$u_d ["userid"] = $this->uid;
			$u_d ["weixin_user"] = $this->openid;
			$u_d ["status"] = 1;
			$u_d ["sub_date"] = time ();
			$u_d ["mptype"] = 3;
			$fans->insert_data ( $u_d );
		}
		$fans->close ();
		unset ( $fans );
	}
	/**
	 * 服务窗请求响应
	 */
	public function responseMsg() {
		$biz_content = $_POST ["biz_content"];
		if (empty ( $biz_content )) {
			exit ();
		}
		if (! strstr ( $biz_content, "version" )) {
			$biz_content = '<?xml version="1.0" encoding="utf-8"?>' . $biz_content;
			$biz_content = iconv ( "GBK", "UTF-8", $biz_content );
		}
		if (! empty ( $biz_content )) {
			$this->_biz_content = ( array ) simplexml_load_string ( $biz_content, 'SimpleXMLElement', LIBXML_NOCDATA );
			$this->openid = $this->_biz_content ["FromUserId"];
			$this->appid = $this->_biz_content ["AppId"];
			$Text = json_encode ( $this->_biz_content ["Text"] );
			$Content = json_decode ( $Text, true );
			$this->keyword = $Content ["Content"];
		}
		$msgtype = $this->_biz_content ["MsgType"]; // 消息类型
		
		$this->init_fans ();
		$log = new WxMsg ();
		$data ["userid"] = $this->uid;
		$data ["fromusername"] = $this->openid;
		$data ["tousername"] = trim ( $this->appid );
		$data ["content"] = $this->keyword;
		$data ["msgtype"] = trim ( $msgtype );
		$data ["picurl"] = "";
		$data ["event"] = $this->_biz_content ["EventType"];
		$data ["eventkey"] = $this->_biz_content ["ActionParam"];
		$data ["createtime"] = time ();
		$data ["msgid"] = time () . rand ( 1000, 9999 );
		$data ["msg_from"] = 3;
		$data ["fans_id"] = $this->fans_id;
		$retid = $log->insert_data ( $data );
		$wei=new WxUserFans();
        $this->fans_id=$wei->subscribe($data["fromusername"],$this->uid,$this->from_type);
        $wei->close();
        unset($wei);
		switch ($msgtype) {
			// 文本消息
			case "text" :
				$ret = $this->get_key_msg ( $this->keyword);
				$kg = new WxOnOff ();
				$info = $kg->get_row_byuid ( $this->uid );
			    if ($ret && isset ( $ret ["not_match"] )) { // 未匹配到数据
					if ($info && $info ["auto_reply"]) { // 开通智能客服
						$data ["msg_type"] = 1;
					} else { // 关闭智能客服
						$data ["msg_type"] = 2;  
					}
				}
				$this->response_act();
				$this->to_msg($ret);
				break;
			// 事件消息
			case "event" :
				$event = $this->_biz_content ["EventType"];
				switch ($event) {
					// 关注事件
					case "follow" :
						$wei = new WxUserFans ();
						//$this->text("openid:".$this->openid.",uid:".$this->uid."|".$this->from_type);
						$wei->close ();
						unset ( $wei );
						$ret = $this->get_default_msg ( $this->uid, 2 ); // 欢迎词
						$this->to_msg ( $ret );
						break;
					// 点击菜单事件
					case "click" :
					    $eventkey = trim ( $data ["eventkey"] );
						$ret = null;
						if ($eventkey) {
							$arr = explode ( '_', $eventkey );
							$id = $arr [2];
							
							if ($id) {
								$menu = new WxFootMenu ();
								$rs = $menu->get_row_byid ( $id );
								if ($rs) {
									$from_type = 0;
									if ($rs ["menutype"] == '1') 									// 文字
									{
										$from_type = 1;
									} else if ($rs ["menutype"] == '2') 									// 单图文
									{
										$from_type = 2;
									} else if ($rs ["menutype"] == '5') 									// 多图文
									{
										$from_type = 3;
									}
									if ($from_type) {
										$ret = $this->get_data_byid ( $rs ["pk_id"], $from_type );
										$this->save_view_log ( $from_type, $rs ["pk_id"] );
									}
								}
								$menu->close ();
								unset ( $menu );
							}
						}
						if ($ret) {
							$this->to_msg ( $ret );
						} else {
							$ret = $this->get_default_msg ( $this->uid, 1 );
							$this->to_msg ( $ret );
						}
						$this->response_act();
						break;
					// 验证事件
					case "verifygw" :
						$this->valid ( $this->rsaPubkey );
						$wx = new WxUserFwc ();
						$data ["appid"] = $this->appid;
						$wx->update ( $data, $this->uid );
						break;
					// 进入会话事件
					case "enter" :
						$this->response_act();
						break;
					// 取消关注事件
					case "unfollow" :
						$wei = new WxUserFans ();
						$wei->unsubscribe ( $data ["fromusername"], $this->uid );
						$wei->close ();
						unset ( $wei );
						break;
					default :
						$ret = $this->text ( "谢谢，消息已收到" )->push_message ();
				}
				break;
			default :
				$ret = $this->text ( "谢谢，消息已收到" )->push_message ();
		}
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         回复微信
	 *         $ret 数组
	 */
	public function to_msg($ret) {
		if ($ret) {
			if (isset ( $ret ["msg_type"] ) && $ret ["msg_type"] == '1') 			// 文字回复
			{
				$this->text ($ret ["data"] ["reply_msg"] )->push_message ();
			}
			if (isset ( $ret ["msg_type"] ) && $ret ["msg_type"] == '4') 			// 纯文字
			{
				$this->text ($ret ["data"] ["reply_msg"] )->push_message ();
			}
			 else 			// 图文
			{
				$count = count($ret ["data"]);
				//s($count);
				if($count>6){
					//切割多图文数组，服务窗最多支持6条的多图文
					$newData = array_chunk($ret ["data"],6);
					foreach ($newData as $news){
						$this->news($news)->push_message ();
					}
				}else{
					$newData = $ret["data"];
					$this->news($newData)->push_message ();
				}
			}
		}
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         设置发送消息
	 * @param array $msg
	 *        	消息数组
	 * @param bool $append
	 *        	是否在原消息数组追加
	 */
	public function Message($msg = '') {
		if (is_null ( $msg )) {
			$this->_msg = array ();
		} else {
			$this->_msg = $msg;
		}
		return $this->_msg;
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         被动回复微信服务器
	 *         Example: $this->text('msg tips')->reply();
	 *        
	 * @param string $msg
	 *        	要发送的信息, 默认取$this->_msg
	 * @param bool $return
	 *        	是否返回信息而不抛出到浏览器 默认:否
	 */
	public function reply($msg = array(), $return = false) {
		if (empty ( $msg ))
			$msg = $this->_msg;
		$xmldata = $this->xml_encode ( $msg, "XML" );
		if ($return)
			return $xmldata;
		else
			header ( 'Content-type: text/html; charset=utf-8' );
		echo $xmldata;
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         主动发送消息
	 *        
	 * @param array $msg
	 *        	消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
	 * @return boolean array
	 */
	public function push_message() {
		$msg = $this->_msg;
		$method = "alipay.mobile.public.message.custom.send";
		$biz_content = json_encode($msg);
		return $this->execute ( $method, $biz_content );
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         创建自定义菜单
	 */
	public function menu_create() {
		$method = "alipay.mobile.public.menu.add";
		$biz_content = $this->get_menu_info ();
		return $this->execute ( $method, $biz_content );
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         更新自定义菜单
	 */
	public function menu_update() {
		$method = "alipay.mobile.public.menu.update";
		$biz_content = $this->get_menu_info ();
		return  $this->execute ( $method, $biz_content );
	    //if($respObject)
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         获取菜单数据
	 */
	public function get_menu_info() {
		$str = '';
		$str .= '{';
		$str .= '"button":[';
		$menu = new WxFootMenu ();
		$menu_data = $menu->fetchAll ( "select * from " . $menu->_name . " where userid=" . $this->uid . " and status=1 and parent_id=0 order by id asc" ); // 父菜单
		
		if ($menu_data) {
			foreach ( $menu_data as $k => $v ) {
				$child_list = $menu->fetchAll ( "select * from " . $menu->_name . " where userid=" . $this->uid . " and menutype>0 and status=1 and parent_id=" . $v ["id"] . " order by id asc" ); // 子菜单
				if ($child_list) {
					$tmp = '{';
					$tmp .= '"name":"' . $v ["menuname"] . '",';
					$tmp .= '"subButton":[';
					foreach ( $child_list as $key => $val ) {
						if ($val ["menutype"] == '1' || $val ["menutype"] == '2' || $val ["menutype"] == '5') 						// 文字、图片
						{
							$tmp .= '{"actionType":"out","name":"' . $val ["menuname"] . '","actionParam":"' . $val ["hid_link_url"] . '"}';
						} else {
							$tmp .= '{"actionType":"link","name":"' . $val ["menuname"] . '","actionParam":"' . $val ["hid_link_url"] . '"}';
						}
						if ((count ( $child_list ) - 1) > $key) {
							$tmp .= ',';
						}
					}
					$tmp .= ']';
					$tmp .= '},';
					
					$str .= $tmp;
					$tmp = '';
				} else {
					$val = $v;
					if ($val ["menutype"] == '1' || $val ["menutype"] == '2' || $val ["menutype"] == '5') 					// 文字、图片
					{
						$str .= '{"actionType":"out","name":"' . $val ["menuname"] . '","actionParam":"' . $val ["hid_link_url"] . '"},';
					} else if ($val ["hid_link_url"] != '') {
						$str .= '{"actionType":"link","name":"' . $val ["menuname"] . '","actionParam":"' . $val ["hid_link_url"] . '"},';
					}
				}
			}
		}
		if ($this->from_type == '3') { // 服务窗去掉最后一个逗号
			$str = substr ( $str, 0, strlen ( $str ) - 1 );
		}
		$str .= ']';
		$str .= '}';
		return $str;
	}
	/**
	 * 构造图文消息xml包
	 * 
	 * @author dengjianjun@dodoca.com
	 * @param array $newsData
	 *        	数组结构:
	 *        	array(
	 *        	"0"=>array(
	 *        	'Title'=>'msg title',
	 *        	'Desc'=>'summary text',
	 *        	'ImageUrl'=>'http://www.domain.com/1.jpg',
	 *        	'Url'=>'http://www.domain.com/1.html'
	 *        	),
	 *        	"1"=>....
	 *        	)
	 */
	public function news($newsData = array()) {
		$count = count ( $newsData );
		 for($i=0;$i<count($newsData);$i++){
			$link_url=str_replace('FANS_ID',$this->fans_id,$newsData[$i]["url"]);
			if(strpos($link_url,'OPEN_ID_E')!==false)
			{
				$tmp_open_id=PubFun::encrypt($this->fans_id,'E');
				$tmp_open_id=base64_encode($tmp_open_id);
				$link_url=str_replace('OPEN_ID_E',$tmp_open_id,$link_url);
				unset($tmp_open_id);
			}
			if(strpos($link_url,'USER_ACC_KEY')!==false)
			{
				$wxt=new WxCardAccTime();
				$rand_key=$wxt->get_acc_key();
				if($rand_key)
				{
					$link_url=str_replace('USER_ACC_KEY',$rand_key,$link_url);
				}
				$wxt->close();
			}
			$newsData[$i]["url"]=$link_url;
			//$newsData[$i]["desc"]=$link_url;
		} 
		$msg = array (
				'toUserId' => $this->openid,
				//'AppId' => $this->appid,
				'createTime' => time (),
				'msgType' => "image-text",
				//'ArticleCount' => $count,
				'articles' => $newsData 
		);
		//PubFun::save_log("图文json数据：".json_encode($newsData),"fwcDebug.txt");
		$this->Message ( $msg );
		return $this;
	}
	/**
	 * 构造文本消息的xml包
	 * 
	 * @author dengjianjun@dodoca.com
	 * @param array $textData
	 *        	eg.
	 *        	$textData=array(
	 *        	'Title'=>'这是标题'
	 *        	'Desc'=>'这是纯文本内容'
	 *        	'ImageUrl'=>'',
	 *        	'Url'=>''
	 *        	)
	 *        	
	 */
	public function text($content) {
		$msg = array (
				'toUserId' => $this->openid,
				'msgType' => "text",
				'createTime' => number_format(microtime(true),3,'',''),
				'text' => array(
				   'content'=>$content
				)
		);
		$this->Message ( $msg );
		return $this;
	}
	/**
	 *
	 * @author dengjianjun@dodoca.com
	 *         调用接口
	 */
	public function execute($method = "", $biz_content = "", $authToken = "") {
		$sysParams = array ();
		$sysParams ["charset"] = $this->charset;
		$sysParams ["app_id"] = $this->appid . "";
		$sysParams ["sign_type"] = $this->signType;
		$sysParams ["method"] = $method;
		$sysParams ["timestamp"] = date ( "Y-m-d H:i:s" );
		$sysParams ["auth_token"] = $authToken;
		$sysParams ["format"] = $this->format;
		$sysParams ["biz_content"] = $biz_content;
		$sysParams ["sign"] = $this->sign ( $sysParams );
		// 系统参数放入GET请求串
		$requestUrl = $this->gateurl . "?";
		// 发起HTTP请求
		try {
			$resp = $this->http_post ( $requestUrl, $sysParams );
		} catch ( Exception $e ) {
			return false;
		}
		// 解析返回结果
		$respWellFormed = false;
		$respObject=array();
		if ("json" == $this->format) {
			$respObject = json_decode ( $resp, true );
			if (null !== $respObject) {
				$respWellFormed = true;
			}
		} else if ("xml" == $this->format) {
			$respObject = @ simplexml_load_string ( $resp );
			if (false !== $respObject) {
				$respWellFormed = true;
			}
		}
		// echo $requestUrl;
		$responseApi = str_replace(".", "_", $method);
		if($respObject[$responseApi."_response"]["code"]==200){
			return true;
		}else{
			return $respObject[$responseApi."_response"]["msg"];
		}
	}
	
	/**
	 *
	 * @author sunshichun@dodoca.com
	 *         获取默认回复、欢迎词
	 *         $uid 用户id
	 *         $type 1->默认回复、2->欢迎词
	 */
	public function get_default_msg($uid, $type) {
		if (! $uid || ! $type)
			return;
		$default = new WxReplyDefault ();
		$data = $default->scalar ( "*", " where userid=$uid and reply_type=$type" );
		$ret = array ();
		if ($data) {
			$this->save_view_log ( $data ["from_type"], $data ["pk_id"] );
			$ret = $this->get_data_byid ( $data ["pk_id"], $data ["from_type"] );
		}
		return $ret;
	}
	/**
	 * @author sunshichun@dodoca.com
	 * 通过关键词获取回复内容
	 * $key 关键词
	 * @return array
	 */
	public function get_key_msg($key)
	{
		$ret = array();
		$ret["msg_type"]='4';//纯文字
		$key=trim($key);
		$onoff=new WxOnOff();
		$onoff_info=$onoff->get_row_byuid($this->uid);//趣味互动开关
		$tmp=mb_substr($key,0,2,'utf-8') ;
		$tmp2=mb_substr($key,0,1,'utf-8') ;
		if($tmp=='笑话' && $onoff_info["joke_flag"]){
			$ret["data"]["keywords"]=$key;
			$ret["data"]["reply_msg"]=HappyHud::GetJoke();
		}elseif(mb_substr($key,0,4,'utf-8')=='wifi'){
			$ret["data"]["keywords"]=$key;
			$code=mb_substr($key,4,4,'utf-8');
			//PubFun::save_log('openid->'.$this->openid.',code->'.$code.',uid->'.$this->uid,'wx_wifi');
			$ret["data"]["reply_msg"]=WifiClass::getcodeAction($this->openid,$code,$this->uid);
		}elseif($tmp=="点歌" && $onoff_info["music_flag"]){
			$ret["data"]["keywords"]=$key;
			$music_name='';
			$music_gs='';
			$str=str_replace("点歌",'',$key);
			if(strpos($str,'@')!==false){
				$arr=explode('@',$str);
				$music_name=trim($arr[0]);
				$music_gs=trim($arr[1]);
			}else{
				$music_name=trim($str);
			}
			$ret["data"]["reply_msg"]=HappyHud::GetMusic($music_name,$music_gs);
		}elseif($tmp=="天气"  && $onoff_info["weather_flag"]){
			$ret["data"]["keywords"]=$key;
			$key=trim(str_replace('天气','',$key));
			$ret["data"]["reply_msg"]=HappyHud::GetWeather($key);
		}elseif($tmp=="翻译"  && $onoff_info["translate_flag"]){
			$ret["data"]["keywords"]=$key;
			$key=trim(str_replace('翻译','',$key));
			if(strpos($key,"@")!==false){//日文翻译
				$key=trim(str_replace('@','',$key));
				$ret["data"]["reply_msg"]=HappyHud::GetTranslate('jp',$key);
			}else{//英文
				$ret["data"]["reply_msg"]=HappyHud::GetTranslate('en',$key);
			}
		}elseif($tmp=="快递"  && $onoff_info["kuaidi_flag"]){
			$ret["data"]["keywords"]=$key;
			$key=trim(str_replace('快递','',$key));
			$arr=explode('@',$key);
			if(!$arr[0] || !$arr[1]){
				$ret["data"]["reply_msg"]="数据格式不对！,正确格式：快递申通@868273943254";
			}else{
				$ret["data"]["reply_msg"]=HappyHud::GetKuaidi($arr[0],$arr[1]);
			}
		}elseif($tmp=="车次" && $onoff_info["train_flag"]){
			$ret["data"]["keywords"]=$key;
			$key=trim(str_replace('车次','',$key));
			$ret["data"]["reply_msg"]=HappyHud::GetTrainInfo($key);
		}elseif($tmp=="列车" && $onoff_info["train_flag"]){
			$ret["data"]["keywords"]=$key;
			$key=trim(str_replace('列车','',$key));
			$type=substr($key,0,1);
			$key=substr($key,1);
			$arr=explode('@',$key);
			if(!$arr[0] || !$arr[1]){
				$ret["data"]["reply_msg"]="对不起，您提交的信息格式不对，例如：列车G上海@苏州, 列车类型字母代码:\n G-高速动车\n K-快速 \n T-空调特快 \n D-动车组\n Z-直达特快 \n Q-其他";
			}else{
				$ret["data"]["reply_msg"]=HappyHud::GetTrainList($arr[0],$arr[1],$type);
			}
		}else{//取用户关键词
			$wk=new WxKeywordColl();
			$key_info=$wk->fetchAll("select * from ".$wk->_name."  where userid=".$this->uid." and  binary keywords like '%$key%' and status=1 order by udate desc");
			if($key_info)//匹配到文本、单图文、多图文
			{
				$tmp_pk_id=0;
				$tmp_from_type=0;
				$is_exist=0;
				foreach($key_info as $tmp_key=>$tmp_val)
				{
					if(!$is_exist)
					{
						$tmp_arr=explode(',',$tmp_val["keywords"]);
						if(is_array($tmp_arr))
						{
							foreach($tmp_arr as $kk=>$vv)
							{
								if(trim($vv)==$key)
								{
									$tmp_pk_id=$tmp_val["pk_id"];
									$tmp_from_type=$tmp_val["from_type"];
									$is_exist++;
									break;
								}
							}
						}
					}
				}
				$this->save_view_log($tmp_from_type,$tmp_pk_id);
				if($tmp_pk_id>0)
				{
					$ret = $this->get_data_byid($tmp_pk_id,$tmp_from_type);
				}
				else
				{
					$ret =$this->get_default_msg($this->uid,1);
				}
			}
			else//未匹配到
			{
				$ret=$this->get_default_msg($this->uid,1);
				$ret["not_match"]='1';
			}
		}
		return $ret;
	}
	/*
	 * @author sunshichun@dodoca.com $id 主键 $from_type 来源 1->文字 2->单图文 3->多图文
	 */
	function get_data_byid($id, $from_type) {
		if (! $id || ! $from_type)
			return;
		$obj = null;
		$ret = array();
		$ret ["msg_type"] = $from_type;
		switch ($from_type) {
			case "1" : // 文字
				$obj = new WxReplyMsg ();
				$ret ["data"] = $obj->get_row_byid ( $id );
				break;
			case "2" : // 单图文
				$obj = new WxReplyPic ();
				$article = $obj->get_row_byid ( $id );
				$ret["data"]=array();
			    $temp["actionName"]=$article["title"];
				$temp["desc"]=$article["summary"];
				$temp["imageUrl"]=$article["img_url"];
				$temp["title"]=$article["title"];
				$temp["url"]=$article["hid_link_url"];
				$ret["data"][]=$temp;
			//array_push($ret["data"],$temp);
				unset($temp);
				break;
			case "3" : // 多图文
				$obj = new WxReplyPicMores ();
				$data = $obj->get_row_byid ( $id );
				$ret["data"]=array();
				foreach ($data as $article){
					$temp["actionName"]=$article["title"];
					$temp["desc"]=$article["summary"];
					$temp["imageUrl"]=$article["img_url"];
					$temp["title"]=$article["title"];
					$temp["url"]=$article["hid_link_url"];
					array_push($ret["data"],$temp);
					unset($temp);
				}
				break;
			default :
				$ret ["msg_type"] = 0;
				break;
		}
		if ($obj) {
			$obj->close ();
			unset ( $obj );
		}
		//s($ret);
		return $ret;
	}
	
	/**
	 * 记录日志
	 */
	function save_view_log($log_type, $pk_id) {
		if (! $log_type || ! $pk_id)
			return;
		$data ["log_type"] = $log_type;
		$data ["pk_id"] = $pk_id;
		$data ["fans_id"] = $this->fans_id;
		$data ["userid"] = $this->uid;
		$log = new WxViewLog ();
		$log->insert_data ( $data );
		$log->close ();
		unset ( $log );
	}
	
	/**
	 *
	 * @author sunshichun@dodoca.com
	 *         处理分词
	 *         $key 被分词的文字
	 */
	function get_keywords($key) {
		return false;
	}
	protected function sign($data) {
		$stringToBeSigned = $this->getSignContent ( $data );
		$priKey = $this->rsaPrivateKey;
		$res = openssl_get_privatekey ( $priKey );
		openssl_sign ( $stringToBeSigned, $sign, $res );
		openssl_free_key ( $res );
		$sign = base64_encode ( $sign );
		return $sign;
	}
	// 获取待签名的字符串
	protected function getSignContent($params) {
		ksort ( $params );
		$stringToBeSigned = "";
		$i = 0;
		foreach ( $params as $k => $v ) {
			if (false === $this->checkEmpty ( $v )) {
				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i ++;
			}
		}
		unset ( $k, $v );
		return $stringToBeSigned;
	}
	/**
	 * 校验$value是否非空
	 * if not set ,return true;
	 * if is null , return true;
	 */
	protected function checkEmpty($value) {
		if (! isset ( $value ))
			return true;
		if ($value === null)
			return true;
		if (trim ( $value ) === "")
			return true;
		
		return false;
	}
	public static function xmlSafeStr($str) {
		return '<![CDATA[' . preg_replace ( "/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $str ) . ']]>';
	}
	public static function xmlSafeNum($Num) {
		return $Num;
	}
	/**
	 * 数据XML编码
	 *
	 * @param mixed $data
	 *        	数据
	 * @return string
	 */
	public static function data_to_xml($data) {
		$xml = '';
		foreach ( $data as $key => $val ) {
			is_numeric ( $key ) && $key = "Item";
			$xml .= "<$key>";
			if (is_numeric ( $val )) {
				$xml .= self::xmlSafeNum ( $val );
			} else {
				$xml .= (is_array ( $val ) || is_object ( $val )) ? self::data_to_xml ( $val ) : self::xmlSafeStr ( $val );
			}
			
			list ( $key, ) = explode ( ' ', $key );
			$xml .= "</$key>";
		}
		return $xml;
	}
	
	/**
	 * XML编码
	 *
	 * @param mixed $data
	 *        	数据
	 * @param string $root
	 *        	根节点名
	 * @param string $item
	 *        	数字索引的子节点名
	 * @param string $attr
	 *        	根节点属性
	 * @param string $id
	 *        	数字索引子节点key转换的属性名
	 * @param string $encoding
	 *        	数据编码
	 * @return string
	 */
	public function xml_encode($data, $root = 'xml', $item = 'item', $attr = '', $id = 'id', $encoding = 'GBK') {
		if (is_array ( $attr )) {
			$_attr = array ();
			foreach ( $attr as $key => $value ) {
				$_attr [] = "{$key}=\"{$value}\"";
			}
			$attr = implode ( ' ', $_attr );
		}
		$attr = trim ( $attr );
		$attr = empty ( $attr ) ? '' : " {$attr}";
		$xml = "<{$root}{$attr}>";
		$xml .= self::data_to_xml ( $data, $item, $id );
		$xml .= "</{$root}>";
		return $xml;
	}
	
	/**
	 * GET 请求
	 *
	 * @param string $url        	
	 */
	private function http_get($url) {
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
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
	 * @return string content
	 */
	private function http_post($url, $param) {
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
		}
		if (is_string ( $param )) {
			$strPOST = $param;
		} else {
			$aPOST = array ();
			foreach ( $param as $key => $val ) {
				$aPOST [] = $key . "=" . urlencode ( $val );
			}
			$strPOST = join ( "&", $aPOST );
		}
		$headers = array (
				'content-type: application/x-www-form-urlencoded;charset=UTF-8' 
		);
		curl_setopt ( $oCurl, CURLOPT_HTTPHEADER, $headers );
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
	 * 支付宝不支持中文转义的json结构
	 *
	 * @param array $arr        	
	 */
	static function json_encode($arr) {
		$parts = array ();
		$is_list = false;
		// Find out if the given array is a numerical array
		$keys = array_keys ( $arr );
		$max_length = count ( $arr ) - 1;
		if (($keys [0] === 0) && ($keys [$max_length] === $max_length)) { // See if the first key is 0 and last key is length - 1
			$is_list = true;
			for($i = 0; $i < count ( $keys ); $i ++) { // See if each key correspondes to its position
				if ($i != $keys [$i]) { // A key fails at position check.
					$is_list = false; // It is an associative array.
					break;
				}
			}
		}
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) { // Custom handling for arrays
				if ($is_list)
					$parts [] = self::json_encode ( $value ); /* :RECURSION: */
				else
					$parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
			} else {
				$str = '';
				if (! $is_list)
					$str = '"' . $key . '":';
					// Custom handling for multiple data types
				if (is_numeric ( $value ) && $value < 2000000000)
					$str .= $value; // Numbers
				elseif ($value === false)
					$str .= 'false'; // The booleans
				elseif ($value === true)
					$str .= 'true';
				else
					$str .= '"' . addslashes ( $value ) . '"'; // All other things
						                                           // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts [] = $str;
			}
		}
		$json = implode ( ',', $parts );
		if ($is_list)
			return '[' . $json . ']'; // Return numerical JSON
		return '{' . $json . '}'; // Return associative JSON
	}
}

/**
 * 数组转XML类库
 *
 * @filesource ArrayToXML.php
 * @author gentwolf
 * @version 1.0 2013/08/23
 *         
 *          使用说明：
 *          echo ArrayToXml::parse($array, 'root');
 */
class ArrayToXML {
	// 文档对象
	private static $doc = NULL;
	// 版本号
	private static $version = '1.0';
	
	/**
	 * 初始化文档版本及编码
	 *
	 * @param string $version
	 *        	版本号
	 * @param string $encoding
	 *        	XML编码
	 */
	public static function init($version, $encoding) {
		self::$doc = new DomDocument ( $version, $encoding );
		self::$doc->formatOutput = true;
	}
	
	/**
	 * 转换数组到XML
	 *
	 * @param array $array
	 *        	要转换的数组
	 * @param string $rootName
	 *        	要节点名称
	 * @param string $version
	 *        	版本号
	 * @param string $encoding
	 *        	XML编码
	 *        	
	 * @return string
	 */
	public static function parse($array, $rootName = 'root', $version = '1.0', $encoding = 'UTF-8') {
		self::init ( $version, $encoding );
		
		// 转换
		$node = self::convert ( $array, $rootName );
		self::$doc->appendChild ( $node );
		
		return self::$doc->saveXML ();
	}
	
	/**
	 * 递归转换
	 *
	 * @param array $array
	 *        	数组
	 * @param string $nodeName
	 *        	节点名称
	 *        	
	 * @return object (DOMElement)
	 */
	private static function convert($array, $nodeName) {
		if (! is_array ( $array ))
			return false;
			
			// 创建父节点
		$node = self::createNode ( $nodeName );
		
		// 循环数组
		foreach ( $array as $key => $value ) {
			$element = self::createNode ( $key );
			
			// 如果不是数组，则创建节点的值
			if (! is_array ( $value )) {
				$element->appendChild ( self::createValue ( $value ) );
				$node->appendChild ( $element );
			} else {
				// 如果是数组，则递归
				$node->appendChild ( self::convert ( $value, $key, $element ) );
			}
		}
		return $node;
	}
	private static function createNode($name) {
		$node = NULL;
		
		// 如果是字符串，则创建节点
		if (! is_numeric ( $name )) {
			$node = self::$doc->createElement ( $name );
		} else {
			// 如果是数字，则创建默认item节点
			$node = self::$doc->createElement ( 'item' );
		}
		
		return $node;
	}
	
	/**
	 * 创建文本节点
	 *
	 * @param
	 *        	string || bool || integer $value
	 *        	
	 * @return object (DOMText || DOMCDATASection );
	 */
	private static function createValue($value) {
		$textNode = NULL;
		
		// 如果是bool型，则转换为字符串
		if (true === $value || false === $value) {
			$textNode = self::$doc->createTextNode ( $value ? 'true' : 'false' );
		} else {
			// 如果含有HTML标签，则创建CDATA节点
			if (strpos ( $value, '<' ) > - 1) {
				$textNode = self::$doc->createCDATASection ( $value );
			} else {
				$textNode = self::$doc->createTextNode ( $value );
			}
		}
		
		return $textNode;
	}
}