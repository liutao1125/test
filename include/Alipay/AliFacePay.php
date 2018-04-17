<?php
class CommonUtil {
	public function oAuthRequest($url,$data,$is_post=false){
		//s($data);
		$ch = curl_init();
		if($is_post==false){  //get 请求
			$url =  $url.'?'.http_build_query($data);
		}
		//$this_header = array("Content-type:text/html;charset=UTF-8");
		//curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $is_post);
		if($is_post){
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
	/**
	 * 构造sha1加密
	 */
	public function createSignature($paraMap,$md5key,$urlencode){
		$buff = "";
		ksort($paraMap);
		//s($paraMap);
		foreach ($paraMap as $k => $v){
			if (null != $v && "null" != $v) {
			    if($urlencode){
				   $v = urlencode($v);
				}
				$buff .= $k . "=" . $v . "&";
			}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		//s($reqPar.$md5key);
		return md5($reqPar.$md5key);
	}
	function create_noncestr( $length = 16 ) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
			//$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}
		return $str;
	}
	/**
	 * trim
	 *
	 * @param value
	 * @return
	 */
	function trimString($value){
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
}
/**
 * 支付宝当面付
 * @author dengjianjun@dodoca.com
 *
 */
class AliFacePay extends CommonUtil{
	// 接口网关地址
	private static $APIHOST = 'https://mapi.alipay.com/gateway.do';
	//partner
	private $partner;
	//_input_charset
	private $_input_charset="UTF-8";
	//sign_type  DSA、RSA、MD5三个值可选，必须大写。
	private $sign_type="MD5";
	//notify_url
	private $notify_url;
	//alipay_ca_request  1：证书签名  2：其他密钥签名
	private $alipay_ca_request =2;
	//MD5安全码
	private $md5key ="";
	//$seller_email
	private $seller_email = "";
	//parameters
	private $parameters; //cft 参数
	//product_code
	private $product_code="QR_CODE_OFFLINE";
	function __construct($partner,$md5key,$seller_email){
		$this->partner = $partner;
		$this->md5key = $md5key;
		$this->seller_email = $seller_email;
		$this->notify_url = WHB_DOMAIN."/buy/alifacepaybody";
		 
	}
	function setParameter($parameter, $parameterValue) {
		$this->parameters[self::trimString($parameter)] = self::trimString($parameterValue);
	}
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
	/**
	 * POST wreapper for oAuthRequest.
	 * @return array
	 */
	function postapi($data = array()) {
		$apiurl =self::$APIHOST;
		$response = $this->oAuthRequest($apiurl,$data,true);
		return $response;
	}
	/**
	 * GET wreapper for oAuthRequest.
	 * @return array
	 */
	function getapi($data = array()) {
		$apiurl =self::$APIHOST;
		$response = $this->oAuthRequest($apiurl,$data,false);
		return $response;
	}
	/**
	 * 统一预下单接口
	 */
	function precreate($out_trade_no,$subject,$total_fee){
		$this->parameters["service"] = "alipay.acquire.precreate";
		$this->parameters["partner"] = $this->partner;
		$this->parameters["_input_charset"] = $this->_input_charset;
		$this->parameters["notify_url"] = $this->notify_url;
		$this->parameters["alipay_ca_request"] = $this->alipay_ca_request;
		$this->parameters["out_trade_no"] = $out_trade_no;
		$this->parameters["subject"] = $subject;
		$this->parameters["product_code"] = $this->product_code;
		$this->parameters["total_fee"] = $total_fee;
		$this->parameters["seller_email"] = $this->seller_email;
		$sign = $this->createSignature($this->parameters,$this->md5key,false);
		$this->parameters["sign"] = $sign;
		$this->parameters["sign_type"] = $this->sign_type;
		$ret =  $this->getapi($this->parameters);
		return $this->xml_to_array($ret);
	}
	function xml_to_array($xml)
	{
		$array = (array)(simplexml_load_string($xml));
		foreach ($array as $key=>$item){
			$array[$key]  =  $this->struct_to_array((array)$item);
		}
		return $array;
	}
	function struct_to_array($item) {
		if(!is_string($item)) {
			$item = (array)$item;
			foreach ($item as $key=>$val){
				$item[$key]  =  $this->struct_to_array($val);
			}
		}
		return $item;
	}
}