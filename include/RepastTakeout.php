<?php
/*
 *
 * 餐饮接口类
 * @author ailiya
 *
 */
class RepastTakeout
{
    public $pay_type = array(
//    	"-1"=>"货到付款",
    	"9"=>"粉丝卡支付",
        "1"=>"汇付天下",
        "2"=>"支付宝",
        "3"=>"微信支付",
        "4"=>"财付通",	
        "5"=>"易宝支付",
//    	"6"=>"货到付款",
        "7"=>"直接发货",
        "10"=>"新版微信支付",
    );
    
    /**
     * 单门店 - 外卖订单接口
     * @author aly
     */
     public function orderinfo($id)
     {
        if($id > 0){
            //获取订单信息，订单详细，以及门店名称 外卖电话
            $repast_restaurant = new WxRepastrestaurant();   //门店管理
            $repast_takeout = new WxRepastTakeout();         //外卖主表
            $repast_indent  = new WxRepastindent();          //外卖订单
            $repast_indent_goods = new WxRepastindentgoods();//外卖订单明细
            $WxRepastFood = new WxRepastFood();              //菜品表
            $WxRepastType = new WxRepastType();              //菜品分类表
            
            $order = $repast_indent -> get_one($id);
            if($order)
            {
                //$order['order_time'] = date('Y-m-d H:i:m', $order['insert_time']); //下单时间
                $goods = $repast_indent_goods->selectall('indent_id = '.$id, '', 'id ASC'); //获取该订单下的菜品
                $goodArr = array();
                $gtype = array();
                $goodtype = $WxRepastType -> selectall(" userid = ".$order['userid']);//获取订单type类型
                if($goodtype)
                {
                    foreach($goodtype as $val)
                    {
                        $gtype[$val['id']] = $val['type_name'];
                    }
                }
                foreach($goods['data'] as $val)
                {
                    if($val['indent_type'] != 4)
                    {
                        //菜品成成拼接数量
                        $val['indent_name'].= ' x '.$val['indent_sum'];
                    }
                    
                    //菜品则查询
                    switch($val['indent_type']) 
                    {
                        case 1:
                            //菜品
                            $food = $WxRepastFood -> selectone(' id = '.$val['indent_goods_id'].' AND userid = '.$order['userid']);
                            if($food)
                            {
                                if(isset($gtype[$food['type_id']]))
                                {
                                    $goodArr[$gtype[$food['type_id']]][] = $val;
                                } else {
                                    
                                }
                            }
                            break;
                        case 2:
                            //套餐
                            $goodArr['套餐'][] = $val;
                            break;
                        case 3:
                            //其他 餐盒
                            $goodArr['其他'][] = $val;
                            break;
                        case 4:
                            //其他 外送费
                            $goodArr['其他'][] = $val;
                            break;
                        
                    }
                }
                if(!empty($goodArr))
                {
                    foreach($goodArr as $key => $val)
                    {
                        $good[] = array('indent_name' => $key, 'is_type' => 1);
                        foreach($val as $v)
                        {
                            $v['is_type'] = 2;
                            $good[] = $v;
                        }
                    }
                }
                $resstaurant = $repast_restaurant -> get_one_by_userid($order['userid']); //用订单userid获取门店信息
                $takeout = $repast_takeout -> get_one_by_userid($order['userid']); //用订单userid获取外卖信息
                $order['restaurant_name'] = $resstaurant['restaurant_name']; //赋值门店名称
                $order['takeout_mobile'] = isset($takeout['takeout_mobile']) ? $takeout['takeout_mobile'] : ''; //赋值外卖电话
                $order['multi_print'] = isset($takeout['multi_print']) ? $takeout['multi_print'] : 1; //赋值多联打印订单
                //$order['paytype_name'] = $order['paytype'] == -1 ? '货到付款' : $this->pay_type[$order['paytype']]; //添加付款方式名称
        		if($order['paytype'] == -1)
        		{
        		    $order['paytype_name'] = '货到付款';
        		}
        		else if($order['paytype'] == 9)
        		{
        		    $order['paytype_name'] = '粉丝卡支付'; 
        		}
        		else
        		{
        		    $order['paytype_name'] = $GLOBALS['pay_type'][$order['paytype']];
        		}
                return array('order' => $order, 'goods' => $good);
            }
        }
        return false;//无效订单
     }
     
     
    /**
     * 单门店 - 外卖当日统计接口
     * @author aly
     */
     public function statistics($uid)
     {
        //只要调用 就返回 当天 当月 的汇总数据（状态：3 配送中；4 已完成）
        $repast_indent  = new WxRepastindent();//外卖订单
        $repast_restaurant = new WxRepastrestaurant();//门店管理
        $now = time();//当前时间
        $today = strtotime(date('Y-m-d', time()));//当前日凌晨
        $current_month =  strtotime(date('Y-m-1', time()));//当前月
        $today_arr = array('order_count' => 0, 'total_price' => 0);
        $current_month_arr = array('order_count' => 0, 'total_price' => 0, 'avg_price' => 0);
        $title = array('restuarant_name' => '', );
        
        $restaurant = $repast_restaurant -> get_one_by_userid($uid); //用订单userid获取门店信息
        if($restaurant){
            //当日统计
            $where = 'userid = '.$uid.' AND type IN (2, 3) AND insert_time BETWEEN '.$today.' AND '.$now;
            $day = $repast_indent -> get_count_orders($where, '`date`', '', '1', $islist=0, $dtype = "%Y-%m-%d");
            if($day)
            {
                $today_arr = array('order_count' => $day['cnt'], 'total_price' => $day['prc_total']);
            }
            //当月统计
            $where = 'userid = '.$uid.' AND type IN (2, 3) AND insert_time BETWEEN '.$current_month.' AND '.$now;
            $month = $repast_indent -> get_count_orders($where, '`date`', '', '1', $islist=0, $dtype = "%Y-%m");
            if($month)
            {
                $current_month_arr = array('order_count' => $month['cnt'], 'total_price' => $month['prc_total'], 'avg_price' => sprintf("%.2f", $month['prc']));
            }
            return array('today' => $today_arr, 'current_month' => $current_month_arr, 'restaurant_name' => $restaurant['restaurant_name'], 'date' => date('Y-m-d', $now), 'restaurant_end_datetime' => $restaurant['end_datetime']);
        }
        return false;
     }
     
     
    /**
     * 单门店 - 外卖打单状态更新
     * @author aly
     */
     public function update_type($id, $type, $userid)
    {
        if($id > 0)
        {
            $repast_indent  = new WxRepastindent();//外卖订单
            $order = $repast_indent -> get_one_by_id_uid($id, $userid);//查询是否有此id
            if($order)  //确认此订单id为有效id
            {
                if($type == 1 && $order['type'] == 1)
                {
                    $data['type'] = 2;
                    $data['print_time'] = time();
                    $repast_indent -> update_data($data, 'id = '.$id.' AND userid = '.$userid);
                }
                else if($type == 2 && $order['type'] == 1)
                {
                    //修改订单失败
                    $data['type'] = 5;
                    $repast_indent -> update_data($data, 'id = '.$id.' AND userid = '.$userid);
                }
            }
        }
    }
    
    
    /**
     * 单门店 - 堂吃打印订单小票
     * @author aly
     */
    public function meal($id, $userid)
    {
        if($id > 0)
        {
            $repast_restaurant = new WxRepastrestaurant();//门店管理
            $repast_meal = new WxRepastMeal();            //堂吃订单
            $repast_meal_goods = new WxRepastMealGoods(); //堂吃订单菜品
            $repast_table = new WxRepastTable();          //堂吃餐桌
            $WxRepastdishset = new WxRepastdishset();     //堂吃设置
            $WxRepastFood = new WxRepastFood();           //菜品表
            $WxRepastType = new WxRepastType();           //菜品分类表
            
            $meal = $repast_meal -> get_phone_data($id, $userid);
            $time = time();
            $goods = $repast_meal_goods->get_by_meal($id);              //获取该订单下的菜品
            $goodArr = array();
            $gtype = array();
            $goodtype = $WxRepastType -> selectall(" userid = ".$userid);//获取订单type类型
            if($goodtype)
            {
                foreach($goodtype as $val)
                {
                    $gtype[$val['id']] = $val['type_name'];
                }
            }
            //var_dump($gtype);
            //var_dump($goods);
            foreach($goods as $val)
            {
                //菜品则查询
                switch($val['meal_type']) 
                {
                    case 1:
                        //菜品
                        $food = $WxRepastFood -> selectone(' id = '.$val['meal_goods_id'].' AND userid = '.$userid);
                        if($food)
                        {
                            if(isset($gtype[$food['type_id']]))
                            {
                                $goodArr[$gtype[$food['type_id']]][] = $val;
                            } else {
                                
                            }
                        }
                        break;
                    case 2:
                        //套餐
                        $goodArr['套餐'][] = $val;
                        break;
                    case 3:
                        //其他 餐盒
                        $goodArr['其他'][] = $val;
                        break;
                    case 4:
                        //其他 外送费
                        $goodArr['其他'][] = $val;
                        break;
                    
                }
            }
            //var_dump($goodArr);exit;
            if(!empty($goodArr))
            {
                foreach($goodArr as $key => $val)
                {
                    $good[] = array('meal_name' => $key, 'is_type' => 1);
                    foreach($val as $v)
                    {
                        $v['is_type'] = 2;
                        $good[] = $v;
                    }
                }
            }
            $restaurant = $repast_restaurant -> get_one_by_userid($userid); //用订单userid获取门店信息
            $table = $repast_table -> get_row($meal['table_id']);
            $set = $WxRepastdishset -> get_one($userid);
            $meal['print_time'] = time();
            $meal['table_name'] = $table['table_name'];
            $meal['restaurant_name'] = $restaurant['restaurant_name']; //赋值门店名称
            if(strlen($restaurant['restaurant_wifi']) > 0)
            {
                $wifi = explode(';', $restaurant['restaurant_wifi']);
                $arr = array('wifi_id' => '', 'wifi_pw' => '');
                foreach($wifi as $val)
                {
                    list($arr['wifi_id'], $arr['wifi_pw']) = explode(',', $val);
                    $meal['restaurant_wifi'][] = $arr;
                }
            } else {
                $meal['restaurant_wifi'] = '';
            }
            
            $meal['multi_print'] = isset($set['multi_print']) ? $set['multi_print'] : 1; //赋值多联打印订单
            //$paytype_name = $meal['paytype'] == -1 ? '到店支付' : $this->pay_type[$meal['paytype']]; //添加付款方式名称
    		if($meal['paytype'] == -1)
    		{
    		    $paytype_name = '到店支付';
    		}
    		else if($meal['paytype'] == 9)
    		{
    		    $paytype_name = '粉丝卡支付'; 
    		}
    		else
    		{
    		    $paytype_name = $GLOBALS['pay_type'][$meal['paytype']];
    		}
            $paytype_status_name = $meal['pay_state'] == 1 ? '待支付' : '已支付'; //添加付款方式名称
            $meal['paytype_name'] = $paytype_name.' '.$paytype_status_name;
            //修改打票时间
            $repast_meal->update_data(
                array('print_time' => $time), 
                'WHERE userid = '.$userid.' AND id = '.$id
            );
            
            return array('order' => $meal, 'goods' => (isset($good) ? $good : array()));
        }
    }
    
    //-----------------------------------------------------------多门店接口
    
    /**
     * 多门店 - 外卖订单接口
     * @author aly
     */
     public function multiorderinfo($id)
     {
        if($id > 0){
            //获取订单信息，订单详细，以及门店名称 外卖电话
            $StorefrontInfo = new StorefrontInfo();           //门店管理
            $repast_takeout = new WxRepastsTakeout();         //外卖主表
            $repast_indent  = new WxRepastsindent();          //外卖订单
            $repast_indent_goods = new WxRepastsindentgoods();//外卖订单明细
            $WxRepastsFood = new WxRepastsFood();             //菜品表
            $WxRepastsType = new WxRepastsType();             //菜品分类表
            
            $order = $repast_indent -> get_one($id);
            if($order)
            {
                $goods = $repast_indent_goods->selectall('indent_id = '.$id, '', 'id ASC'); //获取该订单下的菜品
                $goodArr = array();
                $gtype = array();
                $goodtype = $WxRepastsType -> selectall(" restaurant_id = ".$order['restaurant_id']);//获取订单type类型
                if($goodtype)
                {
                    foreach($goodtype as $val)
                    {
                        $gtype[$val['id']] = $val['type_name'];
                    }
                }
                foreach($goods['data'] as $val)
                {
                    if($val['indent_type'] != 4)
                    {
                        //菜品成成拼接数量
                        $val['indent_name'].= ' x '.$val['indent_sum'];
                    }
                    //菜品则查询
                    switch($val['indent_type']) 
                    {
                        case 1:
                            //菜品
                            $food = $WxRepastsFood -> selectone(' id = '.$val['indent_goods_id'].' AND userid = '.$order['userid']);
                            if($food)
                            {
                                if(isset($gtype[$food['type_id']]))
                                {
                                    $goodArr[$gtype[$food['type_id']]][] = $val;
                                }
                            }
                            break;
                        case 2:
                            //套餐
                            $goodArr['套餐'][] = $val;
                            break;
                        case 3:
                            //其他 餐盒
                            $goodArr['其他'][] = $val;
                            break;
                        case 4:
                            //其他 外送费
                            $goodArr['其他'][] = $val;
                            break;
                        
                    }
                }
                if(!empty($goodArr))
                {
                    foreach($goodArr as $key => $val)
                    {
                        $good[] = array('indent_name' => $key, 'is_type' => 1);
                        foreach($val as $v)
                        {
                            $v['is_type'] = 2;
                            $good[] = $v;
                        }
                    }
                }
                $resstaurant = $StorefrontInfo -> get_row_byid($order['restaurant_id']); //用订单userid获取门店信息
                $takeout = $repast_takeout -> get_one_by_userid($order['userid'], $order['restaurant_id']); //用订单userid获取外卖信息
                $order['restaurant_name'] = $resstaurant['storefront_name']; //赋值门店名称
                $order['takeout_mobile'] = isset($takeout['takeout_mobile']) ? $takeout['takeout_mobile'] : ''; //赋值外卖电话
                $order['multi_print'] = isset($takeout['multi_print']) ? $takeout['multi_print'] : 1; //赋值多联打印订单
                //$order['paytype_name'] = $order['paytype'] == -1 ? '货到付款' : $this->pay_type[$order['paytype']]; //添加付款方式名称
        		if($order['paytype'] == -1)
        		{
        		    $order['paytype_name'] = '货到付款';
        		}
        		else if($order['paytype'] == 9)
        		{
        		    $order['paytype_name'] = '粉丝卡支付'; 
        		}
        		else
        		{
        		    $order['paytype_name'] = $GLOBALS['pay_type'][$order['paytype']];
        		}
                return array('order' => $order, 'goods' => $good);
            }
        }
        return false;//无效订单
     }
     
    /**
     * 多门店 - 外卖当日统计接口
     * @author aly
     */
     public function multistatistics($restaurant_id)
     {
        //只要调用 就返回 当天 当月 的汇总数据（状态：3 配送中；4 已完成）
        $repast_indent  = new WxRepastsindent();//外卖订单
        $repast_restaurant = new WxRepastsrestaurant();//门店管理
        $now = time();//当前时间
        $today = strtotime(date('Y-m-d', time()));//当前日凌晨
        $current_month =  strtotime(date('Y-m-1', time()));//当前月
        $today_arr = array('order_count' => 0, 'total_price' => 0);
        $current_month_arr = array('order_count' => 0, 'total_price' => 0, 'avg_price' => 0);
        $title = array('restuarant_name' => '', );
        
        $restaurant = $repast_restaurant -> get_one($restaurant_id);//获取门店信息
        if($restaurant){
            //当日统计
            $where = 'restaurant_id = '.$restaurant_id.' AND type IN (2, 3) AND insert_time BETWEEN '.$today.' AND '.$now;
            $day = $repast_indent -> get_count_orders($where, '`date`', '', '1', $islist=0, $dtype = "%Y-%m-%d");
            if($day)
            {
                $today_arr = array('order_count' => $day['cnt'], 'total_price' => $day['prc_total']);
            }
            //当月统计
            $where = 'restaurant_id = '.$restaurant_id.' AND type IN (2, 3) AND insert_time BETWEEN '.$current_month.' AND '.$now;
            $month = $repast_indent -> get_count_orders($where, '`date`', '', '1', $islist=0, $dtype = "%Y-%m");
            if($month)
            {
                $current_month_arr = array('order_count' => $month['cnt'], 'total_price' => $month['prc_total'], 'avg_price' => sprintf("%.2f", $month['prc']));
            }
            return array('today' => $today_arr, 'current_month' => $current_month_arr, 'restaurant_name' => $restaurant['restaurant_name'], 'restaurant_id' => $restaurant['id'], 'date' => date('Y-m-d', $now), 'restaurant_end_datetime' => $restaurant['end_datetime']);
        }
        return false;
     }
     
     
    /**
     * 多门店 - 外卖打单状态更新
     * @author aly
     */
     public function multi_update_type($id, $type)
    {
        if($id > 0)
        {
            $repast_indent  = new WxRepastsindent();//外卖订单
            $order = $repast_indent -> get_one($id);//查询是否有此id
            if($order)  //确认此订单id为有效id
            {
                if($type == 1 && $order['type'] == 1)
                {
                    $data['type'] = 2;
                    $data['print_time'] = time();
                    $repast_indent -> update_data($data, 'id = '.$id);
                }
                else if($type == 2 && $order['type'] == 1)
                {
                    //修改订单失败
                    $data['type'] = 5;
                    $repast_indent -> update_data($data, 'id = '.$id);
                }
            }
        }
    }
    
    /**
     * 多门店 - 堂吃打印订单小票
     * @author aly
     */
    public function multimeal($id)
    {
        if($id > 0)
        {
            $StorefrontInfo = new StorefrontInfo();        //门店管理
            $StorefrontRepasts = new StorefrontRepasts();  //多门店-餐饮
            $repast_meal = new WxRepastsMeal();            //堂吃订单
            $repast_meal_goods = new WxRepastsMealGoods(); //堂吃订单菜品
            $repast_table = new WxRepastsTable();          //堂吃餐桌
            $WxRepastsdishset = new WxRepastsdishset();    //堂吃设置
            $WxRepastsFood = new WxRepastsFood();          //菜品表
            $WxRepastsType = new WxRepastsType();          //菜品分类表
            
            $meal = $repast_meal -> get_one('id = '.$id);//($id);
            $time = time();
            $goods = $repast_meal_goods->get_by_meal($id);              //获取该订单下的菜品
            $goodArr = array();
            $gtype = array();
            $goodtype = $WxRepastsType -> selectall(" restaurant_id = ".$meal['restaurant_id']);//获取订单type类型
            if($goodtype)
            {
                foreach($goodtype as $val)
                {
                    $gtype[$val['id']] = $val['type_name'];
                }
            }
            //var_dump($gtype);
            //var_dump($goods);
            foreach($goods as $val)
            {
                //菜品则查询
                switch($val['meal_type']) 
                {
                    case 1:
                        //菜品
                        $food = $WxRepastsFood -> selectone(' id = '.$val['meal_goods_id'].' AND restaurant_id = '.$meal['restaurant_id']);
                        if($food)
                        {
                            if(isset($gtype[$food['type_id']]))
                            {
                                $goodArr[$gtype[$food['type_id']]][] = $val;
                            } else {
                                
                            }
                        }
                        break;
                    case 2:
                        //套餐
                        $goodArr['套餐'][] = $val;
                        break;
                    case 3:
                        //其他 餐盒
                        $goodArr['其他'][] = $val;
                        break;
                    case 4:
                        //其他 外送费
                        $goodArr['其他'][] = $val;
                        break;
                    
                }
            }
            //var_dump($goodArr);exit;
            if(!empty($goodArr))
            {
                foreach($goodArr as $key => $val)
                {
                    $good[] = array('meal_name' => $key, 'is_type' => 1);
                    foreach($val as $v)
                    {
                        $v['is_type'] = 2;
                        $good[] = $v;
                    }
                }
            }
            $restaurant = $StorefrontInfo -> get_row_byid($meal['restaurant_id']); //用订单userid获取门店信息
            $repasts = $StorefrontRepasts -> get_one($meal['restaurant_id'], $meal['userid']); //用订单userid获取门店信息
            $table = $repast_table -> get_data($meal['table_id']);
            $set = $WxRepastsdishset -> get_one($restaurant['userid'], $meal['restaurant_id']);//获取堂吃设置
            $meal['print_time'] = time();
            $meal['table_name'] = $table['table_name'];
            $meal['restaurant_name'] = $restaurant['storefront_name']; //赋值门店名称
            if(strlen($repasts['restaurant_wifi']) > 0)
            {
                $wifi = explode(';', $repasts['restaurant_wifi']);
                $arr = array('wifi_id' => '', 'wifi_pw' => '');
                foreach($wifi as $val)
                {
                    list($arr['wifi_id'], $arr['wifi_pw']) = explode(',', $val);
                    $meal['restaurant_wifi'][] = $arr;
                }
            } else {
                $meal['restaurant_wifi'] = '';
            }
            $meal['multi_print'] = isset($set['multi_print']) ? $set['multi_print'] : 1; //赋值多联打印订单
            //$paytype_name = $meal['paytype'] == -1 ? '到店支付' : $this->pay_type[$meal['paytype']]; //添加付款方式名称
    		if($meal['paytype'] == -1)
    		{
    		    $paytype_name = '到店支付';
    		}
    		else if($meal['paytype'] == 9)
    		{
    		    $paytype_name = '粉丝卡支付'; 
    		}
    		else
    		{
    		    $paytype_name = $GLOBALS['pay_type'][$meal['paytype']];
    		}
            $paytype_status_name = $meal['pay_state'] == 1 ? '待支付' : '已支付'; //添加付款方式名称
            $meal['paytype_name'] = $paytype_name.' '.$paytype_status_name;
            //修改打票时间
            $repast_meal->update_data(
                array('print_time' => $time), 
                'WHERE restaurant_id = '.$meal['restaurant_id'].' AND id = '.$id
            );
            return array('order' => $meal, 'goods' => (isset($good) ? $good : array()));
        }
    }
    
    //-------------------------------------------------------------------------------------------------外卖行业版
    /**
     * 外卖行业版 - 外卖订单接口
     * @author aly
     */
     public function takeout_orderinfo($id)
     {
        if($id > 0){
            $takeout_restaurant = new WxTakeoutRestaurant();
            $takeout_takeout = new WxTakeoutTakeout();
            $takeout_indent  = new WxTakeoutIndent();//订单表
            $takeout_indent_commodity = new WxTakeoutIndentCommodity();
            
            $order = $takeout_indent -> get_one($id);
            if($order)
            {
                $goods = $takeout_indent_commodity->selectall('indent_id = '.$id, '', 'id ASC'); //获取该订单下的菜品
                $good = array();
                if(isset($goods['data']))
                {
                    foreach($goods['data'] as $val)
                    {
                        if($val['indent_type'] != 4)
                        {
                            //菜品成成拼接数量
                            $val['indent_name'].= ' x '.$val['indent_sum'];
                            $good[] = $val;
                        }
                    }
                }
                $resstaurant = $takeout_restaurant -> get_one_by_userid($order['userid']);  //用订单userid获取门店信息
                $takeout = $takeout_takeout -> get_one_by_userid($order['userid']);                 //用订单userid获取外卖信息
                $order['restaurant_name'] = $resstaurant['restaurant_name']; //赋值门店名称
                $order['restaurant_mobile'] = isset($resstaurant['restaurant_mobile']) ? $resstaurant['restaurant_mobile'] : ''; //赋值门店电话
                $order['multi_print'] = isset($takeout['multi_print']) ? $takeout['multi_print'] : 1; //赋值多联打印订单
                //$order['paytype_name'] = $order['paytype'] == -1 ? '货到付款' : $this->pay_type[$order['paytype']]; //添加付款方式名称
        		if($order['paytype'] == -1)
        		{
        		    $order['paytype_name'] = '货到付款';
        		}
        		else if($order['paytype'] == 9)
        		{
        		    $order['paytype_name'] = '粉丝卡支付'; 
        		}
        		else
        		{
        		    $order['paytype_name'] = $GLOBALS['pay_type'][$meal['paytype']];
        		}
                return array('order' => $order, 'goods' => $good);
            }
        }
        return false;//无效订单
     }
     
     
    /**
     * 外卖行业版 - 外卖当日统计接口
     * @author aly
     */
     public function takeout_statistics($uid)
     {
        //只要调用 就返回 当天 当月 的汇总数据（状态：3 配送中；4 已完成）
        $takeout_indent  = new WxTakeoutIndent();       //订单表
        $takeout_restaurant = new WxTakeoutRestaurant();//门店管理
        $now = time();//当前时间
        $today = strtotime(date('Y-m-d', time()));//当前日凌晨
        $current_month =  strtotime(date('Y-m-1', time()));//当前月
        $today_arr = array('order_count' => 0, 'total_price' => 0);
        $current_month_arr = array('order_count' => 0, 'total_price' => 0, 'avg_price' => 0);
        $title = array('restuarant_name' => '', );
        
        $restaurant = $takeout_restaurant -> get_one_by_userid($uid); //用订单userid获取门店信息
        if($restaurant){
            //当日统计
            $where = 'userid = '.$uid.' AND type IN (2, 3) AND insert_time BETWEEN '.$today.' AND '.$now;
            $day = $takeout_indent -> get_count_orders($where, '`date`', '', '1', $islist=0, $dtype = "%Y-%m-%d");
            if($day)
            {
                $today_arr = array('order_count' => $day['cnt'], 'total_price' => $day['prc_total']);
            }
            //当月统计
            $where = 'userid = '.$uid.' AND type IN (2, 3) AND insert_time BETWEEN '.$current_month.' AND '.$now;
            $month = $takeout_indent -> get_count_orders($where, '`date`', '', '1', $islist=0, $dtype = "%Y-%m");
            if($month)
            {
                $current_month_arr = array('order_count' => $month['cnt'], 'total_price' => $month['prc_total'], 'avg_price' => sprintf("%.2f", $month['prc']));
            }
            return array('today' => $today_arr, 'current_month' => $current_month_arr, 'restaurant_name' => $restaurant['restaurant_name'], 'date' => date('Y-m-d', $now), 'restaurant_end_datetime' => $restaurant['end_datetime']);
        }
        return false;
     }
     
     
    /**
     * 外卖行业版 - 外卖打单状态更新
     * @author aly
     */
     public function takeout_update_type($id, $type, $userid)
    {
        if($id > 0)
        {
            $takeout_indent  = new WxTakeoutIndent();//外卖订单
            $order = $takeout_indent -> get_one($id);//查询是否有此id
            if($order && $order['userid'] == $userid)  //确认此订单id为有效id
            {
                if($type == 1 && $order['type'] == 1)
                {
                    $data['type'] = 2;
                    $data['print_time'] = time();
                    $takeout_indent -> update_data($data, 'id = '.$id.' AND userid = '.$userid);
                }
                else if($type == 2 && $order['type'] == 1)
                {
                    //修改订单失败
                    $data['type'] = 5;
                    $takeout_indent -> update_data($data, 'id = '.$id.' AND userid = '.$userid);
                }
            }
        }
    }
}

?>