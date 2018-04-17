<?php
/*
 * @author sunshichun@dodoca.com
 * 粉丝卡
 */
include_once "Template.php";
class FansCard
{
	/**
	 * 通过优惠码获取优惠信息
	 * $code->优惠码
	 * @return array
	 * $ret["money"]=$moeny;  //金额
		$ret["types"]=$types;    //类型  1->现金,2->折扣
		$type->码的类型,1->红包 ,2->优惠券,3->现金兑换券,4->折扣兑换券,5->实物兑换券,6->游戏中将码,7->电商虚拟抵用券,8->通用券,9->团购
	 */
	static public function get_info_bycode($code)
	{
		if(!$code)return;
		$moeny=0;
		$types=1;
		$first_code=mb_substr($code,0,1,'utf-8') ;
		switch ($first_code)
		{
			case "1":
				$redcode=new WxRedpacketCode();
				$rs=$redcode->scalar("id,redcode,codestatus,money,redid"," where redcode='$code' and  status=1 and codestatus=1");
				if($rs)
				{
					$redinfo=new WxRedpacketInfo();
					$r=$redinfo->scalar("id"," where  id=".$rs["redid"]." and status=1 and endtime>=".time()." and starttime<=".time());
					$redinfo->close();
					$moeny=$r?$rs['money']:0;
				}else{
					return '';
				}
				$redcode->close();
				break;
			case "3":
				$sub=new WxClubcardExchangeSubtabulation();
				$sub_info=$sub->scalar("id,exchange_id"," where sn='$code' and type=1 and is_deleted=1 and holdtime<".time());
				if($sub_info)
				{
					$ch=new WxClubcardExchange();
					$info=$ch->scalar("id,exchange_flag,exchange_discount,exchange_money"," where id=".$sub_info["exchange_id"]." and end_date>".time()." and start_date<".time()." and is_deleted=1");
					if($info)
					{
						if($info["exchange_flag"]=="2"  )//折扣
						{
							$moeny=$info["exchange_discount"];
							$types="2";
						}	
						else if($info["exchange_flag"]=="3")
						{
							$moeny=$info["exchange_money"];
						}		
					}else{
						return '';
					}
					$ch->close();
					unset($ch);
				}else{
					return '';
				}
				$sub->close();
				unset($sub);
				break;
			case "4":
				$sub=new WxClubcardExchangeSubtabulation();
				$sub_info=$sub->scalar("id,exchange_id"," where sn='$code' and type=1 and is_deleted=1 and holdtime<".time());
				if($sub_info)
				{
					$ch=new WxClubcardExchange();
					$info=$ch->scalar("id,exchange_flag,exchange_discount,exchange_money"," where id=".$sub_info["exchange_id"]." and end_date>".time()." and start_date<".time()." and is_deleted=1");
					if($info)
					{
						if($info["exchange_flag"]=="2"  )//折扣
						{
							$moeny=$info["exchange_discount"];
							$types="2";
						}	
						else if($info["exchange_flag"]=="3")
						{
							$moeny=$info["exchange_money"];
						}		
					}else{
						return '';
					}
					$ch->close();
					unset($ch);
				}else{
					return '';
				}
				$sub->close();
				unset($sub);
				break;
			case "7":
				$q=new WxGoodsQuanList();
				$info=$q->scalar("id,goods_id,order_id,voucher_number"," where voucher_number='$code' and vouchers_state=0  and over_time<".time());
				if($info)
				{
					$mgt=new WxGoodsOrderMgt();
					$rs=$mgt->scalar("id,unit_price"," where order_id=".$info["order_id"]." and goods_id=".$info["goods_id"]." and status=1");
					if($rs)
					{
						$moeny=$rs["unit_price"];
					}
					$mgt->close();
					unset($mgt);
				}else{
					return '';
				}
				$q->close();
				unset($q);
				break;
			case "2":
				return '';
				break;
			case "5":
				return '';
				break;
			case "6":
				return '';
				break;
		}
		$ret["money"]=$moeny;
		$ret["types"]=$types;
		if((!$moeny || $moeny=='0.00') && $types=='2')//折扣券是0
		{
			$moeny=1;
		}
		return $ret;
	}
	
	/**
	 * 修改券的使用状态
	 */
	static public function update_status($code)
	{
		if(!$code)return;
		$first_code=mb_substr($code,0,1,'utf-8') ;
		$sub=new WxClubcardExchangeSubtabulation();
		$sub_info = $sub->scalar("*"," where sn='$code'");
		switch ($first_code)
		{
			case "1":
				$redcode=new WxRedpacketCode();
				$redcode->update(array("exchange_date"=>time(),"codestatus"=>"0")," where redcode='$code'");
				$redcode->close();
				break;
			case "3":
				$sub=new WxClubcardExchangeSubtabulation();
				$r=$sub->update(array("type"=>"0","exchange_date"=>time())," where sn='$code'");
				if($r)
				{		
					$ch=new WxClubcardExchange();
					$ch->execute("update wx_club_card_exchange set exchange_usedcount=exchange_usedcount+1 where id=".$sub_info["exchange_id"]);
					$ch->close();
					unset($ch);
				}
				$sub->close();
				unset($sub);
				break;
			case "4":
				$sub=new WxClubcardExchangeSubtabulation();
				$r=$sub->update(array("type"=>"0","exchange_date"=>time())," where sn='$code'");
				if($r)
				{		
					$ch=new WxClubcardExchange();
					$ch->execute("update wx_club_card_exchange set exchange_usedcount=exchange_usedcount+1 where id=".$sub_info["exchange_id"]);
					$ch->close();
					unset($ch);
				}
				$sub->close();
				unset($sub);
				break;
			case "7":
				$q=new WxGoodsQuanList();
				$info=$q->scalar("id,goods_id,order_id,voucher_number"," where voucher_number='$code' and vouchers_state=0 and over_time<".time());
				$q->update(array("vouchers_state"=>"1")," where voucher_number='$code' ");
				$q->close();
				unset($q);
				break;
		}
	}
	/**
	 * 创建优惠码
	 * $type->码的类型,1->红包 ,2->优惠券,3->现金兑换券,4->折扣兑换券,5->实物兑换券,6->游戏中将码,7->电商虚拟抵用券,8->通用券,9->团购券
	 */
	static public function create_code($type)
	{
		if(!$type)return '';
		$code=$type.date("d").rand(100000000,999999999);
		return $code;
	}
	
	/**
	 * 创建订单号
	 */
	static public function create_order_num()
	{
		$code=date("d").rand(1000000000,9999999999);
		return $code;
	}

	/**
	 * 改变粉丝卡用户的关注状态
	 * $flag 1、关注 2、取消关注
	 * $openid 用户openid
	 */
	static public function alter_fanscard_flag($flag,$openid){
		if(!$openid)return;
		$fanscardModel = new WxClubcard();
		$fanscardModel->updateuse(array('card_flag'=>intval($flag)),"where openid=".$openid);
	}
	
	/**
	 * 获取打印小票账单数据
	 * @param $id 账单数据id
	 * @author ailiya
	 */
	static public function get_print_bills_data($id)
	{
	    if($id > 0)
	    {
	        $data = array();
	        $WxClubcardGoldenCalendar = new WxClubcardGoldenCalendar();
	        $record = $WxClubcardGoldenCalendar -> get_one($id);
	        if($record)
	        {
	            $WxClubcard = new WxClubcard();
	            $card = $WxClubcard -> get_databyopen($record['userid'],
	                                                  $record['openid']);
	            if($card)
	            {
    	            return array(
    	                'card_number' => $card['card_number'],                      //卡号
    	                'newtype' => $GLOBALS['golden_type'][$record['newtype']],   //操作类型
    	                'sum' => abs($record['sum']),                               //操作金额
    	                'card_golden' => $card['card_golden'],                      //金点数
    	            );
    	        }
	        }
	    }
	    return false;
	}
}  
