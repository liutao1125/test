<?php
/**
 * 维达通用方法
 * @author
 */
header("Content-Type:text/html; charset=utf-8");
class VindaApi{
	/*
	 * 扫码注册会员
	* $n_id 网点id, $openid ,$name会员名
	*/
	static function registermember($open_id,$name=""){
		if(!$open_id) return array('status'=>-1, 'msg'=>'参数不正确');
		$membermod = new VindaMember();
		if($membermod->get_userid($open_id)){
			return array('status'=>-1, 'msg'=>'用户已关注');
		}
	
		$data['card'] = self::createmembercard();
		$data['n_id'] = $n_id;
		$data['open_id'] = $open_id;
		$data['name'] = $name;
		$id = $membermod->insert_data($data);
		$membermod->close();
		if($id){
			return array('status'=>1, 'id'=>$id);
		}else{
			PubFun::save_log("n_id->".$n_id.",fans_id->".$open_id.":创建失败", 'registermember_log');
			return array('status'=>-1, 'msg'=>'创建失败');
		}
	}
	
	/*
	 * 生成会员卡号
	*/
	static function createmembercard(){
		$card = "9";
		$num = rand(1, 900000000);
		$filter_arr = array('0','1','2','3','5','6','8','9');
		$filter_4 = $filter_arr[array_rand($filter_arr)];
		$filter_7 = $filter_arr[array_rand($filter_arr)];
		$num = str_replace('4', $filter_4, $num);
		$num = str_replace('7', $filter_7, $num);
		$card .= sprintf("%09d", $num);
		$member = new VindaMember();
		if($member->get_count(" card='{$card}'")){
			$card = self::createmembercard();
		}
		$member->close();
		return $card;
	}
	


	/*
	 * 发放积分
	 * $type  '首次关注;完善个人资料;每日签到;兑换产品'
	*/
	static function givepoints($type, $points, $member_id){
		if($member_id){
			
		}
	}
}