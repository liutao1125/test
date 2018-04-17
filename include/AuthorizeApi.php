<?php
/**
 * 微信网页授权
 * @author zcc
*/

class AuthorizeApi{
	
	private $userid;
	private $url;
	
	public function __construct($userid,$url) {
		$this->userid = $userid;	//用户userid
		$this->url = $url;			//调用者调回的url
	}
	
	//获取code(第一步)
	public function getOpenInfo() {
// 	    $DzDebugLog = new DzDebugLog();
// 	    $DzDebugLog->insert(array('descript'=>'start Authorize','jsondata'=>'in getOpenInfo'));
		$WxUser = new WxUser();
		$wxuseraccount = $WxUser->get_user_account($this->userid,1);
		
		$appid = $wxuseraccount['appid'];
		$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].'/authorize/getcode?url='.urlencode($this->url);
		$state = $this->userid.'-'.get_session('uid');
		if($wxuseraccount['is_auth']) {
			if($wxuseraccount['account_attribute']!='1') {
				header("Location:/authorize/error?msg=".urlencode("公众账号类型配置错误，请与管理员联系。")); exit;
			}
			if(!$wxuseraccount['appid'] || !$wxuseraccount['appsecret']) {
				header("Location:/authorize/error?msg=".urlencode("公众账号appid或appserver未填写，请与管理员联系。")); exit;
			}
			if(SYS_RELEASE == '1') { //测试环境 
				$component_appid = $GLOBALS['Component_Config']['test']['component_appid'];
			}elseif(SYS_RELEASE == '2') {
				$component_appid = $GLOBALS['Component_Config']['sc']['component_appid'];
			}
			$oauth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($redirect_uri)."&response_type=code&scope=snsapi_base&state=".$state."#wechat_redirect";
		}else{
			if($wxuseraccount['account_attribute']!='1') {
				header("Location:/authorize/error?msg=".urlencode("公众账号类型配置错误，请与管理员联系！")); exit;
			}
			if(!$wxuseraccount['appid'] || !$wxuseraccount['appsecret']) {
				header("Location:/authorize/error?msg=".urlencode("公众账号appid或appserver未填写，请与管理员联系！")); exit;
			}
			$oauth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($redirect_uri)."&response_type=code&scope=snsapi_base&state=".$state."#wechat_redirect";
		}
		header("Location:".$oauth_url);
	}
	
	//获取openid(第二步)
	public function getOpenid($code) {
		$WxUser = new WxUser();
		$wxuseraccount = $WxUser->get_user_account($this->userid,1);
		$appid = $wxuseraccount['appid'];
		$secret = $wxuseraccount['appsecret'];
		$wxuseraccount['is_auth'] = 0;
		if($wxuseraccount['is_auth']) {
			if(SYS_RELEASE == '1') { //测试环境 
				$component_appid = $GLOBALS['Component_Config']['test']['component_appid'];
			}elseif(SYS_RELEASE == '2') {
				$component_appid = $GLOBALS['Component_Config']['sc']['component_appid'];
			}
			$component_access_token = mc_get("component_access_token_".$component_appid);
			$url="https://api.weixin.qq.com/sns/oauth2/component/access_token";
			$data = array ();
			$data ['appid'] = $appid;
			$data ['secret'] = $secret;
			$data ['code'] = $code;
			$data ['grant_type'] = "authorization_code";
			$data ['component_appid'] = $component_appid;
			$data ['component_access_token'] = $component_access_token;
		}else{
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token";
			$data = array ();
			$data ['appid'] = $appid;
			$data ['secret'] = $secret;
			$data ['code'] = $code;
			$data ['grant_type'] = "authorization_code";
		}
		$info=WeiXin::sub_curl($url,$data,$is_post=0);
		$info=json_decode($info,true);
		return $info;
// 		if(!$info || $info["errcode"]=='40029') {
// 			return array('state'=>2,'cont'=>'获取用户数据失败,请重新进入！'); exit;
// 		}
		
		
// 		$WxUserFans = new WxUserFans();
// 		$fan = $WxUserFans->get_row_byusername($this->userid,$info['openid']);
// 		if($fan){
// 			$fansid = $fan['id'];
// 			//更新粉丝状态为已关注
// 			$data['status'] = 1;
// 			$where="userid=".$this->userid." and id=$fansid ";
// 			$WxUserFans->update_data($data,$where);
// 		}else{
// 			$fansid = $WxUserFans->insert_data(array('userid'=>$this->userid,'weixin_user'=>$info['openid']));
// 		}
// 		if(strstr($this->url,'OPEN_ID_E')) {		//openid加密过
// 			$url = str_replace('OPEN_ID_E',base64_encode(PubFun::encrypt($fansid,'E')),$this->url);
// 		} else if(strstr($this->url,'OPEN_ID')) {	//未加密
// 			$url = str_replace('OPEN_ID',$fansid,$this->url);
// 		} else {
// 			$url = $this->url;
// 		}
		
// 		header("Location:".$url);
	}
	
}

?>