<?php
/**
 * @author sunshichun@dodoca.com
 *  文字编辑器
 */
class EditorFont
{
	private $uid;
	private $user_info;
	public function __construct($uid){
		$this->uid=$uid;
		$info=User::get_user_info($uid);
		$this->user_info=$info;
	}
   
	//获取登录用户信息
	public function get_auth_info()
	{//http://tp4.sinaimg.cn/1629680283/50/1268738414/1
		if(!$this->user_info)return;
		$realname=$this->user_info["link_name"];
		$ret["code"]=0;
		$ret["message"]='ok';
		$ret["user"]=array(
					"email"=>"",
					"nickname"=>$realname,
				    "avatar_url"=>"",
				    "auth_type"=>""
				);
		
		return $ret;
	}
	
	/**
	 * 获取图片
	 */
	public function get_img_list()
	{
		$img=new EditorImg();
		$rs=$img->fetchAll("select * from ".$img->_name." where uid=".$this->uid." and status=1 order by id asc");
		$img->close();
		$data["code"]=0;
		$data["message"]='ok';
		$t=array();
		if(is_array($rs))
		{
			foreach($rs as $key=>$val)
			{
				$t[]=array(
							"asset_image_id"=>$val["id"],
							"host_context"=>100,
							"hash_value"=>md5(trim($val["img_url"])),
							"target_uri"=>trim($val["img_url"]),
							"created_at"=>'',
							"updated_at"=>''
						);
			}
		}
		$data["data"]=$t;
		return $data;
	}
	
	/**
	 * 获取模板
	 */
	public function get_temp_list()
	{
		$temp=new EditorTemp();
		$rs=$temp->fetchAll("select * from ".$temp->_name." where uid=".$this->uid." and status=1 order by id asc");
		$temp->close();
		$data["code"]=0;
		$data["message"]='ok';
		$t=array();
		if(is_array($rs))
		{
			$baseurl='';
			if(SYS_RELEASE=='2')
			{
				$baseurl="www.dodoca.com";
				if(HTTP_HOST=='www.dodoca.com')
				{
					$baseurl="new.dodoca.com";
				}
			}
			else if(SYS_RELEASE=='1')
			{
				$baseurl="t.new.dodoca.com";
			}
			else
			{
				$baseurl="wx.dodoca.dev";
				if(HTTP_HOST=='wx.dodoca.dev')
				{
					$baseurl="www.xiumi.us";
				}
			}
			foreach($rs as $key=>$val)
			{//WHB_DOMAIN
				$t[]=array(
							"fragment_id"=>$val["id"],
							"fragment_url"=>'http://'.$baseurl.'/editor/tempconext?id='.$val["id"],
							"created_at"=>'',
							"updated_at"=>''
						);
			}
		}
		$data["data"]=$t;
		return $data;
	}
	
	
	
	
}
?>
