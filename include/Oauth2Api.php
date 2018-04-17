<?php
/*
 * @author2 zhangyong 用户授权API
 */
class Oauth2Api {
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
	
	//代付授权
	public static function daifuoauth2_snsapi_base($userid,$url)
	{
		$WxUser=new WxUser();
		$wxuseraccount=$WxUser->get_user_account($userid,1);
		if($wxuseraccount['appid'])
		{
			$appid=$wxuseraccount['appid'];
			$oauth_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=dodoca&connect_redirect=1#wechat_redirect";
		}
		return $oauth_url;
	}
	
	//获取授权用户信息
	public static function authuserinfo_snsapi_base($userid,$code,$type)
	{
		$WxUser=new WxUser();
		$wxuseraccount=$WxUser->get_user_account($userid,$type);
		if(!empty($wxuseraccount['appid'])&&!empty($wxuseraccount['appsecret']))
		{
			$info=array();
			$appid=$wxuseraccount['appid'];
			$secret=$wxuseraccount['appsecret'];
			$url="https://api.weixin.qq.com/sns/oauth2/access_token";
			$data=array();
			$data['appid']=$appid;
			$data['secret']=$secret;
			$data['code']=$code;
			$data['grant_type']="authorization_code";
			$info=WeiXin::sub_curl($url,$data,$is_post=0);
			$info=json_decode($info,true);
			if($info["errcode"]=='40029')
			{
	
			}else{
				$refresh_token=$info['refresh_token'];
				$url="https://api.weixin.qq.com/sns/oauth2/refresh_token";
				$data=array();
				$data['appid']=$appid;
				$data['grant_type']="refresh_token";
				$data['refresh_token']=$refresh_token;
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
}
?>