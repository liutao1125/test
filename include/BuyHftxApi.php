<?php
	
/**
* @author jiahaiming
* 汇付天下支付
*/
	
class BuyHftxApi{
	
	private $Version = "10";												//版本号 固定为10
	private $CmdId = "MobilePay";											//手机支付后台支持的交易类型 
	private $MerId;															//商户号
	private $CurCode = "RMB";												//币种
	private $RetUrl;														//页面返回地址
	private $BgRetUrl;														//后台返回地址
	private $MerPriv = "";													//商户私有数据项
	private $DivDetails = "";												//分账明细 暂未使用
	private $IsBalance = "";												//是否自动结算
	private $url;															//IP
	private $dkou;															//端口
	private $OrdId;															//订单号 由调取方产生
	private $OrdAmt;														//支付金额需要保留两位小数点
	private $Pid;															//商品编号
	private $UsrMp;															//用户手机号码
	private $PayUsrId;														//用户UID
	
	public function __construct($data = ''){
		/*if(SYS_RELEASE==2){
			$this->url = '203.195.194.119'; //线上
			$this->dkou = '11811';
		}elseif(SYS_RELEASE==1){
			$this->url = '127.0.0.1'; //测试
			$this->dkou = '8733';
		}else{
			$this->url = '203.110.164.95'; //本地
			$this->dkou = '8733';
		}*/
		$this->MerId = $data['MerId'];
		//$this->url = $data['url'];
		$this->url = "203.195.151.212";
		$this->dkou = $data['dkou'];
		$this->OrdId = $data['OrdId'];
		$this->OrdAmt = $data['OrdAmt'];
		$this->Pid = $data['Pid'];
		$this->UsrMp = $data['UsrMp'];
		$this->PayUsrId = $data['PayUsrId'];
		$this->RetUrl = WHB_DOMAIN."/buy/hftxbody";
		$this->BgRetUrl = API_DOMAIN."/hftx.php"; 	
	}
	
	//获取签名
	public function GetChkValue(){
		$fp = fsockopen($this->url, $this->dkou, $errno, $errstr, 10);
		if (!$fp) {
			$data['status'] = -1;
			$data['info'] = "$errstr ($errno)";
		}else{
			$MsgData = $this->Version.$this->CmdId.$this->MerId.$this->OrdId.$this->OrdAmt.$this->CurCode.$this->Pid.$this->RetUrl.$this->MerPriv.$this->GateId.$this->UsrMp.$this->DivDetails.$this->PayUsrId.$this->BgRetUrl;
			$MsgData_len =strlen($MsgData);
			if($MsgData_len < 100 ){
				$MsgData_len = '00'.$MsgData_len;
			}elseif($MsgData_len < 1000 ){
				$MsgData_len = '0'.$MsgData_len;
			}
			$out = 'S'.$this->MerId.$MsgData_len.$MsgData;
			$out_len = strlen($out);
			if($out_len < 100 ){
				$out_len = '00'.$out_len;
			}elseif($out_len < 1000 ){
				$out_len = '0'.$out_len;
			}
			$out =$out_len.$out;
			fputs($fp, $out);
			$ChkValue ='';
			while (!feof($fp)) {
				$ChkValue .= fgets($fp, 128);
			}
			$stawz = stripos($ChkValue, '0000')+4;
			$ChkValue = substr($ChkValue, $stawz, 256);
			fclose($fp);
			$data['status'] = 1;
			$data['Version'] = $this->Version;
			$data['CmdId'] = $this->CmdId;
			$data['MerId'] = $this->MerId;
			$data['CurCode'] = $this->CurCode;
			$data['RetUrl'] = $this->RetUrl;
			$data['BgRetUrl'] = $this->BgRetUrl;
			$data['MerPriv'] = $this->MerPriv;
			$data['DivDetails'] = $this->DivDetails;
			$data['IsBalance'] = $this->IsBalance;
			$data['OrdId'] = $this->OrdId;
			$data['OrdAmt'] = $this->OrdAmt;
			$data['Pid'] = $this->Pid;
			$data['UsrMp'] = $this->UsrMp;
			$data['PayUsrId'] = $this->PayUsrId;
			$data['ChkValue'] = $ChkValue;
		}
		return $data;
	}
	
	//验证支付
	public function CheckBuy($data){
		$fp = fsockopen($this->url, $this->dkou, $errno, $errstr, 10);
		if (!$fp) {
			$data['status'] = -1;
			$data['info'] = "$errstr ($errno)";
		}else{
			$MsgData = $data['CmdId'].$data['MerId'].$data['RespCode'].$data['TrxId'].$data['OrdAmt'].$data['CurCode'].$data['Pid'].$data['OrdId'].$data['MerPriv'].$data['RetType'].$data['DivDetails'].$data['GateId'];
			$MsgData_len =strlen($MsgData);
			if($MsgData_len < 100 ){
				$MsgData_len = '00'.$MsgData_len;
			}
			elseif($MsgData_len < 1000 ){
				$MsgData_len = '0'.$MsgData_len;
			}
			$out = 'V'.$data['MerId'].$MsgData_len.$MsgData.$data['ChkValue'];
			$out_len = strlen($out);
			if($out_len < 100 ){
				$out_len = '00'.$out_len;
			}
			elseif($out_len < 1000 ){
				$out_len = '0'.$out_len;
			}
			$out =$out_len.$out;
			fputs($fp, $out);
			$ChkValue = '';
			while (!feof($fp)) {
				$ChkValue .= fgets($fp, 128);
			}
			fclose($fp);
		}
		$SignData = $ChkValue;
		$checksign = "0011V".$data['MerId']."0000";
		if($SignData == $checksign){	
			if($data['RespCode'] == "000000"){
				$data['status'] = 1;
			}else{
				$data['status'] = 2;
			}
			$data['info'] = "RECV_ORD_ID_".$data['OrdId'];
		}else{
			//验签失败
			$data['status'] = -1;
			$data['info'] = "验签失败[".$SignData."]";
		}
		return $data;
	}
}
?>
