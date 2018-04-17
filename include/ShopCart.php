<?php
/*
 * @author sunshichun@dodoca.com
 * 购物车
 */
class ShopCart
{
	/**
	 * 获取粉丝id
	 */
	static public function get_fans_info()
	{
		$u_info=$_SESSION['clubdata'];
		//$u_info=array("userid"=>"1","openid"=>"26");
		return $u_info;
	}
	
	/**
	 * 添加、减少 购物车商品
	 * $id 商品id
	 * $count 商品数量 (大于0是加数量，小于0减数量)
	 * 
	 * @return $array
	 * Array(
    [code] => 1 // 1->成功 0->失败
    [data] => Array //设置成功后返回购物车商品
        (
            [1_0] => Array
                (
                    [goods_id] => 1 //商品编号
                    [attr_id] => 0 //属性id
                    [price] => 1.00 //价格
                    [count_num] => 2 //数量
                )
        )
    [msg] =>   //发生错误时的提示
)
	 */
	static public function cart_set_good($id,$attr_id=0,$count=1)
	{
			$code='0';
			$data=array();
			$msg='';
			if($count==0)
			{
				return	PubFun::to_msg($code,$data,'缺少数量');
			}
			if($id)
			{
				$arr_key=$id.'_'.$attr_id;
				$data=self::cart_get_good();
				if($data && is_array($data) && array_key_exists($arr_key,$data) )
				{
					$data[$arr_key]["count_num"]=$data[$arr_key]["count_num"]+$count;
					if(!$data[$arr_key]["count_num"])//数量删除完，清空该商品
					{
						unset($data[$arr_key]);
					}
					$code=1;
				}
				else
				{
					if($count>0)
					{
						$tmp_info=self::get_good_info($id,$attr_id);
						if(!$tmp_info)
						{
							$msg='编号不存在!';
						}
						else
						{
							$tmp["goods_id"]=$id;
							$tmp["attr_id"]=$attr_id;
							$tmp["price"]=$tmp_info["price"];
							$tmp["count_num"]=$count;
							$data[$arr_key]=$tmp;
							unset($tmp);
							$code=1;
						}
					}
					else
					{
						$msg='商品不存在!';
					}
				}
				$key=self::get_cart_session_key();
				$_SESSION[$key]=$data;
			}
			else
			{
				$msg='缺少参数!';
			}
			return	PubFun::to_msg($code,$data,$msg);
	}
	
	/**
	 * 获取商品
	 * $id ->商品id
	 * $attr_id-> 属性id
	 */
	static public function get_good_info($id,$attr_id=0)
	{
		$ret=array();
		if($attr_id)
		{
			$w=new WxGoodsAttrDetail();
			$info=$w->scalar("id,price"," where id=".$attr_id." and goods_id=".$id);
			if($info)
			{
				$ret["price"]=$info["price"];
			}
			$w->close();
		}
		else
		{
			$w=new WxGoods();
			$info=$w->scalar("id,present_price"," where id=".$id." and status=1");
			if($info)
			{
				$ret["price"]=$info["present_price"];
			}
			$w->close();
		}
		return $ret;
	}
	
	/**
	 * 删除购物车商品
	 * $id 商品id
	 * @return array
	 * Array
		(   [code] => 1   // 1->成功 0->失败
		    [data] => Array //删除之后还剩余的商品
		        (
		        )
			    [msg] => //发生错误时的提示
		)
	 */
	static public function cart_remove_good($id,$attr_id=0)
	{
		$arr_key=$id.'_'.$attr_id;
		$data=self::cart_get_good();
		if($data)
		{
			unset($data[$arr_key]);
		}
		$key=self::get_cart_session_key();
		$_SESSION[$key]=$data;
		return	PubFun::to_msg(1,$data);
	}
	
	//获取购物车会话key
	static public function get_cart_session_key()
	{
		$user_info=self::get_fans_info();
		return $user_info["openid"].'cart_data';
	}
	
	/**
	 * 获取购物车商品
	 * @return array
	 */
	static public function cart_get_good()
	{
		$key=self::get_cart_session_key();
		return isset($_SESSION[$key])?$_SESSION[$key]:null;
	}
	
	/**
	 * 清空购物车商品
	 */
	static public function cart_clean()
	{
		$key=self::get_cart_session_key();
		$_SESSION[$key]=null;
	}
	
	/**
	 * 创建订单
	 * $order_amount 订单金额(元)
	 * $from_type 订单类型(1->粉丝卡充值,2->店铺,3->团购,4->限时购,5->秒杀，6->竞拍,7->商城)
	 * $pay_type 支付方式(1->汇付天下,2->支付宝,3->微信支付,4->财付通,5->易宝,7->货到付款)
	 * @return  string  订单号
	 */
	static public function create_order($order_amount,$from_type,$pay_type)
	{
		$data=self::cart_get_good();
		if(!$data)return false;
		
		$fans_info=self::get_fans_info();
		if(!$fans_info["openid"] || !$fans_info["userid"])
		{
			return false;
		}
		$msg='';
		$code=1;
		$order=new WxOrder();
		$order_data["order_no"]=get_order_nonumbers();
		$order_data["order_amount"]=$order_amount;
		$order_data["from_type"]=$from_type;
		$order_data["pay_type"]=$pay_type;
		$order_data["userid"]=$fans_info["userid"];
		$order_data["openid"]=$fans_info["openid"];
		$order_data["over_time"]="";
		$order_data["order_status"]=1;//有效订单
		$order_id=$order->insert_data($order_data);
		if($order_id)
		{
			$msg=$order_id;
			$list=new WxGoodsOrderMgt();
			foreach($data as $key=>$val)
			{ 
				$list_data["order_id"]=$order_id;
				$list_data["goods_id"]=$val["goods_id"];
				$list_data["attr_id"]=$val["attr_id"];
				$list_data["unit_price"]=$val["price"];
				$list_data["count_num"]=$val["count_num"];
				$list_data["status"]=1;
				$list->insert_data($list_data);
				unset($list_data);
			}
			self::cart_clean();
		}
		else
		{
			return false;
		}
		$order->close();
		return $order_id;
	}
	
	/**
	 * 拍商品
	 * $id -> 商品编号
	 * $money -> 价格
	 * @return array
	 */
	static public function set_pai($id,$money)
	{
		$id=intval($id);
		//$money=intval($money);
		if(!$id || !$money)
		{
			return	PubFun::to_msg(0,array(),'数据不合法');
		}
		$fans_info=self::get_fans_info();
		if(!$fans_info["openid"] || !$fans_info["userid"])
		{
			return PubFun::to_msg(0,array(),'缺少用户信息!');
		}
		$record=new WxGoodsJpRecords();
		$mc_key=CacheKey::get_pai_list($id);
		$data=mc_get($mc_key);
		if(!$data)
		{
			$data=$record->fetchAll("select openid,jp_price,cdate from ".$record->_name." where goodsid=$id order by id desc limit 3");
		}
		$high_price=0;
		if($data)
		{
			$high_price=$data[count($data)-1]["jp_price"];
			if($high_price>=$money)
			{
				 return PubFun::to_msg(0,array(),"目前最高价是 $high_price 元，请重新出价!");
			}
		}
		else 
		{
			$high_price=$money;
		}

		$insert_data["goodsid"]=$id;
		$insert_data["userid"]=$fans_info["userid"];
		$insert_data["openid"]=$fans_info["openid"];
		$insert_data["jp_price"]=$money;
		$record_id=$record->insert_data($insert_data);
		unset($insert_data);
		if($record_id)
		{
			$data[]=array("openid"=>$fans_info["openid"],"jp_price"=>$money,'cdate'=>time());
			//判断是否有代理出价
			$agent=new WxGoodsJpAgent();
			$agent_info=$agent->fetchAll("select userid,openid,agent_price from ".$agent->_name." where goodsid=$id and userid=".$fans_info["userid"]." and  agent_price>$money order by agent_price asc limit 1 ");
			if($agent_info)//有代理出价
			{
				$agent_info=$agent_info[0];
				$jp=new WxGoodsJpMgt();
				$jp_info=$jp->scalar("id,markups"," where goods_id=$id and userid=".$fans_info["userid"]);
				if($jp_info && $jp_info["markups"])//自动出价
				{
					$auto_price=$money+$jp_info["markups"];
					$insert_data["goodsid"]=$id;
					$insert_data["userid"]=$agent_info["userid"];
					$insert_data["openid"]=$agent_info["openid"];
					$insert_data["jp_price"]=$auto_price;
					$insert_data["is_agent"]=1;
					$r=$record->insert_data($insert_data);
					if($r)
					{
						$data[]=array("openid"=>$agent_info["openid"],"jp_price"=>$auto_price,'cdate'=>time());
					}
				}
			}
			$record->close();
			unset($record);
			unset($insert_data);
		}
		mc_set($mc_key,$data,36000);
		if($record_id)
		{
			return PubFun::to_msg(1,$record_id,'出价成功!');
		}
		else
		{
			return PubFun::to_msg(0,0,'出价失败!');
		}
	}
	
	/**
	 * 获取拍卖纪录
	 * $id->商品编号
	 * @return array
	 */
	static public function get_pai($id)
	{
		$id=intval($id);
		if(!$id)
		{
			return	PubFun::to_msg(0,array(),'数据不合法');
		}
		$fans_info=self::get_fans_info();
		if(!$fans_info["openid"] || !$fans_info["userid"])
		{
			return PubFun::to_msg(0,array(),'缺少用户信息!');
		}
		$record=new WxGoodsJpRecords();
		$mc_key=CacheKey::get_pai_list($id);
		$data=mc_get($mc_key);//拍卖缓存纪录
		$exist_select=false;

		if(!$data)
		{
			$exist_select=true;
			$data=$record->fetchAll("select openid,jp_price,cdate from ".$record->_name." where goodsid=$id order by id desc limit 3");
		}
		else
		{
			$data=array_reverse($data);//从小往大转
		}

		if(!$data || !is_array($data))
		{
			return PubFun::to_msg(1);
		}
		else
		{
				$ret=array();
				$u_exist=0;//前三条纪录是否有当前用户纪录
				foreach($data as $k=>$v)
				{
					if($k<3)
					{
						$ret[]=$v;
						if($v["openid"]==$fans_info["openid"])
						{
							$u_exist=1;
						}
					}
					else
					{
						break;
					}
				}
				if(!$u_exist)
				{
					$curr_u_data=$record->fetchAll("select openid,jp_price,cdate from ".$record->_name." where goodsid=$id and openid=".$fans_info["openid"]." order by id desc limit 1");
					if(!empty($curr_u_data)){
						$ret[]=$curr_u_data[0];
						
					}
					
				}
				$record->close();
				unset($record);
				return  PubFun::to_msg(1,$ret);
		}
	}
	
}  
