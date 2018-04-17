<?php
/**
 *
* @author dengjianjun@dodoca.com
*
*/
class WxUserFwc extends My_EcSysTable
{
	public $_name ='wx_user_fwc';
	public $_primarykey ='id';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'pubkey');
		$this->fill_str($para,$data,'prikey');
		$this->fill_int($para,$data,'uid');
		$this->fill_str($para,$data,'appid');
		Return $data;
	}
	
	function update_data($data,$uid)
	{
		if(!$uid)return;
		$r=$this->update($data," where uid=".$uid);
		$this->clean_cache($uid);
		return $r;
	}
	function insert_data($data)
	{
		if(!$data)return;
		$r=$this->insert($data);
		return $r;
	}
	
	//获取单个用户服务窗信息
	public function get_fwc_info($uid)
	{
		$db = $this->getAdapter();
		$sql = "select * from ".$this->_name." where `uid`=$uid limit 1";	//. $orderby_clause	." limit ".($currpage-1)*$page_size.','.$page_size;
		$rs = $db->scalar($sql);
		return $rs;
	}
	/**
	 * 获取绑定参数
	 */
	public function get_bind_info($uid){
		$data = $this->get_user_rsa($uid);
		$info["pubkey"] = $data["pubkey"];
		$wxuser = new WxUser();
		$wxuserinfo = $wxuser->get_row_byid($uid);
		$userkey = $wxuserinfo["userkey"];
		$server=$_SERVER['HTTP_HOST'];
		$url = "http://data.dodoca.com/fwc/3/{$userkey}/";
		if(strpos($server,"t.")!==false)//测试环境
		{
			$url = "http://t.data.dodoca.com/fwc/3/{$userkey}/";
		}
		$info["url"] = $url;
		return json_encode($info);
	}
	     
	/**
	 * 获取用户公钥、私钥
	 */
	public function get_user_rsa($uid)
	{
		if(!$uid) return;
		//$mc_key=CacheKey::get_userrsa_key($uid);
		//$data=mc_get($mc_key);
		//if(!$data)
		//{
			$data=$this->scalar("*"," where uid=".$uid);
			if($data && $data["pubkey"] && $data["prikey"])
			{
				//mc_set($mc_key,$data,36000);
			}
			else
			{
				$rs=$this->create_rsa($uid);
				if(!$rs)return;
				$data["pubkey"]=$rs["pubkey"];
				$data["prikey"]=$rs["prikey"];
				if($data["uid"])
				{
					$this->update_data($rs,$uid);
				}
				else
				{
					$data["uid"]=$uid;
					$rt=$this->insert_data($data);
					if($rt)
					{
						return;
					}
				}
				//mc_set($mc_key,$data,36000);
			}
		//}
		return $data;
	}
	
	/**
	 * 清除用户缓存
	 */
	public function clean_cache($uid)
	{
		$mc_key=CacheKey::get_userrsa_key($uid);
		mc_unset($mc_key);
	}
	
	/**
	 * 创建用户公钥、私钥
	 */
	public function create_rsa($uid)
	{
		if(!$uid)return;
		$ret=array();
		$app_url='/usr/bin/openssl';
		$base_url='/usr/local/wwwroot/www_log';
		if($_SERVER["SYS_RELEASE"]=='2')//生产环境
		{
			$app_url='/usr/bin/openssl';
			$base_url="/data/wwwroot/www_log";
		}
		$out_file_private=$base_url."/rsa_private_key_$uid.pem";
		$exec=$app_url." genrsa -out ".$out_file_private." 1024";//私钥
		exec($exec,$ret);
		$out_file_public=$base_url."/rsa_public_key_$uid.pem";
		$exec=$app_url."  rsa -in $out_file_private -pubout -out $out_file_public";
		exec($exec,$ret);
		clearstatcache();
		$private_data='';
		$public_data='';
		if(!is_file($out_file_private) || !is_file($out_file_public))//秘钥文件创建失败
		{
			return;
		}
		$private_data=file_get_contents($out_file_private);
		$public_data=file_get_contents($out_file_public);
		$t=array();
		if($private_data && $public_data)
		{
			$t["prikey"]=$private_data;
			$temp = trim(str_replace(array('-----BEGIN PUBLIC KEY-----','-----END PUBLIC KEY-----'),'',$public_data));
			$t["pubkey"] = str_replace(PHP_EOL,'', $temp);
		}
		@unlink($out_file_private);
		@unlink($out_file_public);
		return $t;
	}
	
}