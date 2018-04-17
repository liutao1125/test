<?php
class FacePay {
	private $userid;
	private $orderinfo;
	private $type=1; //1:微信扫码支付  2：支付宝扫码支付
	private $errcode=-1;
	private $errmsg="系统繁忙！";
	private $qrcode="";
	function __construct($userid,$order_no){
		$wxorder = new WxOrder();
		$orderinfo = $wxorder->get_data_order_no($order_no);
		if($orderinfo&&$orderinfo["userid"]==$userid&&!empty($orderinfo["total_amount"])){
			if($orderinfo["pay_status"]==1){
				$this->errmsg = "该订单已支付完成，请勿重复支付！";
			}else{
				$this->userid = $userid;
				$this->orderinfo = $orderinfo;
				$pay_type = $orderinfo["pay_type"];
				switch ($pay_type){
					case 10 :
						$this->wx_face();
						break;
					case 13:
						$this->ali_face();
						break;
					default:
						$this->wx_face();
						break;
				}
			}
		}else{
			$this->errmsg = "请求未通过安全校验！";
		}
	}
	function get_qrcode(){
		if($this->errcode!=0){
			$arr = array("errcode"=>$this->errcode,"errmsg"=>$this->errmsg);
		}else{
			$arr = array("errcode"=>$this->errcode,"qrcode"=>WHB_DOMAIN."/buy/qrshow?url=".urlencode($this->qrcode));
		}
		return $arr;
	}
	//微信支付
	function wx_face(){
		$orderinfo = $this->orderinfo;
		$userid = $this->userid;
		$WxBuyinfo = new WxBuyinfo ();
		$buyinfo = $WxBuyinfo->scalar ( "*" ,"where userid='$userid' and type=10 and status=1" );
		$wxdata ['MCHID'] = $buyinfo ['merid'];
		$wxdata ['KEY'] = $buyinfo ['key1'];
		$wxdata ['APPID'] = $buyinfo ['key2'];
		$wxdata ['AppSecret'] = $buyinfo ['key3'];
		if(!empty($wxdata ['MCHID'])&&!empty($wxdata ['KEY'])&&!empty($wxdata ['APPID'])&&!empty($wxdata ['AppSecret'])){
			$BuyWxjsApimch = new BuyWxjsApimch($wxdata);
			$this->qrcode = $BuyWxjsApimch->createorder($this->orderinfo);
			$this->errcode = 0;
		}else{
			$this->errmsg = "该商户未设置新版微信支付！";
		}
		
	}
	//支付宝扫码支付
	function ali_face(){
		include_once("Alipay/AliFacePay.php");
		$orderinfo = $this->orderinfo;
		$userid = $this->userid;
		$WxBuyinfo = new WxBuyinfo ();
		$buyinfo = $WxBuyinfo->scalar ( "*" ,"where userid='$userid' and type=13 and status=1" );
		$seller_mail = $buyinfo['merid'];
		$md5key = $buyinfo['key1'];
		$partner= $buyinfo['key2'];
		if(!empty($seller_mail)&&!empty($md5key)&&!empty($partner)){
			$facepay = new AliFacePay($partner,$md5key,$seller_mail);
			$ret = $facepay->precreate($orderinfo["order_no"],"支付宝结账",$orderinfo["total_amount"]);
			if($ret["is_success"][0]=="T"){
				if(!empty($ret["response"]["alipay"]["qr_code"])){
					$this->qrcode = $ret["response"]["alipay"]["qr_code"];
					$this->errcode = 0;
				}else{
					$this->errmsg = "支付宝参数设置错误或无接口权限！";
					$this->errcode = -1;
				}
				
			}else{
				$this->errmsg = $ret["error"][0];
			}
		}else{
			$this->errmsg = "该商户未设置支付宝手机WAP支付！";
		}
	}
}