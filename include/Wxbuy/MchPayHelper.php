<?php
/**
* 微信商户平台版接口
*/
include_once("CommonUtilV3.php");
include_once("SDKRuntimeException.class.php");
include_once("MD5SignUtil.php");
class MchPayHelper extends CommonUtilV3
{
	var $parameters; //cft 参数
	private static $unifiedorder = "https://api.mch.weixin.qq.com/pay/unifiedorder";//统一支付接口
	private static $orderquery = "https://api.mch.weixin.qq.com/pay/orderquery";//订单查询接口
	function __construct($data){
		define(KEY ,$data['KEY']); //商户key
		define(MCHID,$data['MCHID']); //商户MCHID
		define(APPID , $data['APPID']);  //appid
		define(SIGNTYPE, "MD5"); //method
	}
	function setParameter($parameter, $parameterValue) {
		$this->parameters[self::trimString($parameter)] = self::trimString($parameterValue);
	}
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
	function check_cft_parameters(){
		if($this->parameters["appid"] == null || $this->parameters["mch_id"] == null || $this->parameters["nonce_str"] == null || 
			$this->parameters["body"] == null || $this->parameters["out_trade_no"] == null ||
			$this->parameters["total_fee"] == null || $this->parameters["spbill_create_ip"] == null || $this->parameters["notify_url"] == null ||
			$this->parameters["trade_type"] == null || $this->parameters["openid"] == null)
		{
			return false;
		}
		return true;
	}

	/**
	 * 	作用：生成签名
	 */
     protected function get_biz_sign($bizObj){
		foreach ($bizObj as $k => $v){
			if(!empty($v)){ //值为空的参数不参与签名;
			   $bizParameters[$k] = $v;
			}
		}
		try {
			if(KEY == ""){
				throw new SDKRuntimeException("KEY为空！" . "<br>");
			}
			ksort($bizParameters);
			$bizString = $this->formatBizQueryParaMap($bizParameters, false);
			$bizString = $bizString."&key=".KEY;
			return strtoupper(md5($bizString));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
    /**
     * 订单查询
     * @return array
     *  正确返回结果数据
     *  Array
		([return_code] => SUCCESS
	    [return_msg] => OK
	    [appid] => wx90524ac7c96be0c4
	    [mch_id] => 10010620
	    [sub_mch_id] => SimpleXMLElement Object
	        (
	            [0] => 
	        )
	    [nonce_str] => eJon1cdHjcJgfn5K
	    [sign] => 012B734C4D859ADCCB39D11CE1D2D0A3
	    [result_code] => SUCCESS
	    [trade_state] => NOTPAY
	    )
     */
	public function order_query($out_trade_no){
		$param = array();
		$param['appid'] = APPID;
		$param['mch_id'] = MCHID;
		$param['out_trade_no'] = $out_trade_no;
		$param['nonce_str'] = $this->createNoncestr(32);
		$sign = $this->get_biz_sign($param);
		$param['sign'] = $sign;
		$data = $this->arrayToXml($param);
		$callbackxml = $this->postXmlCurl($data,self::$orderquery);
		$ret = (array)simplexml_load_string($callbackxml, 'SimpleXMLElement', LIBXML_NOCDATA);
		//s($data);
		//s($callbackxml);
		//s($ret);
		//业务或协议级别错误
		if($ret["return_code"]=="SUCCESS"&&$ret["result_code"]=="SUCCESS"){
			if($ret["trade_state"]=="SUCCESS"){
				return $ret["transaction_id"];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * 统一支付接口
	 *  公众账号ID   appid       		是 	String(32) 		微信分配的公众账号 ID
	 *	商户号                   mch_id      		是 	String(32) 		微信支付分配的商户号
	 *	子商户号               sub_mch_id  		否 	String(32) 		微信支付分配的子商户号，受理模式下必填
	 *	设备号                   device_info 		否 	String(32) 		微信支付分配的终端设备号
	 *	随机字符串           nonce_str   		是 	String(32) 		随机字符串，不长于 32 位
	 *	签名                       sign        		是 	String(32) 		签名,详细签名方法见 3.2 节
	 *	商品描述 	  body 		       	是 	String(127) 	商品描述
	 *	附加数据              attach       		否 	String(127) 	附加数据，原样返回
	 *	商户订单号          out_trade_no 		是 	String(32) 		商户系统内部的订单号 ,32个字符内、 可包含字母,确保
	 *	总金额                 total_fee     		是 	Int 			订单总金额，单位为分，不能带小数点
	 *	终端 IP     spbill_create_ip  是	String(16) 		订单生成的机器 IP
	 *	交易起始时间      time_start   		否 	String(14) 		订 单 生 成 时 间 ， 格 式 为yyyyMMddHHmmss， 如 2009 年12 月 25 日 9 点 10 分 10 秒表示为 20091225091010。时区为 GMT+8 beijing。 该时间取自商户服务器
	 *	交易结束时间     time_expire 		否	String(14) 		订 单 失 效 时 间 ， 格 式 为yyyyMMddHHmmss， 如 2009 年12 月 27 日 9 点 10 分 10 秒表示为 20091227091010。时区为 GMT+8 beijing。 该时间取自商户服务器
	 * 	商品标记             goods_tag 			否	String(32) 		商品标记，该字段不能随便填，不使用请填空，使用说明详见第 5 节
	 *	通知地址             notify_url 		是	String(256) 	接收微信支付成功通知
	 *	交易类型            trade_type 		是 	String(16) 		JSAPI、NATIVE、APP
	 *	用户标识            openid 			否 	String(128) 	用户在商户 appid 下的唯一标识，trade_type 为 JSAPI时，此参数必传，获取方式见表头说明。
	 *	商品 ID    product_id 		否 	String(32)		只在 trade_type 为 NATIVE时需要填写。 此 id 为二维码中包含的商品 ID，商户自行维护。
	 * https://api.mch.weixin.qq.com/pay/unifiedorder
	 */
	protected function get_prepare_id(){
		try {
			$parameters = $this->parameters;
			//PubFun::save_log('微信支付get_prepare_id生成预支付ID参数缺失！：'.json_encode($parameters),'wx_paymch.txt');
			if($this->check_cft_parameters() == false) {
				throw new SDKRuntimeException("生成预支付ID参数缺失！" . "<br>");
			}
			$parameters["sign"]=$this->get_biz_sign($parameters);
			$data = $this->arrayToXml($parameters);
			//s($data);
			$callbackxml = $this->postXmlCurl($data, self::$unifiedorder);
			//s($callbackxml);
			return simplexml_load_string($callbackxml, 'SimpleXMLElement', LIBXML_NOCDATA);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	//获取微信navtive数据包
	function get_nav_biz_package($return_code="SUCCESS",$result_code="SUCCESS",$err_code_des=""){
		try {
			$parameters = $this->parameters;
			//PubFun::save_log('微信支付create_biz_package生成package参数缺失！：'.json_encode($parameters),'wx_paymch.txt');
			if($this->check_cft_parameters() == false) {
				//s($parameters);
				throw new SDKRuntimeException("生成package参数缺失！" . "<br>");
			}
			//s($parameters);
			$preparedata = (array)$this->get_prepare_id();
			//业务或协议级别错误
			if($preparedata["return_code"]=="FAIL"||$preparedata["result_code"]=="FAIL"){
				return $this->arrayToXml($preparedata);
			}else {
				$nativeObj["return_code"] = $return_code;
				$nativeObj["result_code"] = $result_code;
				$nativeObj["err_code_des"] = $err_code_des;
				$nativeObj["appid"] = APPID;
				$nativeObj["mch_id"] = MCHID;
				$nativeObj["prepay_id"] = $preparedata["prepay_id"];
				$nativeObj["nonce_str"] = $this->createNoncestr(32);
				$nativeObj["sign"] = $this->get_biz_sign($nativeObj);
				return   $this->arrayToXml($nativeObj);
			}
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	function create_biz_package(){
		 try {
		 	$parameters = $this->parameters;
		 	//PubFun::save_log('微信支付create_biz_package生成package参数缺失！：'.json_encode($parameters),'wx_paymch.txt');
			if($this->check_cft_parameters() == false) { 
				//s($parameters);  
			   throw new SDKRuntimeException("生成package参数缺失！" . "<br>");
		    }
		    $preparedata = (array)$this->get_prepare_id();
		    //业务或协议级别错误
		    if($preparedata["return_code"]=="FAIL"||$preparedata["result_code"]=="FAIL"){
		    	$nativeObj["errcode"] = false;
		    	$nativeObj["errmsg"] = $preparedata["return_msg"].$preparedata["err_code_des"];
		    	return json_encode($nativeObj);
		    	exit();
		    }
		    $nativeObj["appId"] = APPID;
		    $nativeObj["package"] = "prepay_id=".$preparedata["prepay_id"];
		    $nativeObj["timeStamp"] = time();
		    $nativeObj["nonceStr"] = $this->createNoncestr(32);
		    $nativeObj["signType"] = SIGNTYPE;
		    $nativeObj["paySign"] = $this->get_biz_sign($nativeObj); 
		    return   json_encode($nativeObj);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		   
	}
	
	/**
	 * 生成Native支付链接二维码
	 */
	function createLink($product_id)
	{
		try
		{
			$this->parameters["product_id"] = $product_id;//时间戳
			$this->parameters["appid"] = APPID;//公众账号ID
			$this->parameters["mch_id"] = MCHID;//商户号
			$time_stamp = time();
			$this->parameters["time_stamp"] = "$time_stamp";//时间戳
			$this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
			$this->parameters["sign"] = $this->get_biz_sign($this->parameters);//签名
			$bizString = $this->formatBizQueryParaMap($this->parameters, false);
			return "weixin://wxpay/bizpayurl?".$bizString;
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
}
?>