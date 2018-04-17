<?php
class WxPrint{
//接受微信图片接口     发出消费码
	public function printPic($picUrl,$code){
		
		$pr_code = new WxPrintCode();
		$pr = new WxPrintInfo();
		$pr_log = new WxPrintLog();
		$pic = new PicData();
		
		$terid = $pr_code->scalar("id,terid", "where code={$code} and status=0");
		if($terid){
			$pr_code->consume_code($terid['id']);
			$prinfo = $pr->scalar("macid,token,mac_type,guid,cnCode", "where id=".$terid['terid']);
			$api=new PrintApi($prinfo['macid'],$prinfo['token'],$prinfo['mac_type'],$prinfo['guid']);
			$re=$api->jobSubmitLomo($picUrl);
		}
		
		if($prinfo['cnCode']){
			$newcode=$pr_code->create_code($terid['terid']);
			if($newcode){
				$api->terminalUpadateLomo(Array('terminal_free_rcode'=>$newcode));
			}
		}
	}
	public function dataSend($data){
		//$datain= json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)),TRUE);
		//if(explode($datain['Content'],
		//$datain= json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)),TRUE);
		
		$getcon=$data;//$datain['Content'];
		if(strstr($getcon, "@")!=false){
			$xl=explode("@", $getcon);
			$data=$xl[0].$xl[1];
		}
		
		$pr_api=new PrintApi('12',"",1,"");//畅印api
		$reply=$pr_api->dataSubmitCh($data);
		$reply= json_decode(json_encode(simplexml_load_string($reply, 'SimpleXMLElement', LIBXML_NOCDATA)),TRUE);
		//$reply['Content']="回复打印码打印照片[n]要加入文字，回复#文字[n]例如：#我在广州塔，摄于2014";
		//图片操作
		$content=$reply['Content'];
		if(strstr($content, "裁剪照片")!=false){
			$xl=explode("裁剪照片", $reply['Content']);
			$reply['Content']=$xl[0]."裁剪照片  如需添加文字，请输入#文字； 完成以上定制需求后请输入消费码进行打印，输入格式：@1234";
		}
		if(strstr($content, "您的文字已记录，回复打印码打印照片")!=false){
			$reply['Content']=$content."输入格式：@1234";
		}
		//var_dump(strstr($reply['Content'], "裁剪照片"));
		return  $reply['Content'];
	}
	//uid :  用户ID      data：微信传入的XML数据
	public function dataSendYin($uid,$data){
		
		$getcon=$data;//$datain['Content'];
		if(strstr($getcon, "#")!=false){
			$xl=explode("#", $getcon);
			$data=$xl[0].$xl[1];
		}
		if(strstr($getcon, "@")!=false){
			$xl=explode("@", $getcon);
			$data=$xl[0].$xl[1];
		}
		$WxUserAccount = new WxUserAccount();
		$yinId = $WxUserAccount->scalar("yin_id", "where userid={$uid}");
		$pr_api=new PrintApi('12',"",2,$yinId,$uid);//畅印api
		$reply=$pr_api->dataSubmitYin($data,$uid);
		$reply= json_decode(json_encode(simplexml_load_string($reply, 'SimpleXMLElement', LIBXML_NOCDATA)),TRUE);
		//$reply['Content']="回复打印码打印照片[n]要加入文字，回复#文字[n]例如：#我在广州塔，摄于2014";
		//图片操作
		$content=$reply['Content'];
		if(strstr($content, "制作文字Lomo请直接回复文字")!=false){
			$xl=explode("制作文字Lomo请直接回复文字", $reply['Content']);
			$reply['Content']="如需添加文字，请输入#文字； 完成以上定制需求后请输入验证码进行打印，输入格式：@验证码（四位数字）".$xl[1];
		}
		if(strstr($content, "请输入微信验证码")!=false){
			$xl=explode("请输入微信验证码！", $reply['Content']);
			$reply['Content']="文字添加成功，请输入消费码，输入格式：@1234";
		}
		//var_dump(strstr($reply['Content'], "裁剪照片"));
		return  $reply['Content'];
		
	}

}