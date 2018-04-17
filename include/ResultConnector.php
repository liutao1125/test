<?php
/**
 * @author wangyu
 * 支付返回结果处理
 */
class ResultConnector
{
	/**
	 * 
	 * 接收订单主键进行处理	并加钱
	 * @param 订单表订单号 $orderid 
	 */
	function addmoney($orderid){
		$model = new WxOrder(); 
		$orderlogmodel = new WxOrderLog();
		//若订单日志已存在，则忽略
		$logtmp = $orderlogmodel->get_data_order_no($orderid);
		if($logtmp) {
			echo '-1'; exit;
		}
		$orderdata = $model->get_data_order_no($orderid);//获取订单数据
		
		$logdata['orderno'] = $orderdata['order_no'];
		$logdata['ordertime'] = date("Y-m-d H:i:s");
		$logdata['orderid'] = $orderdata['id'];
		$logdata['order_amount'] = $orderdata['order_amount'];
		$logdata['from_type'] = $orderdata['from_type'];
		$logdata['pay_type'] = $orderdata['pay_type'];
		$logdata['pay_status'] = $orderdata['pay_status'];
		$logdata['order_status'] = $orderdata['order_status'];
		$logdata['pk_order_num'] = $orderdata['pk_order_num'];
		$logdata['openid'] = $orderdata['openid'];
		
		$orderlogmodel->insert_data($logdata);
		
		//订单完成，订单类型粉丝卡充值
		if($orderdata['from_type']==1){ 
			ResultConnector::clubcard($orderdata);
			echo 1;
		}elseif($orderdata['from_type']==2){	
			//店铺粉丝卡处理
			ResultConnector::modifycardstatus($orderid);
			ResultConnector::createvirtualgoodsnumber($orderdata['id'],$orderdata['from_type'],$orderdata['userid'],$orderdata['openid']);
			
			//千人大会
			$WxGoodsOrderMgt = new WxGoodsOrderMgt();
			//$goodsorderinfo = $WxGoodsOrderMgt->scalar("id,unit_price"," WHERE order_id=".$orderdata['id']." AND (goods_id='623' or goods_id='624' or goods_id='625')");
			$goodsorderinfo = $WxGoodsOrderMgt->scalar("id,unit_price"," WHERE order_id=".$orderdata['id']." AND (goods_id='40389' or goods_id='40391' or goods_id='40786')");
			if($goodsorderinfo)
			{
				$WxOrderLogistics  = new WxOrderLogistics();
				$WxOrderShippingAddress  = new WxOrderShippingAddress ();
				$post_id = $WxOrderLogistics->scalar("post_id"," WHERE order_id=".$orderdata['id']." ");
				$shippingaddress= $WxOrderShippingAddress->scalar("name,phone"," WHERE id=".$post_id." ");
				GoogsApi::saveqrdhmp($orderdata['userid'],$shippingaddress['name'],$shippingaddress['phone'],$goodsorderinfo['unit_price']);
			}
			//千人大会
			
		}elseif($orderdata['from_type']==3 or $orderdata['from_type']==4 or $orderdata['from_type']==5 ){	
			//团购，秒杀，限时购虚拟商品券号生成
			ResultConnector::createvirtualgoodsnumber($orderdata['id'],$orderdata['from_type'],$orderdata['userid'],$orderdata['openid']);
		}elseif($orderdata['from_type']==9 || $orderdata['from_type']==15) { //餐饮堂吃、餐饮当面付
			ResultConnector::takeordersuccess($orderdata);
			echo 1;
		}elseif($orderdata['from_type']==10) { //餐饮外卖
			ResultConnector::takeawaysuccess($orderdata);
			echo 1;
		}elseif($orderdata['from_type']==11) { //外卖行业版
			ResultConnector::takeoutsuccess($orderdata);
			echo 1;
		}elseif($orderdata['from_type']==13) { //酒店行业版
			ResultConnector::hotelsuccess($orderdata);
			echo 1;
		}elseif($orderdata['from_type']==14) { //餐饮到店前支付
			ResultConnector::takeoutpayago($orderdata);
			echo 1;
		}else{
			echo -1;
		}
		
		
		if($orderdata['from_type']==2 or $orderdata['from_type']==3 or $orderdata['from_type']==4 or $orderdata['from_type']==5 or $orderdata['from_type']==6   or $orderdata['from_type']==7){
			
			//粉丝卡消费奖励
			ResultConnector::cludspendreward($orderdata['userid'],$orderdata['openid'],$orderdata['order_amount']);
			
			//发送短信
			$userid=$orderdata['userid'];
			$model= new SmsAlertSwitch();
			$res=$model->scalar('phone,switch',$where=" WHERE userid=$userid AND type=3");
			
			$WxDpSet = new WxDpSet();
			$dp_sms=$WxDpSet->scalar('sms_buyers,sms_seller',$where=" WHERE userid=$userid AND type=1");
			
			if($res['switch']==1 && $dp_sms['sms_seller']==1 )
			{
				$send_mobile=NULL;
				$accept_mobile=$res['phone'];
				$content="您有一个新订单";
				$content.=",订单号".$orderdata['order_no'];
				$content.=",金额￥".$orderdata['order_amount'];
				$content.=",下单时间".date("m-d H:i",$orderdata['cdate']);
				if($accept_mobile)
				{
					ResultConnector::sendMessage($send_mobile,$accept_mobile,$content,$userid);
				}
			}
			
			if($res['switch']==1 && $dp_sms['sms_buyers']==1 )
			{
				$send_mobile=NULL;
				$WxOrderLogistics=new WxOrderLogistics();
				$post_id=$WxOrderLogistics->scalar('post_id',$where="WHERE order_id='".$orderdata['id']."'");
				$WxOrderShippingAddress=new WxOrderShippingAddress();
				$accept_mobile=$WxOrderShippingAddress->scalar('phone',$where="WHERE id='".$post_id."'");
				$content="您的订单";
				$content.=",订单号".$orderdata['order_no'];
				$content.=",已提交成功，我们会尽快处理";
				if($accept_mobile)
				{
					ResultConnector::sendMessage($send_mobile,$accept_mobile,$content,$userid);
				}
			}
			//发送短信
		}
	}

	
	//粉丝卡加钱
	function clubcard($orderdata){
		
		$WxClubcardGoldenCalendar = new WxClubcardGoldenCalendar();
		$openid = $orderdata['openid'];
		$userid = $orderdata['userid'];
		$WxClubcard = new WxClubcard();
		$user = $WxClubcard->get_databyopen($userid,$openid);
		if(!$user){
			$this->display(null,"orderprompt.tpl");
			exit;
		}
		//增加用户金点数
		$tmp = $WxClubcard->updateuse(array('card_golden'=>$user['card_golden']+$orderdata['order_amount']),"where openid=$openid and userid=$userid");
		if($tmp) {
			//记录用户充值金点
			$moneydata = array(
				'datetimes'	=>	time(),
				'flag'		=>	'2',
				'newtype'	=>	'6',
				'sum'		=>	$orderdata['order_amount'],
				'openid'	=>	$openid,
				'userid'	=>	$userid
			);
			$WxClubcardGoldenCalendar->insert_data($moneydata);
			
			//获取奖励规则
			$WxClubcardAwards = new WxClubcardAwards();
			$usercard = $WxClubcardAwards->get_data($user['card_type']);
			//条件 1：奖励规则存在  2：单笔充值开关开启  3：单笔充值奖励积分或者奖励金点
			if($usercard && $usercard['single_charge_on_off']=='1' && $orderdata['order_amount'] >= $usercard['single_charge'] && ($usercard['single_charge_golden']>0 || $usercard['single_charge_integral']>0)) {
				if($usercard['single_charge_golden']>0) {  //赠送金点
					$tmp1 = $WxClubcard->get_databyopen($userid,$openid);
					if($tmp1) {
						//增加用户赠送金点数
						$tmp2 = $WxClubcard->updateuse(array('card_golden'=>$tmp1['card_golden']+$usercard['single_charge_golden']),"where openid=$openid and userid=$userid");
						if($tmp2) {
							$moneydata = array(
								'datetimes'	=>	time(),
								'flag'		=>	'1',
								'newtype'	=>	'11',
								'sum'		=>	$usercard['single_charge_golden'],
								'openid'	=>	$openid,
								'userid'	=>	$userid
							);
							$WxClubcardGoldenCalendar->insert_data($moneydata);
						}
					}
				}
				if($usercard['single_charge_integral']>0) {  //赠送积分
					$WxClubcardIntegralCalendar = new WxClubcardIntegralCalendar();
					$jifen = array(
						'datetimes'	=>	time(),
						'flag'		=>	'1',
						'type'		=>	'6',
						'sum'		=>	$usercard['single_charge_integral'],
						'openid'	=>	$openid,
						'userid'	=>	$userid
					);
					$WxClubcardIntegralCalendar->insert_data($jifen);	
				}
			}
		} else {
			echo '充值失败';
		}
	}
	
	//pos机 粉丝卡 充值
	function posclubcard($userid,$phone,$money){
		if(!$userid) {
			return array('code'=>'2','cont'=>'userid为空'); exit;
		}
		if(!$phone) {
			return array('code'=>'3','cont'=>'电话号码为空'); exit;
		}
		if(!$money || $money<=0) {
			return array('code'=>'4','cont'=>'充值金额不合法'); exit;
		}
		$WxClubcard = new WxClubcard();
		$user = $WxClubcard->get_data_where("where userid=".$userid." and phone='".$phone."' and card_flag=1");
		if(!$user || !$user[0]){
			return array('code'=>'5','cont'=>'充值用户不存在'); exit;
		}
		$user = $user[0];
		if($user['openid']<=0) {
			return array('code'=>'6','cont'=>'充值用户不存在'); exit;
		}
		$openid = $user['openid'];
		$userid = $user['userid'];
		
		$WxClubcardGoldenCalendar = new WxClubcardGoldenCalendar();
		//增加用户金点数
		$tmp = $WxClubcard->updateuse(array('card_golden'=>$user['card_golden']+$money),"where openid=$openid and userid=$userid");
		if($tmp) {
			//记录用户充值金点
			$moneydata = array(
				'datetimes'	=>	time(),
				'flag'		=>	'2',
				'newtype'	=>	'12',
				'sum'		=>	$money,
				'openid'	=>	$openid,
				'userid'	=>	$userid
			);
			$WxClubcardGoldenCalendar->insert_data($moneydata);
			
			//获取奖励规则
			$WxClubcardAwards = new WxClubcardAwards();
			$usercard = $WxClubcardAwards->get_data($user['card_type']);
			//条件 1：奖励规则存在  2：单笔充值开关开启  3：单笔充值奖励积分或者奖励金点
			if($usercard && $usercard['single_charge_on_off']=='1' && $money >= $usercard['single_charge'] && ($usercard['single_charge_golden']>0 || $usercard['single_charge_integral']>0)) {
				if($usercard['single_charge_golden']>0) {  //赠送金点
					$tmp1 = $WxClubcard->get_databyopen($userid,$openid);
					if($tmp1) {
						//增加用户赠送金点数
						$tmp2 = $WxClubcard->updateuse(array('card_golden'=>$tmp1['card_golden']+$usercard['single_charge_golden']),"where openid=$openid and userid=$userid");
						if($tmp2) {
							$moneydata = array(
								'datetimes'	=>	time(),
								'flag'		=>	'1',
								'newtype'	=>	'13',
								'sum'		=>	$usercard['single_charge_golden'],
								'openid'	=>	$openid,
								'userid'	=>	$userid
							);
							$WxClubcardGoldenCalendar->insert_data($moneydata);
						}
					}
				}
				if($usercard['single_charge_integral']>0) {  //赠送积分
					$WxClubcardIntegralCalendar = new WxClubcardIntegralCalendar();
					$jifen = array(
						'datetimes'	=>	time(),
						'flag'		=>	'1',
						'type'		=>	'6',
						'sum'		=>	$usercard['single_charge_integral'],
						'openid'	=>	$openid,
						'userid'	=>	$userid
					);
					$WxClubcardIntegralCalendar->insert_data($jifen);	
				}
			}
			return array('code'=>'1','cont'=>'充值成功'); exit;
		} else {
			return array('code'=>'-1','cont'=>'充值失败'); exit;
		}
	}
	
	//修改粉丝卡状态
	function modifycardstatus($orderid){
		$list=array();
		$model = new WxOrder();
		$query="SELECT a.coupon_id,b.openid FROM wx_goods_order_coupon AS a 
					  INNER JOIN wx_order AS b ON a.order_id = b.id 
					  WHERE b.order_no = '".$orderid."'  ";
		$list=$model->fetchAll($query);
		$id=$list[0]['coupon_id'];
		$openid=$list[0]['openid'];
		if(!empty($id) && !empty($openid)){
			$model2 =new WxClubcardExchangeSubtabulation();
			$data['type']=0;
			$where=" id=$id AND openid=$openid ";
			$model2->update_data($data,$where);
		}
	}
	
	//虚拟商品生成券号
	function createvirtualgoodsnumber($order_id,$from_type,$userid,$openid)
	{
		if(isset($order_id)&&!empty($order_id) && isset($from_type)&&!empty($from_type) )
		{
			$WxGoodsOrderMgt = new WxGoodsOrderMgt();
			$query="SELECT b.id,b.is_virtua,a.goods_id,a.count_num,
					c.coupon_start_time,c.coupon_end_time,
					c.coupon_use_start_time,c.coupon_use_end_time FROM wx_goods_order_mgt  AS a
					INNER JOIN wx_goods AS b ON a.goods_id=b.id ";
			if($from_type==3)	//团购
			{
				$query.="INNER JOIN wx_goods_tg_mgt  AS c ON c.goods_id=b.id ";
			}elseif($from_type==4){//限时购
				$query.="INNER JOIN wx_goods_xs_mgt  AS c ON c.goods_id=b.id ";
			}elseif($from_type==5){//秒杀
				$query.="INNER JOIN wx_goods_ms_mgt AS c ON c.goods_id=b.id ";
			}elseif($from_type==2){//店铺
				$query.="INNER JOIN wx_goods_dp_mgt  AS c ON c.goods_id=b.id ";
			}
			$query.=" WHERE a.order_id = '".$order_id."' ";
			$res = $WxGoodsOrderMgt->fetchAll ( $query );
			$list=$res;
			if(count($list)>0)
			{
				  $WxGoodsQuanList=new WxGoodsQuanList();
				  foreach($list as $k2=>$v2)
				  {
				  	if($v2['is_virtua']==1 && ($v2['goods_id']!='40389' && $v2['goods_id']!='40391' && $v2['goods_id']!='40786') )
				 	//if($v2['is_virtua']==1 && ($v2['goods_id']!='623' && $v2['goods_id']!='624' && $v2['goods_id']!='625' ) )
				  	{
				  		 for($i=0;$i<$v2['count_num'];$i++)
						 {
							   if($from_type==3)	//团购
							   {
									//$data['voucher_number']="TG".substr(date('YmdHis'),2,13).mt_rand(1000,9999);//订单号
									$voucher_number=FansCard::create_code('7');
									$voucher_number=substr($voucher_number,0,-1).substr(str_replace(substr($voucher_number,-1),$i,substr($voucher_number,-1)),-1);
									$voucher_number=substr($voucher_number,0,12);
									$data['voucher_number']=$voucher_number;
							   }elseif($from_type==5){//秒杀
									//$data['voucher_number']="MS".substr(date('YmdHis'),2,13).mt_rand(1000,9999);//订单号
									$voucher_number=FansCard::create_code('7');
									$voucher_number=substr($voucher_number,0,-1).substr(str_replace(substr($voucher_number,-1),$i,substr($voucher_number,-1)),-1);
									$voucher_number=substr($voucher_number,0,12);
									$data['voucher_number']=$voucher_number;
							   }elseif($from_type==4){//限时
									//$data['voucher_number']="XS".substr(date('YmdHis'),2,13).mt_rand(1000,9999);//订单号
									$voucher_number=FansCard::create_code('7');
									$voucher_number=substr($voucher_number,0,-1).substr(str_replace(substr($voucher_number,-1),$i,substr($voucher_number,-1)),-1);
									$voucher_number=substr($voucher_number,0,12);
									$data['voucher_number']=$voucher_number;
							   }elseif($from_type==2){//店铺
									$voucher_number=FansCard::create_code('7');
									$voucher_number=substr($voucher_number,0,-1).substr(str_replace(substr($voucher_number,-1),$i,substr($voucher_number,-1)),-1);
									$voucher_number=substr($voucher_number,0,12);
									$data['voucher_number']=$voucher_number;
							   }
							  $data['goods_id']=$v2['id'];
							  $data['order_id']=$order_id;
							  $data['over_time']=strtotime(date("Y-m-d",$v2['coupon_end_time']).' '.$v2['coupon_use_end_time']);
							  $data['openid']=$openid;
							  $data['userid']=$userid;
							  $insertid=$WxGoodsQuanList->insert_data($data);
							  /*
							  if( $insertid && $userid=='102' && ($v2['goods_id']=='40389' or $v2['goods_id']=='40390' or $v2['goods_id']=='40391') )
							  {
							  	$Ticket=new Ticket();
							  	//$v2['unit_price']=floor($v2['unit_price']);
							  	$unit_price=array('1088','688','388');
							  	$v2['unit_price']=$unit_price[rand(0,2)];
							  	//开启事务
							  	$Ticket->execute("START TRANSACTION");
							  	$ticketinfo=$Ticket->scalar('*',$where=" WHERE ticket_price='".$v2['unit_price']."' AND is_ceshi=1 AND is_use=0 ");
							  	$Ticket->execute("COMMIT");
							  	//提交事务
							  	if($ticketinfo['id'])
							  	{
							  		$qrcode=GoogsApi::creategoodsvouchersqrcode($userid,$ticketinfo['ticket_code']);
							  		if($qrcode>0)
							  		{
							  			$qrcode_data['qrcode']=$qrcode;
							  			$qrcode_data['voucher_number']=$ticketinfo['ticket_code'];
							  			$WxGoodsQuanList->update_data ( $qrcode_data, $where="where id='".$insertid."'and userid='".$userid."'" );
							  				
							  			$ticket_data['is_use']=1;
							  			$Ticket->update_data ( $ticket_data, $where="where id='".$ticketinfo['id']."'");
							  		}
							  	}
							  }
							  */
						 }
				  	}
				 }
			}
		}
	}
	
	/* *
	 * @author zhangyong
	 * 发送短信 
	 */
	function sendMessage($send_mobile,$accept_mobile,$content,$userid)
	{
		$msg=SendMessageApi::send_sms($accept_mobile,$content);
		$msgarr=explode(',',$msg);
		$msgstatus=substr($msgarr[1],0,1);
		if(!$msgstatus)
		{
			$WxUser=new WxUser();
			$sms_count=$WxUser->scalar('sms_count',$where="WHERE uid=$userid");
			if($sms_count>0)
			{
				$where="uid=$userid";
				$data['sms_count']=$sms_count-1;
				$WxUser->update_data($data,$where);
			}
		}
		ResultConnector::saveMessageHistory($send_mobile,$accept_mobile,$content,$userid,$msg);
	}
	
	/* *
	 * @author zhangyong
	 * 保存发送短信记录
	 */
	function saveMessageHistory($send_mobile,$accept_mobile,$content,$userid,$msg)
	{
			$data=array();
			$model= new SmsHistory();
			$data['sms_sender']=$send_mobile;
			$data['sms_recipient']=$accept_mobile;
			$data['sms_content']=$content;
			$data['userid']=$userid;
			$data['type']=4;
			$data['send_status']=$msg;
			$data['send_time']=time();
			$model->insert_data($data);
	}
	
	/**
	 * 验证粉丝卡状态
	 * @author zcc
	 */
	public static function cludstate($userid='',$openid='') {
		if(!$userid || !$openid) {
			return array('state'=>'2','cont'=>'userid或openid不存在');
		}
		$WxClubcard = new WxClubcard();
		$list = $WxClubcard->get_databyopen($userid,$openid);
		if(!$list) {
			return array('state'=>'3','cont'=>'该粉丝卡不存在');
		}
		if($list['card_flag']!='1') {
			return array('state'=>'4','cont'=>'粉丝卡未开卡');
		}
		return array('state'=>'1','money'=>$list['card_golden'],'cont'=>'粉丝卡正常');
	}
	
	
	/**
	 * 验证粉丝卡密码
	 * @author zcc
	 */
	public static function cludpassword($userid='',$openid='',$password='') { 
		if(!$userid || !$openid || !trim($password)) {
			return array('state'=>'3','cont'=>'userid、openid或password不存在');
		}
		$WxClubcard = new WxClubcard();
		$list = $WxClubcard->get_databyopen($userid,$openid);
		if(!$list) {
			return array('state'=>'4','cont'=>'该粉丝卡不存在');
		}
		if($list['card_flag']=='0') {
			return array('state'=>'5','cont'=>'粉丝卡未开卡');
		}
		if($list['card_password'] == trim($password)) {
			return array('state'=>'1','cont'=>'粉丝卡密码正确');	
		} else {
			return array('state'=>'2','cont'=>'粉丝卡密码错误');
		}
	}
	
	/**
	 * 粉丝卡奖励规则
	 * @author zcc
	 */
	public static function cluduserreward($userid='',$openid='') { 
		if(!$userid || !$openid) {
			return array('state'=>'3','cont'=>'userid或openid不存在');
		}
		$WxClubcard = new WxClubcard();
		$list = $WxClubcard->get_databyopen($userid,$openid);
		if(!$list) {
			return array('state'=>'4','cont'=>'该粉丝卡不存在');
		}
		if($list['card_flag']=='0') {
			return array('state'=>'5','cont'=>'粉丝卡未开卡');
		}
		if($list['card_type']) {
			$WxClubcardAwards = new WxClubcardAwards();
			$awards = $WxClubcardAwards->get_data($list['card_type']);
			if($awards) {
				if($awards['single_consumption_on_off']=='1' && ($awards['single_consumption_golden']>0 || $awards['single_consumption_integral']>0)) {
					$single_consumption_golden = $awards['single_consumption_golden']>0 ? $awards['single_consumption_golden'] : '0';
					$single_consumption_integral = $awards['single_consumption_integral']>0 ? $awards['single_consumption_integral'] : '0';
					$single_consumption = $awards['single_consumption']>0 ? $awards['single_consumption'] : '0';
					//money 满多少送	  gorden 送金点数		jifen 送积分数
					return array('state'=>'1','money'=>$single_consumption,'gorden'=>$single_consumption_golden,'jifen'=>$single_consumption_integral);
				} else {
					return array('state'=>'-1','cont'=>'该粉丝卡无奖励规则');
				}
			} else {
				return array('state'=>'7','cont'=>'粉丝卡奖励规则不存在');
			}
		} else {
			return array('state'=>'6','cont'=>'粉丝卡类型不存在');
		}		
	}
	
	/**
	 * 粉丝卡消费奖励
	 * @author zcc
	 */
	public static function cludspendreward($userid='',$openid='',$money='') {
		if(!$userid || !$openid || !$money) {
			return array('state'=>'3','cont'=>'userid或openid或money不存在');
		}
		$WxClubcard = new WxClubcard();
		$list = $WxClubcard->get_databyopen($userid,$openid);
		$WxClubcardGoldenCalendar = new WxClubcardGoldenCalendar();
		$WxClubcardIntegralCalendar = new WxClubcardIntegralCalendar();		
		if(!$list) {
			return array('state'=>'4','cont'=>'该粉丝卡不存在');
		}
		if($list['card_flag']=='0') {
			return array('state'=>'5','cont'=>'粉丝卡未开卡');
		}
		if($list['card_type']) {
			$WxClubcardAwards = new WxClubcardAwards();
			$awards = $WxClubcardAwards->get_data($list['card_type']);
			if($awards && $awards['single_consumption_on_off']=='1' && ($awards['single_consumption_golden']>0 || $awards['single_consumption_integral']>0)) {
				if($money >= $awards['single_consumption']) {
					if($awards['single_consumption_golden']>0) {  //送金点
						$tmp = $WxClubcard->updateuse(array('card_golden'=>$list['card_golden']+$awards['single_consumption_golden']),"where openid=$openid and userid=$userid");
						if($tmp) {
							$sendgolden = $awards['single_consumption_golden'];
							$moneydata = array(
								'datetimes'	=>	time(),
								'flag'		=>	'1',
								'newtype'	=>	'10',
								'sum'		=>	$awards['single_consumption_golden'],
								'openid'	=>	$openid,
								'userid'	=>	$userid
							);
							$WxClubcardGoldenCalendar->insert_data($moneydata);
						}
					}
					if($awards['single_consumption_integral']>0) {  //送积分
						$sendjifen = $awards['single_consumption_integral'];
						$jifen = array(
							'datetimes'	=>	time(),
							'flag'		=>	'1',
							'type'		=>	'7',
							'sum'		=>	$awards['single_consumption_integral'],
							'openid'	=>	$openid,
							'userid'	=>	$userid
						);
						$WxClubcardIntegralCalendar->insert_data($jifen);						
					}
					$sendgolden = isset($sendgolden) ? $sendgolden : '0';
					$sendjifen = isset($sendjifen) ? $sendjifen : '0';
					if($sendgolden || $sendjifen) {
						//奖励成功 送金点	积分		
						return array('state'=>'1','gorden'=>$sendgolden,'jifen'=>$sendjifen);
					} else {
						return array('state'=>'11','cont'=>'没有奖励');
					}
				} else {
					return array('state'=>'10','cont'=>'奖励最低金额不满足');
				}
			} else {
				return array('state'=>'7','cont'=>'该粉丝卡无奖励规则');
			}
		} else {
			return array('state'=>'6','cont'=>'粉丝卡类型不存在');
		}
	}
	
	/**
	 * 粉丝卡支付扣钱
	 * @author zcc 
	 * $userid	:	点客用户
	 * $openid	:	粉丝标示
	 * $money	:	支付金额
	 * $ctype	:	操作标示（1检查 2扣钱）
	 * $paytype	:	支付来源 //2->店铺,3->团购,4->限时购,5->秒杀，6->竞拍,7->商城,10->餐饮外卖支付,11->餐饮堂吃支付,12->外卖行业版
	 */
	public static function cludsubmoney($userid='',$openid='',$money='',$ctype='',$paytype='') {
		if(!$userid || !$openid) {
			return array('state'=>'2','cont'=>'userid或openid不存在');
		}
		$WxClubcard = new WxClubcard();
		$list = $WxClubcard->get_databyopen($userid,$openid);
		if(!$list) {
			return array('state'=>'3','cont'=>'该粉丝卡不存在');
		}
		if($list['card_flag']=='0') {
			return array('state'=>'4','cont'=>'粉丝卡未开卡');
		}
		if($money<=0) {
			return array('state'=>'5','cont'=>'支出金额必须大于0');
		}
		$amount = $list['card_golden']-$money;
		if($amount<0) {
			return array('state'=>'6','cont'=>'粉丝卡余额不足');
		}
		if($ctype=='1') {	//检查金额是否满足
			return array('state'=>'10','cont'=>'金额足够');
		} else if($ctype=='2') {
			if(!$paytype) {
				return array('state'=>'7','cont'=>'请填写支付来源');
			}
			$WxClubcardGoldenCalendar = new WxClubcardGoldenCalendar();
			$WxClubcardIntegralCalendar = new WxClubcardIntegralCalendar();
			
			$tmp = $WxClubcard->updateuse(array('card_golden'=>$amount),"where openid=$openid and userid=$userid");
			if($tmp) {
				$tpaytype = '7';
				switch($paytype) { //2->店铺,3->团购,4->限时购,5->秒杀，6->竞拍,7->商城,10->餐饮支付,11->餐饮堂吃支付,12->外卖行业版
					case '2':	$tpaytype = '15';	break;
					case '3':	$tpaytype = '16';	break;
					case '4':	$tpaytype = '17';	break;
					case '5':	$tpaytype = '18';	break;
					case '6':	$tpaytype = '19';	break;
					case '7':	$tpaytype = '20';	break;
					case '10':	$tpaytype = '22';	break;
					case '11':	$tpaytype = '23';	break;
					case '12':	$tpaytype = '24';	break;
					default:	$tpaytype = '-100';	break;
				}
				//记录金点消费
				$data = array(
					'datetimes'	=>	time(),
					'flag'		=>	'3',
					'newtype'	=>	$tpaytype,
					'sum'		=>	-$money,
					'openid'	=>	$openid,
					'userid'	=>	$userid
				);
				$res = $WxClubcardGoldenCalendar->insert_data($data);
				//消费赠送金点或积分
				$card_type = (int)$list['card_type'];
				if($card_type) {
					$WxClubcardAwards = new WxClubcardAwards();
					$awards = $WxClubcardAwards->get_data($card_type);
					//赠送满足条件
					//1:粉丝卡规则存在  2：单笔消费开关打开 3：消费金额大于单笔消费最低值 4：单笔消费奖励金点或积分存在一个
					if($awards && $awards['single_consumption_on_off']=='1' && $money>=$awards['single_consumption'] && ($awards['single_consumption_golden']>0 || $awards['single_consumption_integral']>0)) {
						if($awards['single_consumption_golden']>0) {  //送金点
							$tmp1 = $WxClubcard->get_databyopen($userid,$openid);
							if($tmp1) {
								$tmp2 = $WxClubcard->updateuse(array('card_golden'=>$tmp1['card_golden']+$awards['single_consumption_golden']),"where openid=$openid and userid=$userid");
								if($tmp2) {
									$moneydata = array(
										'datetimes'	=>	time(),
										'flag'		=>	'1',
										'newtype'	=>	'10',
										'sum'		=>	$awards['single_consumption_golden'],
										'openid'	=>	$openid,
										'userid'	=>	$userid
									);
									$WxClubcardGoldenCalendar->insert_data($moneydata);
								}
							}
						}
						if($awards['single_consumption_integral']>0) {  //送积分
							$jifen = array(
								'datetimes'	=>	time(),
								'flag'		=>	'1',
								'type'		=>	'7',
								'sum'		=>	$awards['single_consumption_integral'],
								'openid'	=>	$openid,
								'userid'	=>	$userid
							);
							$WxClubcardIntegralCalendar->insert_data($jifen);						
						}
					}
				}
				return array('state'=>'1','cont'=>'粉丝卡消费成功');
			} else {
				return array('state'=>'-1','cont'=>'粉丝卡消费失败');
			}
		}
	}
	
	/**
	 * 点菜支付
	 * @author zcc
	 */
	public static function takeordersuccess($orderdata){
		//餐饮单门店
		$WxRepastMeal = new WxRepastMeal();
		$WxRepastMealRecord = new WxRepastMealRecord();
		$meal = $WxRepastMeal->get_one("userid=".$orderdata['userid']." and ordernum=".$orderdata['id']." and type=2");
		
		if($meal && $meal['table_id']>0) {
			$WxRepastMeal->update_data(array('type'=>'3','pay_state'=>2,'end_time'=>time()),"userid=".$orderdata['userid']." and type=2 and table_id=".$meal['table_id']);
			
			//更新订单信息表为已支付
			$data['pay_state'] = 2;
			$WxRepastMealRecord->update_data($data," where userid=".$orderdata['userid']." and ordernum=".$orderdata['id']);
			$recordinfo = $WxRepastMealRecord->get_one($orderdata['userid'],$orderdata['id']);

			//如果有兑换券，改成已使用
			if($recordinfo['coupons_id']!=0){
				$ClubcardExchange = new WxClubcardExchange();
				$ClubcardExchangeSubtabulation = new WxClubcardExchangeSubtabulation();
				$exchange_one = $ClubcardExchangeSubtabulation->get_data_by_id($recordinfo['coupons_id']);
				$data1['type'] = 0;
				$data1['exchange_date'] = time();
				$ClubcardExchangeSubtabulation->update_data($data1,"where id=".$recordinfo['coupons_id']);//更新改兑换券状态
				$ClubcardExchange->update_u_count($orderdata['userid'],$exchange_one['exchange_id']);//主表总数+1
			}
		} else {
			//餐饮多门店
			$WxRepastsMeal = new WxRepastsMeal();
			$WxRepastMealRecord = new WxRepastsMealRecord();
			$meal_db = $WxRepastsMeal->get_one("userid=".$orderdata['userid']." and ordernum=".$orderdata['id']." and type=2");
			if($meal_db && $meal_db['table_id']>0) {
				$WxRepastsMeal->update_data(array('type'=>'3','pay_state'=>2,'end_time'=>time()),"userid=".$orderdata['userid']." and type=2 and table_id=".$meal_db['table_id']);

				//更新订单信息表为已支付
				$data['pay_state'] = 2;
				$WxRepastMealRecord->update_data($data," where userid=".$orderdata['userid']." and ordernum=".$orderdata['id']);
				$recordinfo = $WxRepastMealRecord->get_one($orderdata['userid'],$orderdata['id']);
				
				//如果有兑换券，改成已使用
				if($recordinfo['coupons_id']!=0){
					$ClubcardExchange = new WxClubcardExchange();
					$ClubcardExchangeSubtabulation = new WxClubcardExchangeSubtabulation();
					$exchange_one = $ClubcardExchangeSubtabulation->get_data_by_id($recordinfo['coupons_id']);
					$data1['type'] = 0;
					$data1['exchange_date'] = time();
					$ClubcardExchangeSubtabulation->update_data($data1,"where id=".$recordinfo['coupons_id']);//更新改兑换券状态
					$ClubcardExchange->update_u_count($orderdata['userid'],$exchange_one['exchange_id']);//主表总数+1
				}
			}
		}
		echo '1';
	}
	
	/**
	 * 外卖支付
	 * @author zcc
	 */
	public static function takeawaysuccess($orderdata){
		//餐饮单门店
		$WxRepastindent = new WxRepastindent();
		$indent = $WxRepastindent->get_one_by_wxordernum($orderdata['userid'],$orderdata['id']);
		if($indent) {
			if($indent['pay_state']=='1') {
				$WxRepastindent->update_data(array('pay_state'=>'2'),"userid=".$orderdata['userid']." and wxordernum=".$orderdata['id']);
				$WxGprsInfo = new WxGprsInfo();
				$WxGprsInfo->insert_data(array(
					'userid'	=>	$orderdata['userid'],
					'sid'		=>	get_nonumbers($indent['id']),
					'status'	=>	'0',
					'cdate'		=>	time(),
					'cid'		=>	$indent['id'],
					'ctype'		=>	'1'
				));
			}
		} else {
			//餐饮多门店
			$WxRepastsindent = new WxRepastsindent();
			$sindent = $WxRepastsindent->get_one_by_wxordernum($orderdata['userid'],$orderdata['id']);
			if($sindent && $sindent['pay_state']=='1') {
				$WxRepastsindent->update_data(array('pay_state'=>'2'),"userid=".$orderdata['userid']." and wxordernum=".$orderdata['id']);
				$WxGprsInfo = new WxGprsInfo();
				$WxGprsInfo->insert_data(array(
					'userid'	=>	$orderdata['userid'],
					'sid'		=>	get_nonumbers($sindent['id']),
					'status'	=>	'0',
					'cdate'		=>	time(),
					'cid'		=>	$sindent['id'],
					'ctype'		=>	'3'
				));
			}
		}
		echo '1';
	}
	
	/**
	 * 行业版外卖
	 * @author zcc
	 */
	public static function takeoutsuccess($orderdata){
		$WxTakeoutIndent = new WxTakeoutIndent();
		$indent = $WxTakeoutIndent->get_one_by_wxordernum($orderdata['userid'],$orderdata['id']);
		if($indent) {
			if($indent['pay_state']=='1') {
				$WxTakeoutIndent->update_data(array('pay_state'=>'2'),"userid=".$orderdata['userid']." and wxordernum=".$orderdata['id']);
				$WxGprsInfo = new WxGprsInfo();
				$WxGprsInfo->insert_data(array(
					'userid'	=>	$orderdata['userid'],
					'sid'		=>	get_nonumbers($indent['id']),
					'status'	=>	'0',
					'cdate'		=>	time(),
					'cid'		=>	$indent['id'],
					'ctype'		=>	'5'
				));
			}
		}
		echo '1';
	}
	
	/**
	 * 行业版酒店
	 * @author zcc
	 */
	public static function hotelsuccess($orderdata){
		$WxApartmentOrder = new WxApartmentOrder();
		$hotel = $WxApartmentOrder->get_one_by_wxordernum($orderdata['userid'],$orderdata['id']);
		if($hotel) {
			if($hotel['pay_state']=='1') {
				$WxApartmentOrder->update_data(array('pay_state'=>'2','pay_complete_datetime'=>time()),"userid=".$orderdata['userid']." and wxordernum=".$orderdata['id']);
			}
		}
		echo '1';
	}
	
	/**
	 * 餐饮到店前支付
	 * @author zcc
	 */
	public static function takeoutpayago($orderdata){
		//餐饮单门店
		$WxRepastMeal = new WxRepastMeal();
		$WxRepastdishset = new WxRepastdishset();
		$meal = $WxRepastMeal->get_one("userid=".$orderdata['userid']." and ordernum=".$orderdata['id']);
		if($meal) {
			if($meal['pay_state']=='1' && $meal['type']=='1') {
				$WxRepastMeal->update_data(array('pay_state'=>'2'),"userid=".$orderdata['userid']." and ordernum=".$orderdata['id']);
				$WxGprsInfo = new WxGprsInfo();
				$WxGprsInfo->insert_data(array(
					'userid'	=>	$orderdata['userid'],
					'sid'		=>	get_nonumbers($meal['id']),
					'status'	=>	'0',
					'cdate'		=>	time(),
					'cid'		=>	$meal['id'],
					'ctype'		=>	'2',
					'ispay'		=>	'1',
				));
				$WxGprsInfo->close();
				$dishset = $WxRepastdishset->get_one($orderdata['userid']);
				//发送邮件通知（到店前）
				if($dishset && $dishset['on_off_email']=='1' && $dishset['email']) {
					$filecon = file_get_contents("http://".$_SERVER['HTTP_HOST']."/common/dishmod?t=1&d=1&id=".$meal['id']);
					send_mail($dishset['email'],'您有新的堂吃订单，请及时查看！',$filecon,'点点客餐饮');
				}
				$WxRepastdishset->close();
			}
		} else {
			//餐饮多门店
			$WxRepastsMeal = new WxRepastsMeal();
			$WxRepastsdishset = new WxRepastsdishset();
			$meal = $WxRepastsMeal->get_one("userid=".$orderdata['userid']." and ordernum=".$orderdata['id']);
			if($meal['pay_state']=='1' && $meal['type']=='1') {
				$WxRepastsMeal->update_data(array('pay_state'=>'2'),"userid=".$orderdata['userid']." and ordernum=".$orderdata['id']);
				$WxGprsInfo = new WxGprsInfo();
				$WxGprsInfo->insert_data(array(
					'userid'	=>	$orderdata['userid'],
					'sid'		=>	get_nonumbers($meal['id']),
					'status'	=>	'0',
					'cdate'		=>	time(),
					'cid'		=>	$meal['id'],
					'ctype'		=>	'4',
					'ispay'		=>	'1',
					'restaurant_id'	=>	$meal['restaurant_id'],
				));
				$WxGprsInfo->close();
				//发送邮件通知（到店前）
				$dishset = $WxRepastsdishset->get_one($meal['userid'],$meal['restaurant_id']);
				if($dishset && $dishset['on_off_email']=='1' && $dishset['email']) {
					$filecon = file_get_contents("http://".$_SERVER['HTTP_HOST']."/common/dishmod?t=2&d=1&id=".$meal['id']);
					send_mail($dishset['email'],'您有新的堂吃订单，请及时查看！',$filecon,'点点客餐饮');
				}
				$WxRepastsdishset->close();
			}
		}
		echo '1';
	}
	
}
?>
