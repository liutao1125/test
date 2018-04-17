<?php
class PrintApi{

	//static $wechat_id;
	private static $mac_id;
	private static $token;
	private static $type;
	private static $url;
	private static $wechat_id;
	
	private  static $lomo_url="http://ra1.lomoment.com/v2/int/int_ext/";
	private  static $lomo_token = "RQE4phpf5MSlc8lk";
	private  static $ch_wxurl="http://114.215.170.25/listener/wechat/";
	private  static $yin_wxurl="http://servertest1weixinprint.com/weixin?publicUserId=";
	private  static $yin_url="http://203.195.151.233:8069/inleader/services/";
	private  static $orgid=619;
	//private  static $yin_wxurl="http://inleader.weixinprint.com/weixin?publicUserId=?";
	private  static $ch_token = "30a05b1dc4e056873cd69ebf3b127e16";

	private $platForm = Array(
	0=>Array(
			'name'=>'lomo',
			'url'=>'http://api.lomoment.com/v2/int/int_ext/'),//LOMO
	1=>Array(
			'name'=>'changyin',
			'url'=>'http://114.215.170.25/api/',
			'token'=>"30a05b1dc4e056873cd69ebf3b127e16"),//畅印
	2=>Array(
	'name'=>'yinlide',
	'url'=>'http://203.195.151.233:8069/inleader/services/',
	//'url'=>'http://180.169.22.191:8069/admin/services/',
	'orgId'=>619,),//15
	);
	private $lomoStatus = Array(
	0=>"离线",
	100=>"正常在线",
	101=>"正在打印",
	200=>"打印机异常",
	201=>"打印机离线",
	202=>"打印机缺纸",
	203=>"打印机卡纸",
	204=>"打印机缺墨",
	205=>"打印机无墨",
	900=>"机器异常");
	//mac         lomo、畅印：机器号                印立得：机器clientID
	//token lomo  机器密钥       畅印：点客固定token      印立得：点客orgid
	//type        0：lomo      1：畅印  2：印立得
	//we          lomo、畅印：微信原生ID           印立得：微信publicId
	public function __construct($mac,$token="",$type=0,$we=""){
		if (empty($mac)){
			self::resendMsg(1001, '非法操作');
		}
		self::$type=$type;
		self::$mac_id=$mac;
		self::$token=$token;
		self::$wechat_id=$we;
		if($type < count($this->platForm)){
			self::$url=$this->platForm[$type]['url'];
			if($type==1){
				self::$token=$this->platForm[$type]['token'];
			}
			if($type==2){
				self::$token=$this->platForm[$type]['orgId'];
			}
			
		}
		else{
			$this->resendMsg("0000","打印机类型输入错误");
		}
		//var_dump($mac);
		$this->auth();
	}
	private function auth(){
	}
	//POST数据  返回主体内容
	public function do_post_request($url, $data, $optional_headers = null)
	{
		$params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
		));
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'r', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}
	/****************yinlide***************************/
	//微信账户添加
	public static function accountAddYin($erweima,$wsid,$version = 1){
	
		$param['orgId'] = self::$orgid;
		$param['weiXinCodeImg'] = $erweima?$erweima:"http://res.dodoca.com/org/1/dc/f28/d46e/acba4cb4d5e3d0dea8ccfe.jpg";
		$param['versionType'] = $version?$version:1;
		$param['publicUserName'] = $wsid;
		//var_dump($param);
		try{
			$client = new SoapClient(self::$yin_url."mobileVsins?wsdl",array('encoding'=>'UTF-8'));
			//$re = $client->addWeixinByOrgId($param);
			//var_dump(($client->__getFunctions()));
			//$client->
			//$param = array_merge($array1)
			$re = $client->__soapCall("addWeixinByOrgId", $param);
			//$re = $client->addWeixinByOrgId($param['orgId'],$param['weiXinCodeImg'],$param['versionType'],$param['publicUserName']);
			return $re;//print_r($arr);
		} catch (SOAPFault $e) {
			print $e;
		}
		// }
	}
	//微信账户修改$publicId,$erweima=NULL,$version=NULL
	public static function accountEditYin($data){
		$param['publicId'] = $data['publicId'];
		//$printinfo=$WxPrintInfo->scalar("qrCode,versionType", "where macid=".self::$mac_id);
		$param['weiXinCodeImg'] = $data['weiXinCodeImg']?$data['weiXinCodeImg']:"http://res.dodoca.com/org/1/dc/f28/d46e/acba4cb4d5e3d0dea8ccfe.jpg";
		$param['versionType'] = $data['versionType']?$data['versionType']:1;
		try{
			$client = new SoapClient(self::$yin_url."mobileVsins?wsdl",array('encoding'=>'UTF-8'));
			$re = $client->__soapCall("editWeixinBypublicId", $param);
			//$re = $client->editWeixinBypublicId($param);
			return $re;//1 成功  0 失败
		} catch (SOAPFault $e) {
			print $e;
		}
	}
	//微信账户回复设置
	public static function replySetYin($data){
		$param['publicId'] = $data['publicId'];
		//$printinfo=$WxPrintInfo->scalar("qrCode,versionType", "where macid=".self::$mac_id);
		$param['weiXinProcedureCode'] = $data['weiXinProcedureCode'];
		$param['weiXinProcedureDesc'] = $data['weiXinProcedureDesc'];
		try{
	 	$client = new SoapClient(self::$yin_url."mobileVsins?wsdl",array('encoding'=>'UTF-8'));
	 	$re = $client->__soapCall("editWeixinProcedureBypublicId", $param);
	 	//$re = $client->editWeixinProcedureBypublicId($param);
	 	return $re;//print_r($arr);1成功
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//根据机器id查询信息
	public static function terminalStatusCid(){
		try{
	 	$client = new SoapClient(self::$yin_url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
	 	//var_dump($client->__getFunctions());
	 	//print("<br/>");
	 	//var_dump($client->__getTypes());
	 	//print("<br/>");
	 	//var_dump($client);
	 	//$param = array('orgId' =>self::$token);
	 	//var_dump($param);
	 	$re = $client->__soapCall("getClientInfoByorgId", Array('orgId'=>self::$orgid));
	 	//$re = $client->getClientInfoByorgId(Array('orgId'=>self::$token));
	 	return json_decode($re,true);//print_r($arr);
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//根据微信账号查询信息
	public function terminalStatusUid(){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
	 	//var_dump(self::$url."mobileClient?wsdl");
	 	//print("<br/>");
	 	//var_dump($client->__getTypes());
	 	//print("<br/>");
	 	//$param = array('publicId' => $publicId);
	 	//var_dump($client->__getFunctions());
	 	//$client->__soapCall("onInit", Array());
	 	$re = $client->__soapCall("getClientInfoBypublicId", Array('publicId'=>self::$wechat_id));
	 	//$re = $client->getClientInfoBypublicId(Array('publicId'=>self::$wechat_id));
	 	return json_decode($re,true);//print_r($arr);
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//机器信息更新
	public function terminalUpadateYin($data=Array()){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
	 	//var_dump($client->__getFunctions());

	 	$param['clientId'] = self::$mac_id;
		$printinfo = self::terminalStatusCid();
		$macinfo = null;
		foreach ($printinfo as $key=>$items){
			if($items['clientId']==$param['clientId']){
				$macinfo = $items;
			}
		}
	 	$param['netName'] = $data['netName']?$data['netName']:($macinfo['netName']?$macinfo['netName']:"");
	 	$param['daymaxnum'] = ($data['daymaxnum']!==null)?$data['daymaxnum']:($macinfo['daymaxnum']?$macinfo['daymaxnum']:-1);
	 	$param['publicMaxnum'] = ($data['publicMaxnum']!==null)?$data['publicMaxnum']:($macinfo['daymaxnumTouser']?$macinfo['daymaxnumTouser']:-1);
	 	$param['bottomImg'] = $data['bottomImg']?$data['bottomImg']:($macinfo['bottomImg']?$macinfo['bottomImg']:"http://res.dodoca.com/org/1/dc/f28/d46e/acba4cb4d5e3d0dea8ccfe.jpg");//底部广告图
	 	$param['isBanner'] = ($data['isBanner']!==null)?$data['isBanner']:($macinfo['isBanner']?$macinfo['isBanner']:2);//是否为文字卡
	 	$param['publicId'] = self::$wechat_id;//公众号
	 	//var_dump($param);
	 	$re = $client->__soapCall("editClientInfoByclientInfoId", $param);
	 	//$re = $client->editClientInfoByclientInfoId($param);
	 	return $re;//print_r($arr);
	 	 
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//机器广告设置(台式)
	public function  advertisingSetYin($data=Array()){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
	 	$param['clientId'] = self::$mac_id;
	 	$param['tempId'] = $data['tempId']?$data['tempId']:2;
	 	$param['bannerTitle'] = $data['bannerTitle']?$data['bannerTitle']:"";
	 	$param['logoUrl'] = $data['logoUrl']?$data['logoUrl']:"http://new.dodoca.com/www/images/home/img/logo_dodoca_new.png";
	 	$param['mainPicIds'] = $data['mainPicIds']?$data['mainPicIds']:"";
	 	$param['leftUrl'] = $data['leftUrl']?$data['leftUrl']:"";
	 	$param['centerUrl'] = $data['centerUrl']?$data['centerUrl']:"";
	 	$param['rightUrl'] = $data['rightUrl']?$data['rightUrl']:"";
	 	$param['newplayUrl'] = $data['newplayUrl']?$data['newplayUrl']:"";
	 	$param['defaultEwm'] = $data['defaultEwm']?$data['defaultEwm']:"";
	 	$param['stepWord1'] = $data['stepWord1']?$data['stepWord1']:"";
	 	$param['stepWord2'] = $data['stepWord2']?$data['stepWord2']:"";
	 	$param['stepWord3'] = $data['stepWord3']?$data['stepWord3']:"";
	 	$param['stepWord4'] = $data['stepWord4']?$data['stepWord4']:"";
	 	$re = $client->__soapCall("editClientTempBaaner", $param);
	 	return $re;
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	
	//机器广告设置(立式)
	public function  advertisingMSetYin($data=Array()){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
	 	$param['clientId'] = self::$mac_id;
	 	$param['tempId'] = $data['tempId']?$data['tempId']:9;
	 	$param['bannerTitle'] = $data['bannerTitle']?$data['bannerTitle']:"";
	 	$param['topPicIds'] = $data['topPicIds']?$data['topPicIds']:"";//顶部大图
	 	$param['middlePicIds'] = $data['middlePicIds']?$data['middlePicIds']:"";
	 	$param['leftPicIds'] = $data['leftPicIds']?$data['leftPicIds']:"http://new.dodoca.com/www/images/zpqimg.jpg";
	 	$param['rightUrl'] = $data['rightUrl']?$data['rightUrl']:"http://new.dodoca.com/www/images/discription.png";
	 	$param['defaultEwm'] = $data['defaultEwm']?$data['defaultEwm']:"";
	 	$re = $client->__soapCall("editClientTempBaaner_M", $param);
	 	return $re;
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	
	//机器广告设置信息获取
	public function  advertisingGetYin($data=Array()){
	try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));

	 	$param['clientId'] = self::$mac_id;
	 	$param['tempId'] = $data['tempId']?$data['tempId']:2;
	 	//var_dump($client->__getFunctions());
	 	var_dump($param);
	 	$re = $client->__soapCall("getClientTempBanner", $param);
	 	//$re = $client->getClientTempBanner($param);
	 	return json_decode($re,true);//print_r($arr);
	 	 
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//打印数据统计
	public function PrintDataGetYin($data=Array()){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
		$param['orgId'] = self::$token;
	 	$param['clientId'] = self::$mac_id;
	 	$param['startTime'] = $data['startTime']?$data['startTime']:date("Y-m-d",(time()-24*60*60));
	 	$param['endTime'] = $data['endTime']?$data['endTime']:date("Y-m-d");
	 	//var_dump($param);
	 	$re = $client->__soapCall("getPrintStatistics", $param);
	 	//$re = $client->getPrintStatistics($param);
	 	return json_decode($re,true);//print_r($arr);
	 	 
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//打印详细任务查询
	public function PrintTaskGetYin($data=Array()){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
		
	 	$param['clientId'] = self::$mac_id;
	 	$param['type'] = $data['type']?$data['type']:1;
	 	$param['startTime'] = $data['startTime']?$data['startTime']:date("Y-m-d",(time()-24*60*60));
	 	$param['endTime'] = $data['endTime']?$data['endTime']:date("Y-m-d");
	 	var_dump($param);
	 	$re = $client->__soapCall("getPrintTaskId", $param);
	 	//$re = $client->getPrintTaskId($param);
	 	return json_decode($re,true);//print_r($arr);
	 	 
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//月份打印数据统计
	public function MonthDataGetYin($data=Array()){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
		
	 	$param['clientId'] = self::$mac_id;
	 	$param['type'] = $data['type']?$data['type']:1;
	 	$param['time'] = $data['time']?$data['time']:date("Y-m");
	 	//var_dump($param);
	 	$re = $client->__soapCall("getChartByMonth", $param);
	 	//$re = $client->getChartByMonth($param);
	 	return json_decode($re,true);//print_r($arr);
	 	 
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//打印码增加获取
	public function CodeGetYin($num){
		try{
	 	$client = new SoapClient(self::$url."mobileClient?wsdl",array('encoding'=>'UTF-8'));
		
	 	$param['clientId'] = self::$mac_id;
	 	$param['orgId'] = self::$token;
	 	$param['publicUserId'] = self::$wechat_id;
	 	$param['codeNum'] = $num?$num:100;
	 	$re = $client->__soapCall("getIdentifyingCode", $param);
	 	return json_decode($re,true);
	 	 
	 } catch (SOAPFault $e) {
	 	print $e;
	 }
	}
	//印立得打印数据提交
	public function dataSubmitYin($data,$uid=''){

/*		$xmlData= json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)),TRUE);
		$WxPrintInfo = new WxPrintInfo();
		$pUid = $WxPrintInfo->scalar('publicUserId', "where guid=".$xmlData['ToUserName']);*/
		$header[] = "Content-type: text/xml";//定义content-type为xml 
		$publicUserId=self::$wechat_id;
		//$url = "http://inleader.weixinprint.com/weixin?publicUserId={$publicUserId}";//{$pUid}";
		//$url = "http://inleader.weixinprint.com/weixin?publicUserId=148";//正式
		$WxUser = new WxUser();
		$url = $WxUser->scalar("yld_url","where uid=$uid");
		$YldApi = new YldApi();
		$codeinfo = $YldApi->getcode($uid);
		$url = $url."&timestamp=".$codeinfo['timestamp']."&nonce=".$codeinfo['nonce']."&signature=".$codeinfo['signature'];
		$re = $this->do_post_request($url,$data,$header[0]);
		return $re;//xml数据
	}
	//********************changyin*******************//
	//畅印绑定设置接口  参数设置  data Array形式
	public function bindCh($data=Array()){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$token);
		$url=self::$url."machine/?signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		
		$getdata = $this->terminalStatusCh();
		//var_dump($getdata['wechat_id']);
		$binddata['wechat_id']=self::$wechat_id;
		$binddata['mac_id']=self::$mac_id;
		$binddata['mac_name']=$data['mac_name']?$data['mac_name']:($getdata['mac_name']?$getdata['mac_name']:"");
		$binddata['visible_verify']=($data['visible_verify']!==NULL)?$data['visible_verify']:($getdata['visible_verify']?$getdata['visible_verify']:0);
		$binddata['free_count']=($data['free_count']!==NULL)?$data['free_count']:($getdata['free_count']?$getdata['free_count']:0);
		$binddata['app_id']=$data['app_id']?$data['app_id']:($getdata['app_id']?$getdata['app_id']:"");
		$binddata['app_secret']=$data['app_secret']?$data['app_secret']:($getdata['app_secret']?$getdata['app_secret']:"");
		$binddata['qrcode']=$data['qrcode']?$data['qrcode']:("");
		$binddata['print_logo']=($data['print_logo']!==NULL)?$data['print_logo']:"";
		$binddata['ads']=$data['ads']?$data['ads']:Array();
		$binddata['province']=$data['province']?$data['province']:($getdata['province']?$getdata['province']:"");
		$binddata['city']=$data['city']?$data['city']:($getdata['city']?$getdata['city']:"");
		$binddata['area']=$data['area']?$data['area']:($getdata['area']?$getdata['area']:"");
		$binddata['street']=$data['street']?$data['street']:($getdata['street']?$getdata['street']:"");
		$binddata['free_count_today']=($data['free_count_today']!==NULL)?$data['free_count_today']:($getdata['free_count_today']?$getdata['free_count_today']:0);
		$binddata['card_ad']=($data['card_ad']!==NULL)?$data['card_ad']:($getdata['card_ad']?$getdata['card_ad']:'mini');
		//var_dump(json_encode($binddata));
		//var_dump($url);
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($binddata));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re = curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return json_decode($re,ture);
	}
	//畅印解绑接口
	public function unbindCh(){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$token);
		$url=self::$url."machine/unbind/?wechat_id=".self::$wechat_id."&mac_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return json_decode($re,ture);;
	}
	//畅印机器查询
	public function terminalStatusCh(){
		$timestamp = time();
		$nonce = rand(999, 99999999);
			
		$signature = $this->chSig($timestamp,$nonce,self::$token);
		$url=self::$url."machine/?wechat_id=".self::$wechat_id."&mac_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		//$url=self::$url."machine/?wechat_id=gh_883dc246883c&mac_id=501621051438&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return json_decode($re,true);
	}
	//回复查询接口
	public function replyCheckCh(){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$ch_token);
		$url=self::$url."smart/reply/?wechat_id=".self::$wechat_id."&mac_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		//curl_setopt($ch, CURLOPT_POST, true);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return json_decode($re,true);
	}
	//回复设置接口 
	public function replySetCh($reply=Array()){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$ch_token);
		$url=self::$url."smart/reply/?signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		$data['wechat_id']=self::$wechat_id;
		$data['mac_id']=self::$mac_id;
		$data['replies']=$reply;
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);	
		return json_decode($re,true);
	}
	//消费码查询
	public function codeCheckCh(){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$token);
		$url=self::$url."verify/?wechat_id=".self::$wechat_id."&mac_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return json_decode($re,true);
	}
	//消费码增加
	public function codeAddCh($count){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$ch_token);
		$url=self::$url."verify/?signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		$data['wechat_id']=self::$wechat_id;
		$data['mac_id']=self::$mac_id;
		$data['count']=$count;
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);	
		return json_decode($re,true);
	}
	//打印数据查询    输入时间戳
	public function dataCheckCh($startdate,$enddate){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$ch_token);
		$url=self::$url."stats/daily/?start_date=".date("Y-m-d",$startdate)."&end_date=".date("Y-m-d",$enddate)."&wechat_id=".self::$wechat_id."&mac_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		var_dump($url);
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return json_decode($re,true);
	}
	
	//畅印打印数据提交
	public function dataSubmitCh($data){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->chSig($timestamp,$nonce,self::$ch_token);
		$url="http://114.215.170.25/listener/wechat/?signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		//var_dump($url);
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return $re;//xml数据
	}
	
	//畅印signature
	public function chSig($timestamp,$nonce,$token){
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		return sha1($tmpStr);
	}
	//lomo signature
	public function lomoSig($mac_id,$timestamp,$nonce,$token){
		return sha1($mac_id.$token.$nonce.$timestamp);
	}
	//**************************LOMO *********************************//
	//lomo  任务提交
	public function jobSubmitLomo($job_img){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->lomoSig(self::$mac_id,$timestamp,$nonce,self::$token);
		$url=self::$url."job_submit.php?job_img_url=".$job_img."&terminal_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		PubFun::save_log($url,'wdy_log');
		//var_dump($url);
		
		
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re = json_decode(curl_exec($ch));
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return $re;
	}
	//lomo  任务查询
	public function jobStatusLomo($job_id){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->lomoSig(self::$mac_id,$timestamp,$nonce,self::$token);
		$url=self::$url."job_status.php?job_id=".$job_id."&terminal_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		var_dump($url);
		
		
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=json_decode(curl_exec($ch),true);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return $re;
	}
	//lomo 机器状态查询
	public function terminalStatusLomo(){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->lomoSig(self::$mac_id,$timestamp,$nonce,self::$token);
		$url=self::$url."terminal_settings_get.php?terminal_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		$re=json_decode($re,true);
		$re['terminal_running_status']=$re['terminal_running_status']==null?"离线":$this->lomoStatus[$re['terminal_running_status']];
		return $re;
	}
	//lomo机器设置
	public function terminalUpadateLomo($data=Array()){
		$timestamp = time();
		$nonce = rand(999, 99999999);
		$signature = $this->lomoSig(self::$mac_id,$timestamp,$nonce,self::$token);
		$url=self::$url."terminal_settings_set.php?".http_build_query($data)."&terminal_id=".self::$mac_id."&signature=".$signature."&timestamp=".$timestamp."&nonce=".$nonce;
		PubFun::save_log($url,'wdy_log');
		//var_dump($url);
		
		
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定进行返回
		// 抓取URL并把它传递给浏览器
		$re=curl_exec($ch);
		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);
		return json_decode($re,true);
	}
	
	//Array  process
	static function arrayRecursive($str,$function,$apply_key=false){
		if(is_array($str)){
			foreach ($str AS $key=>$value){
				if(!is_array($value)){
					if($apply_key)
					$st[$function($key)]=$function($value);
					else
					$st[$key]=$function($value);
				}
				else{
					$st[$key]=self::arrayRecursive($value);
				}
			}
		}
		return $st;
	}
	//状态返回
	private static function resendMsg($num, $msg = '', $result = null){
		if ($result){
			$_RE['content'] = $result;
		}
		$_RE['error'] = $num;
		$_RE['message'] = $msg;

		$_RE=self::arrayRecursive($_RE,urlencode,TRUE);
		echo urldecode(json_encode($_RE));
		exit();
	}
}