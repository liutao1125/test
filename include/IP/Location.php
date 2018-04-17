<?php
 

include_once("iplocation.inc.php");

class IP_Location {

	function getCity()
	{
		$dict_domain= $GLOBALS['cityOpts'];
		$city= "sh";

		//IP来源跳转
		$iplct = new ipLocation();
		$ip = $iplct->getIP();  
		 
		//$ip = "61.135.154.47";	//北京
		//$ip = "222.36.64.221";  //天津
		//$ip = "222.42.118.16";	//武汉
		//$ip = "222.46.16.128";	//杭州

		$show_area = $iplct->getaddress($ip);

		//var_dump($show_area);
		$addr = $show_area["area1"];
		if(strpos($addr, '亚太')!==false)
		{
			$city='hz';
		}
		else
		{
			foreach($dict_domain as $en_name=>$cn_name)
			{
				if (strpos($addr, $cn_name) !== false )
				{
					$city = $en_name;
					break;
				}
			}
		}
		//header("ip: $ip");
		return $city;
	}

	function getIP() {
		$iplct = new ipLocation();
		return $iplct->getIP();  
	}

	function getArea() {
		$iplct = new ipLocation();
		$ip = $iplct->getIP();  
		//$ip = "61.135.154.47";	//北京
		//$ip = "222.36.64.221";  //天津
		//$ip = "222.42.118.16";	//武汉
		//$ip = "222.46.16.128";	//杭州
		//$ip="116.231.72.77";  //上海
		$show_area = $iplct->getaddress($ip);
		Return $show_area;
	}
	
}




 

?>