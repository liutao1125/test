<?php
	
/**
* @author jiahaiming
* POS机支付
*/
	
class PosApi{

	private $MerId;									//商户号
	private $Terminal;								//终端号
	private $Phone;									//手机号码
	private $OrdAmt;								//支付金额需要保留两位小数点
	private $ShoppingType;							//消费类型 1 直接消费  2 粉丝卡充值
	private $UserKey;								//签名
	private $OrderId;								//第三方流水号
	private $BatchNumber;							//唯一验证
	private $Userid;								//用户ID
	
	public function __construct($data = ''){
		if($data['merid']&&$data['ordamt']&&$data['shoppingtype']&&$data['userkey']&&$data['orderid']&&$data['terminal']&&$data['batchnumber']){
			$this->ShoppingType = $data['shoppingtype'];
			if($this->ShoppingType!=1 && $this->ShoppingType!=2){
				$this->resendMsg("10401","消费类型错误");
			}elseif($this->ShoppingType == 2){
				if(!$data['phone']){
					$this->resendMsg("10404","数据缺失");
				}
			}
			$this->MerId = $data['merid'];
			$this->Terminal = $data['terminal'];
			$this->Phone = $data['phone'];
			$this->OrdAmt = $data['ordamt'];
			if(!is_numeric($this->OrdAmt)){
				$this->resendMsg("10401","数据类型错误");
			}
			$WxPosBd = new WxPosBd();
			$this->Userid = $WxPosBd->scalar("userid","where posmerid='".$this->MerId."' and posterminal='".$this->Terminal."'");
			if(!$this->Userid){
				$this->resendMsg("10401","机器未绑定用户");
			}
			$this->UserKey = $data['userkey'];
			$this->OrderId = $data['orderid'];
			$this->BatchNumber = $data['batchnumber'];
		}else{
			$this->resendMsg("10404","数据缺失");
		}
		$Ip = get_client_ip();
		PubFun::save_log($Ip,"pos");
		$IpList = array("127.0.0.1","116.236.224.51","220.248.12.117","180.169.86.124");//授权IP请求
		if(!in_array($Ip,$IpList)){
			$this->resendMsg("10401","IP识别错误");
		}
	}
	
	//主入口
	public function SetPos(){
		$is_pos = $this->CheckBuy();
		if($is_pos){
			$WxPosLog = new WxPosLog();
			$loginfo = $WxPosLog->scalar("id,status","where merid='".$this->MerId."' and terminal='".$this->Terminal."' and batchnumber='".$this->BatchNumber."'");
			if($loginfo && $loginfo['status'] == 1){
				$this->resendMsg("10100","请勿重新发起支付");
			}else{
				if($loginfo && $loginfo['status'] == 0){
					$logid = $loginfo['id'];
				}else{
					$logid = $WxPosLog->insert(array("merid"=>$this->MerId,"batchnumber"=>$this->BatchNumber,"terminal"=>$this->Terminal,"phone"=>$this->Phone,"ordamt"=>$this->OrdAmt,"shoppingtype"=>$this->ShoppingType,"userkey"=>$this->UserKey,"orderid"=>$this->OrderId,"cdate"=>time()));
				}
				if($logid){
					if($this->ShoppingType == 2){//粉丝卡充值
						$ResultConnector = new ResultConnector();
						$info = $ResultConnector->posclubcard($this->Userid,$this->Phone,$this->OrdAmt);
						if($info['code'] == 1){
							$sta = $WxPosLog->update(array("status"=>1),"where id=$logid");
							if($sta){
								$this->resendMsg("200",md5(date("Ymd")."checkposdodcacom2014"));//成功返回签名
							}else{//保证成功 两次处理！
								$sta = $WxPosLog->update(array("status"=>1),"where id=$logid");
								$this->resendMsg("200",md5(date("Ymd")."checkposdodcacom2014"));//成功返回签名
							}
						}else{
							$this->resendMsg("10505","充值失败[{$info['cont']}]");
						}
					}else{//直接消费
						$sta = $WxPosLog->update(array("status"=>1),"where id=$logid");
						if($sta){
							$this->resendMsg("200",md5(date("Ymd")."checkposdodcacom2014"));//成功返回签名
						}else{//保证成功 两次处理！
							$sta = $WxPosLog->update(array("status"=>1),"where id=$logid");
							$this->resendMsg("200",md5(date("Ymd")."checkposdodcacom2014"));//成功返回签名
						}
					}
					
				}else{
					$this->resendMsg("10505","充值失败");
				}	
			}
		}else{
			$this->resendMsg("10500","签名错误");
		}
	}
	
	//验证签名
	public function CheckBuy(){
		$key_str = md5($this->MerId.$this->Phone.$this->OrdAmt.$this->ShoppingType.$this->OrderId.date("Ymd")."checkposdodcacom2014");
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
