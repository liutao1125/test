<?php
	
/**
* @author jiahaiming
* POS机优惠券
*/
	
class PosYhjApi{

	private $MerId;									//商户号
	private $Terminal;								//终端号
	private $UserKey;								//签名
	private $MyCode;								//订单号
	private $Status;								//状态
	private $Ordamt;								//金额
	private $OrderId;								//第三方流水号
	private $BatchNumber;							//批次号
	private $Userid;								//用户ID
	
	public function __construct($data = ''){
		if($data['merid']&&$data['userkey']&&$data['terminal']&&$data['mycode']){
			$this->MerId = $data['merid'];
			$this->Terminal = $data['terminal'];
			$this->UserKey = $data['userkey'];
			$this->MyCode = $data['mycode'];
		}else{
			$this->resendMsg("10404","数据缺失");
		}
		if($data['mytype'] == 2){
			if($data['status']&&$data['orderid']&&$data['batchnumber']){
				$this->PayStatus = $data['status'];
				$this->OrderId = $data['orderid'];
				$this->BatchNumber = $data['batchnumber'];
			}else{
				$this->resendMsg("10404","数据缺失");
			}
		}
		if($data['mytype'] == 1){
			if($data['ordamt']){
				$this->Ordamt = $data['ordamt'];
			}else{
				$this->resendMsg("10404","数据缺失");
			}
		}
		if($data['mytype'] == 3){
			if($data['orderid']&&$data['batchnumber']){
				$this->OrderId = $data['orderid'];
				$this->BatchNumber = $data['batchnumber'];
			}else{
				$this->resendMsg("10404","数据缺失");
			}
		}
		
		$WxPosBd = new WxPosBd();
		$this->Userid = $WxPosBd->scalar("userid","where posmerid='".$this->MerId."' and posterminal='".$this->Terminal."'");
		if(!$this->Userid){
			$this->resendMsg("10401","机器未绑定用户");
		}
		$Ip = get_client_ip();
		//PubFun::save_log($Ip,"xhjip");
		$IpList = array("116.236.224.51","220.248.12.117","127.0.0.1");//授权IP请求
		if(!in_array($Ip,$IpList)){
			//$this->resendMsg("10401","IP识别错误");
		}
	}
	
	//获取优惠券信息
	public function GetOrder(){
		$is_pos = $this->CheckBuy();
		$WxPosyhjlog = new WxPosyhjlog();
		if($is_pos){
			//回复优惠券详细信息
			$codearr = explode(',',$this->MyCode);
			$codearr = array_unique($codearr);
			foreach($codearr as $val){
				$WxPosyhjlog->insert(array("merid"=>$this->MerId,"terminal"=>$this->Terminal,"mycode"=>$val,"ordamt"=>$this->Ordamt,"cdate"=>time()));
			}
			$codearrcount = count($codearr);
			if($codearrcount>1){
				$zmoney = 0;
				foreach($codearr as $val){
					if($val){
						//$zk = FansCard::get_info_bycode($val);
						$zk = Coupons::get_info_bycode($val);
						if($zk){
							if($zk["coupons"]["userid"] != $this->Userid){
								$this->resendMsg("10501","优惠券无效");
							}
							if($zk["codeinfo"]["merchant_type"] == 5){
								$zmoney += $zk["codeinfo"]['money_exempt'];
							}elseif($zk["codeinfo"]["merchant_type"] == 3){
								$this->resendMsg("10501","多张不能使用折扣券");
							}else{
								$this->resendMsg("10501","优惠券无效");
							}
						}else{
							$this->resendMsg("10501","优惠券无效");
						}
					}
				}
				
				if($this->Ordamt>$zmoney){
					$_REinfo['myamount'] = $this->Ordamt-$zmoney;
				}else{
					$_REinfo['myamount'] = 0;
					foreach($codearr as $val){
						$WxPosyhjlog->update(array("status"=>1),"where mycode='$val'");
						//FansCard::update_status($val);
						$codeinfo = Coupons::get_info_bycode($val);
						Coupons::verification_byid($codeinfo["coupons"]['id'],$this->Userid,1,3);
					}
				}
				$_REinfo['myamount'] = sprintf("%.2f",$_REinfo['myamount']); 
				$_REinfo['discount'] = "现金券".$zmoney."元";
				
			}else{
				$zk = Coupons::get_info_bycode($this->MyCode);
				if($zk){
					if($zk["coupons"]["userid"] != $this->Userid){
						$this->resendMsg("10501","优惠券无效");
					}
					if($zk["codeinfo"]["merchant_type"] == 5){
						if($this->Ordamt>$zk["codeinfo"]['money_exempt']){
							$_REinfo['myamount'] = $this->Ordamt-$zk["codeinfo"]['money_exempt'];
						}else{
							$_REinfo['myamount'] = 0;
							$WxPosyhjlog->update(array("status"=>1),"where mycode='".$this->MyCode."'");
							//FansCard::update_status($this->MyCode);
							Coupons::verification_byid($zk["coupons"]['id'],$this->Userid,1,3);
						}
						$_REinfo['myamount'] = sprintf("%.2f",$_REinfo['myamount']); 
						$_REinfo['discount'] = "现金券".$zk["codeinfo"]['money_exempt']."元";
					}elseif($zk["codeinfo"]["merchant_type"] == 3){
						$_REinfo['myamount'] = $this->Ordamt*((100-$zk["codeinfo"]['merchant_discount'])/100);
						$_REinfo['myamount'] = sprintf("%.2f",$_REinfo['myamount']); 
						$_REinfo['discount'] = "折扣券".((100-$zk["codeinfo"]['merchant_discount'])/10)."折";
					}else{
						$this->resendMsg("10501","优惠券无效");
					}
				}else{
					$this->resendMsg("10501","优惠券无效");
				}
			}
			$_RE['error'] = "200"; 
			$_REinfo['mycode'] = $this->MyCode;
			$_REinfo['mykey'] = md5(date("Ymd")."checkposdodcacom2014");
			$_REinfo['merid'] = $this->MerId;
			$_REinfo['terminal'] = $this->Terminal;
			$_REinfo['ordamt'] = $this->Ordamt;
			$_RE['message'] = $_REinfo;
			echo json_encode($_RE);
			exit();
		}else{
			$this->resendMsg("10500","签名错误");
		}
	}
	
	//支付返回
	public function SetOrder(){
		$is_pos = $this->CheckBuy();
		$WxPosyhjlog = new WxPosyhjlog();
		if($is_pos){
			if($this->PayStatus == 1){
				$codearr = explode(',',$this->MyCode);
				foreach($codearr as $val){
					$WxPosyhjlog->update(array("status"=>1,"batchnumber"=>$this->BatchNumber,"orderid"=>$this->OrderId),"where mycode='".$val."'");
					$codeinfo = Coupons::get_info_bycode($val);
					if($codeinfo){
						$info = Coupons::verification_byid($codeinfo["coupons"]['id'],$this->Userid,1,3);
						if($info){
							if($info['status'] == 2){
								$this->resendMsg("10500",$info['cont']);
							}
						}else{
							$this->resendMsg("10500","优惠劵错误");
						}
					}else{
						$this->resendMsg("10500","优惠券无效");
					}
				}
				$this->resendMsg("200",array("mykey"=>md5(date("Ymd")."checkposdodcacom2014"),"orderid"=>$this->OrderId,"batchnumber"=>$this->BatchNumber));//成功返回签名
			}else{
				$this->resendMsg("500","支付失败");//支付失败
			}
		}else{
			$this->resendMsg("10500","签名错误");
		}
	}
	
	
	//核销劵
	public function Verification(){
		$is_pos = $this->CheckBuy();
		$codeinfo = Coupons::get_info_bycode($this->MyCode);
		if($codeinfo){
			if($codeinfo["coupons"]["userid"] != $this->Userid){
				$this->resendMsg("10501","优惠券无效");
			}
			if($codeinfo['codeinfo']['merchant_type'] == 3){
				$discount = ((100-$codeinfo['codeinfo']['merchant_discount'])/10)."折";
			}elseif($codeinfo['codeinfo']['merchant_type'] == 5){
				$discount = "优惠".$codeinfo['codeinfo']['money_exempt']."元";
			}else{
				$this->resendMsg("10501","优惠券无效");
			}
			$info = Coupons::verification_byid($codeinfo["coupons"]['id'],$this->Userid,1,3);
			if($info["status"] == 1){
				$this->resendMsg("200",array("mykey"=>md5(date("Ymd")."checkposdodcacom2014"),"orderid"=>$this->OrderId,"batchnumber"=>$this->BatchNumber,"codeinfo"=>array("code"=>$this->MyCode,"merchant_name"=>$codeinfo["codeinfo"]["merchant_name"],"codename"=>$codeinfo["codeinfo"]["merchant_title_f"],"discount"=>$discount)));//成功返回签名
			}else{
				$this->resendMsg("500","核销失败[{$info['cont']}]");
			}
		}else{
			$this->resendMsg("10501","优惠券无效");
		}
	}
	
	//验证签名
	public function CheckBuy(){
		$key_str = md5($this->MerId.$this->Terminal.$this->MyCode.date("Ymd")."checkposdodcacom2014");
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
