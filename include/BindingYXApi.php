<?php
/*
 * 绑定易信api
 * @author zcc
 */

class BindingYXApi {
	
	private $username;		//易信登陆账号
	private $password;		//易信登陆密码
	private $token;			//点客用户token
	private $cookie;		//本次登陆易信cookie
	
	public function __construct($username='',$password='',$token='') {
		$this->username = $username;
		$this->password = $password;
		$this->token = $token;
	}
	
	//模拟登陆
    public function login() {
		if(!$this->username && !$this->password) {
			echo json_encode(array('state'=>'2','cont'=>'用户名和密码必填')); exit;
		}
		$key=md5($this->username).'cookie';	//定义cookie缓存
		mc_unset($key); //清除cookie
		if(mc_get($key)) {
			$cookie=mc_get($key);
		}
		if(empty($cookie)) {
			$url = "https://cas.yixin.im/login";
			$referer="https://plus.yixin.im/login?service=https://plus.yixin.im/index";
			$data = array(
				'username'	=>	$this->username,
				'password'	=>	md5($this->password),
				'passwordx'	=>	$this->password,
				'auto'		=>	'true',
				'login_from'=>	'https://plus.yixin.im/login',
				'service'	=>	'https://plus.yixin.im/index',
			);
			$cont = $this->curl_submit($url,$data,$referer); //print_r($cont); exit;
			if(!strstr($cont['body'],'doAutoLogin()')) {
				echo json_encode(array('state'=>'9','cont'=>'网络异常，请稍后重试')); exit;
			}
			//302重定向
			//https://plus.yixin.im/index?ticket=ST-22982-7XA5pwy0ubqIQNWJCa10-cas.yixin.im
			//https://plus.yixin.im/index?ticket=ST-23229-MSzlUaoExwcchzumrkLd-cas.yixin.im
			//https://plus.yixin.im/index?ticket=ST-23223-ACh9jKdtJsIpcj1wDve4-cas.yixin.im
			$url2 = "https://cas.yixin.im/login?service=https://plus.yixin.im/index";
			$referer2 = 'https://cas.yixin.im/index';
			preg_match_all('/(input[^\>]+\>)/i',$cont['body'],$twoarr);
			if(isset($twoarr[1]) && is_array($twoarr[1])) {
				foreach($twoarr[1] as $v) {
					if(preg_match('/name="lt" value="([0-9a-z-A-Z-]+)"/i',$v,$twoarr1)) {
						$lt = $twoarr1[1];
					}
					if(preg_match('/name="execution" value="([0-9a-z-A-Z-]+)"/i',$v,$twoarr1)) {
						$execution = $twoarr1[1];
					}
					if(preg_match('/name="_eventId" value="([0-9a-z-A-Z-]+)"/i',$v,$twoarr1)) {
						$_eventId = $twoarr1[1];
					}
					if(preg_match('/name="username" value="([^\"]+)"/i',$v,$twoarr1)) {
						$username = $twoarr1[1];
					}
					if(preg_match('/name="password" value="([^\"]+)"/i',$v,$twoarr1)) {
						$password = $twoarr1[1];
					}
					if(preg_match('/name="login_from" value="([^\"]+)"/i',$v,$twoarr1)) {
						$login_from = $twoarr1[1];
					}
					if(preg_match('/name="captcha" value="([^\"]+)"/i',$v,$twoarr1)) {
						$captcha = $twoarr1[1];
					}
				}
			}
			$data2 = array(
				'lt'		=>	$lt,
				'execution'	=>	$execution,
				'_eventId'	=>	$_eventId,
				'username'	=>	$username,
				'password'	=>	$password,
				'login_from'=>	$login_from,
				'captcha'=>	empty($captcha)?"":$captcha,
			
			);//print_r($data2); exit;
			$cont = $this->curl_submit($url2,$data2,$referer2); //print_r($cont); exit;
			
			$res = $this->csumbit('https://plus.yixin.im/index','','GET',$this->cookie);
			echo json_encode(array('state'=>'-1','cont'=>'网络异常,请稍后再试','mess'=>$cont)); exit; 
			if($cont) {
				mc_set($key,$cont['cookie'],600);
				$cookie = $cont['cookie'];
				/*$res = json_decode($cont['body']);
				if($res->message=='' && $res->code=='200') {
					if($res->result=='') {
						mc_set(md5($this->username).'cookie',$cont['cookie']);
						$cookie = $cont['cookie'];
					} else {
						echo json_encode(array('state'=>'3','cont'=>'公众平台尚未审核通过或未完善公众平台信息')); exit;
					}
				} else {
					//{"message":"","result":"","code":200}		=>	成功
					//{"message":"验证码错误","result":"","code":401}
					//{"message":"用户名或密码输入错误","result":"","code":400}
					echo json_encode(array('state'=>'4','cont'=>$res->message,'code'=>$res->code,'message'=>'1'.$cont['body'])); exit;
				}*/
			} else {
				echo json_encode(array('state'=>'-1','cont'=>'网络异常,请稍后再试','mess'=>'login')); exit;
			}
		}
		
		$this->cookie = $cookie;
		if($cookie) {
			$this->openkfbutton();
		}
    }
	
	//关闭自动回复并开启开发模式
	public function openkfbutton() {
		$url = 'https://plus.yixin.im/rest/advanced';
		$data = '{"autoreply":false,"develop":true,"customMenu":false}';
		$res = $this->csumbit($url,$data,'POST',$this->cookie);
		if($res) {
			$cont = json_decode($res);
			if($cont->code=='200' && $cont->result=='1') {
				$this->setmess();
			} else {
				//{"message":"","result":1,"code":200}
				echo json_encode(array('state'=>'5','cont'=>$cont->message,'code'=>$cont->code,'cookie'=>$this->cookie)); exit;
			}
		} else {
			echo json_encode(array('state'=>'-1','cont'=>'网络异常,请稍后重试','mess'=>'openkf')); exit;
		}
	}
	
	//配置开发着信息
	public function setmess() {
		$interfaceurl = API_DOMAIN."/wx/2/".$this->token."/";
		$url = 'https://plus.yixin.im/rest/advanced/developer';
		$data = '{"url":"'.$interfaceurl.'","token":"'.$this->token.'"}';
		$res = $this->csumbit($url,$data,'POST',$this->cookie);
		if($res) {
			$cont = json_decode($res);
			if($cont->code=='200' && $cont->result==true) {
				$this->getmess();
			} else {
				//{"message":"","result":true,"code":200}
				echo json_encode(array('state'=>'6','cont'=>$cont->message,'code'=>$cont->code)); exit;
			}
		} else {
			echo json_encode(array('state'=>'-1','cont'=>'网络异常,请稍后重试','mess'=>'setmess')); exit;
		}
	}
	
	//获取易信用户信息
	public function getmess() {
		$url = 'https://plus.yixin.im/advanced/developer';
		$res = $this->csumbit($url,'','GET',$this->cookie);
		if($res) {
			//AppID	AppSecret
			if(preg_match_all('/<span class=\"u-ipt\">([a-zA-Z0-9]+)<\/span>/',$res,$arr1)) {
				if($arr1[1]) {
					$AppID = $arr1[1][0];
					$AppSecret = $arr1[1][1];
				}
			}
			//wsid 公众号名 <a  href="/set">gamephp</a>
			if(preg_match('/<a[ ]+href=\"\/set\">([^>]+)<\/a>/',$str,$arr2)) {
				if($arr2 && $arr2[1]) {
					$wsid = $arr2[1];
				}
			}
		} else {
			echo json_encode(array('state'=>'7','cont'=>'未获取到用户信息')); exit;
		}
		mc_unset(md5($this->username).'cookie'); //清除cookie
		//更新用户易信记录	
		$uid = get_uid();
		$user_info=User::get_user_info($uid);
		$WxUserAccount = new WxUserAccount();
		$WxUserAccount->insert_data(array(
			'account_username'	=>	$this->username,
			'account_password'	=>	$this->password,
			'userid'			=>	$uid,
			'account_type'		=>	'2',
			'appid'				=>	$AppID,
			'appsecret'			=>	$AppSecret,
			'wsid'				=>	$wsid,
			'nick_name'			=>	$user_info['link_name'],
			'status'			=>	'1',
			'is_band'			=>	'1',
			'api_status'		=>	'1'
		));
		echo json_encode(array('state'=>'1','cont'=>'绑定成功')); exit; 
	}
	
	//curl模拟提交
	public function curl_submit($url,$data,$referer,$cookie=false,$isPost=true) {
		$dataStr = "";
		if($data && is_array($data)) {
			foreach($data as $key => $value) {
				$dataStr .= "$key=$value&";
			}
		}else $dataStr.=$data;
		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在		
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		// curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Referer:$referer"));
		
		if($isPost) {
			curl_setopt($curl, CURLOPT_POST, 0); // 发送一个常规的Post请求
			curl_setopt($curl, CURLOPT_POSTFIELDS, $dataStr); // Post提交的数据包
		}
		
		curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		
		if($cookie) {
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		}
		
		$tmpInfo = curl_exec($curl); // 执行操作
		if (curl_errno($curl)) {	//抓取异常
			echo json_encode(array('state'=>'-1','cont'=>curl_error($curl),'mess'=>'curl_1'.$url)); exit;
		}
		
		curl_close($curl); // 关闭CURL会话
		// 解析HTTP数据流
		list($header, $body) = explode("\r\n\r\n", $tmpInfo);
		
		if(!$cookie) {
			// 解析COOKIE
			$cookie = "";
			preg_match_all("/Set\-cookie: (.*)/i", $header, $matches);
			if(count($matches == 2)) {
				foreach($matches[1] as $each) {
					$cookie .= trim($each). ";";
				}
			}
		}
		$rs=array("cookie" => $cookie, "body" => trim($body),"ALL"=>trim($tmpInfo));
		return $rs;
	}
	
	//构造简易curl
	public function csumbit($url,$data='',$method='POST',$cookie=false) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
		$cont = curl_exec($ch);
		
		if (curl_errno($ch)) {	//抓取异常
			echo json_encode(array('state'=>'-1','cont'=>curl_error($ch),'mess'=>'curl_2'.$url)); exit;
		}
		curl_close($ch);
		return $cont;
	}

}
?>