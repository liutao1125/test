<?php	//utf-8

/*
 *	临时缓存key的统一接口
 */

class CacheKey {

	static $prefix='sxzx';
	//获取用户key
	static function get_user_key($uid)
	{
		return self::$prefix.__FUNCTION__.$uid.'aa';
	}
	//获取用户name_key
	static function get_user_name_key($name)
	{
		return self::$prefix.__FUNCTION__.$name;
	}
    //获取班级key
    static function get_grade_class_key($id)
    {
        return self::$prefix.__FUNCTION__.$id;
    }
    //获取角色key
    static function get_role_name_key($id)
    {
        return self::$prefix.__FUNCTION__.$id;
    }
    //获取学校key
    static function get_school_name_key($id)
    {
        return self::$prefix.__FUNCTION__.$id;
    }
    //获取区域key
    static function get_area_name_key($id)
    {
        return self::$prefix.__FUNCTION__.$id;
    }
    //获取类别key
    static function get_category_name_key($id)
    {
        return self::$prefix.__FUNCTION__.$id;
    }
	//获取用户账号key
	static function get_user_acc_key($uid,$type)
	{
		return self::$prefix.__FUNCTION__.$uid.$type;
	}
	//获取用户key
	static function get_user_bykey($key)
	{
		return self::$prefix.md5(__FUNCTION__.$key.'sa');
	}
	
	//获取粉丝key
	static function get_userfans_byid($id)
	{
		return self::$prefix.md5(__FUNCTION__.$id);
	}
	
	//获取粉丝key
	static function get_userfans_bykey($username)
	{
		return self::$prefix.md5(__FUNCTION__.$username);
	}

    /**
     * 新闻缓存key
     * @param $id
     * @return string
     */
    static function getNewsKey($id)
    {
        return self::$prefix.__FUNCTION__.$id.'news';
    }

	//获取用户key
	static function get_user_list_key($uid,$type)
	{
		return self::$prefix.__FUNCTION__.$uid.$type.'ss';
	}
	
	//获取用户开关key
	static function get_user_onoff_key($uid)
	{
		return self::$prefix.__FUNCTION__.$uid;
	}
	
	//获取单条文字key
	static function get_rep_msg_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	//获取单条单图文key
	static function get_rep_pic_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	//获取单条多图文key
	static function get_rep_morepic_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	//获取单条多图文key
	static function get_rep_morespic_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	//获取单条多图文key
	static function get_rep_morespic_key_single($id){
		return self::$prefix.__FUNCTION__.$id.'a';
	}
	//获取单条底部菜单key
	static function get_rep_foot_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
	//获取单条代理商key
	static function get_agent_row($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
	//获取系统用户信息
	static public function getsysuserkey($uid)
	{
		return self::$prefix.__FUNCTION__.$uid;
	}
	
	//获取图片信息
	static public function getpicdatakey($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
    /*
     * 获取相册key
     * @author xukan
     */ 
	static function get_photo_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/**
	 * 相册分类key
	 * @author 张勇
	 */
	static function get_photo_class_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 获取签名key
     * @author xukan
     */ 
	static function get_signature_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 获取360全景看房key
     * @author xukan
     */ 
	static function get_panoramic_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 微排队key
     * @author xukan
     */ 
	static function get_lineup_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 优惠券key
     * @author xukan
     */ 
	static function get_coupon_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 子账户key
     * @author xukan
     */ 
	static function get_child_account_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 消息通知key
     * @author xukan
     */ 
	static function get_sys_msg_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
    /*
     * 系统消息通知key
     * @author xukan
     */ 
	static function get_sys_msg_all_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/*
	 * 文本信息key
	* @author maojingjing 2014-03-12
	*/
	static function get_msg_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}

	/*
	 * 单图文信息key
	* @author maojingjing 2014-03-12
	*/
	static function get_pic_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	/*
	 * 多图文信息key
	* @author maojingjing 2014-03-12
	*/
	static function get_picmore_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/*
	 * 位置信息key
	* @author maojingjing 2014-03-12
	*/
	static function get_lbs_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/*
	 * 表情信息key
	* @author maojingjing 2014-03-12
	*/
	static function get_ex_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/*
	 * 微网站基本信息
	* @author sunshichun@dodoca.com
	*/
	static function get_whbddk_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
	/**
	 * 用户是否安全认证
	 */
	static function get_user_check($uid)
	{
		return self::$prefix.__FUNCTION__.$uid;
	}
	
	/*
	 * 店铺商品表信息
	* @author maojingjing 2014-05-21
	*/
	static function get_rep_goods_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
	static function get_pai_list($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	/**
	 * 收货地址缓存
	 * @param  $id 商户+用户ID
	 * 
	 *  add by wuwudong
	 */
	static  function get_logistics_list($id){
		return self::$prefix.__FUNCTION__.$id;
	}
        
        /**
         * 店铺设置
         * @param type $userid
         * @return type
         */
        static function get_dpset_key($str){
            return self::$prefix.__FUNCTION__.$str;
        }
        
        /**
         * 商品分类列表
         * @param type $str
         */
        static  function get_category_key($str){
            return self::$prefix.__FUNCTION__.$str;
        }
        
        //会务文字key
	static function get_conference_key($conference_id)
	{
		return self::$prefix.__FUNCTION__.$conference_id;
	}
	
	/**
	 * 店铺抵用券
	 * @author xukan
	 */
	static function get_dp_coupon_key($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
	/**
	 * 商品总库存
	 * @author zhangyong
	 */
	static function get_goods_total_inventory_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	

	/**
	 * 商品明细属库存
	 * @author zhangyong
	 */
	static function get_goods_atrr_detail_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/**
	 * 商品明细属性库存
	 * @author zhangyong
	 */
	static function get_property_stocks_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/**
	 * 商品明细属性选中
	 * @author zhangyong
	 */
	static function get_select_properties_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	/**
	 * 限时购时间段
	 * @author xukan
	 */
	static function get_goods_xs_times($id)
	{
		return self::$prefix.__FUNCTION__.$id;
	}
	
	/**
	 * 商品商家信息
	 * @author 张勇
	 */
	static function get_goods_business_infor_key($str)
	{
		return self::$prefix.__FUNCTION__.$str;
	}
	
	//微餐饮菜品时间段key
	static function get_repast_datetime_key($userid)
	{
		return self::$prefix.__FUNCTION__.$userid;
	}
	
	//微餐饮多门店菜品时间段key
	static function get_repasts_datetime_key($restaurant_id)
	{
		return self::$prefix.__FUNCTION__.$restaurant_id;
	}
        
    //立即购买兑换券修改密码key
    static function get_nocart_key($openid){
        return self::$prefix.__FUNCTION__.$openid;
    }
        
    //购物车兑换券修改密码key
    static function get_cart_key($openid){
        return self::$prefix.__FUNCTION__.$openid;
    }
    
    /**
     * 商品商家信息
     * @author 张勇
     */
    static function get_question_naire_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    //问卷手机端
	static function get_phone_question_naire_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	/**
     * 店铺分享key
     * @author 张勇
     */
    static function get_shop_share_key($id)
    {
    	return __FUNCTION__.$id;
    }
    
    //获取用户公钥、私钥
    static function get_userrsa_key($uid)
    {
    	return self::$prefix.__FUNCTION__.$uid;
    }

    //微网站统计key
    static function get_website_count_key($weiid)
    {
    	return self::$prefix.__FUNCTION__.$weiid;
    }

    //微网站自定义统计key
    static function get_websitet_count_key($weiid)
    {
    	return self::$prefix.__FUNCTION__.$weiid;
    }
	
	//海报统计Key
	static function get_handbill_key($id) {
		return self::$prefix.__FUNCTION__.$id;
	}

	//指尖海报banner
	static function get_handbillbanner_key($weiid) {
		return self::$prefix.__FUNCTION__.$weiid;
	}
    
    //导航菜单
    static function get_menu_key($controller,$types)
    {
    	return self::$prefix.__FUNCTION__.$controller.$types;
    }
    //子账号导航菜单
    static function get_submenu_key($uid)
    {
    	return self::$prefix.__FUNCTION__.$uid;
    }
	//子账号优惠券详情页
    static function get_submenu_coupon_key($uid)
    {
    	return self::$prefix.__FUNCTION__.$uid;
    }
    
	//多门店获取门店
    static function get_repasts_restaurant_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	//预约设置
    static function get_microappset_byid_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	//编辑预约项
    static function get_customfield_byid_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	//预约菜单
    static function get_microappomenu_byid_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }

    //政务预约设置
    static function get_microappset_gov_byid_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	//政务编辑预约项
    static function get_customfield_gov_byid_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	//政务预约菜单
    static function get_microappomenu_gov_byid_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }

    //微喜帖主表单条记录
    static function get_wedding_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
	/**
	 * wangyu
	 * 开放平台 服务令牌（component_access_token）
	 */
    static function get_component_access_token()
    {
    	return self::$prefix.__FUNCTION__;
    }
    
	/**
	 * wangyu
	 * 开发平台 预授权码，预授权码用于公众号授权时的服务方安全验证。
	 */
    static function get_pre_auth_code()
    {
    	return self::$prefix.__FUNCTION__;
    }
    
    //店铺商品已售数量
    static function good_snumber_sold_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    //酒店行业版获取酒店
    static function get_apartment_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	//网页授权
    static function get_auth_acc_token_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    	//机构首页
    static function get_agent_index_key($id)
    {
    	return self::$prefix.md5(__FUNCTION__.$id.'ai');
    }
    

    /**
     * 
     * wangyu
	 * 优惠券主表
     * @param $id
     */
    static function get_coupons_info_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    /**
     * wangyu
	 * 优惠券子表
     * @param $id
     */
    static function get_coupons_subtabulation_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    /**
     * 
     * wangyu
     * 优惠券来源
     * @param $coupons_info_id
     * @param $userid
     */
	static function get_couponsissuechannel_list_id($coupons_info_id,$userid)
    {
    	return self::$prefix.$userid.__FUNCTION__.$coupons_info_id;
    }
    
    //券-门店 获取门店
    static function get_storefront_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    //餐饮多店 获取门店子表
    static function get_storefront_repasts_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    //酒店 获取门店子表
    static function get_storefront_apartment_key($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	/**
	 * 餐饮点菜设置
	 * @author zcc
	 * @param userid 点客平台id
	 * @param restaurant_id 门店id	0:单门店
	 */
    static function repast_dishset($userid,$restaurant_id='0') {
    	return self::$prefix.__FUNCTION__.$userid.'_'.$restaurant_id;
    }
    
	/**
	 * 新版海报访问量
	 * @author zcc
	 * @param weiid 海报id
	 * @param putid 投放点id
	 */
    static function hb_new_count($weiid,$putid='0') {
    	return self::$prefix.__FUNCTION__.$weiid.'_'.$putid;
    }
    
    /**
	 * 圣诞派总数缓存
	 */
    static function christmas_new_count($actid,$userid) {
    	return self::$prefix.__FUNCTION__.$actid.'_'.$userid.'a';
    }
	/**
	 * 圣诞派每多少天总数缓存
	 */
    static function christmas_new_counta($actid,$userid,$ceils) {
    	return self::$prefix.__FUNCTION__.$actid.'_'.$userid.'_'.$ceils;
    }
    
    /**
	 * 博博乐缓存
	 */
    static function newchristmas_new_count($actid,$userid) {
    	return self::$prefix.__FUNCTION__.$actid.'_'.$userid.'a';
    }
	 /**
	 * 博博乐每多少天总数缓存
	 */
    static function newchristmas_new_counta($actid,$userid,$ceils) {
    	return self::$prefix.__FUNCTION__.$actid.'_'.$userid.'_'.$ceils;
    }

    /**
     * 
     * maoyuhao
	 * 刮刮卡主表
     * @param $id
     */
    static function get_activity_ggk_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }

    /**
     * maoyuhao
	 * 福袋主表
     * @param $id
     */
    static function get_lucky_bag_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    /**
     * wangshen
     * 大转盘主表
     * @param $id
     */
    static function get_activity_bigwheel_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }

    /**
     * wangshen
     * 魔法星星主表
     * @param $id
     */
    static function get_activity_star_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    /**
     * wangshen
     * 砸金蛋主表
     * @param $id
     */
    static function get_activity_egg_id($id)
    {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
	/**
	 * 微政务 - 人员
	 * @author zcc
	 * @param id 人员id
	 */
    static function gov_person($id) {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	/**
	 * 微政务 - 部门
	 * @author zcc
	 * @param id 部门id
	 */
    static function gov_department($id) {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	/**
	 * 微政务 - 窗口
	 * @author zcc
	 * @param id 窗口id
	 */
    static function gov_window($id) {
    	return self::$prefix.__FUNCTION__.$id;
    }
	
	/**
	 * 微政务 - 政厅子菜单
	 * @author zcc
	 * @param id 窗口id
	 */
    static function gov_menu($id) {
    	return self::$prefix.__FUNCTION__.$id;
    }

    static function get_vcard_id($id) {
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    /**
     * 维达定制 - 会员信息
     * @author maojingjing@dodoca.com
     * @param id 用户id
     */
    static function get_vinda_member_id($id){
    	return self::$prefix.__FUNCTION__.$id;
    }
    
    /**
     * 消息模板接收员绑定标示
     */
    static function message_temp_receve($userid) {
        return self::$prefix.__FUNCTION__.$userid;
    }
    
    /**
     * 消息模板设置表
     */
    static function message_temp_set($userid) {
        return self::$prefix.__FUNCTION__.$userid;
    }
    
    
    /**
     * 单条新闻缓存key
     */
    static function get_news_content($newsid) {
        return self::$prefix.__FUNCTION__.$newsid;
    }

    
    /**
     * 升学在线微网首页广告缓存key
     */
    static function get_home_bannerid() {
        return self::$prefix.__FUNCTION__.'bannerid';
    }


}
?>