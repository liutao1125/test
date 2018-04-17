<?php
/**
 * 
 * 优惠券
 * @author wangyu
 *
 */
class Coupons
{
	/**
	 * 通过优惠券序列号码获取优惠信息
	 * $code->优惠券序列号
	 * @return array
	 * $data["coupons"]->券种信息
	 * $data["codeinfo"]->子券数据
	 */
	static public function get_info_bycode($code)
	{
		$table_change = array("'"=>"","\""=>"","<"=>"",">"=>""," "=>""); 
		$code=strtr($code,$table_change);
		
		if(empty($code))return;
		
		$CouponsInfoMod = new CouponsInfo();
		$CouponsSubtabulationMod = new CouponsSubtabulation();
		
		
		$CouponsSubtabulationData = $CouponsSubtabulationMod->get_row_bywhere(" where coupons_code = '$code'");
		
		if($CouponsSubtabulationData){
			$CouponsInfoData = $CouponsInfoMod->get_row_byid($CouponsSubtabulationData['coupons_info_id']);
			if($CouponsInfoData){
				$data['coupons'] = $CouponsSubtabulationData;
				$data['codeinfo'] = $CouponsInfoData;
			}
		}

		$CouponsInfoMod->close();
		$CouponsSubtabulationMod->close();
		
		return $data;
	}

	
	/**
	 * 通过优惠券序列号码使用优惠券
	 * 
	 * $id->优惠券子表coupons_subtabulation 主键
	 * $userid->userid
	 * $byholdtime->是否验证占用时间 0：不验证  1：验证
	 * $verification_type->核销渠道类型 1：主账号核销  2： 子账户核销  3：杉德扫码核销
	 * $verification_type_id->核销渠道id
	 * $verification_uid->核销员
	 * 
	 * @return array
	 * $data["status"]->使用状态 1：成功  2：失败
	 * $data["cont"]->操作结果说明
	 */
	static public function verification_byid($id,$userid,$byholdtime=0,$verification_type=0,$verification_type_id=0,$verification_uid=0)
	{

		if(empty($id) || !is_numeric($id))return array('status'=>2,'cont'=>'优惠券数据错误');
		
		$data = array();
		
		$CouponsInfoMod = new CouponsInfo();
		$CouponsSubtabulationMod = new CouponsSubtabulation();
		
		$CouponsSubtabulationData = $CouponsSubtabulationMod->get_row_byid($id);
		
		if($CouponsSubtabulationData){
			$storefront_verify = 1;
			$CouponsInfoData = $CouponsInfoMod->get_row_byid($CouponsSubtabulationData['coupons_info_id']);
			if (!empty($verification_uid) && $CouponsInfoData['storefront_id']!='0' && !empty($CouponsInfoData['storefront_id'])){ //增加了当前传入子账户的判断 ailiya
				$storefront_verify = 0;
				$userinfo = User::get_user_info($verification_uid);
				$storefront_id_array = explode(',',$CouponsInfoData['storefront_id']);
				foreach ($storefront_id_array as $storefront_id){
					$storefront_id == $userinfo['storefront']?$storefront_verify = 1 : null;
				}
			}
			if($storefront_verify == 1){
				if($CouponsSubtabulationData['status'] == 1){
					if($CouponsInfoData['status'] == 1){
						if($CouponsSubtabulationData['start_datetimes'] <= time() && $CouponsSubtabulationData['end_datetimes'] >= time()){
							if($byholdtime==0 || $CouponsSubtabulationData['holdtime']<time()){
								$verification_data['status'] = 2;
								$verification_data['verification_type'] = $verification_type;
								$verification_data['verification_type_id'] = $verification_type_id;
								$verification_data['verification_uid'] = $verification_uid;
								$verification_data['verification_name'] = Coupons::get_verification_name($verification_type, $verification_type_id);
								$verification_data['verification_datetimes'] = time();
								$flag = $CouponsSubtabulationMod->update_data($verification_data, " id = $id and userid = $userid", $id);
								if($flag){
									$data = array('status'=>1,'cont'=>'使用成功');
								}else{
									$data = array('status'=>2,'cont'=>'优惠券数据错误');
								}
							}else{
								$data = array('status'=>2,'cont'=>'该优惠券处于占用状态');
							}
						}else{
							$data = array('status'=>2,'cont'=>'该优惠券不在有效期');
						}
					}else{
						$data = array('status'=>2,'cont'=>'该券不属于点点客平台优惠券');
					}
				}else{
					$data = array('status'=>2,'cont'=>'优惠券已被使用');
				}
			}else{
				$data = array('status'=>2,'cont'=>'您无权操作其他门店的优惠券');
			}	
		}else{
			$data = array('status'=>2,'cont'=>'优惠券数据错误');
		}
		
		$CouponsInfoMod->close();
		$CouponsSubtabulationMod->close();
		
		return $data;
	}
	

	/**
	 * 
	 * 获取核销渠道名称
	 * 
	 * $verification_type-> 核销渠道类型 1：主账号核销  2： 子账户核销   3：杉德扫码核销
	 * $verification_type_id-> 核销渠道id
	 */
	private function get_verification_name($verification_type,$verification_type_id){
		
		$verification_name = "总店";
		
		//子账号核销
		if($verification_type == 2){
			$verification_name = "分店";
			
			$userinfo = User::get_user_info($verification_type_id);
			if(!empty($userinfo['storefront'])){
				$StorefrontInfoMod = new StorefrontInfo();
				$StorefrontInfoData = $StorefrontInfoMod->get_row_byid($verification_type_id);
				if(!empty($StorefrontInfoData['storefront_name'])){
					$verification_name = $StorefrontInfoData['storefront_name'];
				}
			}
		}elseif($verification_type == 3){
			$verification_name = "杉德扫码";
		}
		
		return $verification_name;
	}
	
	/**
	 * 
	 * 记录优惠券的发放渠道
	 * 
	 * $coupons_info_id->优惠券种表coupons_subtabulation 主键
	 * $type-> 发放活动渠道  1：新刮刮卡 2：福袋 3：单图文 4：多图文 5：新大转盘 6：魔法星星 7：砸金蛋 8：带参二维码 9:爱贴贴
	 * $type_id-> 外键关联活动表主键
	 * $userid->userid
	 * @return int
	 */
	static function insert_issue_channel($coupons_info_id,$type,$type_id,$userid)
	{
		
		$CouponsIssueChannelMod = new CouponsIssueChannel();
		$count = $CouponsIssueChannelMod->selectallcount(" where coupons_info_id = $coupons_info_id and type = $type and type_id = $type_id");
		
		if(!$count){
			$data['coupons_info_id'] = $coupons_info_id;
			$data['type'] = $type;
			$data['type_id'] = $type_id;
			$data['userid'] = $userid;
			$flag = $CouponsIssueChannelMod->insert_data($data);
		}
		
		$CouponsIssueChannelMod->close();
		return $flag;
	}
	
	/**
	 * 
	 * 获取领券URL
	 * 
	 * $coupons_info_id->优惠券种表coupons_subtabulation 主键
	 * $type-> 发放活动渠道  1：新刮刮卡 2：福袋 3：单图文 4：多图文 5：新大转盘 6：魔法星星 7：砸金蛋 8：带参二维码 9:爱贴贴
	 * $type_id-> 外键关联活动表主键
	 * $openid->所属用户
	 * $data->所需其他参数
	 * @return int
	 */
	static function get_coupons_url($coupons_info_id,$type,$type_id,$openid=0,$data="")
	{
		$userid_url = "";
		
		$CouponsInfoMod = new CouponsInfo();
		$CouponsInfoData = $CouponsInfoMod->get_row_byid($coupons_info_id);
		
		if(!empty($CouponsInfoData['userid'])){
			$userid_url = "/".$CouponsInfoData['userid'];
		}
		$coupons_array = array('coupons_info_id'=>$coupons_info_id,'type'=>$type,'type_id'=>$type_id,'openid'=>$openid,'data'=>$data);
		$url = BASE_DOMAIN . $userid_url . "/mobilecoupons/getcoupons?wechat_card_js=1&lq_data=".base64_encode(PubFun::encrypt(json_encode($coupons_array),'E'));
		if(empty($openid)){
			$url .= "&openid=OPEN_ID_E";
		}
		$url .= "#wechat_redirect";
		return $url;
	}
}  
