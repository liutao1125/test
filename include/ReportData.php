<?php
/*
 * @author sunshichun@dodoca.com
 * 粉丝卡
 */
class ReportData
{
	static $is_debug=false;
	/**
	 * 获取海报一段时间范围内的统计数据
	 */
	static public function hb_data($id='',$start_date,$end_date)
	{
		$flag=0;//区分是主账号还是海报id
		$mc_key=md5('hb'.$id."_".$start_date."_".$end_date.date("Y-m-d"));
		if(empty($id)){
			$id = get_uid();
			$flag=1;
			$mc_key=md5('hb_userid'.$id."_".$start_date."_".$end_date.date("Y-m-d"));
		}
		
		$data=mc_get($mc_key);
		if(!$data)
		{
			if(self::$is_debug)
			{
				s('$id->'.$id.',$start_date->'.$start_date.',$end_date->'.$end_date);
			}
			$start_date=trim($start_date);
			$end_date=trim($end_date);
			if(!$id || !$start_date || !$end_date)return;
			$start_date=strtotime($start_date);
			$end_date=strtotime($end_date);
			if($start_date>$end_date)return;
			$diff_day=($end_date-$start_date)/86400+1;//相差天数
			//s('$diff_day->'.$diff_day);
			$data=array();
			
			if($diff_day==1)//一天数据
			{
				if($flag==0){
					$data=self::get_one_day($id,date("Y-m-d",$start_date));
				}else{
					$data=self::get_one_day($id='',date("Y-m-d",$start_date));
				}
				
			}
			else
			{
				$t_array=self::get_date_rang($start_date,$end_date);
				if(self::$is_debug)
				{
					s($t_array);
				}
				$pv_count=0;
				$uv_count=0;
				$pv_array=array();
				$uv_array=array();
				foreach($t_array as $key=>$val)
				{
					$hb=new HbVisitDayModel(date("y",strtotime($val["start_time"])));
					if($flag==0){//单个海报统计
						$rs=$hb->fetchAll(" select sum(pv_count) as pv_count,sum(uv_count) as uv_count from  ".$hb->_name." where weiid=$id and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
					}else{//主账号海报统计
						$rs=$hb->fetchAll(" select sum(pv_count) as pv_count,sum(uv_count) as uv_count from  ".$hb->_name." where userid=$id and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
					}
					
					$hb->close();
					if($rs)
					{
						$pv_count+=$rs[0]["pv_count"];
						$uv_count+=$rs[0]["uv_count"];
						$pv_array[$val["time_key"]]=$rs[0]["pv_count"]?$rs[0]["pv_count"]:0;
						$uv_array[$val["time_key"]]=$rs[0]["uv_count"]?$rs[0]["uv_count"]:0;
					}
				}
				$data["x_data"]["pv"]=$pv_array;
			    $data["x_data"]["uv"]=$uv_array;
				$data["pv_count"]=$pv_count;
				$data["uv_count"]=$uv_count;
			}
			if(self::$is_debug)
			{
				s($data);
			}
			mc_set($mc_key,$data,86400);
		}
		//s($data);
		return $data;
	}
	
	
	/**
	 * 海报浏览量数据统计
	 */
	static public function hb_tj_data($id,$start_date,$end_date)
	{
		$mc_key=md5('hb_tj_userid'.$id."_".$start_date."_".$end_date.date("Y-m-d"));
		$data=mc_get($mc_key);
		if(!$data)
		{
			if(self::$is_debug)
			{
				s('$id->'.$id.',$start_date->'.$start_date.',$end_date->'.$end_date);
			}
			$start_date=trim($start_date);
			$end_date=trim($end_date);
			if(!$id || !$start_date || !$end_date)return;
			$start_date=strtotime($start_date);
			$end_date=strtotime($end_date);
			if($start_date>$end_date)return;
			$diff_day=($end_date-$start_date)/86400+1;//相差天数
			$pu=new Weixinwebsitey();
			$pu_data=$pu->fetchAll("select id,title from ".$pu->_name." where userid=$id and delete_state=1");
			$pu->close();
			if(!$pu_data)return;
	
			$data=array();
			$pv_count_total=0;
			$uv_count_total=0;
			$tmp_array=array();
			if($diff_day==1)//一天数据
			{
				foreach($pu_data as $key=>$val)
				{
					$t_r=array();
					$t=self::get_one_day($val["id"],date("Y-m-d",$start_date));
					$t_r["id"]=$val["id"];
					$t_r["title"]=$val["title"];
					$t_r["pv_count"]=$t["pv_count"];
					$t_r["uv_count"]=$t["uv_count"];
					$t_r["x_data"]["list_pv"]=$t["x_data"]["pv"];
					$t_r["x_data"]["list_uv"]=$t["x_data"]["uv"];
					$pv_count_total+=$t["pv_count"];
					$uv_count_total+=$t["uv_count"];
					$tmp_array[]=$t_r;
					unset($t_r);
				}
			}
			else
			{
				$t_array=self::get_date_rang($start_date,$end_date);
				if(self::$is_debug)
				{
					s($t_array);
				}
					
				foreach($pu_data as $keys=>$vals)
				{
					$t_r=array();
					$pv_count=0;
					$uv_count=0;
					$pv_array=array();
					$uv_array=array();
					foreach($t_array as $key=>$val)
					{
						$hb=new HbVisitDayModel(date("y",strtotime(($val["start_time"]))));
						$rs=$hb->fetchAll(" select sum(pv_count) as pv_count,sum(uv_count) as uv_count from  ".$hb->_name." where weiid=".$vals["id"]." and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
						$hb->close();
						if($rs)
						{
							$pv_count+=$rs[0]["pv_count"];
							$uv_count+=$rs[0]["uv_count"];
							$pv_array[$val["time_key"]]=$rs[0]["pv_count"]?$rs[0]["pv_count"]:0;
							$uv_array[$val["time_key"]]=$rs[0]["uv_count"]?$rs[0]["uv_count"]:0;
						}
					}
	
					$t_r["id"]=$vals["id"];
					$t_r["title"]=$vals["title"];
					$t_r["pv_count"]=$pv_count;
					$t_r["uv_count"]=$uv_count;
					$t_r["x_data"]["list_pv"]=$pv_array;
					$t_r["x_data"]["list_uv"]=$uv_array;
	
					$pv_count_total+=$pv_count;
					$uv_count_total+=$uv_count;
						
					$tmp_array[]=$t_r;
					unset($t_r);
				}
			}
			if(is_array($tmp_array))
			{
				$t_sort_array=array();
				foreach($tmp_array as $kk=>$val)
				{
					$t_sort_array[]=$val["pv_count"];
				}
				array_multisort($t_sort_array,SORT_DESC,$tmp_array);
			}
			$data["pv_count_total"]=$pv_count_total;
			$data["uv_count_total"]=$uv_count_total;
			$data["list"]=$tmp_array;
			if(self::$is_debug)
			{
				s($data);
			}
			mc_set($mc_key,$data,86400);
		}
		return $data;
	}
	
	
	/**
	 * 获取海报投放一段时间范围内的统计数据
	 */
	static public function hb_tf_data($id,$start_date,$end_date,$is_tf=0)
	{
		$mc_key=md5('tf'.$id."_".$start_date."_".$end_date.date("Y-m-d"));
		$data=mc_get($mc_key);
		if(!$data)
		{
			if(self::$is_debug)
			{
				s('$id->'.$id.',$start_date->'.$start_date.',$end_date->'.$end_date);
			}
			$start_date=trim($start_date);
			$end_date=trim($end_date);
			if(!$id || !$start_date || !$end_date)return;
			$start_date=strtotime($start_date);
			$end_date=strtotime($end_date);
			if($start_date>$end_date)return;
			$diff_day=($end_date-$start_date)/86400+1;//相差天数
			$pu=new Weixinwebsiteyputin();
			$pu_data=$pu->fetchAll("select id,title from ".$pu->_name." where weiid=$id and is_deleted=1");
			$pu->close();
			if(!$pu_data)return;
	
			$data=array();
			$pv_count_total=0;
			$uv_count_total=0;
			$tmp_array=array();
			if($diff_day==1)//一天数据
			{
				foreach($pu_data as $key=>$val)
				{
					$t_r=array();
					$t=self::get_one_day($val["id"],date("Y-m-d",$start_date),1);
					$t_r["id"]=$val["id"];
					$t_r["title"]=$val["title"];
					$t_r["pv_count"]=$t["pv_count"];
					$t_r["uv_count"]=$t["uv_count"];
					$t_r["x_data"]["list_pv"]=$t["x_data"]["pv"];
					$t_r["x_data"]["list_uv"]=$t["x_data"]["uv"];
					$pv_count_total+=$t["pv_count"];
					$uv_count_total+=$t["uv_count"];
					$tmp_array[]=$t_r;
					unset($t_r);
				}
			}
			else
			{
					$t_array=self::get_date_rang($start_date,$end_date);
					if(self::$is_debug)
					{
						s($t_array);
					}
					
					foreach($pu_data as $keys=>$vals)
					{
						$t_r=array();
						$pv_count=0;
						$uv_count=0;
						$pv_array=array();
						$uv_array=array();
						foreach($t_array as $key=>$val)
						{
							$hb=new HbVisitPuDayModel(date("y",strtotime(($val["start_time"]))));
							$rs=$hb->fetchAll(" select sum(pv_count) as pv_count,sum(uv_count) as uv_count from  ".$hb->_name." where putin=".$vals["id"]." and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
							$hb->close();
							if($rs)
							{
								$pv_count+=$rs[0]["pv_count"];
								$uv_count+=$rs[0]["uv_count"];
								$pv_array[$val["time_key"]]=$rs[0]["pv_count"]?$rs[0]["pv_count"]:0;
								$uv_array[$val["time_key"]]=$rs[0]["uv_count"]?$rs[0]["uv_count"]:0;
							}
						}
						
						$t_r["id"]=$vals["id"];
						$t_r["title"]=$vals["title"];
						$t_r["pv_count"]=$pv_count;
						$t_r["uv_count"]=$uv_count;
						$t_r["x_data"]["list_pv"]=$pv_array;
						$t_r["x_data"]["list_uv"]=$uv_array;
						
						$pv_count_total+=$pv_count;
						$uv_count_total+=$uv_count;
							
						$tmp_array[]=$t_r;
						unset($t_r);
					}
				}
				if(is_array($tmp_array))
				{
					$t_sort_array=array();
					foreach($tmp_array as $kk=>$val)
					{
						$t_sort_array[]=$val["pv_count"];
					}
					array_multisort($t_sort_array,SORT_DESC,$tmp_array);
				}
				$data["pv_count_total"]=$pv_count_total;
				$data["uv_count_total"]=$uv_count_total;
				$data["list"]=$tmp_array;
				if(self::$is_debug)
				{
					s($data);
				}
				mc_set($mc_key,$data,86400);
			}
			return $data;
		}
		

		/**
		 * 根据投放id获取投放数据
		 */
		static public function get_hb_tf_data($id,$start_date,$end_date)
		{
			
				$start_date=trim($start_date);
				$end_date=trim($end_date);
				if(!$id || !$start_date || !$end_date)return;
				$start_date=strtotime($start_date);
				$end_date=strtotime($end_date);
				if($start_date>$end_date)return;
				$diff_day=($end_date-$start_date)/86400+1;//相差天数
				$pu=new Weixinwebsiteyputin();
				$pu_data=$pu->fetchAll("select id,title from ".$pu->_name." where id=$id and is_deleted=1");
				$pu->close();
				if(!$pu_data)return;
		
				$data=array();
				$pv_count_total=0;
				$uv_count_total=0;
				$tmp_array=array();
				if($diff_day==1)//一天数据
				{
					foreach($pu_data as $key=>$val)
					{
						$t_r=array();
						$t=self::get_one_day($val["id"],date("Y-m-d",$start_date),1);
						$t_r["id"]=$val["id"];
						$t_r["title"]=$val["title"];
						$t_r["pv_count"]=$t["pv_count"];
						$t_r["uv_count"]=$t["uv_count"];
						$t_r["x_data"]["list_pv"]=$t["x_data"]["pv"];
						$t_r["x_data"]["list_uv"]=$t["x_data"]["uv"];
						$pv_count_total+=$t["pv_count"];
						$uv_count_total+=$t["uv_count"];
						$tmp_array[]=$t_r;
						unset($t_r);
					}
				}
				else
				{
					$t_array=self::get_date_rang($start_date,$end_date);
					if(self::$is_debug)
					{
						s($t_array);
					}
						
					foreach($pu_data as $keys=>$vals)
					{
						$t_r=array();
						$pv_count=0;
						$uv_count=0;
						$pv_array=array();
						$uv_array=array();
						foreach($t_array as $key=>$val)
						{
							$hb=new HbVisitPuDayModel(date("y",strtotime(($val["start_time"]))));
							$rs=$hb->fetchAll(" select sum(pv_count) as pv_count,sum(uv_count) as uv_count from  ".$hb->_name." where putin=".$vals["id"]." and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
							$hb->close();
							if($rs)
							{
								$pv_count+=$rs[0]["pv_count"];
								$uv_count+=$rs[0]["uv_count"];
								$pv_array[$val["time_key"]]=$rs[0]["pv_count"]?$rs[0]["pv_count"]:0;
								$uv_array[$val["time_key"]]=$rs[0]["uv_count"]?$rs[0]["uv_count"]:0;
							}
						}
		
						$t_r["id"]=$vals["id"];
						$t_r["title"]=$vals["title"];
						$t_r["pv_count"]=$pv_count;
						$t_r["uv_count"]=$uv_count;
						$t_r["x_data"]["list_pv"]=$pv_array;
						$t_r["x_data"]["list_uv"]=$uv_array;
		
						$pv_count_total+=$pv_count;
						$uv_count_total+=$uv_count;
							
						$tmp_array[]=$t_r;
						unset($t_r);
					}
				}
				if(is_array($tmp_array))
				{
					$t_sort_array=array();
					foreach($tmp_array as $kk=>$val)
					{
						$t_sort_array[]=$val["pv_count"];
					}
					array_multisort($t_sort_array,SORT_DESC,$tmp_array);
				}
				$data["pv_count_total"]=$pv_count_total;
				$data["uv_count_total"]=$uv_count_total;
				$data["list"]=$tmp_array;
				if(self::$is_debug)
				{
					s($data);
				}
			return $data;
		}
		
	/**
	 * $id 海报id
	 * $start_date 年月日
	 */
	static public function get_one_day($id='',$start_date,$is_tf=0)
	{
		$flag=0;//区分是主账号还是海报id
		if(empty($id)){
			$id = get_uid();
			$flag=1;
		}
		$hb=null;
		if(!$is_tf)//海报
		{
			$hb=new HbVisitHourModel(date("y",strtotime($start_date)));
		}
		else
		{
			$hb=new HbVisitPuHourModel(date("y",strtotime($start_date)));
		}
		if(self::$is_debug)
		{
			$hb->debug();
		}
		
		$pv_count_total=0;
		$uv_count_total=0;
		$pv_count1=0;
		$pv_count2=0;
		$pv_count3=0;
		$pv_count4=0;
		$pv_count5=0;
		$pv_count6=0;
		$uv_count1=0;
		$uv_count2=0;
		$uv_count3=0;
		$uv_count4=0;
		$uv_count5=0;
		$uv_count6=0;
		
		if($flag==0){
			$tmp_data=$hb->get_day_data($id,$start_date);
			if(self::$is_debug)
			{
				s($tmp_data);
			}
			if(is_array($tmp_data))
			{
				foreach($tmp_data as $key=>$val)
				{
					if($key && strpos($key,'hourpv')!==false)
					{
						$pv_count_total+=$val;
					}
					if($key && strpos($key,'houruv')!==false)
					{
						$uv_count_total+=$val;
					}
				}
			}
			for($i=0;$i<24;$i++)
			{
				$tk=str_pad($i+1,2,'0',STR_PAD_LEFT);
				$tk_pv='hourpv'.$tk;
				$tk_uv='houruv'.$tk;
				if($i>=0 && $i<4)
				{
				$pv_count1+=$tmp_data[$tk_pv];
				$uv_count1+=$tmp_data[$tk_uv];
				}
				else if($i>=4 && $i<8)
				{
				$pv_count2+=$tmp_data[$tk_pv];
				$uv_count2+=$tmp_data[$tk_uv];
				}
				else if($i>=8 && $i<12)
				{
				$pv_count3+=$tmp_data[$tk_pv];
				$uv_count3+=$tmp_data[$tk_uv];
				}
				else if($i>=12 && $i<16)
				{
				$pv_count4+=$tmp_data[$tk_pv];
				$uv_count4+=$tmp_data[$tk_uv];
				}
				else if($i>=16 && $i<20)
				{
				$pv_count5+=$tmp_data[$tk_pv];
				$uv_count5+=$tmp_data[$tk_uv];
				}
				else if($i>=20 && $i<=24)
				{
				$pv_count6+=$tmp_data[$tk_pv];
				$uv_count6+=$tmp_data[$tk_uv];
				}
			}
		}else{
			$rs=$hb->fetchAll(" select * from  ".$hb->_name." where userid=$id and statdate='$start_date'");
			foreach($rs as $key=>$value){
				if(is_array($value))
				{
					foreach($value as $key=>$val)
					{
						if($key && strpos($key,'hourpv')!==false)
						{
							$pv_count_total+=$val;
						}
						if($key && strpos($key,'houruv')!==false)
						{
							$uv_count_total+=$val;
						}
					}
				}
				
				for($i=0;$i<24;$i++)
				{
					$tk=str_pad($i+1,2,'0',STR_PAD_LEFT);
					$tk_pv='hourpv'.$tk;
					$tk_uv='houruv'.$tk;
					if($i>=0 && $i<4)
					{
					$pv_count1+=$value[$tk_pv];
					$uv_count1+=$value[$tk_uv];
					}
					else if($i>=4 && $i<8)
					{
					$pv_count2+=$value[$tk_pv];
					$uv_count2+=$value[$tk_uv];
					}
					else if($i>=8 && $i<12)
					{
					$pv_count3+=$value[$tk_pv];
					$uv_count3+=$value[$tk_uv];
					}
					else if($i>=12 && $i<16)
					{
					$pv_count4+=$value[$tk_pv];
					$uv_count4+=$value[$tk_uv];
					}
					else if($i>=16 && $i<20)
					{
					$pv_count5+=$value[$tk_pv];
					$uv_count5+=$value[$tk_uv];
					}
					else if($i>=20 && $i<=24)
					{
					$pv_count6+=$value[$tk_pv];
					$uv_count6+=$value[$tk_uv];
					}
				}
				
			}
		}
		
		$hb->close();
		
		$ret["x_data"]["pv"]=array("00:00"=>$pv_count1,"04:00"=>$pv_count2,"08:00"=>$pv_count3,"12:00"=>$pv_count4,"16:00"=>$pv_count5,"20:00"=>$pv_count6);
		$ret["x_data"]["uv"]=array("00:00"=>$uv_count1,"04:00"=>$uv_count2,"08:00"=>$uv_count3,"12:00"=>$uv_count4,"16:00"=>$uv_count5,"20:00"=>$uv_count6);
		$ret["pv_count"]=$pv_count_total;
		$ret["uv_count"]=$uv_count_total;
		if(self::$is_debug)
		{
			s($ret);
		}
		return $ret;
	}
	
	/**
	 * 获取海报一段时间范围内的统计数据
	 */
	static public function hb_area_data($id='',$start_date,$end_date)
	{
		$flag=0;//区分是主账号还是海报id
		$mc_key=md5('area'.$id."_".$start_date."_".$end_date.date("Y-m-d"));
		if(empty($id)){
			$id = get_uid();
			$flag=1;
			$mc_key=md5('hb_area_userid'.$id."_".$start_date."_".$end_date.date("Y-m-d"));
		}
		$data=mc_get($mc_key);
		if(!$data)
		{
			if(self::$is_debug)
			{
				s('$id->'.$id.',$start_date->'.$start_date.',$end_date->'.$end_date);
			}
			$start_date=trim($start_date);
			$end_date=trim($end_date);
			if(!$id || !$start_date || !$end_date)return;
			$start_date=strtotime($start_date);
			$end_date=strtotime($end_date);
			if($start_date>$end_date)return;
			//s('$diff_day->'.$diff_day);
			$data=array();
			
				$t_array=array();
				if(date("ym",$start_date)==date("ym",$end_date))//同一个月
				{
					$t_array[]=array("time_key"=>date("Y-m-d",$start_date),"start_time"=>date("Y-m-d",$start_date),"end_time"=>date("Y-m-d",$end_date));
				}
				else 
				{
					$dff_month=intval(date("m",$end_date))-intval(date("m",$start_date));
					for($i=0;$i<=$dff_month;$i++)
					{
						$next_month=date("Y-m-d",strtotime(date("Y-m-d",$start_date)."+".$i."month"));
						$next_month_first_day=$start=date('Y-m-01', strtotime($next_month));
						$start='';
						if($i==0)
						{
							$start=date('Y-m-d', $start_date);
							$end=date('Y-m-d', strtotime("$next_month_first_day +1 month -1 day"));
						}
						else if($i==$dff_month)
						{
							$start=date('Y-m-01', strtotime($next_month));
							$end=date('Y-m-d', $end_date);
						}
						else 
						{
							$start=date('Y-m-01', strtotime($next_month));
							$end=date('Y-m-d', strtotime("$next_month_first_day +1 month -1 day"));
						}
						$t_array[]=array("time_key"=>date("Y-m-d",strtotime(date("Y-m-d",$start_date)."+".$i."month")),"start_time"=>$start,"end_time"=>$end);
					}
				}
				if(self::$is_debug)
				{
					s($t_array);
				}
				$pv_count_total=0;
				$uv_count_total=0;
				$t_data_array=array();
				foreach($GLOBALS['province'] as $k=>$v)//省份
				{
					$tmp_data["province"]=$v;
					$pv_count=0;
					$uv_count=0;
					foreach($t_array as $key=>$val)
					{
						$hb=new HbVisitDayAreaModel(date("ym",strtotime($val["start_time"])));
						if(self::$is_debug)
						{
							$hb->debug();
						}
						if($flag==0){
							$rs=$hb->fetchAll(" select sum(pv) as pv_count,sum(uv) as uv_count from  ".$hb->_name." where weiid=$id and province=$k and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
						}else{
							$rs=$hb->fetchAll(" select sum(pv) as pv_count,sum(uv) as uv_count from  ".$hb->_name." where userid=$id and province=$k and statdate>='".$val["start_time"]."' and statdate<='".$val["end_time"]."'");
						}
						
						$hb->close();
						if($rs)
						{
							$pv_count+=$rs[0]["pv_count"];
							$uv_count+=$rs[0]["uv_count"];
						}
					}
					$pv_count_total+=$pv_count;
					$uv_count_total+=$uv_count;
					$tmp_data["pv_count"]=$pv_count;
					$tmp_data["uv_count"]=$uv_count;
					$t_data_array[]=$tmp_data;
				}
				if(is_array($t_data_array))
				{
					$t_sort_array=array();
					foreach($t_data_array as $kk=>$val)
					{
						$t_sort_array[]=$val["pv_count"];
					}
					array_multisort($t_sort_array,SORT_DESC,$t_data_array);
				}
				//s($t_data_array);
				$data["list"][]=$t_data_array;
				$data["pv_count_total"]=$pv_count_total;
				$data["uv_count_total"]=$uv_count_total;
				if(self::$is_debug)
				{
					s($data);
				}
				mc_set($mc_key,$data,86400);
		}
			return $data;
	}
	
	/**
	 * 获取时间访问
	 * $start 开始时间戳
	 * $end 结束时间戳
	 */
	static public function get_date_rang($start_date,$end_date)
	{
		if(!$start_date || !$end_date)return;
		$diff_day=($end_date-$start_date)/86400+1;//相差天数
		$t_array=array();
		
		if($diff_day>1 && $diff_day<10)//10天以内 1天
		{
			for($i=0;$i<$diff_day;$i++)
			{
				$t_array[]=array("time_key"=>date("Y-m-d",strtotime("+".$i."days",$start_date)),"start_time"=>date("Y-m-d",strtotime("+".$i."days",$start_date)),"end_time"=>date("Y-m-d",strtotime("+".$i."days",$start_date)));
			}
		}
		else if(($diff_day>=10 && $diff_day<20) || ($diff_day>=20 && $diff_day<90))//10-20天 2天 20-90 天/周
		{
			$mod_num=0;
			if($diff_day>=10 && $diff_day<20)
			{
				$mod_num=2;
			}
			else
			{
				$mod_num=7;
			}
			$j=1;
			for($i=0;$i<$diff_day;$i++)
			{
				if($j%$mod_num==0)
				{
					$start=date("Y-m-d",strtotime("+".($i-($mod_num-1))."days",$start_date));
					$end=date("Y-m-d",strtotime("+".$i."days",$start_date));
					$t_array[]=array("time_key"=>date("Y-m-d",strtotime("+".($i)."days",$start_date)),"start_time"=>$start,"end_time"=>$end);
				}
				if($diff_day%$mod_num>0 && $i==($diff_day-1))//非倍数
				{
					$start=date("Y-m-d",strtotime("+".($i-($diff_day%$mod_num-1))."days",$start_date));
					$end=date("Y-m-d",strtotime("+".$i."days",$start_date));
					$t_array[]=array("time_key"=>date("Y-m-d",strtotime("+".($i)."days",$start_date)),"start_time"=>$start,"end_time"=>$end);
				}
				$j++;
			}
		}
		else if($diff_day>=90)
		{
			$dff_month=intval(date("m",$end_date))-intval(date("m",$start_date));
			for($i=0;$i<=$dff_month;$i++)
			{
				$next_month=date("Y-m-d",strtotime(date("Y-m-d",$start_date)."+".$i."month"));
				$next_month_first_day=$start=date('Y-m-01', strtotime($next_month));
				$start='';
				if($i==0)
				{
					$start=date('Y-m-d', $start_date);
					$end=date('Y-m-d', strtotime("$next_month_first_day +1 month -1 day"));
				}
				else if($i==$dff_month)
				{
					$start=date('Y-m-01', strtotime($next_month));
					$end=date('Y-m-d', $end_date);
				}
				else
				{
					$start=date('Y-m-01', strtotime($next_month));
					$end=date('Y-m-d', strtotime("$next_month_first_day +1 month -1 day"));
				}
				$t_array[]=array("time_key"=>date("Y-m",strtotime(date("Y-m-d",$start_date)."+".$i."month")),"start_time"=>$start,"end_time"=>$end);
			}
		}
		return $t_array;
	}
	
	
}  
