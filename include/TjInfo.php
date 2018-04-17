<?php

/* *
*	jhm 
*	统计
*/
class TjInfo{
	
	public static function tj($uid,$dqdate){
		$statime = strtotime($dqdate." 00:00:00");
		$endtime = strtotime($dqdate." 23:59:59");
		$WxUserFans = new WxUserFans();
		$WxViewLog = new WxViewLog();
		$WxMsg = new WxMsg();
		$WxTjinfo = new WxTjinfo();
		$wherestr = "where userid=$uid ";
		$new_num = $WxUserFans->getCount($wherestr."and status=1 and sub_date>=$statime and sub_date<=$endtime");
		$out_num = $WxUserFans->getCount($wherestr."and status=0 and unsub_date>=$statime and unsub_date<=$endtime");
		$add_num = $new_num-$out_num;
		$sum_num = $WxUserFans->getCount($wherestr." and status=1");
		$imgtext_num = $WxViewLog->getCount($wherestr."and cdate>=$statime and cdate<=$endtime and log_type>1");
		$text_num = $WxViewLog->getCount($wherestr." and cdate>=$statime and cdate<=$endtime and log_type=1");
		$sms_rs = $WxMsg->fetchAll("select count(*) as count from (select * from wx_msg where userid=$uid and createtime>=$statime and createtime<=$endtime group by fans_id) as a");
		$sms_rs = $sms_rs[0]['count']?$sms_rs[0]['count']:0;
		$sms_num = $WxMsg->getCount("where userid=$uid and createtime>=$statime and createtime<=$endtime");
		if($sms_rs){
			$sms_ave = round($sms_num/$sms_rs);
		}else{
			$sms_ave = $sms_num;
		}
		$WxTjinfo->insert_data(array('userid'=>$uid,'new_num'=>$new_num,'out_num'=>$out_num,'add_num'=>$add_num,'sum_num'=>$sum_num,'imgtext_num'=>$imgtext_num,'text_num'=>$text_num,'sms_rs'=>$sms_rs,'sms_num'=>$sms_num,'sms_ave'=>$sms_ave,'cdate'=>$dqdate));
		/*
		$ztdate = date('Y-m-d',strtotime($date .'-1 day'));
		$ztinfo = $WxTjinfo->scalar("*","where userid=$uid and cdate='$ztdate'");
		if($ztinfo['new_num']){
			$new_numbfb = round(($new_num-$ztinfo['new_num'])/$ztinfo['new_num']*100);
		}
		if($ztinfo['out_num']){
			$out_numbfb = round(($out_num-$ztinfo['out_num'])/$ztinfo['out_num']*100);
		}
		if($ztinfo['add_num']){
			$add_numbfb = round(($add_num-$ztinfo['add_num'])/$ztinfo['add_num']*100);
		}
		if($ztinfo['sum_num']){
			$sum_numbfb = round(($sum_num-$ztinfo['sum_num'])/$ztinfo['sum_num']*100);
		}
		if($ztinfo['imgtext_num']){
			$imgtext_numbfb = round(($imgtext_num-$ztinfo['imgtext_num'])/$ztinfo['imgtext_num']*100);
		}
		if($ztinfo['text_num']){
			$text_numbfb = round(($text_num-$ztinfo['text_num'])/$ztinfo['text_num']*100);
		}
		*/
	}
}