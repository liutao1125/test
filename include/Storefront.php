<?php
/**
 * 
 * 获取门店对象
 * @author wangyu
 *
 */
class Storefront
{
    static public function access(){

		$user_info = User::get_user_info(get_uid());
		
		if(empty($user_info['storefront'])){
						
			return 0;
			
		}else{
			
			$StorefrontInfoMod = new StorefrontInfo();
			$StorefrontInfoData = $StorefrontInfoMod->get_row_byid($user_info['storefront']);
			if(empty($StorefrontInfoData) || empty($StorefrontInfoData['id'])){
			
				return 0;
				
			}else{
				return $StorefrontInfoData;
			}
			
		}
    }
  
}

?>