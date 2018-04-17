<?php
/**
* @author jiahaiming
* 易宝支付
*/
class BuyYeeApi{
	private $merchantaccount;			//商户编号
	private $merchantPrivateKey;		//商户私钥
	private $merchantPublicKey;			//商户公钥
	private $yeepayPublicKey;			//易宝公钥
	private $order_id;					//客户订单号
	private $transtime;			  		//交易时间
	private $product_catalog = '13'; 	//商品类别码(虚拟产品)
	private $identity_id; 				//支付身份标识  商户生成的用户账号唯一标识UID
	private $identity_type = 2; 		//支付身份标识类型码(用户ID)
	private $user_ip; 					//用户支付时使用的网络终端IP
	private $user_ua; 					//用户使用的移动终端的(选填)
	private $other; 					//手机传IMEI，其他设备传MAC地址(选填)
	private $callbackurl; 				//商户后台系统回调地址，前后台的回调结果一样
	private $fcallbackurl; 				//商户前台系统回调地址，前后台的回调结果一样
	private $product_name; 				//商品名称
	private $product_desc; 				//商品描述
	private $amount; 					//交易金额以"分"为单位的整型，必须大于零

	public function __construct($data=''){
		$this->transtime = time();
		$this->merchantaccount = $data['merchantaccount'];
		$this->merchantPrivateKey = $data['merchantPrivateKey'];
		$this->merchantPublicKey = $data['merchantPublicKey'];
		$this->yeepayPublicKey = $data['yeepayPublicKey'];
		$this->product_catalog = $data['productcatalog'];
		$this->order_id = $data['OrdId'];
		$this->amount = (int)$data['OrdAmt'];
		if($data['openid']>0){
			$this->identity_id = $data['openid'];
		}else{
			$this->identity_id = time();
		}
		$this->user_ip = get_client_ip();
		$this->product_name = $data['product_name'];
		$this->product_desc = $data['product_desc'];
		$this->callbackurl = WHB_DOMAIN."/buy/postyeecallback/".$this->order_id;
		$this->fcallbackurl = WHB_DOMAIN."/buy/getyeecallback/".$this->order_id;
		$this->user_ua = $_SERVER['HTTP_USER_AGENT'];
		$this->other = '111';
	}
	
	//获取签名
	public function GetChkValue(){
		include('Yeepay/yeepayMPay.php');
		$WxOrder = new WxOrder();
		$userid = $WxOrder->scalar("userid","where order_no='".$this->order_id."'");
		$yeepay = new yeepayMPay($this->merchantaccount,$this->merchantPublicKey,$this->merchantPrivateKey,$this->yeepayPublicKey,$userid);
		$url = $yeepay->webPay($this->order_id,$this->transtime,$this->amount,$this->product_catalog,$this->identity_id,$this->identity_type,$this->user_ip,$this->user_ua,$this->callbackurl,$this->fcallbackurl,156,$this->product_name,$this->product_desc,$this->other);
		return $url;
	}
	
	//回调
	public function CallBack($data){
		include('Yeepay/yeepayMPay.php');
		$yeepay = new yeepayMPay($this->merchantaccount,$this->merchantPublicKey,$this->merchantPrivateKey,$this->yeepayPublicKey);
		try {
			$return = $yeepay->callback($data['data'], $data['encryptkey']);
			return $return;
		}catch (yeepayMPayException $e) {
			return 'error';
		}
	}
}
?>
