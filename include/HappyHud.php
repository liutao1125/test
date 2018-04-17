<?php

/* *
*	jhm 20140310
*	趣味互动统一接口，解析第三方数据
*/
class HappyHud{
	
	//经典笑话
	public static function GetJoke(){
		$joke_url = "http://www.djdkx.com/open/randxml ";
		$jokes = self::getWebData($joke_url);
		$xml = simplexml_load_string($jokes);
		$contentStr = trim($xml->content);
		$arr_search = array('<br/>','&nbsp;',);
		$arr_replace = array(' ',' ');
		$contentStr=str_ireplace($arr_search,$arr_replace,$contentStr);
		return  $contentStr;
	}
	
	/*中英互译，中日互译$language(en英文 jp日文) $keyword(翻译文本)
	* 我们可以为您进行中英互译，中日互译，中英互译请发送“翻译”加上要翻译的词句，如“翻译圣诞节”中日互译请发送“翻译@”加上要翻译的词句，如“翻译@请多多关照”
	*/
	public static function GetTranslate($language,$keyword){
		if(!$language||!$keyword)	return false;
		$key = "mt5maPp8M7G1GD6Hg16vKIYV";
		$host = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=$key&q=$keyword&from=auto&to=$language";
		$api2str = self::getWebData2($host);
		$str = json_decode($api2str,true);
		$contentStr = "翻译结果：".$str["trans_result"][0]["dst"];
		return  $contentStr;
	}
	
	//获取快递（我们提供国内90多家快递物流订单查询服务比如申通快递、顺丰快递、圆通快递、EMS快递、汇通快递、宅急送快递等知名快递订单查询服务。\n  查询方式：请发送“快递”加上快递或物流公司的名称加上“@”再加上运单号，如“快递申通@868273943254”,(快递单号请注意区分大小写)）
	//$name 快递(顺丰)
	//$code 运单号
	public static function GetKuaidi($name,$code){
		if(!$name||!$code)	return false;
		$AppKey="b4aa192b451843d29b4a9c72caffa7bf";
		$url="http://www.aikuaidi.cn/rest/?key=$AppKey&order=$code&id=".self::Pinyin($name,1);
		$kuaidi= self::getWebData2($url);
		$json2arr=json_decode($kuaidi,true);
		//errCode 返回错误码：0：无错误，1：快递KEY无效，2：快递代号无效，3：访问次数达到最大额度，4：查询服务器返回错误即返回状态码非200,5：程序执行出错
		switch($json2arr["errCode"]){
		case "0":switch ($json2arr["status"]){
				case "1":
					$status="暂无记录";
					break;
				case "2":
					$status="在途中";
					break;
				case "3":
					$status="派送中";
					break;
				case "4":
					$status="已签收";
					break;
				case "5":
					$status="拒收";
					break;
				case "6":
					$status="疑难件";
					break;
				case "7":
					$status="退回";
					break;
			}
			$str="";
			foreach ($json2arr["data"] as $data){
				$str.="\n".$data["time"]."：".$data["content"];
			}
			$str1="订单详情：".$json2arr["name"]."($status)"."\n 订单号：".$json2arr["order"]."\n";
			$contentStr=$str1.trim($str);
			break;
		case "2":
			$contentStr="对不起，你输入的快递或物流公司不存在";
			break;
		case "3":
			$contentStr="对不起，今天的查询次数已经达到最大额度";
			break;
		default:
			$contentStr="对不起，查询出错，请稍候重试";
			break;
		}
		return $contentStr;
	}
	
	//天气查询 查询天气情况请发送“天气”加上城市名，如“天气上海”
	//$city 城市名
	public static function GetWeather($city){
		include_once "Xml/simple_html_dom.php" ;
		if(!$city) return false;
		$city = iconv('UTF-8', 'GB2312', $city);
		$city = urlencode($city);
		$host="http://php.weather.sina.com.cn/xml.php?city=".$city."&password=DJOYnieT8234jlsK&day=0";
		$html = new simple_html_dom();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $host);
		curl_setopt($curl, CURLOPT_TIMEOUT, 600);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content−Type:application/html;charset=utf−8"));
		$res = curl_exec($curl);
		curl_close($curl);
		$html->load($res, TRUE, TRUE);
		$arr = array();
		$city = $html->find('city',0);//城市名
		$status1 = $html->find('status1',0);//天气情况
		$direction1 = $html->find('direction1',0);// 风向
		$power1 = $html->find('power1',0);// 风向等级
		$temperature1 = $html->find('temperature1',0);// 最高温度
		$temperature2 = $html->find('temperature2',0);// 最底温度
		$ktk_l = $html->find('ktk_l',0);// 空调指数
		$chy_l = $html->find('chy_l',0);// 穿衣指数
		$gm_s = $html->find('gm_s',0);// 感冒
		$yd_s = $html->find('yd_s',0);// 运动指数
		$xcz_s = $html->find('xcz_s',0);// 洗车
		if($city->innertext){
			$contentStr="【".$city->innertext."】\n今日天气: ".$status1->innertext.
			" "."\n温度:".$temperature2->innertext.'-'.$temperature1->innertext."(摄氏度) "."\n风向:".$direction1->innertext.
			"\n等级:".$power1->innertext."\n空调指数:".$ktk_l->innertext."\n穿衣指数:".$chy_l->innertext."\n感冒指数:".$gm_s->innertext."\n运动指数:".$yd_s->innertext.
			"\n洗车指数:".$xcz_s->innertext;
		}else{
			$contentStr="/::~ 非常抱歉，没有找到你要查询的城市";
		}
		return $contentStr;	
	}

	//车次查询 $entityName 车次号
	public static function GetTrainInfo($entityName){
		if(!$entityName) return false;
		$AppKey="833d3d6c9273b48650fbf2d34a944c59";
		$url="http://apis.juhe.cn/train/s?name=$entityName&key=$AppKey";
		$train= self::getWebData2($url);
		$json2arr=json_decode($train,true);//station_name
		if($json2arr["resultcode"]=="200"){
			$train_info=$json2arr["result"]["train_info"];
			$contentStr="车次".$train_info["name"].
			"（".$train_info["starttime"]."-".$train_info["endtime"].
			"）\n".$train_info["start"].
			"至".$train_info["end"].
			"，全程".$train_info["mileage"];
			foreach ($json2arr["result"]["station_list"] as $station){
				$contentStr.="\n第".$station["train_id"]."站：".$station["station_name"].
				"\n到达时间：".$station["arrived_time"]."\n停留".$station["stay"]."分钟";
			}
		}
		else if($json2arr["resultcode"]=="201"){
			$contentStr=" 列次名称不能为空";
		}
		else if($json2arr["resultcode"]=="202"){
			$contentStr=" 查询不到车次".$entityName."的信息";
		}
		return $contentStr;
	}
	
	//站到站查询列车信息(站到站查询请输入”列车“加上列车类型加上”起始站@终点站“如“列车G上海@苏州\n列车类型字母代码:\n G-高速动车\n K-快速 \n T-空调特快 \n D-动车组\n Z-直达特快 \n Q-其他)
	//$start 始发站   $end 终点站   $type 列车类型
	public static function GetTrainList($start,$end,$type='G'){
		if(!$start||!$end) return false;
		if(in_array($type, array("G","K","T","D","Z","Q"))){
			$AppKey = "833d3d6c9273b48650fbf2d34a944c59";
			$url = "http://apis.juhe.cn/train/s2s?start=".urlencode($start)."&end=".urlencode($end)."&traintype=$type&key=$AppKey";
			$train = self::getWebData2($url);
			$json2arr = json_decode($train,true);
			if($json2arr["resultcode"]=="200"){
				$contentStr="查询到的列车信息如下：";
				foreach ($json2arr["result"]["data"] as $traindetail){
					$contentStr.="\n".$traindetail["trainOpp"].
					"(".$traindetail["leave_time"].
					"-".$traindetail["arrived_time"].
					")\n".$traindetail["start_staion"].
					"至".$traindetail["end_station"];
				}
			}
		}else{
			$contentStr="请输入正确的列车类型\n G-高速动车\n K-快速 \n T-空调特快 \n D-动车组\n Z-直达特快 \n Q-其他\n 如”列车G上海@济南“，表示查询从上海到济南的高铁。";
		}
		return $contentStr;
	}
	
	//获取音乐 $musicname歌名  $gsname 歌手
	public static function GetMusic($musicname,$gsname=''){
		if(!$musicname) return false;
		include_once "Xml/simple_html_dom.php" ;
		$url="http://box.zhangmen.baidu.com/x?op=12&count=1&title=$musicname$$$gsname$$$$";
		$html = new simple_html_dom();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 600);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content−Type:application/html;charset=utf−8"));
		$res = curl_exec($curl);
		curl_close($curl);
		$html->load($res, TRUE, TRUE);
		$arr = array();
		$count = $html->find('count',0);
		if($count->innertext==0){
			$contentStr="没有找到你点的音乐，可能不是歌名或者检索失败，请换首歌试试！";
		}else{
			$url1 = $html->find('encode',0);
		
			$url2 = $html->find('decode',0);
			$contentStr=$url1->xmltext.$url2->xmltext."#.mp3";
		}
		return $contentStr;
	}
	
	private function getcode($entityName){
		$cityinfo=$GLOBALS["all_city_code"];
		$allcity=json_decode($cityinfo,true);
		$citycode=$allcity["CityCode"];
		foreach ($citycode as $info){
			foreach ($info["city"] as $one){
				if($one["cityname"]==$entityName){
					$code=$one["code"];
					return $code;
				}
			}
		}
	}
	
	private function Pinyin($_String, $_Code='gb2312'){
		$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
			"|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
			"cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
			"|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
			"|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
			"|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
			"|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
			"|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
			"|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
			"|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
			"|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
			"she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
			"tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
			"|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
			"|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
			"zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
		$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
			"|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
			"|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
			"|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
			"|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
			"|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
			"|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
			"|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
			"|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
			"|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
			"|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
			"|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
			"|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
			"|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
			"|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
			"|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
			"|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
			"|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
			"|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
			"|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
			"|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
			"|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
			"|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
			"|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
			"|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
			"|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
			"|-10270|-10262|-10260|-10256|-10254";
		$_TDataKey = explode('|', $_DataKey);
		$_TDataValue = explode('|', $_DataValue);
		$_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : self::_Array_Combine($_TDataKey, $_TDataValue);
		arsort($_Data);
		reset($_Data);
		if($_Code != 'gb2312') $_String = self::_U2_Utf8_Gb($_String);
		$_Res = '';
		for($i=0; $i<strlen($_String); $i++){
			$_P = ord(substr($_String, $i, 1));
			if($_P>160) {
				$_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
			}
			$_Res .= self::_Pinyin($_P, $_Data);
		}
		return preg_replace("/[^a-z0-9]*/", '', $_Res);
	}
	
	function _Pinyin($_Num, $_Data){
		if ($_Num>0 && $_Num<160 ) return chr($_Num);
		elseif($_Num<-20319 || $_Num>-10247) return '';
		else {
		foreach($_Data as $k=>$v){
			if($v<=$_Num) break;
		}
			return $k;
		}
	}
	function _U2_Utf8_Gb($_C){
		$_String = '';
		if($_C < 0x80) $_String .= $_C;
		elseif($_C < 0x800){
			$_String .= chr(0xC0 | $_C>>6);
			$_String .= chr(0x80 | $_C & 0x3F);
		}elseif($_C < 0x10000){
			$_String .= chr(0xE0 | $_C>>12);
			$_String .= chr(0x80 | $_C>>6 & 0x3F);
			$_String .= chr(0x80 | $_C & 0x3F);
		} elseif($_C < 0x200000) {
			$_String .= chr(0xF0 | $_C>>18);
			$_String .= chr(0x80 | $_C>>12 & 0x3F);
			$_String .= chr(0x80 | $_C>>6 & 0x3F);
			$_String .= chr(0x80 | $_C & 0x3F);
		}
		return iconv('UTF-8', 'GB2312', $_String);
	}
	
	function _Array_Combine($_Arr1, $_Arr2){
		for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
		return $_Res;
	}
	
	static private function getWebData($host){
		$context = stream_context_create(array(
		'http' => array(
		'method' => 'GET',
		'timeout' => 5)));
		$data = file_get_contents($host,0,$context);
		return $data;
	}
	
	static private function getWebData2($host){
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $host);
		curl_setopt ($curl, CURLOPT_HEADER,false);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
		curl_setopt ($curl, CURLOPT_TIMEOUT,5);
		$data = curl_exec($curl);
		curl_close ($curl);
		return $data;
	}
}