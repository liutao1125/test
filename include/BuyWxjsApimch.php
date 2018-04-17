<?php

/**
* @author dengjianjun 
* Class BuyWxjsApimch derivation from Class BuyWxjsApi By jiahaiming
* 微信商户平台JSAPI支付
*/
include_once("Wxbuy/MchPayHelper.php");

class BuyWxjsApimch{

	private $MCHID;
	private $KEY;
	private $APPID;
	private $AppSecret;
	private $spbill_create_ip;
	private $notify_url;
	private $body = "商品";
	private $out_trade_no;
	private $total_fee;
	private $trade_type;
	private $openid;
	private $nonce_str;
	private $wxPayHelper;
	public function __construct($data = '',$trade_type="JSAPI"){
		$this->KEY = $data['KEY'];
		$this->APPID = $data['APPID'];
		$this->AppSecret = $data['AppSecret'];
		$this->MCHID = $data['MCHID'];
		$this->trade_type  = $trade_type;
		$this->notify_url = WHB_DOMAIN."/buy/wxmchbody";
		$this->spbill_create_ip = "127.0.0.1";
		$this->out_trade_no = $data['out_trade_no'];
		$this->total_fee = $data['total_fee'];
		$this->openid = $data['openid'];
		//实例化MchApi类
		$keyinfo["KEY"] = $data['KEY'];
		$keyinfo["MCHID"] = $data['MCHID'];
		$keyinfo["APPID"] = $data['APPID'];
		$this->wxPayHelper = new MchPayHelper($keyinfo);
		$this->nonce_str = $this->wxPayHelper->createNoncestr(32);
	}
	
	//获取签名
	public function GetChkValue(){
		$this->wxPayHelper->setParameter("appid",$this->APPID); 		
		$this->wxPayHelper->setParameter("mch_id", $this->MCHID);								    //商户号
		$this->wxPayHelper->setParameter("nonce_str",$this->nonce_str);
		$this->wxPayHelper->setParameter("body", $this->body);									//商品描述
		$this->wxPayHelper->setParameter("out_trade_no", $this->out_trade_no);					//订单号
		$this->wxPayHelper->setParameter("total_fee", $this->total_fee);							//订单总金额
		$this->wxPayHelper->setParameter("spbill_create_ip", $this->spbill_create_ip);			//用户端IP
		$this->wxPayHelper->setParameter("notify_url",$this->notify_url);						    //通知URL
		$this->wxPayHelper->setParameter("trade_type",$this->trade_type); 						//交易类型JSAPI、NATIVE、APP
		$this->wxPayHelper->setParameter("openid",$this->openid);									//用户openid
		return $this->wxPayHelper->create_biz_package();
	}  
    public function order_query($out_trade_no){
    	return $this->wxPayHelper->order_query($out_trade_no);
    }
    public function get_nav_prepareid($order_no,$openid){
    	$wxorder = new WxOrder();
    	$orderinfo = $wxorder->get_data_order_no($order_no);
    	$this->wxPayHelper->setParameter("appid",$this->APPID);
    	$this->wxPayHelper->setParameter("mch_id", $this->MCHID);								    //商户号
    	$this->wxPayHelper->setParameter("nonce_str",$this->nonce_str);
    	$this->wxPayHelper->setParameter("body","微信结账");									//商品描述
    	$this->wxPayHelper->setParameter("out_trade_no",$order_no);					        //订单号
    	$this->wxPayHelper->setParameter("total_fee",$orderinfo ['order_amount'] * 100);							//订单总金额
    	$this->wxPayHelper->setParameter("spbill_create_ip", $this->spbill_create_ip);			//用户端IP
    	$this->wxPayHelper->setParameter("notify_url",$this->notify_url);						    //通知URL
    	$this->wxPayHelper->setParameter("trade_type","NATIVE"); 						//交易类型JSAPI、NATIVE、APP
    	$this->wxPayHelper->setParameter("openid",$openid);								//用户openid
    	$this->wxPayHelper->setParameter("product_id",$order_no);	
    	if($orderinfo["pay_status"]==1){
    		return $this->wxPayHelper->get_nav_biz_package("SUCCESS","FAIL","该订单已支付，请勿重复支付！");
    	}else{
    		return $this->wxPayHelper->get_nav_biz_package();
    	}
    }
    /**
     * 创建预支付订单
     */
    public function createorder($reqdata){
    	/* $data["order_no"] = $reqdata["order_no"];
    	$data["total_amount"] = $data["order_amount"] = $reqdata["total_amount"];
    	$data["from_type"] = 14;
    	$data["pay_type"] = 10;
    	$data["userid"] = $reqdata["userid"];
    	$data["pay_status"] = 0;
    	$data["over_time"] = time()+3600*2;
    	$data["order_status"] = 1;
    	$data["post_status"] = 1;
    	$wxorder = new WxOrder();
    	if($wxorder->insert_data($data)){ */
    		return $this->wxPayHelper->createLink($reqdata["order_no"]);
    	/* }else{
    		return false;
    	} */
    }
}
?>
