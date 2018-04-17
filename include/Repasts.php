<?php
/**
 * 
 * 餐饮多店版 子账户权限控制
 * @author wangyu
 *
 */
class Repasts
{
     static public function access(){

		$user_info = User::get_user_info(get_uid());
		
		if(empty($user_info['repasts_restaurant'])){
						
			return 0;
			
		}else{
			
			$repast_restaurantmod = new WxRepastsrestaurant();
			$repast_restaurant = $repast_restaurantmod->get_one($user_info['repasts_restaurant']);
			if(empty($repast_restaurant) || empty($repast_restaurant['id'])){
			
				return 0;
				
			}else{
				return $repast_restaurant;
			}
			
		}
     }
     
	static public function access_apartment(){

		$user_info = User::get_user_info(get_uid());
		if(empty($user_info['apartment'])){
						
			return 0;
			
		}else{
			
			$wxapartmentinfomod = new WxApartmentInfo();
			$apartmentinf = $wxapartmentinfomod->get_one($user_info['apartment'],$user_info['parent_uid']);
			if(empty($apartmentinf) || empty($apartmentinf['id'])){
			
				return 0;
				
			}else{
				return $apartmentinf;
			}
			
		}
     }
}

?>