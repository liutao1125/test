<?php 
/**
 * 所有V3接口的基类
 */
class CommonUtilV3
{
	function __construct($argument) {
	}

	function trimString($value)
	{
		$ret = null;
		if (null != $value)
		{
			$ret = $value;
			if (strlen($ret) == 0)
			{
				$ret = null;
			}
		}
		return $ret;
	}

	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr( $length = 32 )
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		}
		return $str;
	}

	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v){
			//	if (null != $v && "null" != $v && "sign" != $k) {
			if($urlencode){
				$v = urlencode($v);
			}
			//s(strtolower($k));
			$buff .= $k. "=" . $v . "&";
			//}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
	
		return $reqPar;
	}

	/**
	 * 	作用：array转xml
	 */
	function arrayToXml($arr)
	{
		$xml = "<xml>";
		foreach ($arr as $key=>$val)
		{
			if (is_numeric($val))
			{
				$xml.="<".$key.">".$val."</".$key.">";

			}
			else
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
		}
		$xml.="</xml>";
		return $xml;
	}

	/**
	 * 	作用：将xml转为array
	 */
	public function xmlToArray($xml)
	{
		//将XML转为array
		$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $array_data;
	}
	public function sub_curl($url,$data,$is_post=1,$datatype="json"){
		$ch = curl_init();
		if(!$is_post){//get 请求
			$url =  $url.'?'.http_build_query($data);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $is_post);
		if($is_post){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$info = curl_exec($ch);
		$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($datatype=="json"){
			return json_decode($info,true);
		}else{
			return $info;
		}
	}
	/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml,$url)
	{
		//初始化curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, 0);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else
		{
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>";
			curl_close($ch);
			return false;
		}
	}

	/**
	 * 	作用：使用证书，以post方式提交xml到对应的接口url
	 */
	function postXmlSSLCurl($xml,$url)
	{
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_URL,$url);
		//设置证书
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
		/* curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, WxPayConf::SSLCERT_PATH);
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, WxPayConf::SSLKEY_PATH); */
		//post提交方式
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		}
		else {
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>";
			curl_close($ch);
			return false;
		}
	}

	/**
	 * 	作用：打印数组
	 */
	function printErr($wording='',$err='')
	{
		print_r('<pre>');
		echo $wording."</br>";
		var_dump($err);
		print_r('</pre>');
	}
}
?>