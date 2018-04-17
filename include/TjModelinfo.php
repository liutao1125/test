<?php
/**
* @author jiahaiming
* 统计数据收集
*/
class TjModelinfo{

	public function settjdata($url, $data){
		
		//url填写格式为Controller/Action

		$model_list = array(
			array("name" => "微网站", "url" => "phonewebsite/website", "keyname" => "id","gettable"=>"Weixinwebsite","tableid"=>"userid","settable"=>"TjPvuvWeiweb","selectname"=>"id"), 
			array("name" => "微网站（自定义）", "url" => "phonewebsitet/websitet", "keyname" => "id","gettable"=>"Weixinwebsitet","tableid"=>"userid","settable"=>"TjPvuvZdyweiweb","selectname"=>"id"), 
			//array("name" => "微电商主页", "url" => "mobilegoodsdp/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvWmall","type"=>1), 
			array("name" => "微电商列表页", "url" => "mobilegoodsdp/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvWmall","type"=>1), 
			array("name" => "微电商详情页", "url" => "mobilegoodsdp/productdetails", "keyname" => "id","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvWmall","type"=>2), 
			array("name" => "微电商分类页", "url" => "mobilegoodsdp/categoryproducts", "keyname" => "cid","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvWmall","type"=>3,"selectname"=>"cid"), 
			array("name" => "点点客海报", "url" => "mobilehandbill/index", "keyname" => "id","gettable"=>"Weixinwebsitey","tableid"=>"userid","settable"=>"TjPvuvWeihb","selectname"=>"id"), 
			array("name" => "微相册", "url" => "mobilephoto/mobilephoto", "keyname" => "cid","gettable"=>"PhotoCategory","tableid"=>"userid","settable"=>"TjPvuvPhoto"), 
			array("name" => "微名片", "url" => "mobilebusiness/index", "keyname" => "uid","gettable"=>"WxBusinesscardClass","tableid"=>"userid","settable"=>"TjPvuvCard","selectname"=>"userid"), 
			array("name" => "微预约", "url" => "mobilemicroapp/index", "keyname" => "appid","gettable"=>"WxMicroAppointmentSet","tableid"=>"userid","settable"=>"TjPvuvMake"), 
			array("name" => "微问卷", "url" => "mobilequestion/index", "keyname" => "qus_id","gettable"=>"WxQuestionNaire","tableid"=>"userid","settable"=>"TjPvuvQuestion"), 
			array("name" => "大转盘", "url" => "phoneactivitybigwheel/index", "keyname" => "id","gettable"=>"ActivityBigwheel","tableid"=>"userid","settable"=>"TjPvuvGamedzp","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "刮刮乐", "url" => "phoneactivityggk/index", "keyname" => "id","gettable"=>"ActivityGgk","tableid"=>"userid","settable"=>"TjPvuvGameggl","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "魔法星星", "url" => "phoneactivitystar/index", "keyname" => "id","gettable"=>"ActivityStar","tableid"=>"userid","settable"=>"TjPvuvGamemfxx","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "砸金蛋", "url" => "phoneactivityegg/index", "keyname" => "id","gettable"=>"ActivityEgg","tableid"=>"userid","settable"=>"TjPvuvGamezjd","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "福袋", "url" => "phoneluckybag/index", "keyname" => "id","gettable"=>"LuckybagInfo","tableid"=>"userid","settable"=>"TjPvuvLuckybag","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "抢红包", "url" => "phoneredpacketqiang/redpacketphone", "keyname" => "id","gettable"=>"WxRedpacketInfo","tableid"=>"userid","settable"=>"TjPvuvHongbao","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "摇红包", "url" => "phoneredpacketz/redpacketphone", "keyname" => "id","gettable"=>"WxRedpacketzInfo","tableid"=>"userid","settable"=>"TjPvuvHongbaoyao","encrypt"=>1,"selectname"=>"id"), 
			array("name" => "全民挖宝", "url" => "wabaophone/help", "keyname" => "mybxid","gettable"=>"WxWabaoXiangzi","tableid"=>"userid","settable"=>"TjPvuvGamewabao"), 
			array("name" => "微助力", "url" => "phoneactivityz/getcode", "keyname" => "id","gettable"=>"WxActivityz","tableid"=>"userid","settable"=>"TjPvuvZhuli","selectname"=>"id"), 
			array("name" => "博博乐", "url" => "newchristmasphone/help", "keyname" => "paraone","gettable"=>"WxChristmasNewInfo","tableid"=>"userid","settable"=>"TjPvuvGamebobole"), 
			//array("name" => "微店铺", "url" => "mobilegoodsdp/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvShop","type"=>1), 
			//array("name" => "微店铺列表页", "url" => "mobilegoodsdp/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvShop","type"=>2), 
			//array("name" => "微店铺详情页", "url" => "/productdetails", "keyname" => "id","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvShop","type"=>3), 
			//array("name" => "微店铺分类页", "url" => "mobilmobilegoodsdpegoodsdp/categoryproducts", "keyname" => "cid","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvShop","type"=>4,"selectname"=>"cid"), 
			//array("name" => "微团购", "url" => "mobilegoodstg/goodstglist", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvTuangou","type"=>1), 
			array("name" => "微团购列表页", "url" => "mobilegoodstg/goodstglist", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvTuangou","type"=>1), 
			array("name" => "微团购详情页", "url" => "mobilegoodstg/goodstgdetails", "keyname" => "id","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvTuangou","type"=>2), 
			//array("name" => "限时购", "url" => "mobilegoodsxs/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvXianshi","type"=>1), 
			array("name" => "限时购列表页", "url" => "mobilegoodsxs/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvXianshi","type"=>1), 
			array("name" => "限时购详情页", "url" => "mobilegoodsxs/xsgoodsinfos", "keyname" => "goodsid","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvXianshi","type"=>2), 
			//array("name" => "微秒杀", "url" => "mobilegoodsms/msindex", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvMiaosha","type"=>1), 
			array("name" => "微秒杀列表页", "url" => "mobilegoodsms/msindex", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvMiaosha","type"=>1), 
			array("name" => "微秒杀详情页", "url" => "mobilegoodsms/msinfo", "keyname" => "id","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvMiaosha","type"=>2), 
			//array("name" => "微竞拍", "url" => "mobilegoodsjp/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvJingpai","type"=>1), 
			array("name" => "微竞拍列表页", "url" => "mobilegoodsjp/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvJingpai","type"=>1), 
			array("name" => "微竞拍详情页", "url" => "mobilegoodsjp/goodsinfos", "keyname" => "goodsid","gettable"=>"WxGoods","tableid"=>"userid","settable"=>"TjPvuvJingpai","type"=>2), 
			
			
			array("name" => "微餐饮点菜列表页（单店）", "url" => "phonetakeorder/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFood","type"=>1), 
			array("name" => "微餐饮点菜详情页（单店）", "url" => "phonetakeorder/cartchg", "keyname" => "id","gettable"=>"WxRepastFood","tableid"=>"userid","settable"=>"TjPvuvFood","type"=>2,"selectname"=>"id"), 
			array("name" => "微餐饮订座列表页（单店）", "url" => "mobilerepastorder/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFood","type"=>3), 
			array("name" => "微餐饮外卖列表页（单店）", "url" => "phonetakeaway/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFood","type"=>4), 
			array("name" => "微餐饮外卖详情页（单店）", "url" => "phonetakeaway/cartchg", "keyname" => "id","gettable"=>"WxRepastFood","tableid"=>"userid","settable"=>"TjPvuvFood","type"=>5,"selectname"=>"id"), 
			
			array("name" => "微餐饮门店列表页（多店）", "url" => "mobilerepastsrestaurant/index", "keyname" => "","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFoods","type"=>6), 
			array("name" => "微餐饮点菜列表页（多店）", "url" => "mobilerepaststakeorder/index", "keyname" => "restaurantid","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFoods","type"=>1), 
			array("name" => "微餐饮点菜详情页（多店）", "url" => "mobilerepaststakeorder/cartchg", "keyname" => "id","gettable"=>"WxRepastsFood","tableid"=>"userid","settable"=>"TjPvuvFoods","type"=>2,"selectname"=>"id"), 
			array("name" => "微餐饮订座列表页（多店）", "url" => "mobilerepastsorder/index", "keyname" => "restaurantid","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFoods","type"=>3), 
			array("name" => "微餐饮外卖列表页（多店）", "url" => "mobilerepaststakeaway/index", "keyname" => "restaurantid","gettable"=>"","tableid"=>"","settable"=>"TjPvuvFoods","type"=>4), 
			array("name" => "微餐饮外卖详情页（多店）", "url" => "mobilerepaststakeaway/cartchg", "keyname" => "id","gettable"=>"WxRepastsFood","tableid"=>"userid","settable"=>"TjPvuvFoods","type"=>5,"selectname"=>"id"), 
		);
		foreach($model_list as $val){
			if($val['url'] == $url){
				if($val['encrypt']){//参数解密
					$setdata['zid'] = (int)PubFun::encrypt(base64_decode($data[$val['keyname']]),'D');
				}else{
					$setdata['zid'] = $data[$val['keyname']];
				}
				if($val['gettable']){//通过条件查询
					$gettable = new $val['gettable']();
					if($val['selectname']){
						$wherestr = "where ".$val['selectname']."=".$setdata['zid'];
					}else{
						$wherestr = "where id=".$setdata['zid'];
					}
					$setdata['userid'] = $gettable->scalar($val['tableid'],$wherestr);
				}else{//通过会话查询
					$setdata['userid'] = $_SESSION['clubdata']['userid'];
				}
				if(!$setdata['userid']){
					$setdata['userid'] = $data['uid'];
				}
				if(!$setdata['userid']){
					$setdata['userid'] = $data['userid'];
				}
				$setdata['ip'] = get_client_ip();
				$setdata['cdate'] = time();
				if($val['type']){
					$setdata['type'] = $val['type'];
				}
				$setdata['province'] = IpaddressApi::getProvinceId($setdata["ip"]);
				if(!empty($setdata['userid'])||!empty($setdata['zid'])){
					$settable = new $val['settable']();
					$settable->insert($setdata);
				}
			}
		}
	}
	
}
?>
