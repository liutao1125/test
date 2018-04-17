<?php
/**
 * @author sunshichun@dodoca.com
 * 公共函数， 构造where条件以及执行sql查询
 */
class PubFun
{
	public static function get_where($data,$filter_data,$pre_fix='')
	{
		$where ='';
		foreach($filter_data as $k=>$v)
		{
			if(array_key_exists($v["key"],$data) && $data[$v["key"]]!='')
			{
				$key_val='';
				$key_val=addslashes(trim($data[$v["key"]]));
				
				if($v["js"]!='like')
				{
					if($v["data_type"]=='int')
					{
						$key_val=is_numeric($key_val)?intval($key_val):0;
					}
					if(!$v["fields"])
					{
						$where.=' and '.$v["key"].$v["js"].($v["data_type"]=="int"?'':"'").$key_val.($v["data_type"]=="int"?'':"'");
					}
					else
					{
						$where.=' and '.$v["fields"].$v["js"].($v["data_type"]=="int"?'':"'").$key_val.($v["data_type"]=="int"?'':"'");
					}
				}
				else
				{
					$fields=$v["fields"];
					$arr=explode(',',$fields);
					if(is_array($arr))
					{
						$where.=" and (";
						foreach($arr as $kk=>$vv)
						{
							$where.="  binary ".$vv .' '.$v["js"]." '%".addslashes(trim($data[$v["key"]]))."%' or";
						} 
						$where=trim($where,"or");
						$where.=")";
					}
				}
			}
		}
		if($pre_fix=='')
		{
			return $where==''?'':' where '.trim(trim($where),"and");
		}
		else
		{
			return $pre_fix.$where;
		}
	}
	/**
	 * $obj->对象
	 * $select_fields->字段
	 * $where_clause->搜索条件
	 * $orderby_clause->排序
	 * $currpage->当前页码
	 * $page_size->一页显示条数
         * $sqlreturn -> 是否返回sql
	 */
	public static function get_data_by_where($obj,$select_fields,$where_clause,$orderby_clause,$currpage=1,$page_size=10,$t=0)
	{
		$total_rows=$obj->fetchAll("select count(*) as c from ".$obj->_name." ". $where_clause);
		if($total_rows)
		{
			$total_rows=$total_rows[0]["c"];
		}
		else
		{
			$total_rows=0;
		}

		$total_page=ceil($total_rows/$page_size);
		$currpage=($currpage>$total_page && $total_page>0)?$total_page:$currpage;
		$sql="select ".$select_fields." from   ".$obj->_name." ".$where_clause."  ".$orderby_clause." limit  ".($currpage-1)*$page_size.','.$page_size;
             //   echo $sql;
		$rs=$obj->fetchAll($sql);
		$hash["data"]=$rs;
		$hash["total_rows"]=$total_rows;
		$hash["currpage"]=$currpage;
		$hash["total_page"]=$total_page;
		$sqlreturn && $hash["sql"]=$sql;
		if(SYS_MODULE=='admin')
		{
			$hash["page_list"]=PublicSys::pagelist($_GET,$total_rows,$currpage,$page_size);
		}
		else
		{
			$hash["page_list"]=html_page($_GET,$total_rows,$currpage,$page_size);
		}
		return $hash;
	}
	
	static function save_log($txt,$filename='log')
	{
		$dir=$_SERVER["SINASRV_LOG_DIR"].'/log/';
		if(!is_dir($dir))
		{
			mkdir($dir);
		}
		$dir=$_SERVER["SINASRV_LOG_DIR"].'/log/'.date("Y-m-d")."/";
		if(!is_dir($dir))
		{
			mkdir($dir);
		}
		error_log(date("H:i:s").'> '.$txt."\n", 3, $dir.$filename.date("Y-m-d").".log");
	}
	
	static function get_array_string($arr)
	{
		if(!is_array($arr))return '';
		$str='';
		foreach($arr as $k=>$v)
		{
			$str.='&'.$k.'='.$v;
		}
		return $str;
	}
	
	/**
	 * 随机密码
	 */
	static function rand_pwd()
	{
		$ret_val='';
		$str='a,c,d,e,f,g,h,i,j,k,m,1,2,3,4,5,6,7,8,9,n,p,q,r,s,t,u,v,w,x,y,z';
		$arr=explode(',',$str);
		for($i=0;$i<6;$i++)
		{
			$ret_val.=$arr[rand(0,31)];
		}
		return $ret_val;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 检查密码是否有违禁的关键词
	 */
	static function check_pwd($pwd)
	{
		$exit=false;
		$filte_arr=array('123456','111111','222222','333333','666666','888888','000000');
		foreach($filte_arr as $k=>$v)
		{
			if(strpos($pwd,$v)!==false)
			{
				$exit=true;
				break;
			}
		}
		return $exit;
	}
	
	/**
	 * double MD5
	 */
	static function md5md5($str)
	{
		$str=trim($str);
		if(!$str)return '';
		return md5(md5($str));
	}
	
	 /**
	  * 函数名称:encrypt
	函数作用:加密解密字符串
	使用方法:
	加密     :encrypt('str','E','nowamagic');
	解密     :encrypt('被加密过的字符串','D','nowamagic');
	参数说明:
	$string   :需要加密解密的字符串
	$operation:判断是加密还是解密:E:加密   D:解密
	$key      :加密的钥匙(密匙);
	*********************************************************************/
	
	static function encrypt($string,$operation,$key='dodoca2014AdminJS'){
		
		$key=md5($key);
		$key_length=strlen($key);
		$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
		$string_length=strlen($string);
		$rndkey=$box=array();
		$result='';
		for($i=0;$i<=255;$i++){
			$rndkey[$i]=ord($key[$i%$key_length]);
			$box[$i]=$i;
		}
		
		
		for($j=$i=0;$i<256;$i++){
			
			$j=($j+$box[$i]+$rndkey[$i])%256;
			$tmp=$box[$i];
			$box[$i]=$box[$j];
			$box[$j]=$tmp;
		}
		
		for($a=$j=$i=0;$i<$string_length;$i++){
			
			$a=($a+1)%256;
			$j=($j+$box[$a])%256;
			$tmp=$box[$a];
			$box[$a]=$box[$j];
			$box[$j]=$tmp;
			$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
		}
		
		if($operation=='D'){
			
			if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
				return substr($result,8);
			}else{
				return'';
			}
		}else{
			return str_replace('=','',base64_encode($result));
		}
	}
	

	/**
	 * 接口返回值
	 */
	static  function to_msg($code='1',$data=array(),$msg='')
	{
		$ret["code"]=$code;
		$ret["data"]=$data;
		$ret["msg"]=$msg;
		return $ret;
	}
	//上传图片key
	static public function get_upload_key()
	{
		$t="qweasdZXC!@#6yQ";
		return md5($t.date("Y-m-d"));
	}
	//上传图片url
	static public function get_upload_url()
	{
		return IMG_UPLOAD.'/upload.php?k='.self::get_upload_key();
	}
	//本地化图片url
	static public function get_down_img_url()
	{
		return IMG_UPLOAD.'/down_img.php?k='.self::get_upload_key();
	}
	//合成图片
	static public function get_merge_img_url()
	{
		return IMG_UPLOAD.'/merge_img.php?k='.self::get_upload_key();
	}
	//上传文件
	static public function get_upload_video_url()
	{
		return IMG_UPLOAD.'/upload_video.php?k='.self::get_upload_key();
	}
	
	//检查是否要提示
	static public function check_free_user($uid,$controller,$act)
	{
		if(!$uid || !$controller || !$act)return false;
		$info=User::get_user_info($uid);
		if(!$info)return false;
		$is_check=0;
		if($info["from_type"]=='3' )//后台添加用户只判断时间
		{
			if($info["over_time"]>date("Y-m-d"))
			{
				return true;
			}
		}

		if($info["is_free"]=='0' )
		{
			if($info["is_update"]=='0' )//未升级
			{
				$is_check=1;
			}
			else//已经升级过
			{
				if($info["over_time"]<date("Y-m-d"))//已经过试用期
				{
					$is_check=1;
				}
			}
		}
		else
		{
			if($info["over_time"]<date("Y-m-d"))//已经过试用期
			{
				$is_check=1;
			}
		}
		
		if($is_check)
		{
			foreach($GLOBALS['free_controller_action'] as $key=>$val)
			{
				foreach($val as $k=>$v)
				{
					if($key==$controller && $act==$v)
					{
					    return true;
					    break;
					}
				}
			}
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * 获取分享信息
	 */
	static public function get_share_info($domain='share.dodoca.net',$uid='0')
	{
		if(SYS_RELEASE!='2')//测试或者本机
		{
			return  array("domain"=>"t.dodoca.com","AppID"=>"wx99f38bec82119bed","AppSecret"=>"0a35d14a14a4146ef6fa3e28eda5bc9c","is_use"=>"0","mod_key"=>"","SysUid"=>"102","uid"=>"");
		}
		if($uid=="102")//公司账号
		{
			return  array("domain"=>"w.xhceumbln.com","AppID"=>"wx309bfacd9313e534","AppSecret"=>"7cab28d74b93c48f6e3593dc2ede576c","is_use"=>"0","mod_key"=>"","SysUid"=>"102","uid"=>"");
		}
		if(!$domain)$domain=$_SERVER["[HTTP_HOST"];
		foreach($GLOBALS['sys_share'] as $key=>$val)
		{
			if(trim($val["uid"])!='')//指定uid对应的公众号
			{
				$t=explode(',',trim($val["uid"]));
				if(is_array($t))
				{
					foreach($t as $k=>$v)
					{
						if($v && $v==$uid)
						{
							return $val;
						}
					}
				}
			}
		}
		foreach($GLOBALS['sys_share'] as $key=>$val)
		{
			if($val["is_use"]=='0' && $val["domain"]==trim($domain) )//原来的分享保存不变,已经不能分享
			{
				return $val;
			}
		}
		if(!$uid)return;
		foreach($GLOBALS['sys_share'] as $key=>$val)
		{
			if($val["is_use"]=='1' &&  $uid%2==$val["mod_key"])
			{
				return $val;
			}
		}
		return;
	}
	
	//检查是否要
	static public function check_free_user_flag($uid)
	{
		if(!$uid)return false;
		$info=User::get_user_info($uid);
		if(!$info)return false;
		$is_check=0;
		if($info["from_type"]=='3' )//后台添加用户只判断时间
		{
			if($info["over_time"]>date("Y-m-d"))
			{
				return true;
			}
		}
		if($info["is_free"]=='0' )
		{
			if($info["is_update"]=='0' )//未升级
			{
				$is_check=1;
			}
			else//已经升级过
			{
				if($info["over_time"]<date("Y-m-d"))//已经过试用期
				{
					$is_check=1;
				}
			}
		}
		else
		{
			if($info["over_time"]<date("Y-m-d"))//已经过试用期
			{
				$is_check=1;
			}
		}
		
		if($is_check)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	//输出导出的数据
	static public function import_data($data,$head_fields)
	{
		if(!$data)return;
		$header   =  implode("\",\"", array_values($head_fields));
		$header   = "\"" . $header;
		$header .= "\"\r\n";
		header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("X-DNS-Prefetch-Control: off");
		header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		 
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=users.csv");
		$header = iconv("UTF-8", "GB2312//IGNORE", $header);
		echo $header;
		foreach ($data as $val) {
			$content = "";
			$new_arr = array();
			foreach($head_fields as $tk => $tv)
			{
				$value='';
				if(array_key_exists($tk,$val))
				{
					$value=$val[$tk];
				}
				array_push($new_arr, preg_replace("/\"/", "\"\"", "\t" . $value));
			}
			$line = implode("\",\"", $new_arr);
			$line = "\"" . $line;
			$line .= "\"\r\n";
			$content .= $line;
			$content = iconv("UTF-8", "GB2312//IGNORE", $content);
			echo $content;
		}
		exit;
	}
	
        
        
}
?>
