<?php
	
/**
* @author jiahaiming
* POS机订单号支付
*/
	
class PosOrderIdApi{

	private $MerId;									//商户号
	private $Terminal;								//终端号
	private $UserKey;								//签名
	private $OrderId;								//第三方流水号
	private $MyOrderId;								//订单号
	private $PayStatus;								//状态
	private $BatchNumber;							//批次号
	
	public function __construct($data = ''){
		if($data['merid']&&$data['userkey']&&$data['terminal']&&$data['myorderid']){
			$this->MerId = $data['merid'];
			$this->Terminal = $data['terminal'];
			$this->UserKey = $data['userkey'];
			$this->MyOrderId = $data['myorderid'];
		}else{
			$this->resendMsg("10404","数据缺失");
		}
		if($data['mytype'] == 2){
			if($data['pay_status']&&$data['orderid']&&$data['batchnumber']){
				$this->PayStatus = $data['pay_status'];
				$this->OrderId = $data['orderid'];
				$this->BatchNumber = $data['batchnumber'];
			}else{
				$this->resendMsg("10404","数据缺失");
			}
		}
		$Ip = get_client_ip();
		$IpList = array("116.236.224.51","220.248.12.117","127.0.0.1");//授权IP请求
		if(!in_array($Ip,$IpList)){
			$this->resendMsg("10401","IP识别错误");
		}
	}
	
	//获取订单信息
	public function GetOrder(){
		$is_pos = $this->CheckBuy();
		$WxPosyhjlog = new WxPosyhjlog();
		$WxPosyhjlog->insert(array("merid"=>$this->MerId,"terminal"=>$this->Terminal,"mycode"=>$this->MyOrderId,"type"=>2,"cdate"=>time()));
		if($is_pos){
			$WxOrder = new WxOrder();
			$mytime = time();
			$oinfo = $WxOrder->scalar("*","where order_status=1 and over_time>'$mytime' and order_no='".$this->MyOrderId."'	");
			if($oinfo){
				if($oinfo['pay_status'] == 1){
					$this->resendMsg("10260","订单已经支付完成");
				}
				$_RE['error'] = "200";
				$_REinfo['myamount'] = $oinfo['order_amount'];
				$_REinfo['myorderid'] = $oinfo['order_no'];
				$_REinfo['mykey'] = md5(date("Ymd")."checkposdodcacom2014");
				$_REinfo['merid'] = $this->MerId;
				$_REinfo['terminal'] = $this->Terminal;
				$_RE['message'] = $_REinfo;
				echo json_encode($_RE);
				exit();
			}else{
				$this->resendMsg("10404","订单号不存在或者已经过期");
			}
		}else{
			$this->resendMsg("10500","签名错误");
		}
	}
	
	//支付返回
	public function SetOrder(){
		$is_pos = $this->CheckBuy();
		$WxPosyhjlog = new WxPosyhjlog();
		if($is_pos){
			$WxOrder = new WxOrder();
			$mytime = time();
			$oinfo = $WxOrder->scalar("*","where order_status=1 and over_time>'$mytime' and order_no='".$this->MyOrderId."'	");
			if($oinfo){
				if($oinfo['pay_status'] == 1){
					$this->resendMsg("10260","订单已经支付完成");
				}
				if($this->PayStatus == 1){
					$dbsta = $WxOrder->update(array("pk_order_num" => $this->OrderId, "pay_status" => 1,"pay_type"=>11), "where order_no='".$this->MyOrderId."'");
					$WxPosyhjlog->update(array("orderid"=>$this->OrderId,"batchnumber"=>$this->BatchNumber,"status"=>1),"where type=2 and mycode='".$this->MyOrderId."'");
					if($dbsta){
						$this->resendMsg("200",array("mykey"=>md5(date("Ymd")."checkposdodcacom2014"),"orderid"=>$this->OrderId,"batchnumber"=>$this->BatchNumber));//成功返回签名
					}else{//做两次处理
						$dbsta = $WxOrder->update(array("pk_order_num" => $this->OrderId, "pay_status" => 1,"pay_type"=>11), "where order_no='".$this->MyOrderId."'");
						if($dbsta){
							$this->resendMsg("200",array("mykey"=>md5(date("Ymd")."checkposdodcacom2014"),"orderid"=>$this->OrderId,"batchnumber"=>$this->BatchNumber));//成功返回签名
						}else{
							$this->resendMsg("500","支付失败");//支付失败
						}
					}
				}else{
					$this->resendMsg("500","支付失败");//支付失败
				}
			}else{
				$this->resendMsg("10404","订单号不存在或者已经过期");
			}
		}else{
			$this->resendMsg("10500","签名错误");
		}
	}
	
	//验证签名
	public function CheckBuy(){
		$key_str = md5($this->MerId.$this->Terminal.$this->MyOrderId.date("Ymd")."checkposdodcacom2014");
		if($key_str == $this->UserKey){
			return true;
		}else{
			return false;
		}
	}
	
	//状态返回
	public function resendMsg($num, $msg = ''){
		$_RE['error'] = $num;
		$_RE['message'] = $msg;
		echo json_encode($_RE);
		exit();
	}
}
?>
