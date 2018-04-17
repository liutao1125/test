<?php
class WxLocation extends My_EcSysTable
{
	public $_name = "wx_location";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_int($para, $data, 'userid');
		if(array_key_exists('map_location',$para))
		{
			$data["map_location"]=$para["map_location"];
		}
		$this->fill_str($para, $data, 'title');
		$this->fill_str($para, $data, 'description');
		$this->fill_int($para, $data, 'status');
		$this->fill_int($para, $data, 'cdate');
		$this->fill_int($para, $data, 'udate');
		
		return $data;
	}

	public function insert_data($data, $isImport = false)
	{
		if($isImport == false){
			$data['userid'] = get_uid();
		}
		$sql = "insert into wx_location (userid, map_location, title, description, status, cdate, udate) values ( ".$data['userid'].", GeomFromText('POINT(".trim($data['x'])." ".trim($data['y']).")')
		       ,'".trim($data['title'])."', '".trim($data['description'])."', 1, ".time().", ".time().")";
		return $this->execute($sql);
	}

	public function update_data($data, $where)
	{
		$sql = "update wx_location set map_location = GeomFromText('POINT(".trim($data['x'])." ".trim($data['y']).")'), 
		       title = '".$data['title']."', description = '".$data['description']."', udate = ".time()." where $where";
		return $this->execute($sql);
	}

	public function delete_data($where)
	{
		$data['status'] = -1;
		$data['udate'] = time();
		return $this->update($data, $where);
	}

	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 * $id 表主键
	 */
	public function get_row_byid($id)
	{
		if(!$id || !is_numeric($id))return;
		$data = $this->scalar("*,X(map_location) as x,Y(map_location) as y"," where  ".$this->_primarykey."=".$id." and status = 1");
		return $data;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取坐标值
	 */
	function get_near_location($lx,$ly,$uid)
	{
		$sql_qry="set @center=geomfromtext('POINT(".$lx.' '.$ly.")');";
		$sql_r= $this->fetchAll($sql_qry);
		$sql_qry=" SELECT title,description,SQRT(POW(ABS(X(map_location)-X(@center)),2)+POW(ABS(Y(map_location)-Y(@center)),2))*100000 AS distance
		FROM  wx_location where userid=$uid and  status=1 and  map_location is not null order by distance asc limit 1";
		$rs= $this->fetchAll($sql_qry);
		return $rs;
	}

}

?>