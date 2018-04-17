<?php
/**
* @author jiahaiming
* 微信JS支付
*/
class BuyWxjsApi{

	private $APPID;
	private $APPKEY;
	private $PARTNERKEY;
	private $AppSecret;
	private $partner;
	private $bank_type = "WX";
	private $input_charset = "UTF-8";
	private $spbill_create_ip;
	private $notify_url;
	private $fee_type = "1";
	private $body = "商品";
	private $out_trade_no;
	private $total_fee;
	
	public function __construct($data = ''){
		$this->APPID = $data['APPID'];
		$this->APPKEY = $data['APPKEY'];
		$this->PARTNERKEY = $data['PARTNERKEY'];
		$this->AppSecret = $data['AppSecret'];
		$this->partner = $data['partner'];
		$this->spbill_create_ip = get_client_ip();
		$this->notify_url = WHB_DOMAIN."/buy/wxbody";
		$this->out_trade_no = $data['out_trade_no'];
		$this->total_fee = $data['total_fee'];
	}
	
	//获取签名
	public function GetChkValue(){
		$keyinfo['APPID'] = $this->APPID;
		$keyinfo['APPKEY'] = $this->APPKEY;
		$keyinfo['PARTNERKEY'] = $this->PARTNERKEY;
		include_once("Wxbuy/WxPayHelper.php");
		$commonUtil = new CommonUtil();
		$wxPayHelper = new WxPayHelper($keyinfo);
		$wxPayHelper->setParameter("bank_type", $this->bank_type);							//银行通道类型
		$wxPayHelper->setParameter("body", $this->body);									//商品描述
		$wxPayHelper->setParameter("partner", $this->partner);								//商户号
		$wxPayHelper->setParameter("out_trade_no", $this->out_trade_no);					//订单号
		$wxPayHelper->setParameter("total_fee", $this->total_fee);							//订单总金额
		$wxPayHelper->setParameter("fee_type", $this->fee_type);							//现金支付币种(人民币)
		$wxPayHelper->setParameter("notify_url", $this->notify_url);						//通知URL
		$wxPayHelper->setParameter("spbill_create_ip", $this->spbill_create_ip);			//用户端IP
		$wxPayHelper->setParameter("input_charset", $this->input_charset);					//参数字符编码
		
		return $wxPayHelper->create_biz_package();
	}
	
    
    /**
     * 发货通知
     *  
     * openid					购买用户的 OpenId，这个已经放在最终支付结果通知的 PostData 里了 
     * transid					交易单号
     * out_trade_no				第三方订单号
     * deliver_timestamp		发货时间戳
     * deliver_status			发货状态	1:成功 0:失败
     * deliver_msg				发货状态信息	
    
     *
     */
    public function delivernotify($openid,$transid,$out_trade_no,$deliver_status=1,$deliver_msg='ok'){
    	$post = array();
    	$post['appid'] = $this->APPID;
    	$post['appkey'] = $this->APPKEY;
    	$post['openid'] = $openid;
    	$post['transid'] = $transid;
    	$post['out_trade_no'] = $out_trade_no;
    	$post['deliver_timestamp'] = time();
    	$post['deliver_status'] = $deliver_status;
    	$post['deliver_msg'] = $deliver_msg;
    	
    	$post['app_signature'] = $this->create_app_signature($post);
    	$post['sign_method'] = "SHA1";
    	
    	$data = json_encode($post);
    	
    	$url = 'https://api.weixin.qq.com/pay/delivernotify?access_token=' . $this->get_access_token();
	    $ret = $this->sub_curl($url,$data);
	    /*$str="";
	    foreach($ret as $key=>$val){
	    	$str.=$key.":".$val."||";
	    }
	    PubFun::save_log($str,'wzf_log');*/
	    if ( in_array($ret['errcode'],array(40001,40002,42001)) ){
	    	$this->get_access_token(false);
         	return $this->delivernotify($openid,$transid,$out_trade_no,$deliver_status,$deliver_msg);
        }
	    return $ret;
    }
    
    
     /**
     * 订单查询
     * @return array
     */
    public function order_query($out_trade_no){
    	$post = array();
    	$post['appid'] = $this->APPID;
    	$sign = $this->create_sign(array('out_trade_no' => $out_trade_no , 'partner' => $this->PARTNERKEY ));
    	$post['package'] = "out_trade_no=$out_trade_no&partner=".$this->PARTNERKEY."&sign=$sign";
    	$post['timestamp'] = time();
    	
    	$post['app_signature'] = $this->create_app_signature(array('appid' => $this->APPID , 'appkey' => $this->APPKEY , 'package' => $post['package'] , 'timestamp' => $post['timestamp'] ));
    	$post['sign_method'] = "SHA1";
    	
    	$data = json_encode($post);
    	
    	$url = 'https://api.weixin.qq.com/pay/orderquery?access_token=' . $this->get_access_token();
	    $ret = $this->sub_curl($url,$data);
	    if ( in_array($ret['errcode'],array(40001,40002,42001)) ){
	    	$this->get_access_token(false);
         	return $this->order_query($out_trade_no);
        }
	    return $ret;
    }
    
	
	
	 /**
     * 创建app_signature
     * @return string
     */
    public function create_app_signature( $arr ){
        $para = $this->parafilter($arr);
		$para = $this->argsort($para); 
		$signValue = sha1($this->createlinkstring($para));
        return $signValue;
    }
    
    
    /**
     * 创建sign
     * @return string
     */
    public function create_sign( $arr ){
        $para = $this->parafilter($arr);
		$para = $this->argsort($para);
		$signValue = $this->createlinkstring($para);
		$signValue = $signValue."&key=".$this->partnerKey;
		$signValue = strtoupper(md5($signValue));	
        return $signValue;
    }
	
	/**
     * 从xml中获取数组
     * @return array
     */
	public function getXmlArray() {
		$postStr = @file_get_contents('php://input');
		if ($postStr) {
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			if (! is_object($postObj)) {
				return false;
			}
			$array = json_decode(json_encode($postObj), true); // xml对象转数组
			return array_change_key_case($array, CASE_LOWER); // 所有键小写
		} else {
			return false;
		}        
	}
    
    
    /**
	 * 验证服务器通知
	 * @param array $data
	 * @return array
	 */
	public function verifyNotify($post,$sign) {
        $para = $this->parafilter($post);
		$para = $this->argsort($para); 
		$signValue = $this->createlinkstring($para);
		$signValue = $signValue."&key=".$this->PARTNERKEY;
		$signValue = strtoupper(md5($signValue));
		if ( $sign == $signValue ){
			return true;	
		}else{
			return false;
		}
	}
	
	 /**
     * 标记客户的投诉处理状态
     * @return bool
     */
    public function payfeedback_update($openid,$feedbackid){
    	 $url = "https://api.weixin.qq.com/payfeedback/update?access_token=".$this->access_token()."&openid=".$openid."&feedbackid=".$feedbackid;
         $ret = $this->sub_curl($url);
         if ( in_array($ret['errcode'],array(40001,40002,42001)) ){
         	$this->get_access_token(false);
         	return $this->payfeedback_update($openid,$feedbackid);
         }
         return $ret;
    }
	
	
	/**
 	* 除去数组中的空值和签名参数
 	* @param $para 签名参数组
 	* return 去掉空值与签名参数后的新签名参数组
 	*/
	
	public	function parafilter($para) {
		$para_filter = array();
		foreach ($para as $key => $val ) {
			if($key == "sign_method" || $key == "sign" ||$val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	
	/**
	 * 对数组排序
 	* @param $para 排序前的数组
 	* return 排序后的数组
 	*/
	public function argsort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	public function createlinkstring($para) {
		$arg  = "";
		foreach ($para as $key => $val ) {
			$arg.=strtolower($key)."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;	

	}
	
	/**
     * 获取用户基本信息
     * @return array
     */
    public function user_info($openid){
    	$ret = $this->open("https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->get_access_token()."&openid=$openid&lang=zh_CN");
    	if ( in_array($ret['errcode'],array(40001,40002,42001)) ){
        	$this->get_access_token(false);
         	return $this->user_info($openid);
        }
        return $ret;
    }
	
	public function get_access_token($from_cache=true){
		$url="https://api.weixin.qq.com/cgi-bin/token";
		$par["grant_type"]="client_credential";
		$par["appid"]=$this->APPID;
		$par["secret"]=$this->AppSecret;
		$mc_key=$this->APPID."_mc_acc_token";
		$access_token=mc_get($mc_key);
		if($from_cache&&$access_token){
			return $access_token;
		}else{
			$data=$this->sub_curl($url,$par,0);
			if(is_array($data) && isset($data["access_token"]) && $data["access_token"]){
				$access_token=$data["access_token"];
				mc_set($mc_key,$access_token,$data["expires_in"]-1200);
			}
			return $access_token;
		}
	}
	
	public function sub_curl($url,$data,$is_post=1){
		$ch = curl_init();
		if(!$is_post){//get 请求
			$url =  $url.'?'.http_build_query($data);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $is_post);
		if($is_post){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$info = curl_exec($ch);
		$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		return json_decode($info,true);
	}
}
?>
