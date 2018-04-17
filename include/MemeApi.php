<?php
/**
 *  么么微片API
 * @Author Wills Zeng<duanrong_zeng@126.com>
 * @Version 1.0
 * @Right BitSea
 * @ID AccessToken.php  2013-4-23
 **/
class MemeApi {
	private $url = 'http://fp.memeing.cn/api/v1/postcards/create_wechat_url';//么么微片api地址
	private $imageUrl; //用户上传的图片地址
	private $openId; //用户openId
	private $timestamp; //时间戳
	private $signature; //签名
	private $apiKey = '2zQ6L4erFyAamS9H'; //么么微片提供的key
	private $apiSecret = 'eoiKXBSWDG2UamPf'; //么么微片提供的secret
	
	//初始化参数
	public function initParams($imageUrl,$openId){
		if(empty($imageUrl)||empty($openId)){
			return false;
		}
		$this->imageUrl = $imageUrl;
		$this->openId = $openId;
		$this->timestamp = time();
		$this->signature = $this->genSign();
	}
	
	//获取么么微片制作地址
	public function getMemeUrl(){

		$params = $this->getParams();
		$params = array_merge($params,array('signature'=>$this->signature));
		//调用么么微片API
		//$ret = Commons::curlRemote ( $this->url, "POST", $params, array ('application/json'), 20, false );
		$ret = $this->httpCurl($this->url,$params);
		$res = json_decode($ret,true);
		return $res["fp_url"];
			try{
				$json = json_decode ( $ret ['data'], true );
				
				if(!empty($json['status'])&&$json['status']==1){
					return $json['url'];
				}
			}catch(Exception $e){
			}

		return false;
	}
	
	private function httpCurl($url, $data = array()){
		$result = FALSE;
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	private function getParams(){
		return array('image_url'=>$this->imageUrl,'open_id'=>$this->openId,'timestamp'=>$this->timestamp,'api_key'=>$this->apiKey);
	}
	
	//获取签名
	private function genSign(){
		$params = $this->getParams();
		//按字典顺序排序
		ksort($params);
		
		//组装签名字符串
		$signStr = '';
		foreach($params as $key=>$val){
			if($signStr==''){
				$signStr = $key.'='.$val;
			}else{
				$signStr = $signStr.'&'.$key.'='.$val;
			}
		}
		//签名
		return $this->hmac_sha1($signStr,$this->apiSecret);
	}
	
	//hash_hmac签名算法
	public function hmac_sha1($str,$key){

		$signature = "";
		if (function_exists('hash_hmac')){
			$signature = bin2hex(hash_hmac('sha1', $str, $key,true));
		}else{
			$blocksize	= 64;
			$hashfunc	= 'sha1';
			if (strlen($key) > $blocksize){
				$key = pack('H*', $hashfunc($key));
			}
			$key	= str_pad($key,$blocksize,chr(0x00));
			$ipad	= str_repeat(chr(0x36),$blocksize);
			$opad	= str_repeat(chr(0x5c),$blocksize);
			$hmac 	= pack('H*',$hashfunc(($key^$opad).pack('H*',$hashfunc(($key^$ipad).$str))));
			$signature = bin2hex($hmac);
		}
		return $signature;
	}
	
}

