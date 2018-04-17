<?php
	
/**
* @author jiahaiming
* POS机打印消费劵
*/
	
class PosYfjApi{

	private $MerId;									//商户号
	private $Terminal;								//终端号
	private $MyCode;								//消费号
	private $UserKey;								//手机号码
	
	public function __construct($data = ''){
		if($data['merid']&&$data['terminal']&&$data['userkey']&&$data['mycode']){
			$this->MerId = $data['merid'];
			$this->Terminal = $data['terminal'];
			$this->UserKey = $data['userkey'];
			$this->MyCode = $data['mycode'];
			$WxPosBd = new WxPosBd();
			$this->Userid = $WxPosBd->scalar("userid","where posmerid='".$this->MerId."' and posterminal='".$this->Terminal."'");
			if(!$this->Userid){
				$this->resendMsg("10401","机器未绑定用户");
			}
		}else{
			$this->resendMsg("10404","数据缺失");
		}
		$Ip = get_client_ip();
		PubFun::save_log($Ip,"pos");
		$IpList = array("127.0.0.1","220.248.12.117","180.169.86.124");//授权IP请求
		if(!in_array($Ip,$IpList)){
			$this->resendMsg("10401","IP识别错误");
		}
	}
	
	//主入口
	public function SetPos(){
		$is_pos = $this->CheckBuy();
		if($is_pos){
			//返回信息
			$info = FansCard::get_print_bills_data($this->MyCode);
			if($info){
				$_RE['error'] = "200"; 
				$_RE['message']['mykey'] = md5(date("Ymd")."checkposdodcacom2014");
				$_RE['message']['merchant_name'] = "消费单";
				$_RE['message']['buytype'] = "会员卡金点";
				$_RE['message']['amount'] = $info["sum"];
				$_RE['message']['vipno'] = $info["card_number"];
				$_RE['message']['mycode'] = $this->MyCode;
				$_RE['message']['terminal'] = $this->Terminal;
				echo json_encode($_RE);
			}else{
				$this->resendMsg("10501","消费号错误");
			}
		}else{
			$this->resendMsg("10500","签名错误");
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
