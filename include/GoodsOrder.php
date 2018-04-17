<?php

/**
 * @author yangyuechen
 *	微团购订单列表和订单详情
 */
class GoodsOrder
{
	/*
	 * @author yangyuechen
	* 客服电话管理
	*/
	public static function  setphone()
	{
		$phone = $_POST['phone'];
		if(isset($phone) && !empty($phone)){
			
			$data['phone'] = $phone;
			$userid = get_uid();
			$model = new WxDpSet();
			$id = $model -> scalar("id", "where userid = {$userid}");
			if($id == null || empty($id))
			{
				
				$model -> insert($data);
				
			}else{
				
				$where = "where userid = $userid";
				$model -> update($data, $where);
				
			}
		}
	}
	/*
	 * @author yangyuechen
	* 订单取消管理
	*/
	public static function settime()
	{
		$time_limit = $_POST['time_limit'];
		if(isset($time_limit) && !empty($time_limit)){
			
			$data['time_limit'] = $time_limit;
			$userid = get_uid();
			$model = new WxDpSet();
			$id = $model -> scalar("id", "where userid = {$userid}");
			if($id == null || empty($id))
			{
				$model -> insert($data);
				
			}else{
				
				$where = "where userid = $userid";
				$model -> update($data, $where);
				
			}
		}
	}
	/*
	 * @author yangyuechen
	 * 订单列表
	 */
	public static function showgoodsorderlist()
	{
		$userid = get_uid();
		$from_type='';
		$pay_type = $_GET['pay_type'];
		if(isset($_GET['from_type']) && !empty($_GET['from_type']))
		{
			$from_type = $_GET['from_type'];
		}
		else
		{
			$from_type = 2;  //默认店铺
			$_GET['from_type']=$from_type;
		}
		$goods_id='';
		$good_info='';
		$where=" where userid=$userid and order_status != -1";
		$obj=new WxOrder();
		$goods=new WxGoods();
		if(isset($_GET['goods_id']) && !empty($_GET['goods_id']))
		{
			$goods_id=$_GET['goods_id'];
			$tmp_order_ids='';
			$goods_order=$obj->fetchAll("select id,goods_id,order_id from wx_goods_order_mgt where goods_id=".intval($_GET['goods_id']));
			if($goods_order)
			{
				foreach($goods_order as $kk=>$vv)
				{
					$tmp_order_ids.=$vv["order_id"].',';
				}
			}
			$tmp_order_ids=trim($tmp_order_ids,',');
			$tmp_order_ids=$tmp_order_ids?$tmp_order_ids:0;
			$where.=" and id in($tmp_order_ids)";
			
			$good_info=$goods->get_row_byid($goods_id);
			$good_info["start_time"]=$good_info["start_time"]?date("Y-m-d H:i:s",$good_info["start_time"]):0;
			$end_time=$good_info["end_time"];
			if($end_time)
			{
				$diff=$end_time-time();
				if($diff<=0)
				{
					$good_info["diff_time"]='已经结束';
				}
				else
				{
					$day=intval($diff/86400);
					$house=intval(($diff%86400)/3600);
					$second=intval((($diff%86400)%3600)/60);
					$min=(($diff%86400)%3600)%60;
					$diff_time=$day>0?$day."天":'';
					$diff_time.=$house>0?$house."小时":'';
					$diff_time.=$second>0?$second."分钟":'';
					$diff_time.=$min>0?$min."秒":'';
					
					//$diff_time.=$house.":".$second.":".$min;
					$good_info["diff_time"]=$diff_time;
				}
			}
			$good_info["end_time"]=$good_info["end_time"]?date("Y-m-d H:i:s",$good_info["end_time"]):0;
		}
		
		if(isset($_GET["start"]) && $_GET["start"])
		{
			$where.=" and  cdate>=".strtotime($_GET["start"]);
		}
		if(isset($_GET["end"]) && $_GET["end"])
		{
			$where.=" and  cdate<=".strtotime($_GET["end"]." 23:59:59");
		}
		
		$currpage=$_GET["currpage"]?$_GET["currpage"]:1;
		$orderby_clause="order by id desc";
		$filter_array=$filter_data=$obj->get_filter_array();

		$where_clause=PubFun::get_where($_GET,$filter_data,$where);
		//代付筛选条件
		$userinfor = User::get_user_info($userid);
		if($from_type == 2 && ($pay_type == '' || $pay_type == 12) && $userinfor['ver_type'] == 4)
		{
			$_GET['from_type']=12;
			$_GET['pay_type']=12;
			$daifu_where=PubFun::get_where($_GET,$filter_data,$where);
			$where_clause.=' or ('.str_replace('where','',$daifu_where).')';
			$_GET['from_type']=$from_type;
		    $_GET['pay_type']=$pay_type;
			//$where_clause.=" or ((from_type = 12 and pay_type = 12) and userid = $userid and order_status != -1)";
		}
		

		$fields='id,order_no,order_amount,pay_type,pay_status,order_status,from_type,cdate,post_status,post_free,over_time,remark,come_from';
		$hash=PubFun::get_data_by_where($obj,$fields,$where_clause,$orderby_clause,$currpage,20);
		if($hash["data"])
		{
			$order_detail=new WxGoodsOrderMgt();
			$order_log=new WxOrderLogistics();
			foreach($hash["data"] as $k=>$v)
			{
				$v["pay_type_id"]=$v["pay_type"];
				if($v["pay_type"] == 9)
				{
					$v["pay_name"]='粉丝卡支付';
				}elseif($v["pay_type"] == 12){
					$v["pay_name"]='找人代付';
				}else{
					$v["pay_name"]=$GLOBALS['pay_type'][$v["pay_type"]];
				}
				$v["post_free"]=sprintf('%.2f',$v["post_free"]);
				$v["order_amount"]=sprintf('%.2f',$v["order_amount"]);
				
				$v["cdate"]=$v["cdate"]?date("Y-m-d H:i:s",$v["cdate"]):0;
				$tmp=$order_detail->fetchAll("select id,goods_id from ".$order_detail->_name." where order_id=".$v["id"]);
				
				$v["product_count"]=$tmp?count($tmp):0;
				$v["is_vir"]=0;//非虚拟商品
				if($tmp && ($v["from_type"]=='3' || $v["from_type"]=='4' ||  $v["from_type"]=='5' ||  $v["from_type"]=='2' ||  $v["from_type"]=='12'))
				{
					$tmp_ids='';
					foreach($tmp as $tmp_key=>$tmp_val)
					{
						$tmp_ids.=$tmp_val["goods_id"].',';
					}
					$tmp_ids=trim($tmp_ids,',');
					if($tmp_ids)
					{
						$exist=$goods->fetchAll("select id,is_virtua from  ".$goods->_name." where userid=$userid and  id in($tmp_ids) and (is_virtua=2 or is_virtua=0) ");
						if(!$exist)
						{
							$v["is_vir"]=1;//虚拟商品
						}
					}
				}
				
				if($v["over_time"]<=time() && $v["pay_type"]>=1 && $v["pay_type"]<=5 &&  $v["pay_status"]=='0')//过期
				{
					$v["order_status"]=0;
				}
				$v["show_send"]=0;
				if(($v["pay_status"]=="1" && !$v["is_vir"] && !$v["post_status"] && $v["order_status"])  || ($v["pay_status"]=="0" && $v["pay_type"]=="6" && !$v["post_status"] && $v["order_status"]))//非虚拟商品已经支付
				{
					$v["show_send"]=1;
				}
				$v["is_over_time"]=0;
				if($v["pay_status"]=='0' && $v["over_time"]<time() && $v["pay_type"]!='6')
				{
					$v["is_over_time"]='1';
				}
				
				$add_info=$order_log->fetchAll("select b.name,b.phone from wx_order_logistics as a inner join wx_order_shipping_address as b on a.order_id=".$v["id"]." where a.post_id=b.id");
				$v["post_info"]=$add_info?$add_info[0]:'';
				unset($tmp);
				$hash["data"][$k] = $v;
			}
			$order_detail->close();
			$order_log->close();
		}
		$goods->close();
		$obj->close();
		$buy=new WxBuyinfo();
		$buy_info=$buy->get_info($userid,1);
		$buy->close();
		$hash["pay_list"]=$buy_info;
		$hash['ver_type'] = self::getUserVertype($userid);
		
		//左侧菜单
		$hash["page_key"] =self::get_page_key($from_type);
		$hash['from_type'] = $from_type;		//店铺类型
		$hash['para']=$_GET;
		$hash["goods_id"]=$goods_id;
		$hash["good_info"]=$good_info;
		return $hash;
	}
	/*
	 * @author yangyuechen
	 * 订单详情
	 */
	public static function showgoodsorderdetail()
	{
		$obj=new WxOrder();
		$userid = get_uid();
		$id = intval($_GET['order_id']);
		$hash["order_id"]=$id;
		$hash["from_type"]=trim($_GET["from_type"]);
		$hash['show_send'] = $_GET['show_send'];
		$hash["goods_id"]=$_GET["goods_id"];
		$hash['is_vir'] = $_GET['is_vir'];
		$order_info=$obj->scalar("*"," where id=".$id." and userid=".$userid);
		$hash["paid_type"] = 0; //代付款状态
		if($order_info["pay_status"]=='0' && $order_info["over_time"]<time() && $order_info["pay_type"]!='6')
		{
			$order_info["status_info"]="已过期";
			$hash["paid_type"] = 0; 
		}
		else
		{
			if($order_info["order_status"]=="0")
			{
				$order_info["status_info"]="已取消";
				$hash["paid_type"] = 0; 
			}
			else if($order_info["order_status"]=="1")
			{
				$order_info["status_info"]="有效";
				$hash["paid_type"] = 1; //代付款
			}
			else if($order_info["order_status"]=="2")
			{
				$order_info["status_info"]="已完成";
				$hash["paid_type"] = 2; 
			}
		}
		
		if($order_info["over_time"]<=time() && $order_info["pay_type"]>=1 && $order_info["pay_type"]<=5 &&  $order_info["pay_status"]=='0')//过期
		{
			$order_info["order_status"]=0;
		}
		$order_info["show_send"]=0;
		if(($order_info["pay_status"]=="1" && !$hash['is_vir'] && !$order_info["post_status"] && $order_info["order_status"])  || ($order_info["pay_status"]=="0" && $order_info["pay_type"]=="6" && !$order_info["post_status"] && $order_info["order_status"]))//非虚拟商品已经支付
		{
			$order_info["is_show_tmp"]=1;
		}
		if($order_info["pay_type"] == 9)
		{
			$order_info["pay_type"]='粉丝卡支付';
		}elseif($order_info["pay_type"] == 12){
			$order_info["pay_type"]='找人代付';
		}else{
			$order_info["pay_type"]=$GLOBALS['pay_type'][$order_info["pay_type"]];
		}
		//收件人
		$add_info=$obj->fetchAll("select b.name,b.phone,b.province,b.city,b.area,b.address,a.express_no,company from wx_order_logistics as a inner join wx_order_shipping_address as b on a.order_id=".$id." where a.post_id=b.id");
		$order_info["post_info"]=$add_info?$add_info[0]:'';
		$address=$order_info["post_info"]["province"].$order_info["post_info"]["city"].$order_info["post_info"]["area"].$order_info["post_info"]["address"];
		$order_info["post_info"]['address']=$address;
		$order_info["post_free"]=sprintf('%.2f',$order_info["post_free"]);
		$order_info["order_amount"]=sprintf('%.2f',$order_info["order_amount"]);
		$hash["order_info"]=$order_info;
		
		//原价
		if($order_info['order_no'])
		{
			$WxOrderPriceHistory = new WxOrderPriceHistory();
			$hash["old_price"] = $WxOrderPriceHistory->scalar("price", " where order_no = 
			'".$order_info['order_no']."' and userid = ".$userid." order by id ");
		}
		//商品列表
		$good=new WxGoods();
		$list=new WxGoodsQuanList();
		$product=$good->fetchAll("select id,goods_id,count_num,unit_price,attr_id from wx_goods_order_mgt where order_id=".$id);
		if($product)
		{
			foreach($product as $key=>&$val)
			{
				$val["product_info"]=$good->get_row_byid($val["goods_id"]);
				$val["total_price"]=sprintf('%.2f',$val["count_num"]*$val["unit_price"]);
				
				//zy140725start
				$attrstr="";
				$attrinfo = GoogsApi::getgoodsattributesAction($val["attr_id"],$userid,$val["goods_id"]);
				if($attrinfo){
					foreach($attrinfo as $val2){
						$attrstr .= " / ".$val2['cname'];
					}
				}
				$val["attrstr"]=$attrstr;
				//zy140725end
				$product[$key]['quan']=$list->fetchAll("select goods_id,voucher_number,vouchers_state,over_time from ".$list->_name." where order_id=".$id." and goods_id = ".$val['goods_id']);
				if($product[$key]['quan'])
				{
					foreach($product[$key]['quan'] as &$v2)
					{
						if($v2["vouchers_state"]=='1')
						{
							$v2["quan_status"]="已使用";
						}
						else
						{
							if($v2["over_time"]<time())
							{
								$v2["quan_status"]="已过期";
							}
							else
							{
								$v2["quan_status"]="未使用";
							}
						}
						$v2["over_time"]=$v2["over_time"]?date("Y-m-d H:i:s",$v2["over_time"]):0;
					}
				}
				$product[$key]=$val;
			}
		}
		$hash["product_list"]=$product;
		$quan=$list->fetchAll("select goods_id,voucher_number,vouchers_state,over_time from ".$list->_name." where order_id=".$id);
		if($quan)
		{
			foreach($quan as $k=>$v)
			{
				if($v["vouchers_state"]=='1')
				{
					$v["quan_status"]="已使用";
				}
				else
				{
					if($v["over_time"]<time())
					{
						$v["quan_status"]="已过期";
					}
					else
					{
						$v["quan_status"]="未使用";
					}
				}
				$v["over_time"]=$v["over_time"]?date("Y-m-d H:i:s",$v["over_time"]):0;
				$quan[$k]=$v;
			}
		}
		$hash["quan"]=$quan;
		
		//节省费用
		$coup=new WxGoodsOrderCoupon();
		$space_info=$coup->scalar("*"," where order_id=".$id);
		if($space_info)
		{
			$club=new WxClubcardExchangeSubtabulation();
			$code_data=$club->scalar("id,sn"," where id=".$space_info["coupon_id"]);
			if($code_data)
			{
					$hash["sn_code"]=$code_data["sn"];
			}
		}
		
		//代付信息
		$hash["total_money"] = 0;
		$hash["paid_info"] = array();
		$hash["paid_info"] = GoogsApi::daifuinfor($userid,$order_info['id']);
		if($hash["paid_info"])
		{
			foreach($hash["paid_info"] as &$v)
			{
				if($v['cdate'])
				{
					$v['cdate'] = date('Y-m-d H:i', $v['cdate']);
				}else{
					$v['cdate'] = '';
				}
				$hash["total_money"] += sprintf("%.2f",$v['total_amount']);
			}
		}
		$hash["total_money"] = sprintf("%.2f",$order_info['total_amount'] - $hash["total_money"]);
		
		
		//代付进度
		$hash["paid_time"] = array();
		$hash["paid_time"] = GoogsApi::daifuprogress($userid,$order_info['id']);
		if($hash["paid_time"])
		{
			$hash["paid_time"]['daifu_progress'] = $hash["paid_time"]['daifu_progress'];
		}
		
		//左侧列表
		$hash["page_key"] =self::get_page_key($hash["from_type"]);
		return $hash;
	}
	
	
	/*
	 * @author yangyuechen
	 * 编辑发货信息
	 */
	public static function editdeliver()
	{
		$company = trim($_POST['company']);
		$express_no = trim($_POST['express_no']);
		$order_id = $_POST['order_id'];
		$dilordermod = new WxOrderLogistics();
		$obj=new WxOrder();
		if($order_id)
		{
			$result=0;
			$update_data['company'] = $company;
			$update_data['express_no'] = $express_no;
			if(is_numeric($order_id))//单条记录
			{
				$result = $dilordermod->update_data($update_data,"where order_id = $order_id");
				$obj->update_data(array("post_status"=>"1","order_status"=>"2")," where id =".$order_id);
			}
			else
			{
				$order_id=trim($order_id,',');
			}
			
			$tmp_arr=explode(',',$order_id);
			if(is_array($tmp_arr))
			{
				foreach($tmp_arr as $k=>$v)
				{
					if($v)
					{
						$order_info=$obj->scalar("*"," where id=".$v." and post_status=0 and order_status=1");
						//$obj->debug();
						if($order_info)
						{
							if($order_info["pay_type"]>=1 && $order_info["pay_type"]<=5 || $order_info["pay_type"]==10 || $order_info["pay_type"]==12)//在线支付
							{
								$obj->update_data(array("post_status"=>"1","order_status"=>"2")," where id =".$v);
							}
							else if($order_info["pay_type"]=='6' || $order_info["pay_type"]=='7' || $order_info["pay_type"]=='9')//货到付款/直接发货/粉丝卡
							{
								/*if(!$order_info["pay_status"])//未支付
								{
									$obj->update_data(array("post_status"=>"1")," where id =".$v);
								}
								else 
								{*/
									$obj->update_data(array("order_status"=>2,"post_status"=>"1")," where id =".$v);
								//}
							}
						}
						unset($order_info);
					}
				}
			}
			$obj->close();
			echo 1;
		}else{
			echo 3;
		}
		exit;
 	}
 	
 	/*
 	* 付款
 	*/
 	public static function paymoney()
 	{
 		$userid = get_uid();
 		$order_id = intval(trim($_POST['order_id']));
 		if($order_id)
 		{
 			$obj=new WxOrder();
 			$order_info=$obj->scalar("*"," where   id=".$order_id."  and post_status=1 and pay_type=6");
 			if($order_info)
 			{
 				$obj->update_data(array("pay_status"=>"1","order_status"=>"2")," where id =".$order_id);
 				//创建虚拟商品券
 				if($order_info['from_type'] != 6)
 				{
 					GoogsApi::createvirtualgoodsnumber($order_id,$order_info['from_type'],$userid,$order_info['openid']);
 				}
 				//使用粉丝卡券
 				ResultConnector::modifycardstatus($order_id);
 			}
 			unset($order_info);
 			$obj->close();
 			echo 1;
 		}else{
 			echo 3;
 		}
 		exit;
 	}
 	
 	public static function get_page_key($from_type)
 	{
 		if(!$from_type)return '';
 		$match["2"]="trade_shop";
 		$match["3"]="trade_tg";
 		$match["4"]="trade_xsg";
 		$match["5"]="trade_wms";
 		$match["6"]="trade_jp";
		return isset($match[$from_type])?$match[$from_type]:'';
 	}

 	/*
 	 * @author yangyuechen
 	 * 获得原先的发货信息显示在编辑卡中
 	 */
 	public static function editexpress()
 	{
 		$orderid = $_POST['order_id'];
 		$expressmod = new WxOrderLogistics();
 		$rs = $expressmod->scalar("company,express_no", "where order_id = $orderid");
 		echo json_encode($rs);
 	}
 	/*
 	 * @author yangyuechen
 	 * 导出订单
 	 */
 	public static function export()
 	{
 		$userid = get_uid();
 		$obj=new WxOrder();
 		$where=" where userid=$userid and order_status>=1";
 		$pay_type = $_GET['pay_type'];
 		if(isset($_GET['goods_id']) && !empty($_GET['goods_id']))
 		{
 			$tmp_order_ids='';
 			$goods=$obj->fetchAll("select id,goods_id,order_id from wx_goods_order_mgt where  goods_id=".intval($_GET['goods_id']));
 			if($goods)
 			{
 				foreach($goods as $kk=>$vv)
 				{
 					$tmp_order_ids.=$vv["order_id"].',';
 				}
 			}
 			$tmp_order_ids=trim($tmp_order_ids,',');
 			$tmp_order_ids=$tmp_order_ids?$tmp_order_ids:0;
 			$where.=" and id in($tmp_order_ids)";
 			$hash["goods_id"]=$_GET['goods_id'];
 		}
 		$from_type='';
 		if(isset($_GET['from_type']) && !empty($_GET['from_type']))
 		{
 			$from_type = $_GET['from_type'];
 		}
 		else
 		{
 			$from_type = 2;  //默认店铺
 			$_GET['from_type']=$from_type;
 		}
 		
 		if(isset($_GET["start"]) && $_GET["start"])
 		{
 			$where.=" and  cdate>=".strtotime($_GET["start"]);
 		}
 		if(isset($_GET["end"]) && $_GET["end"])
 		{
 			$where.=" and  cdate<=".strtotime($_GET["end"]." 23:59:59");
 		}
 		$currpage=$_GET["currpage"]?$_GET["currpage"]:1;
 		$orderby_clause="order by id desc";
 		$filter_array=$filter_data=$obj->get_filter_array();
 		$where_clause=PubFun::get_where($_GET,$filter_data,$where);
 		//代付筛选条件
 		$userinfor = User::get_user_info($userid);
		if($from_type == 2 && ($pay_type == '' || $pay_type == 12) && $userinfor['ver_type'] == 4)
		{
			$_GET['from_type']=12;
			$_GET['pay_type']=12;
			$daifu_where=PubFun::get_where($_GET,$filter_data,$where);
			$where_clause.=' or ('.str_replace('where','',$daifu_where).')';
			$_GET['from_type']=$from_type;
		    $_GET['pay_type']=$pay_type;
			//$where_clause.=" or ((from_type = 12 and pay_type = 12) and userid = $userid and order_status != -1)";
		}
		//
 		$fields='id,order_no,total_amount,order_amount,pay_type,pay_status,cdate,post_status,spare_amount,post_free,remark';
 		$hash=PubFun::get_data_by_where($obj,$fields,$where_clause,$orderby_clause,$currpage,100000);
 		$header1=array("订单编号","订单状态","支付方式","下单时间","商品名称","商品数量","收件人","联系方式","收货地址","物流公司","快递单号","应付金额(元)","实付金额(元)","运费","兑换金额","兑换券号","备注");
 		//$header = implode("\",\"",array_values($header1));
 		//$header = "\"" .$header;
 		//$header .= "\"\r\n";
 		//$content .= $header;
 		
 		
 		$header = implode("\",\"",array_values($header1));
		$header = "\"" .$header;
		$header .= "\"\r\n";
		$content .= $header;
		ob_end_clean();
		header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("X-DNS-Prefetch-Control: off");
		header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=orderlist.csv");
		$content=iconv("UTF-8","GBK//IGNORE",$content) ;
		echo $content;
		
 		if($hash["data"])
 		{
 			$order_detail=new WxGoodsOrderMgt();
 			$order_log=new WxOrderLogistics();
 			$goods=new WxGoods();
 			foreach($hash["data"] as $k2=>$v2)
 			{
 				$tmp = array();
 				$tmp = $order_detail->fetchAll("select id,goods_id,count_num from ".$order_detail->_name." where order_id=".$v2["id"]);
 				if($tmp)
 				{
	 				if(count($tmp) > 1)
	 				{
		 				foreach($tmp as $val)
		 				{
		 					$goods_name = array();
	 						$goods_name = $goods->scalar('name', ' where id = '.$val['goods_id'].' and userid = '.$userid);
		 					$goods_nums = array();
		 					$goods_nums = array('goods_nums'=>$val['count_num'],'goods_name'=>$goods_name);
		 					$goods_nums_temp = array();
		 					$goods_nums_temp = array_merge($v2,$goods_nums);
		 					array_push($hash["data"], $goods_nums_temp);
		 				}
	 					unset($hash["data"][$k2]);
	 				}else{
	 					$goods_name = array();
	 					$goods_name = $goods->scalar('name', ' where id = '.$tmp[0]['goods_id'].' and userid = '.$userid);
	 					$hash["data"][$k2]['goods_nums'] = $tmp[0]['count_num'];
	 					$hash["data"][$k2]['goods_name'] = $goods_name;
	 				}
 				}
 				
 			}
 			$hash["data"] = GoogsApi::shoparraysortAction($hash["data"], 'id', 'desc');
 			foreach($hash["data"] as $k=>$v)
 			{
 				if($hash["data"][$k]["order_no"] == $hash["data"][$k-1]["order_no"])
 				{
 					$tmp_array["order_no"]='';
 					$tmp_array["order_status"]='';
 					$tmp_array["pay_name"]='';
 					$tmp_array["cdate"]='';
 				}else{
 					$tmp_array["order_no"]=$v["order_no"];
 					$tmp_array["order_status"]=$v["pay_status"]=="1"?"已支付":"未支付";
 					if($v["pay_type"] == 9)
	 				{
	 					$tmp_array["pay_name"]='粉丝卡支付';
	 				}elseif($v["pay_type"] == 12){
						$tmp_array["pay_name"]='找人代付';
					}else{
	 					$tmp_array["pay_name"]=$GLOBALS['pay_type'][$v["pay_type"]];
	 				}
	 				$tmp_array["cdate"]=$v["cdate"]?date("Y-m-d H:i:s",$v["cdate"]):0;
 				}
 				//$tmp=$order_detail->fetchAll("select id,goods_id,count_num from ".$order_detail->_name." where order_id=".$v["id"]);
 				//$tmp_array["product_count"]=$tmp?count($tmp):0;
 				
 				$tmp_array["goods_name"]=$v["goods_name"];
 				$tmp_array["goods_nums"]=$v["goods_nums"];
 				if($hash["data"][$k]["order_no"] == $hash["data"][$k-1]["order_no"])
 				{
 					$tmp_array["post_name"]='';
	 				$tmp_array["post_phone"]='';
	 				$tmp_array["addr"]='';
	 				$tmp_array["company"]='';
	 				$tmp_array["express_no"]='';
	 				$tmp_array["total_amount"]='';
	 				$tmp_array["order_amount"]='';
	 				$tmp_array["post_free"]='';
	 				$tmp_array["spare_amount"]='';
	 				$tmp_array["sn_code"]='';
	 				$tmp_array["spare_amount"]='';
	 				$tmp_array["remark"]='';
 				}else{
 					$add_info=$order_log->fetchAll("select express_no,company,b.name,b.phone,address,province,city,area from wx_order_logistics as a inner join wx_order_shipping_address as b on a.order_id=".$v["id"]." where a.post_id=b.id");
	 				$post_info=$add_info?$add_info[0]:'';
	 				if($post_info)
	 				{
	 					$tmp_array["post_name"]=$post_info["name"];
	 					$tmp_array["post_phone"]=$post_info["phone"];
	 					$tmp_array["addr"]=$post_info["province"]." ".$post_info["city"]." ".$post_info["area"]." ".$post_info["address"];
	 					$tmp_array["company"]=$post_info["company"];
	 					$tmp_array["express_no"]=$post_info["express_no"];
	 				}
	 				else
	 				{
	 					$tmp_array["post_name"]='';
	 					$tmp_array["post_phone"]='';
	 					$tmp_array["addr"]='';
	 					$tmp_array["company"]='';
	 					$tmp_array["express_no"]='';
	 				}
	 				$v["total_amount"]?$tmp_array["total_amount"]=$v["total_amount"]:$tmp_array["total_amount"]='';
	 				$v["order_amount"]?$tmp_array["order_amount"]=$v["order_amount"]:$tmp_array["order_amount"]='';
	 				$v["post_free"]?$tmp_array["post_free"]=$v["post_free"]:$tmp_array["post_free"]='';
	 				$v["spare_amount"]?$tmp_array["spare_amount"]=$v["spare_amount"]:$tmp_array["spare_amount"]='';
	 				$tmp_array["sn_code"]='';
	 				$coup=$goods->fetchAll("select sn from wx_club_card_exchange_subtabulation as a inner join  wx_goods_order_coupon as b on a.id=b.coupon_id where b.order_id=".$v["id"]." limit 1");
	 				if($coup)
	 				{
	 					$tmp_array["sn_code"]=$coup[0]["sn"];
	 				}
	 				else
	 				{
	 					$tmp_array["spare_amount"]='';
	 				}
	 				$v["remark"]?$tmp_array["remark"]=$v["remark"]:$tmp_array["remark"]='';
 				}
 				
 				$new_arr = array();
				$content = "";
				foreach ($tmp_array as $key => $value)
				{
					array_push($new_arr, preg_replace("/\"/","\"\"","\t".$value));
				}
				/*$line = implode("\",\"",$new_arr);
				$line = "\"" .$line;
				$line .= "\"\r\n";
				$content .= $line;

 				unset($tmp_array);*/
				$line = implode("\",\"",$new_arr);
				$line = "\"" .$line;
				$line .= "\"\r\n";
				$content .= $line;
				$content=@iconv("UTF-8","GBK//IGNORE",$content) ;
				echo $content;
 			}
 		}

		/*ob_end_clean();
		header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("X-DNS-Prefetch-Control: off");
		header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");

		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=orderlist.csv");
		$content=iconv("UTF-8","GB2312//IGNORE",$content) ;
		print $content;
		unset($content);*/
		//exit;
 	}

 	public static function getUserVertype($userid) {
 		if (!$userid || !is_numeric($userid))
 			return;
 		$usermod = new WxUser();
 		$user = $usermod->get_row_byid($userid);
 		return $user['ver_type'];
 	}
}













