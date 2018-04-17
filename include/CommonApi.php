<?php
/**
 * 通用方法
 * @author zcc
*/
class CommonApi{
	
	/**
	 * 生成优惠券二维码
	 * @author zcc
	 */
	public static function createcode($url){
		if(!$url) { return false; }
		if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/commoncreatecode/")){
			mkdir($_SERVER["SINASRV_DATA_TMP"]."/commoncreatecode/");
		}
		require_once "www/common/phpqrcode/phpqrcode.php";
		//$data => 	二维码中存储的信息
		$data = json_encode(array(
			'status'	=>	'1',	//有效状态
			'url'	=>	$url,
		));
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'M'; 
		// 点的大小：1到10
		$matrixPointSize = 4;
		$coupon_img = $_SERVER["SINASRV_DATA_TMP"]."/commoncreatecode/".md5($url).".png";
		QRcode::png($data, $coupon_img, $errorCorrectionLevel, $matrixPointSize, 2);
		$pic = new PicData();
		$ret = $pic->post_file($coupon_img);
		return $ret;
	}
	
	/**
	 * 生成登陆用户二维码
	 * @author zcc
	 * @$username 用户名
	 * @$password 密码（加密后的密码）
	 * @return array
	 */
	public static function createlogincode($username,$password){
		$username = trim($username);
		$password = trim($password);
		if(!$username || !$password) { return false; }
		if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/commoncreatelogincode/")){
			mkdir($_SERVER["SINASRV_DATA_TMP"]."/commoncreatelogincode/");
		}
		require_once 'www/common/phpqrcode/phpqrcode.php';
		$errorCorrectionLevel = 'M';                             
		$matrixPointSize = 4;	
		$smcont = base64_encode(PubFun::encrypt($username.'_'.$password,'E'));
		$picurl = $_SERVER["SINASRV_DATA_TMP"]."/commoncreatelogincode/user".md5($username).".png";
		QRcode::png($smcont, $picurl, $errorCorrectionLevel, $matrixPointSize, 2);
		$PicData = new PicData();
		$ret = $PicData->post_file($picurl);
		return $ret;
	}
	
	/**
	 * 生成卡券二维码
	 * @author zcc
	 * @ userid 点客平台账号
	 * @ opemid 领取用户id
	 * @ coupons_code 兑换券序列号
	 * @ return array
	 */
	public static function createkqcode($userid,$openid,$coupons_code){
		if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/mobilecouponskq/")){
			mkdir($_SERVER["SINASRV_DATA_TMP"]."/mobilecouponskq/");
		}
		require_once 'www/common/phpqrcode/phpqrcode.php';
		$errorCorrectionLevel = 'M';                             
		$matrixPointSize = 4;
		
		$base64tcode = base64_encode(PubFun::encrypt($userid.'_'.$openid.'_'.$coupons_code,'E'));
		$data = json_encode(array(
			'status'	=>	'1',
			'url'	=>	'http://'.$_SERVER['HTTP_HOST'].'/mobilecoupons/getcouponsmess?tcode='.$base64tcode,
			'code'	=>	$coupons_code,
		));
		$picurl = $_SERVER["SINASRV_DATA_TMP"]."/mobilecouponskq/kq".md5($id).".png";
		QRcode::png($data, $picurl, $errorCorrectionLevel, $matrixPointSize, 2);
		$PicData = new PicData();
		$ret = $PicData->post_file($picurl);
		$PicData->close();
		return $ret;
	}
	
	/**
	 * 冒泡排序
	 * @author zcc
	 * @ arr 需排序的数组
	 * @ key 排序的键值
	 * @ sort 排序方式
	 * @ return array
	 */
	public static function array2sort($arr,$key,$sort='asc'){
		$count=count($arr);
		$sort=strtolower($sort);
		for($i=0;$i<$count;$i++){
			for($j=$count-1; $j>$i; $j--){				
				if($sort=='asc'){
					if($arr[$j][$key]<$arr[$j-1][$key]){
						$temp=$arr[$j];
						$arr[$j]=$arr[$j-1];
						$arr[$j-1]=$temp;
					}
				}elseif($sort=='desc'){
					if($arr[$j][$key]>$arr[$j-1][$key]){
						$temp=$arr[$j];
						$arr[$j]=$arr[$j-1];
						$arr[$j-1]=$temp;
					}
				}else{
					return 'wrong sort';
				}
			}
		}		
		return $arr;
	}
	
	/**
	 * 短信发送
	 * @author		zcc
	 * @ userid		点客平台id
	 * @ openid		粉丝id
	 * @ phone		手机号码
	 * @ content	短信内容
	 * @ type		短信类型
	 */
	public static function sendsms($userid,$openid,$phone,$content,$type){
		$userid = $_SESSION['clubdata']['userid'];
		$openid = $_SESSION['clubdata']['openid'];
		if(!empty($userid) && !empty($openid) && !empty($phone) && !empty($content) && !empty($type)){
			$usermodel = new WxUser ();
			$userdata = $usermodel->get_row_byid($userid);	
			if($userdata['sms_count']<1) {
				return array('type'=>'2','cont'=>'短信不足');
			}			
			$smshistorymodel = new SmsHistory();
			$sh['userid'] = $userid;
			$sh['openid'] = $openid;
			$sh['sms_content'] = $content;
			$sh['sms_recipient'] = $phone;
			$sh['type'] = $type;
			$sh['send_status'] = $falg = SendMessageApi::send_sms($phone,$content);
			$sh['send_time'] = time();
			$res = $smshistorymodel->insert_data($sh);				
			if($res) {
				$smsdata = array (
					"sms_count" =>$userdata['sms_count']-1
				);
				$usermodel->update_row($smsdata,$userid);
				$key=CacheKey::get_user_key($userid);
				mc_unset($key);
			}
			$usermodel->close();
			$smshistorymodel->close();
			return array('type'=>1,'cont'=>'短信已发送');
		} else {
			return array('type'=>2,'cont'=>'参数缺失');
		}
	}
	
	/**
	 * 导出excel
	 * @param  $conf     :   格式配置
	 * @param  $data	 :   导出数据
	 * $conf = array(  //参数实例
			'base' => array(
				'start'	=>	'1',
				'title'	=>	'课程报名表',
				'text'	=>	'B2-C2',
			),
			'conf' => array(
				'0'	=>	array('type'=>'A','width'=>'15','title'=>'姓名','field'=>'username'),
				'1'	=>	array('type'=>'B','width'=>'25','title'=>'性别','field'=>'sex'),
				'2'	=>	array('type'=>'C','width'=>'45','title'=>'预约课程','field'=>'cname'),
				'3'	=>	array('type'=>'D','width'=>'5','title'=>'预约状态','field'=>'cname'),
				'4'	=>	array('type'=>'E','width'=>'15','title'=>'时间','field'=>'cname'),
				'5'	=>	array('type'=>'F','width'=>'15','title'=>'发布人','field'=>'cname'),
				'6'	=>	array('type'=>'G','width'=>'35','title'=>'排序','field'=>'cname'),
				'7'	=>	array('type'=>'H','width'=>'15','title'=>'差评','field'=>'cname'),
			),
			
		);
	 */
	public static function exportexcel($conf=array(),$data=array()) {
		include '../include/PHPExcel.php';;
		$objExcel = new PHPExcel();
		$objExcel->setActiveSheetIndex(0);   
		$objActSheet = $objExcel->getActiveSheet(); 
		
		$index = $conf['base']['start'] ? $conf['base']['start'] : '1';
		//设置字段
		foreach($conf['conf'] as $v) {
			$objActSheet->getColumnDimension($v['type'])->setWidth($v['width']);
		}
		//设置标题
		//$objActSheet->setCellValue('A1','课程报名表');
		//$objActSheet->mergeCells('A1:H1');
		foreach($conf['conf'] as $v) {
			$objActSheet->setCellValue($v['type'].$index,$v['title']);
		}
		foreach($data as $v) {
			$index ++;
			foreach($conf['conf'] as $vv) {
				$objActSheet->setCellValue($vv['type'].$index,' ' . $v[$vv['field']]);
			}
			//设置文本字段
			$extra = explode('-',$conf['base']['text']);
			if($extra) {
				foreach($extra as $ve) {
					$objActSheet->getStyle($ve.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
				}
			}
		}
		$sheetTitle = $conf['base']['title'];
		$objExcel->getActiveSheet()->setTitle($sheetTitle);
		$filename = iconv('utf-8', 'GBK', $sheetTitle) . '.xls';	
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
		$objWriter->save('php://output');
		exit();
	}
	
	
	/**
	 * CURL请求
	 * @createtime 2015.9.13
	 * @param $url curl请求地址
	 * @param $data curl请求数据
	 * @param $is_post curl请求类型，1-POST
	 */
	public static function sub_curl($url,$data,$is_post=1)
	{
	    $ch = curl_init();
	    if(!$is_post) {
	        $url =  $url.'?'.http_build_query($data);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, $is_post);
	    if($is_post) {
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    }
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    $info = curl_exec($ch);
	    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    return $info;
	}
	/**
	 * 生成二维码
	 * @author zcc
	 * @parram content : 二维码存储的信息
	 */
	public static function createtwocode($content){
		if(!$content) { return false; }
		if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/createtwocode/")){
			mkdir($_SERVER["SINASRV_DATA_TMP"]."/createtwocode/");
		}
		require_once "www/common/phpqrcode/phpqrcode.php";		
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'M'; 
		// 点的大小：1到10
		$matrixPointSize = 4;
		$nowpng = $_SERVER["SINASRV_DATA_TMP"]."/createtwocode/".date("ymdHis").rand(10000,100000).".png";
		QRcode::png($content, $nowpng, $errorCorrectionLevel, $matrixPointSize, 2);
		$pic = new PicData();
		$ret = $pic->post_file($nowpng);
		return $ret;
	}

	
	public static function barcodegen($text='',$codebar){
		
		require_once('../include/barcodegen/class/BCGFontFile.php');
		require_once('../include/barcodegen/class/BCGColor.php');
		require_once('../include/barcodegen/class/BCGDrawing.php');
		
		/*'BCGcodabar','BCGcode11','BCGcode39','BCGcode39extended','BCGcode93',
		'BCGcode128','BCGean8','BCGean13','BCGisbn','BCGi25','BCGs25','BCGmsi',
		'BCGupca','BCGupce','BCGupcext2','BCGupcext5','BCGpostnet','BCGothercode'*/
		require_once('../include/barcodegen/class/'.$codebar.'.barcode.php');

		// Loading Font
//		$font = new BCGFontFile('../include/barcodegen/font/Arial.ttf', 18);
		
		// Don't forget to sanitize user inputs
//		$text = isset($text) ? $text : '000000000000';
		
		// The arguments are R, G, B for color.
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255);
		
		$drawException = null;
		try {
		    $code = new $codebar();
		    $code->setScale(2); // Resolution
		    $code->setThickness(30); // Thickness
		    $code->setForegroundColor($color_black); // Color of bars
		    $code->setBackgroundColor($color_white); // Color of spaces
//		    $code->setFont($font); // Font (or 0)
		    $code->parse($text); // Text
		} catch(Exception $exception) {
		    $drawException = $exception;
		}
		
		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$drawing = new BCGDrawing('', $color_white);
		if($drawException) {
		    $drawing->drawException($drawException);
		} else {
		    $drawing->setBarcode($code);
		    $drawing->draw();
		}
		
		// Header that says it is an image (remove it if you save the barcode to a file)
		header('Content-Type: image/png');
		header('Content-Disposition: inline; filename="barcode.png"');
		
		// Draw (or save) the image into PNG format.
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
	}
	
	
	/**
	 * 微信发送模板信息
	 * @author zcc zhangchangchun@dodoca.com
	 * @createtime 2015.7.13
	 * @param $userid 点客userid
	 * @param $openid 粉丝openid，0表示发给商家
	 * @param $tempid 模板类型id
	 * @param $url 模板消息链接
	 * @param $data 模板数据替换
	 */
	public static function sendTempMess($userid,$openid,$tempid,$url,$data) {
	    if(!is_numeric($userid) || !$userid) { return false; }
	    $openid = (int)$openid;
	
	    $WxUserAccount = new WxUserAccount();
	    $nowuser = $WxUserAccount->get_acc_type($userid,1);
	    $WxUserAccount->close();
	    unset($WxUser);
	
	    if(!$nowuser || $nowuser['account_attribute']!=1) {
	        return array('state'=>2,'cont'=>'非认证服务号不支持此功能');
	    }
	    if($openid<1) {
	        return array('state'=>2,'cont'=>'openid非法');
	    }
	
	    $TemData = array();
	    $MessageTemplate = new MessageTemplate();
	    $template = $MessageTemplate->scalar('template_id,data_first,data_remark'," where userid=".$userid." and template_type=".$tempid." and on_off=1 and status=1");
	    $MessageTemplate->close();
	    unset($MessageTemplate);
	    if($template) {
	        $TemData['template_id'] = $template['template_id'];
	        $TemData['url'] = $url;
	        $TemData['first'] = str_replace(PHP_EOL,'',$template['data_first']);
	        $TemData['remark'] = str_replace(PHP_EOL,'',$template['data_remark']);
	        $TemData['body'] = $data;
	        	
	        $WxUserFans = new WxUserFans($userid);
	        $fans = $WxUserFans->get_row_byid($openid);
	        $WxUserFans->close();
	        unset($WxUserFans);
	        	
	        if($userid==$fans['userid']) {
	            $TemData['openid'] = $fans['weixin_user'];
	            $WeiXin = new WeiXin($userid);
	            $res = $WeiXin->sendMessTemplate($TemData);
	            return $res;
	        } else {
	            return array('state'=>2,'cont'=>'平台粉丝无效');
	        }
	    } else {
	        return array('state'=>2,'cont'=>'模板不存在或者未开启');
	    }
	}
}
?>
