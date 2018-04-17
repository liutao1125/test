<?php
/**
 * 
 * 
 * 
 * 定制api 包含扫码
 * @author wuwudong
 *
 */
class ScaneApi {
	public static function scan($userid, $id, $url) {
		if (! $userid || ! $id) {
			return '0';
		}
		if (! is_dir ( $_SERVER ["SINASRV_UPLOAD"] . "/scancode/" )) {
			mkdir ( $_SERVER ["SINASRV_UPLOAD"] . "/scancode/" );
		}
		require_once 'www/common/phpqrcode/phpqrcode.php';
		$errorCorrectionLevel = 'M';
		$matrixPointSize = 4;
		$data=json_encode(array("status"=>1,
				"url"=>$url));
		// $smcont = base64_encode(PubFun::encrypt($userid.'_'.$id,'E'));
		$picurl = $_SERVER ["SINASRV_UPLOAD"] . "/scancode/sm" . md5 ( $userid . $id ) . ".png";
		$smcont = $url;
		QRcode::png ( $data, $picurl, $errorCorrectionLevel, $matrixPointSize, 2 );
		$PicData = new PicData ();
		$md5 = md5 ( $userid . $id );
		$sql = "select id from pic_data  where org like '%$md5%'";
		$picarray = $PicData->fetchAll ( $sql );
		if (empty ( $picarray )) {
			$picid = $PicData->insert_data ( array (
					'org' => "/scancode/sm" . md5 ( $userid . $id ) . ".png" 
			) );
			return $picid;
		} else {
			return $picarray [0] ['id'];
		}
	}
	/*新的二维码生成*/
  public static function createexhibitioncode($url){
		if(!$url) { return false; }
		if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/exhibitioncode/")){
			mkdir($_SERVER["SINASRV_DATA_TMP"]."/exhibitioncode/");
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
		$coupon_img = $_SERVER["SINASRV_DATA_TMP"]."/exhibitioncode/".md5($url).".png";
		QRcode::png($data, $coupon_img, $errorCorrectionLevel, $matrixPointSize, 2);
		$pic = new PicData();
		$ret = $pic->post_file($coupon_img);
		return $ret;
	}
	/*扫码*/
 public static function insertconference($exhibitionid){
 	  
 	  if($exhibitionid){
 	  	$userid=128;
 	  	$conferenceid=1;
 	  	$conferencemod=new WxConference();
 	  	$conferenceusermod=new WxConferenceUser();
 	  	$exhibitionmod=new WxExhibition();
 	    $conferenceinfo=$conferencemod->scalar("conference_on_off_praise,conference_praise_number", "where id=$conferenceid");
 	   $exhibitioninfo=$exhibitionmod->scalar("*", "where id=$exhibitionid");
 	  	if($conferenceinfo){
 	  		
 	  		if($conferenceinfo['conference_on_off_praise']==1){
 	  			$data['praise_number']=$conferenceinfo['conference_praise_number'];
 	  		}else{
 	  			
 	  			$data['praise_number']=0;
 	  		}
 	  		$data['name']=$exhibitioninfo['name'];
 	  		$data['phone']=$exhibitioninfo['phone'];
 	  		$data['praise_datetime']=time();
 	  		$data['conference_id']=$conferenceid;
 	  		
 	  		$insertid=$conferenceusermod->insert_data($data);
 	  		return $insertid;
 	  	}
 	  }
 }

	
		
}

?>