<?php
class WeixinApi{
	//权限
	private $legalAct = array(
		'editcard'
	);
	public static $einfo;//错误消息列表
	public static $uid;
	public function __construct($act){
		if (empty($act) || !in_array(strtolower($act), $this->legalAct)){
			self::resendMsg(1004, '非法操作');
		}
		$this->auth();
	}
	public function auth(){
		$args = array(
			'userid',
			'key'
		);
		foreach ($args as $v){
			if (empty($_GET[$v]) || !isset($_GET[$v])){
				self::resendMsg('1001','缺少参数');
			}
		}
		$WxInterfaceSwitching = new WxInterfaceSwitching();
		$userinfo = $WxInterfaceSwitching->scalar("*","where userid='{$_GET['userid']}'");
		if($userinfo){
			if($userinfo['status'] != 1){
				self::resendMsg(1005,"接口关闭");
			}
			$keyword = md5($userinfo['password'].date("Ymd"));
			if($keyword != $_GET['key']){
				self::resendMsg(1002,"认证失败");
			}
			$bip=get_client_ip();
			if($bip != $userinfo['ip']){
				self::resendMsg(1002,"认证失败");
			}
			self::$uid = $_GET['userid'];
		}else{
			self::resendMsg(1002,"认证失败");
		}
	}

//错误消息处理
	public function sendMsg($num, $msg = '', $result = null){
		if (empty($msg)){
			switch ($num){
				case '200':
					$msg = '成功';
					break;
				case '1001':
					$msg = '缺少参数';
					break;
				case '1002':
					$msg = '认证失败';
					break;
				case '1003':
					$msg = '参数格式错误';
					break;
				case '1004':
					$msg = '非法参数';
					break;
				case '1005':
					$msg = '没有数据';
					break;
				case '404':
					$msg = '操作失败';
					default:
				break;
			}
		}
		if ($result){
			$_RE['id'] = $result;
		}
		$_RE['error'] = $num;
		$_RE['message'] = $msg;
		self::$einfo[]=$_RE;
	}
	//状态返回
	private static function resendMsg($num, $msg = '', $result = null){
		if ($result){
			$_RE['content'] = $result;
		}
		$_RE['error'] = $num;
		$_RE['message'] = $msg;
		//$_RE = iconv_array('gbk', 'utf-8', $_RE);
		echo json_encode($_RE);
		exit();
	}
	
	public function editcard($data){
		$dotype = $data['dotype'];
		$WxCardType = new WxCardType();
		$WxMemberInfo = new WxMemberInfo();
		if($dotype=='add'||$dotype=='modify'){
			$caid=$WxCardType->scalar("id","where typeName='{$data['type']}' and userid".self::$uid);
			if(!$id){
				$data2['typeName'] = $data['type'];
				$data2['userid'] = self::$uid;
				$caid = $WxCardType->insert_data($data2);
			}
			$data1['memberName'] = $data['username'];
			$data1['memberTel'] = $data['mobile'];
			$data1['userid'] = self::$uid;
			$data1['cardid'] = $caid;
			$WxMemberInfo->insert_data($data1);
		}else{
			$sql = "SELECT a.memberId,b.cardId FROM wx_member_info AS a INNER JOIN wx_card_type AS b ON b.cardId = b.cardId WHERE a.memberName = '".$data['username']."' AND a.memberTel = '".$data['mobile']."' AND b.typeName = '".$data['type']."' AND a.userid = '".self::$uid."'";
			$info = $WxMemberInfo->fetchAll($sql);
			if($info){
				$WxCardType->delete("where cardId=".$info[0]['cardId']);
				$WxMemberInfo->delete("where memberId=".$info[0]['memberId']);
			}else{
				self::resendMsg(1005, '信息错误');
			}
		}
	}
}
?>
