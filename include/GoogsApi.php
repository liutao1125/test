<?php

/*
 * @author zhangyong 商品API
 */

class GoogsApi {
    /*
     * @author zhangyong 获取店铺信息
     */

    public static function getdpinforAction($userid, $from_type) {
        $dpinfro = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($from_type) && !empty($from_type) && is_numeric($from_type)) {
            $model = new WxDpSet ();
			$res=$model->list_data($userid,$from_type);
			$dpinfro=$res['dpinfo'];
        }
        return $dpinfro;
    }

    /*
     * @author zhangyong 手机端商品列表
     */

    public static function goodslistAction($userid, $from_type, $where) {
        $rs = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($from_type) && !empty($from_type) && is_numeric($from_type)) {
            $model = new WxGoods ();
            $picobj = new PicData ();
            if (isset($where) && !empty($where)) {
                $where = " AND " . $where;
            }else{
				$where="";
			}
            // 店铺
            if ($from_type == 1) {
				  $query = "SELECT id FROM wx_goods AS a WHERE a.userid='" . $userid . "' AND a.types='" . $from_type . "' AND a.status=1  AND a.finish_status=3 $where ORDER BY a.udate DESC ";
            }else{
            	  $query = "SELECT id FROM wx_goods AS a WHERE a.userid='" . $userid . "' AND a.types='" . $from_type . "' AND a.status=1  AND a.finish_status=3";
            }
            $rs = $model->fetchAll($query);
			foreach($rs as $k=>$v)
			{
				 $rs[$k]=$model->get_row_byid($v['id'],$userid);
			}
			
			foreach($rs as $k2=>$v2)
			{
				 $zkc_key = $userid . "_" . $v2['id'] . "_goods_total_inventory"; // 总库存缓存键值
				 if (mc_get($zkc_key)) {
                		$rs[$k2]['stock'] = mc_get($zkc_key );
				 }
			}
        }
        return $rs;
    }

    /*
     * @author zhangyong 手机端商品详情
     */

    public static function goodsdetailsAction($id, $userid, $from_type) {
        $list = array();
        $goods_details = array();
        // 商品详情
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($from_type) && !empty($from_type) && is_numeric($from_type))
        {
				$model = new WxGoods ();
				$goods_details=$model->get_row_byid($id,$userid);
				$zkc_key = $userid . "_" . $id . "_goods_total_inventory"; // 总库存缓存键值
				if (mc_get($zkc_key)) {
						$goods_details['stock'] = mc_get($zkc_key );
				 }
				 $goods_details['share_intro']=str_replace("\r\n", "", $goods_details['introduction']);
      	}
      	return $goods_details;
    }
    /*
     * @author zhangyong 手机端商品已售数目 商品已售数目（已支付+初始购买人数）
     */

    public static function goodsnumbersoldAction($id, $userid, $from_type) {
        $nums = 0;
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($from_type) && !empty($from_type) && is_numeric($from_type)) {
            $model = new WxOrder ();
            $str='_'.$id.'_'.$userid.'_'.$from_type;
            $key=CacheKey::good_snumber_sold_key($str);
            if(mc_get($key))
            {
            	$nums=mc_get($key);
            }else{
	            $query = "SELECT sum(a.count_num) as nums FROM wx_goods_order_mgt  AS a 
							 INNER JOIN wx_order AS b ON b.id=a.order_id
							 WHERE b.userid='" . $userid . "' AND b.pay_status=1 
							 AND b.from_type='" . $from_type . "' AND a.goods_id='" . $id . "' ";
	            $list = $model->fetchAll($query);
	            if ($list [0] ['nums']) {
	                $nums = $list [0] ['nums'];
	                mc_set($key,$nums,1800);
	            }
            }
        }
        return $nums;
    }
    
    //有属性的商品已售数量
    public static function attrgoodsnumbersoldAction($id, $userid, $from_type,$attr_id) {
        $nums = 0;
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($from_type) && !empty($from_type) && is_numeric($from_type)) {
            $model = new WxOrder ();
            $query = "SELECT sum(a.count_num) as nums FROM wx_goods_order_mgt  AS a 
						 INNER JOIN wx_order AS b ON b.id=a.order_id
						 WHERE b.userid='" . $userid . "' AND b.pay_status=1 
						 AND b.from_type='" . $from_type . "' AND a.goods_id='" . $id . "' and attr_id=".$attr_id;
            $list = $model->fetchAll($query);
            if ($list [0] ['nums']) {
                $nums = $list [0] ['nums'];
            }
        }
        return $nums;
    }

    /*
     * @author zhangyong 手机端商品商家信息
     */

    public static function getgoodsbusinessinforAction($id, $userid) {
        $list = array();
		$address=array();
        $address2 = array();
		$address3 = array();
		$address4 = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid)) {
			$str='_'.$id.'_'.$userid;
			$key=CacheKey::get_goods_business_infor_key($str);
			if(mc_get($key))
			{
				$address=mc_get($key);
			}else{
				
				  $model = new WxGoodsDpMerchant ();
				  $query = "SELECT * FROM wx_goods_dp_merchant 
								WHERE goods_id='" . $id . "' AND userid='" . $userid . "' ";
				  $list = $model->fetchAll($query);
				  $address = $list [0] ['address'];
				  mc_set($key,$address,3600);
			}
			$address2 = explode(";", $address);
			if (!empty($address)) {
				foreach ($address2 as $k => $v) {
					$address3 = explode("$", $v);
					$address4 [$k] ['provinces'] = $address3 [0];
					$address4 [$k] ['city'] = $address3 [1];
					$address4 [$k] ['name'] = $address3 [2];
					$address4 [$k] ['phone'] = $address3 [3];
					$address4 [$k] ['address'] = $address3 [4];
				}
			}
        }
        return $address4;
    }

    /*     *
     * @author zhangyong
     *  手机端获取商品属性
     */

    public static function getgoodsattrAction($id, $userid) {
        $new_atrr = array();
        $atrr_name = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid)) {
            $model = new WxGoodsAttrDetail ();
            $query2 = "SELECT a.attribute_ids,a.number FROM wx_goods_attr_detail AS a
							 WHERE a.userid='" . $userid . "' AND a.goods_id='" . $id . "' AND a.status=1 ";
            $list2 = $model->fetchAll($query2);

            foreach ($list2 as $k => $v) {
                if ($v ['number'] > 0) {
                    $v1 = explode(';', $v ['attribute_ids']);
                    foreach ($v1 as $k2 => $v2) {
                        $v3 = explode(':', $v2);
                        $new_atrr [$v3 [0]] [] = $v3 [1];
                    }
                }
            }

            if (count($new_atrr) > 0) {
                foreach ($new_atrr as $k => $v) {
                    $query3 = "SELECT cname as pname FROM wx_goods_attribute AS a
									   WHERE a.userid='" . $userid . "' AND id='" . $k . "' ";
                    $list4 = $model->fetchAll($query3);
                    $atrr_name [$k] ['pname'] = $list4 [0] ['pname'];
                    foreach ($v as $k1) {
                        $query4 = "SELECT cname as name FROM wx_goods_attribute AS a
									   WHERE a.userid='" . $userid . "' AND id='" . $k1 . "' ";
                        $list5 = $model->fetchAll($query4);
                        $atrr_name [$k] ['cname'] [$k1] ['name'] = $list5 [0] ['name'];
                    }
                }
            }
        }
        return $atrr_name;
    }

    /*
     * @author zhangyong 手机端获取商品属性库存
     */

    public static function propertystocksAction($userid, $goods_id, $attribute_ids, $total_nums, $sel_nums) {
        $total = array();
        if (isset($goods_id) && !empty($goods_id) && is_numeric($goods_id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($attribute_ids) && !empty($attribute_ids) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($total_nums) && !empty($total_nums) && is_numeric($total_nums) && isset($sel_nums) && !empty($sel_nums) && is_numeric($sel_nums)) {
            $model = new WxGoodsAttrDetail ();
            $key = "propertystocks_" . $userid . "_" . $goods_id . "_" . $attribute_ids;
            if (mc_get($key)) {
                $total = mc_get($key);
            } else {
                $query = "SELECT SUM(number) AS total,SUM(price) AS price,original_price FROM wx_goods_attr_detail  AS a 
										WHERE a.goods_id = '" . $goods_id . "' AND a.userid='" . $userid . "'
										AND a.attribute_ids LIKE '%" . $attribute_ids . "%' AND a.status=1  ";
                $list = $model->fetchAll($query);
                if ($total_nums == $sel_nums) {
                    $total['nums'] = $list [0] ['total'];
                    $total['price'] = $list [0] ['price'];
                    $total['original_price'] = $list[0]['original_price'];
                } else {
                    $total['nums'] = $list [0] ['total'];
                    $total['price'] = "-1";
                    $total['original_price'] = "-1";
                }
                mc_set($key, $total, 7200);
            }
        }
        return $total;
    }

    /*
     * @author zhangyong 根据主键id获取商品属性信息
     */

    public static function getgoodsattributesAction($id, $userid, $goods_id) {
        $atrr_name = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($goods_id) && !empty($goods_id) && is_numeric($goods_id)) {
            $model = new WxGoodsAttrDetail ();
            $query = "SELECT attribute_ids FROM wx_goods_attr_detail 
						   WHERE goods_id = '" . $goods_id . "'  AND userid='" . $userid . "'  
						   AND id='" . $id . "'";
            $list2 = $model->fetchAll($query);
            foreach ($list2 as $k => $v) {
                $v1 = explode(';', $v ['attribute_ids']);
                foreach ($v1 as $k2 => $v2) {
                    $v3 = explode(':', $v2);
                    $new_atrr [$v3 [0]] [] = $v3 [1];
                }
            }
            if (count($new_atrr) > 0) {
                foreach ($new_atrr as $k => $v) {
                    $query3 = "SELECT cname as pname FROM wx_goods_attribute AS a
									   WHERE a.userid='" . $userid . "' AND id='" . $k . "' ";
                    $list4 = $model->fetchAll($query3);
                    $atrr_name [$k] ['pname'] = $list4 [0] ['pname'];
                    foreach ($v as $k1) {
                        $query4 = "SELECT cname as name FROM wx_goods_attribute AS a
									   WHERE a.userid='" . $userid . "' AND id='" . $k1 . "' ";
                        $list5 = $model->fetchAll($query4);
                        $atrr_name [$k] ['cname'] = $list5 [0] ['name'];
                    }
                }
            }
        }
        return $atrr_name;
    }

    /*
     * @author zhangyong 手机端获取商品属性单价
     */

    public static function getgoodsattrunitpriceAction($id, $userid, $attribute_ids) {
        $list = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($attribute_ids) && !empty($attribute_ids)) {
            $model = new WxGoodsAttrDetail ();
            $query = "SELECT a.id,a.number,a.price FROM wx_goods_attr_detail  AS a 
								  WHERE a.goods_id = '" . $id . "' AND a.attribute_ids='" . $attribute_ids . "' 
								  AND a.userid='" . $userid . "' ";
            $list = $model->fetchAll($query);
        }
        return $list;
    }
    
    /**
     * 根据属性id获取商品属性单价
     * @param type $id
     * @param type $userid
     * @param type $attribute_id
     * @return type
     */
     public static function getgoodsattrunitbyidpriceAction($id, $userid, $attribute_id) {
        $list = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($attribute_id) && !empty($attribute_id)) {
            $model = new WxGoodsAttrDetail ();
            $query = "SELECT a.id,a.number,a.price FROM wx_goods_attr_detail  AS a 
								  WHERE a.goods_id = '" . $id . "' AND a.id='" . $attribute_id . "' 
								  AND a.userid='" . $userid . "' ";
            $list = $model->fetchAll($query);
        }
        return $list;
    }

    /*
     * @author zhangyong 手机端属性选中
     */

    public static function getselectpropertiesAction($goods_id, $userid, $attribute_ids) {
        $list = array();
        if (isset($goods_id) && !empty($goods_id) && is_numeric($goods_id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($attribute_ids) && !empty($attribute_ids)) {
            $key = "getselectproperties_" . $goods_id . "_" . $userid . "_" . $attribute_ids;
            if (mc_get($key)) {
                $list = mc_get($key);
            } else {
                $model = new WxGoodsAttrDetail ();
                $query = "SELECT a.attribute_ids FROM wx_goods_attr_detail  AS a 
											  WHERE a.goods_id = '" . $goods_id . "' AND a.userid='" . $userid . "'
											  AND a.attribute_ids LIKE '%" . $attribute_ids . "%'  AND a.number=0";
                $list = $model->fetchAll($query);
                foreach ($list as $k => $v) {
                    $pos = strpos($v['attribute_ids'], $attribute_ids);
                    if ($attribute_ids == substr($v['attribute_ids'], $pos, strlen($v['attribute_ids']))) {
                        $list[$k]['attribute_ids'] = str_replace(":", "", str_replace($attribute_ids, "", $v['attribute_ids']));
                    } else {
                        $list[$k]['attribute_ids'] = str_replace(":", "", str_replace($attribute_ids . ";", "", $v['attribute_ids']));
                    }
                }
                mc_set($key, $list, 30); // 设置缓存
            }
        }
        return $list;
    }

    /*
     * @author zhangyong 手机端获取送货信息
     */

    public static function getgoodsdeliveryinforAction($userid, $openid) {
        $list = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid)) {
            $model = new WxOrderLogistics ();
            $query = "SELECT a.recipient,a.phone,a.address FROM 
								wx_order_logistics AS a 
								WHERE a.userid='" . $userid . "' ";
            $list = $model->fetchAll($query);
        }
        return $list;
    }

    /*
     * @author zhangyong 手机端获取支付方式
     */

    public static function getpaymentAction($userid,$openid) {
        $list = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid)) {
            $WxBuyinfo = new WxBuyinfo();
            $paytypes = $WxBuyinfo->get_info($userid,$openid);
        }
        return $paytypes;
    }

    /*
     * @author zhangyong 获取商品总库存
     */

    public static function getgoodstotalinventoryAction($googsid, $userid, $valid_time) {
        if (isset($googsid) && !empty($googsid) && is_numeric($googsid) && isset($userid) && !empty($userid) && is_numeric($userid)) {
            $model = new WxGoods ();
            $query1 = "";
            $query2 = "";
            $key = $userid . "_" . $googsid . "_goods_total_inventory"; // 总库存缓存键值
            if (empty($valid_time)) {
                $valid_time = 0;
            }
            if (mc_get($key)) {
                $number2 = mc_get($key);
            } else {
                // 总库存
                $query2 = "SELECT stock FROM wx_goods 
							   WHERE id = '" . $googsid . "' AND userid='" . $userid . "' ";
                $rs2 = $model->fetchAll($query2);
                $number2 = $rs2 [0] ['stock'];
                if ($valid_time > 0) {
                    mc_set($key, $number2, $valid_time); // 设置缓存
                }
            }
        }
        return $number2;
    }

    /*
     * @author zhangyong 库存计算1 参数：id商品属性id,googsid商品id,number数量,action(1)增加(2)减少,userid商家,valid_time缓存时间
     */

    public static function goodsInventorycalculationAction($id, $googsid, $number, $action, $userid, $valid_time) {
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($googsid) && !empty($googsid) && is_numeric($googsid) && isset($number) && !empty($number) && is_numeric($number) & isset($action) && !empty($action) && is_numeric($action)) {
            $list1 = array();
            $list2 = array();
            $list3 = array();
            $model = new WxGoods ();
            $code = 1;
            $model2 = new WxGoodsAttrDetail ();
            $key1 = $userid . "_" . $googsid . "_" . $id . "_goods_atrr_detail"; // 明细库存缓存键值
            $key2 = $userid . "_" . $googsid . "_goods_total_inventory"; // 总库存缓存键值
			
		    //清除立即购买缓存
			$attribute_ids= $model2->scalar('attribute_ids',$where=" WHERE id='".$id."' ");
		    $key3 = "propertystocks_" . $userid . "_" . $googsid . "_" . $attribute_ids;
			mc_unset($key3);
			
			$key4= CacheKey::get_rep_goods_key($googsid);
			mc_unset($key4);
			//清除立即购买缓存
			
            if (empty($valid_time) || $valid_time <= 0) {
                $valid_time = 30;
            }
			
			//开启事务
			$model->execute("START TRANSACTION");		 
			
            if (mc_get($key1) && mc_get($key2) && $valid_time > 0) {
                $number1 = mc_get($key1);
                $number2 = mc_get($key2);
                if ($action == 1) {
                    // 明细库存
                    $total1 = $number1 + $number;
                    mc_set($key1, $total1, $valid_time); // 设置缓存
                    self::updategoodsdetailedinventoryAction($userid, $id, $total1);

                    // 总库存
                    $total2 = $number2 + $number;
                    mc_set($key2, $total2, $valid_time); // 设置缓存
                    self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                } elseif ($action == 2) {
                    if ($number1 - $number >= 0) {
                        // 明细库存
                        $total1 = $number1 - $number;
                        mc_set($key1, $total1, $valid_time); // 设置缓存
                        self::updategoodsdetailedinventoryAction($userid, $id, $total1);

                        // 总库存
                        $total2 = $number2 - $number;
                        mc_set($key2, $total2, $valid_time); // 设置缓存
                        self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                    } else {
                        mc_unset($key1);
                        mc_unset($key2);
                        echo $code = "0"; // 购买商品数量大于库存
                    }
                }
                // echo $total1;
                // echo '<br>';
                // echo $total2;
            } else {

                // 明细库存
                $query2 = "SELECT a.number FROM wx_goods_attr_detail  AS a
							   INNER JOIN wx_goods  AS b ON b.id=a.goods_id
							   WHERE a.id = '" . $id . "' AND a.userid='" . $userid . "'";
                $rs1 = $model->fetchAll($query2);
                $number1 = $rs1 [0] ['number'];

                // 总库存
                $query3 = "SELECT stock FROM wx_goods 
							       WHERE id = '" . $googsid . "' AND userid='" . $userid . "' ";
                $rs2 = $model->fetchAll($query3);
                $number2 = $rs2 [0] ['stock'];

                if ($action == 1) {
                    // 明细库存
                    $total1 = $number1 + $number;
                    if ($valid_time > 0) {
                        mc_set($key1, $total1, $valid_time); // 设置缓存
                    }
                    self::updategoodsdetailedinventoryAction($userid, $id, $total1);

                    // 总库存
                    $total2 = $number2 + $number;
                    if ($valid_time > 0) {
                        mc_set($key2, $total2, $valid_time); // 设置缓存
                    }
                    self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                } elseif ($action == 2) {
                    if ($number1 - $number >= 0) {
                        // 明细库存
                        $total1 = $number1 - $number;
                        if ($valid_time > 0) {
                            mc_set($key1, $total1, $valid_time); // 设置缓存
                        }
                        self::updategoodsdetailedinventoryAction($userid, $id, $total1);

                        // 总库存
                        $total2 = $number2 - $number;
                        if ($valid_time > 0) {
                            mc_set($key2, $total2, $valid_time); // 设置缓存
                        }
                        self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                    } else {
                        $code = "0"; // 购买商品数量大于库存
                    }
                }
                // echo $total1;
                // echo '<br>';
                // echo $total2;
            }
			
			//提交事务
			$model->execute("COMMIT");
        }
        return $code;
    }

    /*
     * @author zhangyong 库存计算2 参数：googsid商品id,number数量,action(1)增加(2)减少,userid商家,valid_time缓存时间
     */

    public static function goodsInventorycalculation2Action($googsid, $number, $action, $userid, $valid_time) {
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($googsid) && !empty($googsid) && is_numeric($googsid) && isset($number) && !empty($number) && is_numeric($number) & isset($action) && !empty($action) && is_numeric($action)) {
            $list1 = array();
            $list2 = array();
            $list3 = array();
            $model = new WxGoods ();
            $code = 1;
            if (empty($valid_time) || $valid_time <= 0) {
                $valid_time = 30;
            }
            $key2 = $userid . "_" . $googsid . "_goods_total_inventory"; // 总库存缓存键值
			
			//开启事务
			$model->execute("START TRANSACTION");		 
			
            if (mc_get($key2) && $valid_time > 0) {
                $number2 = mc_get($key2);
                if ($action == 1) {
                    // 总库存
                    $total2 = $number2 + $number;
                    mc_set($key2, $total2, $valid_time); // 设置缓存
                    self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                } elseif ($action == 2) {
                    if ($number2 - $number >= 0) {
                        // 总库存
                        $total2 = $number2 - $number;
                        mc_set($key2, $total2, $valid_time); // 设置缓存
                        self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                    } else {
                        mc_unset($key2);
                        echo $code = "0"; // 购买商品数量大于库存
                    }
                }
                //echo $total2;
            } else {

                // 总库存
                $query3 = "SELECT stock FROM wx_goods 
							       WHERE id = '" . $googsid . "' AND userid='" . $userid . "' ";
                $rs2 = $model->fetchAll($query3);
                $number2 = $rs2 [0] ['stock'];

                if ($action == 1) {
                    // 总库存
                    $total2 = $number2 + $number;
                    if ($valid_time > 0) {
                        mc_set($key2, $total2, $valid_time); // 设置缓存
                    }
                    self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                } elseif ($action == 2) {
                    if ($number2 - $number >= 0) {
                        // 总库存
                        $total2 = $number2 - $number;
                        if ($valid_time > 0) {
                            mc_set($key2, $total2, $valid_time); // 设置缓存
                        }
                        self::updategoodstotalinventoryAction($userid, $googsid, $total2);
                    } else {
                        $code = "0"; // 购买商品数量大于库存
                    }
                }
                // echo $total2;
            }
			
			//提交事务
			$model->execute("COMMIT");
			
        }
        return $code;
    }

    /*
     * @author zhangyong 更新总库存
     */

    public static function updategoodstotalinventoryAction($userid, $googsid, $number) {
        $model = new WxGoods ();
        $data ['stock'] = $number;
        //$where = " userid='" . $userid . "' AND id='" . $googsid . "' ";
        //$model->update_data ( $data, $where );
        $model->update_row_stock($data, $googsid);
    }

    /*
     * @author zhangyong 更新明细库存
     */

    public static function updategoodsdetailedinventoryAction($userid, $id, $number) {
        $model = new WxGoodsAttrDetail ();
        $data ['number'] = $number;
        $where = " userid='" . $userid . "' AND id='" . $id . "' ";
        $model->update_data($data, $where);
    }

    /*
     * @author zhangyong 获取商品某一属性库存
     */

    public static function getgoodssingleinventoryAction($id, $userid, $attribute_ids) {
        $list = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid)) {
            $model = new WxGoodsAttrDetail ();
            $query = "SELECT number FROM wx_goods_attr_detail 
						   WHERE goods_id = '" . $id . "'  AND userid='" . $userid . "'  
						   AND attribute_ids='" . $attribute_ids . "'";
            // echo $query;
            $list = $model->fetchAll($query);
        }
        return $list;
    }

    /*
     * @author zhangyong 获取商品某一属性库存
     */

    public static function getgoodssingleinventory2Action($id, $userid, $goods_id) {
        $list = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid)) {
            $model = new WxGoodsAttrDetail ();
            $query = "SELECT price FROM wx_goods_attr_detail 
						   WHERE goods_id = '" . $goods_id . "'  AND userid='" . $userid . "'  
						   AND id='" . $id . "'";
            // echo $query;
            $list2 = $model->fetchAll($query);
            $list = $list2[0];
        }
        return $list;
    }

    /*
     * @author zhangyong 商品库存计算 type 1减库存，2加库存
     */

    public static function goodsinventculationAction($id, $userid, $attribute_names, $nums,$type) {
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($attribute_names) && !empty($attribute_names) && isset($nums) && !empty($nums) && is_numeric($nums)) {
            $model1 = new WxGoods ();
            $model2 = new WxGoodsAttrDetail ();
            // 减库存
            if ($type == 1) {
                $query1 = "UPDATE wx_goods_attr_detail SET number=number-$nums
									WHERE goods_id = '" . $id . "' AND attribute_ids='" . $attribute_names . "' 
									AND userid='" . $userid . "' ";
                $query2 = "UPDATE wx_goods SET stock=stock-$nums
									WHERE id = '" . $id . "' AND a.userid='" . $userid . "' ";
            }
            // 加库存
            if ($type == 2) {
                $query1 = "UPDATE wx_goods_attr_detail SET number=number+$nums
								  WHERE goods_id = '" . $id . "' AND attribute_ids='" . $attribute_names . "' 
								  AND userid='" . $userid . "' ";
                $query2 = "UPDATE wx_goods SET stock=stock+$nums
								  WHERE id = '" . $id . "' AND userid='" . $userid . "' ";
            }
            $model1->execute($query1);
            $model2->execute($query2);
        }
    }

    /*
     * @author zhangyong 获取粉丝券
     */

    public static function fancouponsAction($userid, $openid,$exchange_flag) {
        $list = array();
        $list2 = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($openid) && !empty($openid)) {
            $model = new WxClubcardExchangeSubtabulation ();
            $time = time();
            $start_time = strtotime(date("Y-m-d"));
            $end_time   = strtotime(date("Y-m-d"));
            $query = "SELECT a.id,b.exchange_name,b.exchange_flag,b.exchange_discount,b.exchange_money  FROM wx_club_card_exchange_subtabulation AS a 
							INNER JOIN wx_club_card_exchange AS b ON b.id=a.exchange_id
							WHERE b.is_deleted=1 AND b.start_date<='" . $start_time . "' AND b.end_date>='" . $end_time . "' 
							AND a.userid='" . $userid . "'  AND a.openid='" . $openid . "' AND a.holdtime<'" . $time . "'  and type=1 AND b.exchange_flag IN ($exchange_flag) ";
            $list = $model->fetchAll($query);
            $list2 = array();
            $i = 0;
            $j = 0;
            foreach ($list as $k => $v) {
                if ($v ['exchange_flag'] == 2) {
                    $list2 ['discount'] [$i]['id'] = $list [$k] ['id'] = $v['id'];
                    $list2 ['discount'] [$i]['price_str'] = $list [$k] ['price'] = $v['exchange_discount'] . "折打折券";
                    $list2 ['discount'] [$i]['price'] = $list [$k] ['price'] = $v['exchange_discount'] / 10;
                    $list2 ['discount'] [$i]['type'] = 2;
                    $i++;
                }

                if ($v ['exchange_flag'] == 3) {
                    $list2 ['money'] [$j]['id'] = $list [$k] ['id'] = $v['id'];
                    $list2 ['money'] [$j]['price_str'] = $list [$k] ['price'] = $v['exchange_money'] . "元现金券";
                    $list2 ['money'] [$j]['price'] = $list [$k] ['price'] = $v['exchange_money'];
                    $list2 ['money'] [$j]['type'] = 3;
                    $j++;
                }
            }
        }
        return $list2;
    }

    /*
     * @author zhangyong 更新粉丝券状态 id更新粉丝券状态,validtime粉丝券使用有效期
     */

    public static function updatefancouponsAction($id, $userid, $openid, $validtime) {
        if (isset($id) && !empty($id) && is_numeric($id) && isset($userid) && !empty($userid) && is_numeric($userid) && isset($openid) && !empty($openid) && isset($validtime) && !empty($validtime)) {
            $model = new WxClubcardExchangeSubtabulation ();
            $where = " id=$id AND openid=$openid ";
            $data ['holdtime'] = $validtime;
            $model->update_data($data, $where);
        }
    }

    /*
     * @author zhangyong 商品订单列表
     */

    public static function goodsorderlistAction($userid, $openid, $from_type, $where) {
        $list = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($from_type) && !empty($from_type) && is_numeric($from_type)) {
            $model = new WxGoodsOrderMgt ();
            $query = "SELECT a.id,a.order_no,a.order_amount,a.pay_type,a.cdate,a.pay_status,a.order_status FROM wx_order AS a ";
            // 后台商家商品订单
            if (!isset($openid) && empty($openid)) {

                $query .= " WHERE a.userid= '" . $userid . "'  AND a.from_type='" . $from_type . "' ";
            } else {
                // 手机端用户商品订单
                $query .= " WHERE a.userid= '" . $userid . "'  AND a.openid = '" . $openid . "' AND a.from_type='" . $from_type . "' ";
            }

            if (!empty($where)) {
                $query .= $where;
            }
            $list = $model->fetchAll($query);
        }
        return $list;
    }

    /*
     * @author zhangyong 商品订单详情
     */

    public static function goodsorderdetailsAction($id, $is_virtua, $from_type, $where) {
        $list = array();
        $orderdetails = array();
        if (isset($id) && !empty($id) && is_numeric($id) && isset($from_type) && !empty($from_type) && is_numeric($from_type)) {
            $model = new WxGoodsOrderMgt ();

            if ($is_virtua == 1) {
                $field = "SUM(a.count_num) AS virtua_count_num,";
            } else {
                $field = " ";
            }
            $query1 = "SELECT a.order_no,a.order_status,a.pay_type,b.express_no,b.company,c.name,c.phone,c.address
							FROM wx_order AS a 
							LEFT JOIN wx_order_logistics AS b ON b.order_id = a.id
							LEFT JOIN wx_order_shipping_address AS c ON c.id = b.post_id
							WHERE a.id='" . $id . "' ";

            $query2 = "SELECT a.count_num," . $field . "a.unit_price,a.voucher_number,b.order_status,b.order_amount,c.name AS goodsname,c.pic_id
							,c.postage,g.exchange_name,g.exchange_flag,g.exchange_discount,g.exchange_money,
							h.sn,s.coupon_use_start_time,s.coupon_use_end_time
							FROM wx_goods_order_mgt AS a 
							LEFT JOIN wx_order  AS b ON b.id=a.order_id
							LEFT JOIN wx_goods AS c ON c.id = a.goods_id
							LEFT JOIN wx_order_logistics AS d ON d.order_id = b.id
							LEFT JOIN wx_order_shipping_address AS e ON e.id = d.post_id
							LEFT JOIN wx_goods_order_coupon AS f ON f.order_id=b.id
							LEFT JOIN wx_club_card_exchange AS g ON g.id= f.coupon_id
							LEFT JOIN wx_club_card_exchange_subtabulation AS h ON h.exchange_id=g.id ";

            //团购
            if ($from_type == "2") {
                $query2.=" LEFT JOIN wx_goods_tg_mgt AS s ON s.goods_id = c.id ";
            }
            //限时购
            if ($from_type == "3") {
                $query2.=" LEFT JOIN wx_goods_xs_mgt AS s ON s.goods_id = c.id ";
            }
            //秒杀
            if ($from_type == "4") {
                $query2.=" LEFT JOIN wx_goods_ms_mgt AS s ON s.goods_id = c.id ";
            }
            $query2.="WHERE b.id='" . $id . "'";

            if (!empty($where)) {
                $query1 .= $where;
                $query2 .= $where;
            }
            $list1 = $model->fetchAll($query1);
            $list2 = $model->fetchAll($query2);
            $orderdetails['order_infor'] = $list1;
            $orderdetails['goods_infor'] = $list2;
        }
        return $orderdetails;
    }

    /*
     * @author zhangyong 商品购买限制
     */

    public static function goodspurchaselimitAction($goodsid, $userid, $openid, $from_type, $start_time, $end_time) {
        $sum = 0;
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($goodsid) && !empty($goodsid) && is_numeric($goodsid) && isset($from_type) && !empty($from_type) && is_numeric($from_type) && isset($start_time) && !empty($start_time) && isset($end_time) && !empty($end_time) && isset($openid) && !empty($openid)) {
            $model = new WxGoodsOrderMgt ();
            $query = "SELECT SUM(goods_id) AS sum FROM wx_goods_order_mgt AS a 
							INNER JOIN wx_order  AS b ON b.id=a.order_id
							INNER JOIN wx_goods AS c ON c.id = a.goods_id
							WHERE a.goods_id='" . $goodsid . "' AND b.userid='" . $userid . "' 
							AND b.openid='" . $openid . "' AND from_type='" . $from_type . "' 
							AND b.cdate>'" . $start_time . "' AND b.cdate<'" . $end_time . "' ";
        }
        return $sum;
    }

    // 获取GMTime
    public static function get_gmtime() {
        date_default_timezone_set(PRC);
        return (time() - date('Z'));
    }

    /**
     * add by wuwudong
     * 订单收货地址列表以及添加修改页面
     * @param 商家 $userid        	
     * @param 用户 $openid        	
     * @param提交 类型 $type  add 添加   fix 修改   默认是列表
     * 如果是fix id 必须要传递
     * @param 提交的数据 $data
     */
    public static function ordershippingaddressAction($userid, $openid, $type = '', $id) {
        $shipping_address = new WxOrderShippingAddress ();
        if ($type) {
            $hash ['type'] = $type;
            $area = new WxAreas ();
            $hash ["area_data"] = $area->fetchAll("select * from wx_areas where area_type=1");
            switch ($type) {
                case "add" :
                    return $hash;
                    break;
                case "fix" :
                    if (!isset($id) && !is_numeric($id)) {
                        return;
                    }
                    $hash ['shipping_addressid'] = $id;
                    $goodsship = $shipping_address->scalar("*", "where userid=$userid and  openid=$openid and id=$id and status=1");
                    $hash ['data'] = $goodsship;
                    return $hash;
                    break;
            }
        } else {

            $ordernotes = mc_get("qianggouordernotes");
            $goodsdelivery = $shipping_address->fetchAll("select * from wx_order_shipping_address where userid=$userid and openid=$openid and status=1");
            $hash ['data'] ['deliverylist'] = $goodsdelivery;
            if ($ordernotes) {
                /* 缓存返回到订单确认用 */
                $newodernotes = explode("-", $ordernotes);

                $hash ['data'] ['ids'] = $newodernotes ['0'];
                $hash ['data'] ['nums'] = $newodernotes ['1'];
                $hash ['data'] ['goodsid'] = $newodernotes ['2'];
            }
            return $hash;
        }
    }

    /**
     *
     * @param 商家 $userid        	
     * @param 用户 $openid        	
     * @param提交 类型 $type  add 是新增   fix 是更新    modifyde修改默认地址  deletshipping 是删除
     * @param 提交的数据 $data
     * 返回 json 字符串
     * add by wuwudong
     */
    public static function operateAction($userid, $openid, $type, $data) {
        if ($type && is_array($data)) {
            $shipping_address = new WxOrderShippingAddress ();
            if ($type == 'add') {
                $insertid = $shipping_address->insert_data($data);
                if ($insertid) {
                    $hash ['status'] = 1;
                } else {

                    $hash ['status'] = 0;
                }
            }
            if ($type == 'fix') {
                $id = $data ['shipping_addressid'];
                $updata = $shipping_address->update_data($data, "where id=$id and userid=$userid and openid=$openid");
                if ($updata) {
                    $hash ['status'] = 1;
                } else {

                    $hash ['status'] = 0;
                }
            }
            /* 修改默认地址 */
            if ($type == 'modifyde') {
                $modifydata = array(
                    "is_default" => 1
                );
                $up = $shipping_address->update_data($modifydata, "where openid=$this->openid and id='" . $data ['shippingid'] . "' and status=1");
                if ($up) {
                    $hash ['status'] = 1;
                } else {

                    $hash ['status'] = 0;
                }
            }
            if ($type == "deletshipping") {
                $goodsstatus = $shipping_address->scalar("status", "where openid=$openid userid=$userid and id=$id and status=1");
                if ($goodsstatus) {
                    $shipping_address->delete_data("where id=$id");
                    /* 删除成功 */
                    $rs ['status'] = 1;
                } else {
                    $rs ['status'] = 0;
                }
            }
            echo json_encode($hash);
        }
    }

    /**
     *
     *
     *
     *
     * @param   $ordernoid order表中的id 获取orderno 以及金额  汇款方式
     * add by  wuwudong
     */
    public static function selectorderidenterAction($ordernoid) {
        if (isset($ordernoid) && is_numeric($ordernoid)) {
            $order = new WxOrder;
            $sql = "select m.unit_price,o.pay_type,o.order_no from wx_order as o INNER JOIN wx_goods_order_mgt as m on m.order_id=o.id where o.id='".$ordernoid."'";

            $result = $order->fetchAll($sql);
            if ($result) {
                $result[0]['payname'] = $GLOBALS['pay_type'][$result[0]['pay_type']];
            } else {
                $result = '';
            }


            return $result;
        }
    }

    /**
     * 首页列表排序
     *
     * add by wuwudong
     */
    public static function shoparraysortAction($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array =$keynewarray= array();
        if(count($arr)>0)
        {
		        foreach ($arr as $k => $v) {
		            $keysvalue [$k] = $v [$keys];
		        }
		        if ($type == 'asc') {
		            asort($keysvalue);
		        } else {
		            arsort($keysvalue);
		        }
		        reset($keysvalue);
		        foreach ($keysvalue as $k => $v) {
		            $new_array [$k] = $arr [$k];
		        }
        }
        $keynewarray=array_values($new_array);
        return $keynewarray;
    }

    /**
     * @author zhangyong
     * 获取店铺商品分类
     */
    public static function getdpcategoryAction($userid) {
        $list = array();
        if (isset($userid) && !empty($userid) && is_numeric($userid)) {
            $model = new WxDpCategory();
            $query = "SELECT a.id,a.cname FROM wx_dp_category AS a
						   WHERE a.userid='" . $userid . "' AND a.status=1 ORDER BY a.sort DESC ";
            $list = $model->fetchAll($query);
        }
        return $list;
    }

    /**
     * @author zhangyong
     * 获取发货地址
     */
    public static function getshippingaddress($userid, $openid, $id) {
        $list = array();
        $res=array();
		
        if (isset($userid) && !empty($userid) && is_numeric($userid) && isset($openid) && !empty($openid)) {
			$key=CacheKey::get_logistics_list($openid.$userid);
			if(mc_get($key) && $openid!=-1 )
			{
				$res=mc_get($key);
			}else{
                $shipping_address = new WxOrderShippingAddress ();
				$res = $shipping_address->fetchAll ( "select id,name,phone,address,is_default,province,city,area,is_virtua from wx_order_shipping_address where userid='" . $_SESSION ['clubdata'] ['userid'] . "' and openid='" . $_SESSION ['clubdata'] ['openid'] . "' and status=1  and is_virtua=0 order by is_default desc" );
				mc_set ( $key, $res, 36000 );
          }
		  
		   if(count($res)>0)
							{
								  foreach($res as $k=>$v)
								  {
									  if(!empty($id))
									  {
										  if($v['id']==$id)
										  {
											  $list=$res[$k];
											  $list=$res[$k];
											  break;
										  }
										  
									  }elseif($v['is_default']==1){
											 $list=$res[$k];
											 break;
									  }
								  }
							}
          return $list;
        }
    }

    /**
     * @author zhangyong
     *  秒杀抵用券
     */
    public static function mymsvouchersAction($userid, $where) {
        $WxOrder = new WxOrder();
        $query = "SELECT a.id,a.order_no,a.order_status,b.unit_price,c.name AS goodsname,c.types
				 ,d.coupon_use_start_time,d.coupon_use_end_time,d.notes_use AS methoduse,b.voucher_number,
				 b.vouchers_state,d.notes_use,f.name,f.phone
				 FROM wx_order AS a
				 INNER JOIN wx_goods_order_mgt AS b ON a.id=b.order_id
				 INNER JOIN wx_goods AS c ON c.id = b.goods_id
				 INNER JOIN wx_goods_ms_mgt  AS d ON d.goods_id=c.id
				 LEFT JOIN wx_order_logistics AS e ON e.order_id = a.id
				 LEFT JOIN wx_order_shipping_address AS f ON f.id = e.post_id
				 WHERE a.userid='" . $userid . "' ";
        $query.=$where;
        $rs = $WxOrder->fetchAll($query);
        return $rs;
    }

    /**
     * @author zhangyong
     *  限时抵用券
     */
    public static function myxsvouchersAction($userid, $where) {
        $WxOrder = new WxOrder();
        $query = "SELECT a.id,a.order_no,a.order_status,b.unit_price,c.name AS goodsname,c.types
				 ,d.coupon_use_start_time,d.coupon_use_end_time,d.notes_use AS methoduse,b.voucher_number,
				 b.vouchers_state,d.notes_use,f.name,f.phone
				 FROM wx_order AS a
				 INNER JOIN wx_goods_order_mgt AS b ON a.id=b.order_id
				 INNER JOIN wx_goods AS c ON c.id = b.goods_id
				 INNER JOIN wx_goods_xs_mgt  AS d ON d.goods_id=c.id
				 LEFT JOIN wx_order_logistics AS e ON e.order_id = a.id
				 LEFT JOIN wx_order_shipping_address AS f ON f.id = e.post_id
				 WHERE a.userid='" . $userid . "' ";
        $query.=$where;
        $rs = $WxOrder->fetchAll($query);
        return $rs;
    }

    /**
     * @author zhangyong
     *  团购抵用券
     */
    public static function mytgvouchersAction($userid, $where) {
        $WxOrder = new WxOrder();
        $query = "SELECT a.id,a.order_no,a.order_status,b.unit_price,c.name AS goodsname,c.types
				 ,d.coupon_use_start_time,d.coupon_use_end_time,d.notes_use AS methoduse,b.voucher_number,
				 b.vouchers_state,d.notes_use,f.name,f.phone
				 FROM wx_order AS a
				 INNER JOIN wx_goods_order_mgt AS b ON a.id=b.order_id
				 INNER JOIN wx_goods AS c ON c.id = b.goods_id
				 INNER JOIN wx_goods_tg_mgt  AS d ON d.goods_id=c.id
				 LEFT JOIN wx_order_logistics AS e ON e.order_id = a.id
				 LEFT JOIN wx_order_shipping_address AS f ON f.id = e.post_id
				 WHERE a.userid='" . $userid . "' ";
        $query.=$where;
        $rs = $WxOrder->fetchAll($query);
        return $rs;
    }

    /**
     * @author zhangyong
     * 秒数换算成多少天/多少小时/多少分/多少秒
     */
    public static function timestringAction($second) {
        $date_str = '';
        $day = floor($second / (3600 * 24));
        $second = $second % (3600 * 24); //除去整天之后剩余的时间
        $hour = floor($second / 3600);
        $second = $second % 3600;  //除去整小时之后剩余的时间 
        $minute = floor($second / 60);
        $second = $second % 60;   //除去整分钟之后剩余的时间
        if ($day > 0) {
            $date_str.=$day . '天';
        }
        if ($hour > 0) {
            $date_str.=$hour . '小时';
        }
        if ($minute > 0) {
            $date_str.=$minute . '分';
        }
        if ($second > 0) {
            $date_str.=$second . '秒';
        }
        //返回字符串
        return $date_str;
    }
	
	//虚拟商品生成券号
    public static function createvirtualgoodsnumber($order_id,$from_type,$userid,$openid)
	{
		if(isset($order_id)&&!empty($order_id) && isset($from_type)&&!empty($from_type) )
		{
			$WxGoodsOrderMgt = new WxGoodsOrderMgt();
			$query="SELECT b.id,b.is_virtua,a.goods_id,a.count_num,
					a.unit_price,c.coupon_start_time,c.coupon_end_time,
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
									 		$qrcode=self::creategoodsvouchersqrcode($userid,$ticketinfo['ticket_code']);
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
	
	
	//保存千人大会门票
    public static function saveqrdhmp($userid,$uname,$phone,$price)
	{
		$Ticket=new Ticket();
		$TicketPhone=new TicketPhone();
		$id=$TicketPhone->scalar('id', $where = " WHERE phone=".$phone." ");
		if(!$id)
		{
			//开启事务
			$Ticket->execute("START TRANSACTION");
			$price=floor($price);
			//$unit_price=array('1088','688','388');
			//$price=$unit_price[rand(0,2)];
			/*
			if($price==0.01)
			{
				$price=388;
			}elseif($price==0.02){
				$price=688;
			}elseif($price==0.03){
				$price=1088;
			}
			*/
			$ticketinfo=$Ticket->scalar('*',$where=" WHERE ticket_price='".$price."' AND is_ceshi=0 AND is_use=0 ");
			if($ticketinfo['id'])
			{
				$qrcode=GoogsApi::creategoodsvouchersqrcode($userid,$ticketinfo['ticket_code']);
				if($qrcode>0)
				{
					
					$qrcode_data['uname']=$uname;
					$qrcode_data['phone']=$phone;
					$qrcode_data['ticket_id']=$ticketinfo['id'];
					$qrcode_data['ticket_type']=$price;
					$qrcode_data['ticket_code']=$ticketinfo['ticket_code'];
					$qrcode_data['imgid']=$qrcode;
					$qrcode_data['status']=1;
					$qrcode_data['from_type']=2;
					$TicketPhone->insert_data($qrcode_data);
					
					$ticket_data['is_use']=1;
					$Ticket->update_data ( $ticket_data, $where="where id='".$ticketinfo['id']."'");
				}
			}
			$Ticket->execute("COMMIT");
			//提交事务
		}
	}
	
	 /**
     * @author zhangyong
     * 获取商家信息
     */
    public static function getuserinforAction($userid) {
		$userinfor=array();
		if( isset($userid)&&!empty($userid)&&is_numeric($userid) )
		{
			$WxUser=new WxUser();
			$userinfor=$WxUser->get_row_byid($userid);
		}
		return $userinfor; 
	}
	
	/*
		@author zhangyong 
		分享购买订单信息
	*/
	public static function get_share_order_infor($order_no,$userid)
	{
		if( !isset($_COOKIE["share_user_cookie"]) )
		{
			$cookie_value=session_id().'_'.$userid;
			setcookie('share_user_cookie',$cookie_value,time()+48*60*60,"/");
			$_COOKIE["share_user_cookie"]=$cookie_value;
			$str='_'.$_COOKIE["share_user_cookie"].'_'.$userid;
			$key=CacheKey::get_shop_share_key($str);
		}else{
			$str='_'.$_COOKIE["share_user_cookie"].'_'.$userid;
			$key=CacheKey::get_shop_share_key($str);
		}		
		if(!mc_get($key))
		{
			$share_order[0]=$order_no;
			mc_set($key,$share_order,48*60*60);
		}else{
			$share_order=mc_get($key);
			array_unshift($share_order,$order_no);
			mc_set($key,$share_order,48*60*60);
		}
	}
	
	//粉丝卡金点支付
	public static function fanspointpaymentcard($userid,$openid,$money,$dedpoints,$paytype)
	{
		$msgArr=array();
		if(!empty($userid)&&!empty($openid)&&empty($money))
		{
			$msgArr=ResultConnector::cludstate($userid,$openid);
		}elseif(!empty($userid)&&!empty($openid)&&!empty($money)&&!empty($dedpoints)&&!empty($paytype))
		{
			$msgArr=ResultConnector::cludsubmoney($userid,$openid,$money,$dedpoints,$paytype);
		}
		return $msgArr;
	}
	
	//粉丝卡金点获赠提示
	public static function fancardreceiveprompt($userid,$openid)
	{
		$msgArr=array();
		if(isset($userid)&&!empty($userid)&&isset($openid)&&!empty($openid))
		{
			$msgArr=ResultConnector::cluduserreward($userid,$openid);
		}
		return $msgArr;
	}
	
	//粉丝卡金点获赠充值
	public static function fancardreceiverecharge($userid,$openid,$money)
	{
		$msgArr=array();
		if(isset($userid)&&!empty($userid)&&isset($openid)&&!empty($openid)&&isset($money)&&!empty($money))
		{
			
		}
	}
	
	//商品生成二维码
	public static function creategoodsqrcode($userid,$goodsid,$fromtype)
	{
		if(isset($goodsid)&&!empty($goodsid))
		{
			$WxGoods=new WxGoods();
			$where=" id=$goodsid and userid=$userid ";
			$qrcode = $WxGoods->scalar("qrcode"," where $where");
			if($qrcode<=0){
				$userid=base64_encode(PubFun::encrypt($userid,'E'));
				if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/goodsqrcode/")){
					mkdir($_SERVER["SINASRV_DATA_TMP"]."/goodsqrcode/");
				}
				//生成二维码
				require_once 'www/common/phpqrcode/phpqrcode.php';
				if($fromtype==1){				//店铺
					$url = 'http://'.$_SERVER['SERVER_NAME']."/mobilegoodsdp/productdetails?id=".$goodsid."&fxuserid=".$userid;
				}elseif($fromtype==2){   	//团购
					$url = 'http://'.$_SERVER['SERVER_NAME']."/mobilegoodstg/goodstgdetails?id=".$goodsid."&fxuserid=".$userid;
				}elseif($fromtype==3){  		//限时
					$url = 'http://'.$_SERVER['SERVER_NAME']."/mobilegoodsxs/xsgoodsinfos?goodsid=".$goodsid."&fxuserid=".$userid;
				}elseif($fromtype==4){ 		//秒杀
					$url = 'http://'.$_SERVER['SERVER_NAME']."/mobilegoodsms/msinfo?id=".$goodsid."&fxuserid=".$userid;
				}elseif($fromtype==5){ 		//竞拍
					$url = 'http://'.$_SERVER['SERVER_NAME']."/mobilegoodsjp/goodsinfos?goodsid=".$goodsid."&fxuserid=".$userid;
				}
				// 纠错级别：L、M、Q、H
				$errorCorrectionLevel = 'M';
				// 点的大小：1到10
				$matrixPointSize = 4;
				$root = $_SERVER["SINASRV_DATA_TMP"]."/goodsqrcode/goodsqrcode" . $goodsid . ".png";
				QRcode::png($url, $root, $errorCorrectionLevel, $matrixPointSize, 2);
				$PicData = new PicData();
				$ret = $PicData->post_file($root);
				return $ret['id'];
			}else{
				return 0;
			}
		}
	}
	
	//生成商品券二维码
	public static function creategoodsvouchersqrcode($userid,$ticket_code)
	{
		if(!empty($userid)&&!empty($ticket_code))
		{
			if(!is_dir($_SERVER["SINASRV_DATA_TMP"]."/goodsvouchersqrcode/")){
				mkdir($_SERVER["SINASRV_DATA_TMP"]."/goodsvouchersqrcode/");
			}
			require_once 'www/common/phpqrcode/phpqrcode.php';
			// 纠错级别：L、M、Q、H
			$errorCorrectionLevel = 'M';
			// 点的大小：1到10
			$matrixPointSize = 10;
			$root = $_SERVER["SINASRV_DATA_TMP"]."/goodsvouchersqrcode/" . $ticket_code . ".png";
			$url=$ticket_code;
			QRcode::png($url, $root, $errorCorrectionLevel, $matrixPointSize, 2);
			$PicData = new PicData();
			$ret = $PicData->post_file($root);
			return $ret['id'];
		}else{
			return 0;
		}
	}
	
	//代付地址
	public static function daifuaddress($userid,$orderid)
	{
		$address=array();
		if(!empty($userid)&&!empty($orderid))
		{
			//物流信息
			$WxOrderLogistics=new WxOrderLogistics();
			$WxOrderShippingAddress=new WxOrderShippingAddress();
			$post_id = $WxOrderLogistics->scalar ( "post_id", " where order_id='".$orderid."' " );
			$address=$WxOrderShippingAddress->scalar ( "*", " where id='".$post_id."' " );
			$address['phone']=substr_replace($address['phone'],'****',3,4);
		}
		return $address;
	}
	
	//代付进度
	public static function daifuprogress($userid,$orderid)
	{
		//代付订单信息
		$WxOrder = new WxOrder();
		$daifu_order_amount=0.00;
		$daifu_progress=array();
		$order_infor= $WxOrder->scalar ( "order_no,order_amount,pay_status,over_time", " where userid=$userid and id='".$orderid."' " );
		$query="select * from wx_order where userid=$userid and main_order_no='".$order_infor['order_no']."' and pay_status=1";
		$list = $WxOrder->fetchAll($query);
		$dpinfor=self::getdpinforAction($userid, 1);
		$daifu_progress['over_time']=self::timestringAction($order_infor['over_time']);
		foreach($list as $k=>$v)
		{
			$daifu_order_amount+=$v['order_amount'];
		}
		if($order_infor['pay_status']==1)
		{
			$daifu_progress['daifu_status']=2; 	 //完成
		}else{
			$daifu_progress['daifu_status']=1;	//未完成
		}
		//代付剩余时间
		if($order_infor['over_time']-time()>0)
		{
			$daifu_progress['daifu_time']=self::timestringAction($order_infor['over_time']-time());	
		}else{
			$daifu_progress['daifu_time']=0;
		}
		$daifu_progress['daifu_remaining']=sprintf("%.2f",$order_infor['order_amount']-$daifu_order_amount);	//还差
		
		//已完成
		if($daifu_progress['daifu_remaining']>0.00||$daifu_progress['daifu_remaining']>0)
		{
			$daifu_progress['daifu_progress']=sprintf("%.2f",($daifu_order_amount/$order_infor['order_amount'])*100);
		}elseif($daifu_progress['daifu_remaining']==0.00||$daifu_progress['daifu_remaining']==0){
			$daifu_progress['daifu_progress']=100;
		}
		return $daifu_progress;
	}
	
	//代付信息
	public static function daifuinfor($userid,$orderid)
	{
		$list=array();
		if(!empty($userid)&&!empty($orderid))
		{
			//代付订单信息
			$WxOrder = new WxOrder();
			$order_infor= $WxOrder->scalar ( "order_no,order_amount,pay_status", " where userid=$userid and id='".$orderid."' " );
			$daifu_order_amount=0;
			$query="select * from wx_order where userid=$userid and main_order_no='".$order_infor['order_no']."' and pay_status=1";
			$list = $WxOrder->fetchAll($query);
			foreach($list as $k=>$v)
			{
				$daifuaddress=self::daifuaddress($userid,$v['id']);
				$list[$k]['daifu_name']=$daifuaddress['name'];
				$list[$k]['daifu_desc']=$daifuaddress['desc'];
			}
		}
		return $list;
	}
	
	//代付订单信息
	public static function daifuorder($userid,$orderid)
	{
		$orderinfo=array();
		$WxOrder = new WxOrder();
		$WxGoodsOrderMgt= new WxGoodsOrderMgt();
		$PicData=new PicData();
		$query="select g.*,d.pic_id,d.name from wx_goods_order_mgt g,wx_goods d where g.goods_id=d.id and order_id=$orderid";
		$list = $WxGoodsOrderMgt->fetchAll($query);
		foreach ($list as $k=>$v)
		{
                        $data['pic_ids'] = explode(',', $list[$k]['pic_id']);
                        foreach ($data['pic_ids'] as $key=>$value) {
                            $pic_array = $PicData->get_row_byid($value);
                            $list[$k]['org'][$key] = $pic_array['org'];
                        }
//			$pic_array=$PicData->get_row_byid($list[$k]['pic_id']);
//			$list[$k]['org']=$pic_array['org'];
			$list[$k]["attr_names"]=GoogsApi::getgoodsattributesAction($v["attr_id"], $userid, $v["goods_id"]) ;
		}
                $orderinfo["order_goods_amount"]=$order_goods_amount= $WxOrder->scalar ( "*", " where userid=$userid and id=$orderid " );
                $orderinfo['order_goods']=$list;
		return $orderinfo;
	}
	
	//生成代付订单
	public static function createdaifuorder($username,$desc,$order_amount,$userid,$openid,$orderid)
	{
                $msg=-2;
		$WxOrder = new WxOrder();
		$current_time=time();
		$userid=$userid;
		$orderid=$orderid;
		$openid=$openid;
		$order_amount=$order_amount;
		$dpinfor=self::getdpinforAction($userid, 1);
		$daifu_order_amount=0.00;
		//开启事务
		$WxOrder->execute("START TRANSACTION");
		$order_infor= $WxOrder->scalar ( "id,order_no,order_amount,pay_status,over_time", " where userid=$userid and id='".$orderid."' and order_status=1" );
		if($order_infor['pay_status']==1)
		{
			$msg=-1;  //订单已完成
		}else{
			
			$WxGoodsOrderMgt = new WxGoodsOrderMgt();
			$qrdhmp_goodinfo= $WxGoodsOrderMgt->scalar("goods_id,unit_price"," WHERE order_id=".$orderid."");
			//if($qrdhmp_goodinfo['goods_id']=='623' or $qrdhmp_goodinfo['goods_id']=='624' or $qrdhmp_goodinfo['goods_id']=='625')
			if($qrdhmp_goodinfo['goods_id']=='40389' or $qrdhmp_goodinfo['goods_id']=='40391' or $qrdhmp_goodinfo['goods_id']=='40786')
			{
				$WxOrderLogistics  = new WxOrderLogistics();
				$WxOrderShippingAddress  = new WxOrderShippingAddress ();
				$post_id = $WxOrderLogistics->scalar("post_id"," WHERE order_id=".$orderid." ");
				$shippingaddress= $WxOrderShippingAddress->scalar("name,phone"," WHERE id=".$post_id." ");
				$qrdh_phone=$shippingaddress['phone'];
				$TicketPhone=new TicketPhone();
				$phoneid=$TicketPhone->scalar('id', $where = " WHERE phone=".$qrdh_phone."");
				if($phoneid){
					$msg=-5;
					return $msg;
					exit;
				}
				
				$price=$qrdhmp_goodinfo['unit_price'];
				$price=floor($price);
				/*
				if($price==0.01)
				{
					$price=388;
				}elseif($price==0.02){
					$price=688;
				}elseif($price==0.03){
					$price=1088;
				}
				*/
				$Ticket=new Ticket();
				//开启事务
				$Ticket->execute("START TRANSACTION");
				$id=$Ticket->scalar('id',$where=" WHERE ticket_price='".$price."' AND is_ceshi=0 AND is_use=0 ");
				$Ticket->execute("COMMIT");
				//提交事务
				if(empty($id))
				{
					$msg=-4;
					return $msg;
					exit;
				}
			}
			
			$query="select * from wx_order where userid=$userid and main_order_no='".$order_infor['order_no']."' and pay_status=1";
			$list = $WxOrder->fetchAll($query);
			if(count($list)>0 && is_array($list))
			{
					foreach($list as $k=>$v)
					{
						$daifu_order_amount+=$v['order_amount'];
					}
					$daifu_order_amount+=$order_amount;
                        }else{
                            $daifu_order_amount = $order_amount;
                        }
                        
					if($dpinfor['payment']==1  && $daifu_order_amount<=$order_infor['order_amount'] && $current_time<$order_infor['over_time']*24*3600)
					{
						$data['order_no']=FansCard::create_order_num(); //订单号
						$data['main_order_no']=$order_infor['order_no'];
                                                $data['userid']=$userid;
						$data['openid']=$openid;		
						$data['from_type']=12;
						$data['order_amount']=$order_amount;	    												 //实付金额
						$data['total_amount']=$order_amount;	    												 //应付金额
						$payment=GoogsApi::getpaymentAction($userid,$openid);
						foreach($payment as $key=>$value)
						{
							if($value['type']==3 or $value['type']==10)
							{
								$pay_type=$value['type'];
								break;
							}
						}
						$data['pay_type']=$pay_type;					   													 //支付方式
						$data['over_time']= $current_time+$dpinfor['payment_time']*24*3600;	 //订单过期时间
						$data['order_status']=1;																					 //订单状态
						if($daifu_order_amount==$order_infor['order_amount'])
						{
//							$data['pay_status']=1;																				//支付状态
//							foreach($list as $k=>$v)
//							{
//								$where2="userid=$userid and id='".$v['id']."'";
//								$data2['pay_status']=1;
//								$WxOrder->update_data($data2, $where2);
//							}
                                                     //  $where2="userid=$userid and id='".$order_infor['id']."'";
						     //  $data2['pay_status']=0;
						       //$WxOrder->update_data($data2, $where2);
						}else{
							$data['pay_status']=0;																				
						}
						$data['cdate']=$current_time;						   												 //创建时间
                                                $back_id=$WxOrder->insert_data($data);
						if($back_id)
						{
							$daifu_name=$username;
							$daifu_desc=$desc;
							if(!empty($daifu_name)&&!empty($daifu_desc))
							{
								$WxOrderShippingAddress=new WxOrderShippingAddress();
								$WxOrderLogistics =new WxOrderLogistics();
								$data2['name']=$daifu_name;
								$data2['desc']=$daifu_desc;
                                                                $data2['userid']=$userid;
                                                                $data2['openid']=$openid;
                                                                $data2['is_virtua']=1;
								$back_id2 = $WxOrderShippingAddress->insert_data ($data2);
								if($back_id2)
								{
									$data3['order_id']=$back_id;	
									$data3['post_id']=$back_id2;
									$data3['cdate']=$current_time;
									$back_id3=$WxOrderLogistics->insert_data($data3);
								}
							}
							$msg=$data['order_no'];			//订单提交成功
						}else{
							$msg=-2;						//订单提交失败
						}
				  }else{
                                      $msg = -3;
                                  }
		 }
		 //提交事务
		 $WxOrder->execute("COMMIT");
                 return $msg;
	}
	
	//代付授权
	public static function daifuoauth2_snsapi_base($userid,$url)
	{
		$WxUser=new WxUser();
		$wxuseraccount=$WxUser->get_user_account($userid,1);
		if($wxuseraccount['appid'])
		{
			$appid=$wxuseraccount['appid'];
			$is_auth=$wxuseraccount['is_auth'];
			if($is_auth)
			{
				if(SYS_RELEASE=='1')//测试环境
				{
					$component_appid=$GLOBALS['Component_Config']['test']['component_appid'];
				}elseif(SYS_RELEASE=='2'){
					$component_appid=$GLOBALS['Component_Config']['sc']['component_appid'];
				}
				//$component_access_token = mc_get("component_access_token_".$component_appid);
				$oauth_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=dodoca&component_appid=".$component_appid."#wechat_redirect";
			}else{
				$oauth_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=dodoca&connect_redirect=1#wechat_redirect";
			}	
		}
		return $oauth_url;
	}
	
	//获取授权用户信息
	public static function authuserinfo_snsapi_base($userid,$code,$type)
	{
		$WxUser=new WxUser();
		$wxuseraccount=$WxUser->get_user_account($userid,$type);
		if(!empty($wxuseraccount['appid']))
		{
            $info=array();
			$appid=$wxuseraccount['appid'];
			$secret=$wxuseraccount['appsecret'];
			$is_auth=$wxuseraccount['is_auth'];
			if($is_auth)
			{
				if(SYS_RELEASE=='1')//测试环境
				{
					$component_appid=$GLOBALS['Component_Config']['test']['component_appid'];
				}elseif(SYS_RELEASE=='2'){
					$component_appid=$GLOBALS['Component_Config']['sc']['component_appid'];
				}
				$component_access_token = mc_get("component_access_token_".$component_appid);
				$url="https://api.weixin.qq.com/sns/oauth2/component/access_token";
				$data = array ();
				$data ['appid'] = $appid;
				$data ['secret'] = $secret;
				$data ['code'] = $code;
				$data ['grant_type'] = "authorization_code";
				$data ['component_appid'] = $component_appid;
				$data ['component_access_token'] = $component_access_token;
			}else{
				$url="https://api.weixin.qq.com/sns/oauth2/access_token";
                $data=array();
                $data['appid']=$appid;
                $data['secret']=$secret;
                $data['code']=$code;
                $data['grant_type']="authorization_code";
			}
			$info=WeiXin::sub_curl($url,$data,$is_post=0);
			$info=json_decode($info,true);
			if($info["errcode"]=='40029')
			{
				
			}else{
                                                $refresh_token=$info['refresh_token'];
                                                if($is_auth)
                                                {
                                                	$url="https://api.weixin.qq.com/sns/oauth2/component/refresh_token";
                                                	$data=array();
                                                	$data['appid']=$appid;
                                                	$data['grant_type']="refresh_token";
                                                	$data['refresh_token']=$refresh_token;
                                                	$data ['component_appid'] = $component_appid;
                                                	$data ['component_access_token'] = $component_access_token;
                                                }else{
	                                                $url="https://api.weixin.qq.com/sns/oauth2/refresh_token";
	                                                $data=array();
	                                                $data['appid']=$appid;
	                                                $data['grant_type']="refresh_token";
	                                                $data['refresh_token']=$refresh_token;
                                                }
                                                $info=array();
                                                $info=WeiXin::sub_curl($url,$data,$is_post=0);
                                                $info=json_decode($info,true);
				if($info["errcode"]=='40029')
				{
				
				}else{
					$WxUserFans=new WxUserFans();
					$fansid= $WxUserFans->scalar ( "id", " where userid=$userid and weixin_user='".$info['openid']."' " );
					if($fansid)
					{
						$data['status']=1;
						$where="userid=$userid and id=$fansid ";
						$WxUserFans->update_data($data,$where);
					}else{
						$data['status']=1;
						$data['userid']=$userid;
						$data['weixin_user']=$info['openid'];
						$data['cdate']=time();
						$data['sub_date']=time();
						$fansid=$WxUserFans->insert_data($data);
					}
					$info['openid']=$fansid;
				}
			}
		}
		return $info;
	}
	
	//代付授权
	public static function daifuoauth2($userid,$url)
	{
		$WxUser=new WxUser();
		$wxuseraccount=$WxUser->get_user_account($userid,1);
		if($wxuseraccount['appid'])
		{
			$appid=$wxuseraccount['appid'];
			$is_auth=$wxuseraccount['is_auth'];
			if($is_auth)
			{
				if(SYS_RELEASE=='1')//测试环境
				{
					$component_appid=$GLOBALS['Component_Config']['test']['component_appid'];
				}elseif(SYS_RELEASE=='2'){
					$component_appid=$GLOBALS['Component_Config']['sc']['component_appid'];
				}
				//$component_access_token = mc_get("component_access_token_".$component_appid);
				$oauth_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=dodoca&component_appid=".$component_appid."#wechat_redirect";
			}else{
				$oauth_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=dodoca&connect_redirect=1#wechat_redirect";
			}	
		}
		return $oauth_url;
	}
	
	//获取授权用户信息
	public static function authuserinfo($userid,$code,$type)
	{
		$WxUser=new WxUser();
		$wxuseraccount=$WxUser->get_user_account($userid,$type);
		if(!empty($wxuseraccount['appid']))
		{
            $info=array();
			$appid=$wxuseraccount['appid'];
			$secret=$wxuseraccount['appsecret'];
			$is_auth=$wxuseraccount['is_auth'];
			if($is_auth)
			{
				if(SYS_RELEASE=='1')//测试环境
				{
					$component_appid=$GLOBALS['Component_Config']['test']['component_appid'];
				}elseif(SYS_RELEASE=='2'){
					$component_appid=$GLOBALS['Component_Config']['sc']['component_appid'];
				}
				$component_access_token = mc_get("component_access_token_".$component_appid);
				$url="https://api.weixin.qq.com/sns/oauth2/component/access_token";
				$data = array ();
				$data ['appid'] = $appid;
				$data ['secret'] = $secret;
				$data ['code'] = $code;
				$data ['grant_type'] = "authorization_code";
				$data ['component_appid'] = $component_appid;
				$data ['component_access_token'] = $component_access_token;
			}else{
				$url = "https://api.weixin.qq.com/sns/oauth2/access_token";
				$data = array ();
				$data ['appid'] = $appid;
				$data ['secret'] = $secret;
				$data ['code'] = $code;
				$data ['grant_type'] = "authorization_code";
			}
			$info=WeiXin::sub_curl($url,$data,$is_post=0);
			$info=json_decode($info,true);
			if($info["errcode"]=='40029')
			{
				
			}else{
                                                $refresh_token=$info['refresh_token'];
                                                if($is_auth)
                                                {
                                                	$url="https://api.weixin.qq.com/sns/oauth2/component/refresh_token";
                                                	$data=array();
                                                	$data['appid']=$appid;
                                                	$data['grant_type']="refresh_token";
                                                	$data['refresh_token']=$refresh_token;
                                                	$data ['component_appid'] = $component_appid;
                                                	$data ['component_access_token'] = $component_access_token;
                                                }else{
                                                	$url="https://api.weixin.qq.com/sns/oauth2/refresh_token";
                                                	$data=array();
                                                	$data['appid']=$appid;
                                                	$data['grant_type']="refresh_token";
                                                	$data['refresh_token']=$refresh_token;
                                                }
                                                $info=array();
                                                $info=WeiXin::sub_curl($url,$data,$is_post=0);
                                                $info=json_decode($info,true);
                                                $userinfo=$info;
				if($info["errcode"]=='40029')
				{
				
				}else{
					
					$access_token=$userinfo["access_token"];
					$openid=$userinfo["openid"];
					$url="https://api.weixin.qq.com/sns/userinfo";
                                        $data=array();
                                        $data['access_token']=$access_token;
                                        $data['openid']=$openid;
                                        $data['lang']="zh_CN";
                                        $info=array();
					$info=WeiXin::sub_curl($url,$data,$is_post=0);
					$info=json_decode($info,true);
					if($info["errcode"]=='40003')
					{
					
					}else{
						$WxUserFans=new WxUserFans();
						$fansid= $WxUserFans->scalar ( "id", " where userid=$userid and weixin_user='".$info['openid']."' " );
						if($fansid)
						{
							$data['status']=1;
							$where="userid=$userid and id=$fansid ";
							$WxUserFans->update_data($data,$where);
						}else{
							$data['status']=1;
							$data['userid']=$userid;
							$data['weixin_user']=$info['openid'];
							$data['nick_name']=$info['nickname'];
							$data['sex']=$info['sex'];
							$data['country']=$info['country'];
							$data['province']=$info['province'];
							$data['city']=$info['city'];
							$data['cdate']=time();
							$data['sub_date']=time();
							$fansid=$WxUserFans->insert_data($data);
						}
						$info['openid']=$fansid;
					}
				}
			}
		}
		return $info;
	}
	
	//上传已退款订单
	public static function uploadredaifufundorder($userid,$tmp_dir)
	{
		if(!is_dir($_SERVER["SINASRV_UPLOAD"]."/daifufundorder/")){
			mkdir($_SERVER["SINASRV_UPLOAD"]."/daifufundorder/");
		}
		if (!empty($_FILES)) {
			$targetFolder=$_SERVER["SINASRV_UPLOAD"]."/daifufundorder/";
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$extend = explode("." , $_FILES['Filedata']['name']);							    //获取扩展名
			$va=count($extend)-1;
			$path = time().mt_rand(10000,99999).$userid.".".$extend[$va];
			$targetFile = $targetFolder.$path; 															//$_FILES['Filedata']['name'];//上传后的路径
			$fileTypes = array('csv'); 																			//允许的文件后缀
			$up_status = move_uploaded_file($tempFile,$targetFile);
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			if(!in_array($fileParts['extension'], $fileTypes))
			{
				$arr = array("status"=>"no","data"=>'不支持的文件类型!');
				echo json_encode($arr);
				die;
			}
			if($up_status)
			{
				$file = fopen($targetFile,'r');
				$data=array();
				$WxOrder = new WxOrder();
				while ($data = fgetcsv($file)) { 		//每次读取CSV里面的一行内容
					//$data=fgetcsv($file);
					//print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
					$db['order_status'] = 3;
					$state = 0;
					$state = $WxOrder->scalar('id', " where order_no = '".$data[1]."' and userid = 
					$userid");
					if($state)
					{
						$WxOrder->update_data($db, " where order_no = '".$data[1]."' and userid = 
						$userid");
					}
				}
				fclose($file);
				@unlink($targetFile);
				$arr = array("status"=>"ok","data"=>'上传成功!');
				echo json_encode($arr);
			}else{
				$arr = array("status"=>"no","data"=>'上传的文件出现问题!');
				echo json_encode($arr);
			}
		}
	} 
	
	//支付跳转 
	public static function payjump($userid,$orderid,$from_type)
	{
		$WxOrder = new WxOrder();
                if($from_type==12){
                     $pay_type = $WxOrder->scalar("pay_type","where id=".$orderid." and userid=".$userid);
                     if($pay_type!=12){
                         $url="/mobilegoodsdp/daifusuccess?daifu_id=".$orderid."&status=1";
                     }
                }
		return $url;
	}
	
	//代付订单状态
	public static function daifuordercomplete($order_no)
	{
		$WxOrder = new WxOrder();
		$main_order_info= $WxOrder->scalar ( "main_order_no,userid", " where  order_no='".$order_no."' and pay_status=1" );
		$main_order_no=$main_order_info['main_order_no'];
		$userid=$main_order_info['userid'];
		$main_order_record= $WxOrder->scalar ( "*", " where userid='".$userid."' and order_no='".$main_order_no."' and order_status=1" );
		$order_amount=sprintf("%.2f",$main_order_record['order_amount']);
		$openid=$main_order_record['openid'];
		$orderid=$main_order_record['id'];
		
		$query="select * from wx_order where userid='".$userid."'  and main_order_no='".$main_order_no."' and pay_status=1";
		$list = $WxOrder->fetchAll($query);
		if(count($list)>0 && is_array($list))
		{
			foreach($list as $k=>$v)
			{
				$daifu_order_amount+=$v['order_amount'];
			}
		}
		$daifu_order_amount=sprintf("%.2f",$daifu_order_amount);
		
		if($order_amount==$daifu_order_amount)
		{
			$where="userid='".$userid."' and order_no='".$main_order_no."' and order_status=1 ";
			$data['pay_status']=1;
			$WxOrder->update_data($data, $where);
			self::createvirtualgoodsnumber($orderid,2,$userid,$openid);
			//千人大会
			$WxGoodsOrderMgt = new WxGoodsOrderMgt();
			//$goodsorderinfo = $WxGoodsOrderMgt->scalar("id,unit_price"," WHERE order_id=".$main_order_record['id']." AND (goods_id='623' or goods_id='624' or goods_id='625' )");
			$goodsorderinfo = $WxGoodsOrderMgt->scalar("id,unit_price"," WHERE order_id=".$main_order_record['id']." AND (goods_id='40389' or goods_id='40391' or goods_id='40786' )");
			if($goodsorderinfo)
			{
				$WxOrderLogistics  = new WxOrderLogistics();
				$WxOrderShippingAddress  = new WxOrderShippingAddress ();
				$post_id = $WxOrderLogistics->scalar("post_id"," WHERE order_id=".$main_order_record['id']." ");
				$shippingaddress= $WxOrderShippingAddress->scalar("name,phone"," WHERE id=".$post_id." ");
				GoogsApi::saveqrdhmp($main_order_record['userid'],$shippingaddress['name'],$shippingaddress['phone'],$goodsorderinfo['unit_price']);
			}
			//千人大会
		}
	}

    /**
	 *认证服务号：千人大会
	 */
	public static function checkuser($url){
		$openid   = $_SESSION['clubdata']['openid'];
		$userid   = $_SESSION['clubdata']['userid'];
		if($_SESSION['clubdata']['openid']==-1){
			//验证服务号
	        $WxUser        = new WxUser();
			$wxuseraccount = $WxUser->get_user_account($userid,1);
			$obj           = new WxDpSet();
		    $is_authorize  = $obj->scalar("authorize","where  userid=".$userid." and type=1 ");
			if(!empty($wxuseraccount['appid'])&&!empty($wxuseraccount['appsecret']) &&$is_authorize==1){
				$auth_access   = 1;
			}else{
				$auth_access   = 0;
			}
			$is_auth=$wxuseraccount['is_auth'];
			if(	$is_auth)
			{
				$auth_access   = 1;
			}
			if($auth_access){
				   $flg = $_GET['flg'];
				   if(!$flg){
				    	GoogsApi::authurl($userid,$url,1);
				    }elseif($flg==1){
				    	GoogsApi::authuruserinfor($userid,$url,2);
				    }
			}
		}
	} 
	 
	public static function authurl($userid,$url,$flg){
		      $url = $url."&flg=".$flg;
		      $oauth_url =  self::daifuoauth2_snsapi_base($userid,$url);
              header("Location:".$oauth_url);
	}
	
	public static function authuruserinfor($userid,$url,$flg){
			$userinfo = self::authuserinfo_snsapi_base($userid,$_GET['code'],1);
			if($userinfo["openid"]>0){
				unset($_SESSION['clubdata']['openid']);
				$_SESSION['clubdata']['openid']    = $userinfo['openid'];
			    //$_SESSION['clubdata']['userid']    = $userid;
			}
			$url = $url."&flg=".$flg;
			header("Location:".$url);
	}
	
	//代付
	public static function daifu_authurl($userid,$url,$flg){
		      $url = $url."&flg=".$flg;
		      $oauth_url =  self::daifuoauth2($userid,$url);
              header("Location:".$oauth_url);
	}
	
	public static function daifu_authuruserinfor($userid,$url,$flg){
			$userinfo = self::authuserinfo($userid,$_GET['code'],1);
			if($userinfo["openid"]>0){
				unset($_SESSION['clubdata']['openid']);
				$_SESSION['clubdata']['openid']    = $userinfo['openid'];
			}
			$url = $url."&flg=".$flg;
			header("Location:".$url);
	}
	
}

?>
