<?php

require_once  'core/Mysql.php';


/**
 * 该类仅用于操作房友各个城市分库中的表
 *
 */
abstract class My_TableBase
{
    protected  $_dbname;
	protected  $_charset;
	protected  $_name;
	protected  $_primarykey='id';
	protected  $_op;

	protected $_adapter;
	

	abstract protected function getDbName();

     function __construct() {
         $this->_dbname = $this->getDbName();
     }

    /**
     * 获取当前操作表
     *
     */
    function getTable(){
        return $this->_name;
    }

    final public function getAdapter($dbname='') {
		//读取库名
		if(  !$this->_adapter){
			$character=(isset($this->_charset)?$this->_charset : '');
			if($this->_name=='wx_user_fans')
			{
				$character='utf8mb4';
			}
			$dbname	= $this->getDbName();			//EcSysTable=>'shop_admin'
			$conf=self::getDbConfig($dbname,  $character);
			$this->_adapter=new Mysql($conf);  //拿到一个implode(','conf['s]).implode(',',conf)=>这个conf里面不包括conf['s]了
		}
		Return $this->_adapter;
	}


	/**
	 * 根据$where_clause统计总数
	 *
	 * @param unknown_type $where_clause
	 * @return unknown
	 */
	final public function getCount($where_clause) {
		Return $this->scalar('count(*)', $where_clause);
	}


	/**
	 * 获取指定 字段 的信息
	 *
	 * @param unknown_type $select_fields
	 * @param unknown_type $where_clause
	 * @return 单值、数组
	 */
	final public function scalar($select_fields, $where_clause) {
		try{
			$db=$this->getAdapter();
			$sql='select  '.$select_fields.' from '.$this->_name.' '. $where_clause .' limit 1 ';	//. $orderby_clause	." limit ".($currpage-1)*$page_size.','.$page_size;
// 			echo $sql;
            Return $db->scalar($sql);

		} catch(Exception $e) {
			$frontController=Zend_Controller_Front::getInstance();
			if($frontController->throwExceptions()){
			 var_dump($e,$sql);
			}
		}


	}



	/**
	 * 查询获得记录集
	 *
	 * @param $select_fields
	 * @param unknown_type $where_clause
	 * @param unknown_type $orderby_clause
	 * @param unknown_type $currpage
	 * @param unknown_type $page_size
	 * @return 记录集对象
	 */
	final public function query( $select_fields,$where_clause='',$orderby_clause='',$currpage='',$page_size='' ) {
		try{

			$db=$this->getAdapter();

			$sql='select  '.$select_fields.' from '.$this->_name.' '. $where_clause .' '. $orderby_clause	;

			if(!empty($currpage) && !empty($page_size) ){
				$sql .= ' limit '.($currpage-1)*$page_size.','.$page_size;
			}
			
			Return $db->query($sql);

		} catch(Exception $e) {
			$frontController=Zend_Controller_Front::getInstance();
			if($frontController->throwExceptions()){
			  var_dump($e,$sql);
			}
		}

	}

	/**
	*	获取query后的单行记录
	*/
	final public function  fetch($query)
	{
		if($query){
			$db=$this->getAdapter();
			return $db->fetch($query);
		}else{
			Return null;
		}
	}



	/**
	*	获取query后的记录数
	*/
	final public function  numRows($query){
		if($query){
			$db=$this->getAdapter();
			return $db->numRows($query);
		}else{
			Return false;
		}
	}


	/**
	 * 查询数据库键名、键值两字段，返回一个构造的数组
	 *
	 * @param unknown_type $key_field
	 * @param unknown_type $value_field
	 * @param unknown_type $where_clause
	 * @param unknown_type $orderby_clause
	 * @param unknown_type $limit
	 * @return unknown
	 */
	final public function  fetchHash($key_field,$value_field,$where_clause,$orderby_clause='',$limit=null)
	{
		$db=$this->getAdapter();
		$sql="select $key_field as f1 , $value_field as f2  from ".$this->_name." ". $where_clause . $orderby_clause	. ( is_int($limit) ? " limit ".$limit :'');

		$rs=$db->query($sql);

		while($row=$db->fetch($rs)) {
			$arr[$row['f1']]=$row['f2'];
		}

		return $arr;

	}



	/**
	*	存在跨库问题，不推荐使用
	*/
	final public function  fetchAll($sql)
	{
		$db=$this->getAdapter();

		if(is_resource($sql)){
			$rs = $sql;
		}else{
			$rs=$db->query($sql);
		}

		return $db->fetchAll($rs);
	}





	final public function debug() {
		$db=$this->getAdapter();
		$db->setDebug();
	}


    /**
     * 关闭db
     *
     * @param unknown_type $freeLinks
     */
	final public function close() {
		$db=$this->getAdapter();
		$db->close();
		unset($this->_myhs);
	}


	/**
	*	读取sina主从数据库的配置
	*/
	final static public function getDbConfig($database,$charset='')
	{
		return Mysql::getDefaultConfig($database,$charset);
	}


	/**
	*	高级开发人员使用
	*	它可以更改所有 model（继承至同一抽象类,如FyTable,FyView等） 连接的数据库,要十分小心使用
	*/
	final public function switchDB($database,$charset=''){
		$conf=self::getDbConfig($database,$charset);
		$this->_adapter=new Mysql($conf);
		//s($dbName);
	}

	/**
	*	高级开发人员使用
	*	要注意当前连接的数据库,小心使用
	*/
	final public function execute($sql) {
		$db=$this->getAdapter();
		Return $db->query($sql);
	}


	/**
	*	高级开发人员使用
	*	数据模式: select-update/delete-insert
	*/
	final public function setMode($mode) {
		$db=$this->getAdapter();
		Return $db->setMode($mode);
	}
	/**
	*	高级开发人员使用：切换到读写主库
	*	数据模式: select-update/delete-insert
	*/
	final public function selectMaster() {
		$db=$this->getAdapter();
		Return $db->setMode('m-m-m');
	}

	/**
	*	高级开发人员使用：切换到读写主从分离模式
	*	数据模式: select-update/delete-insert
	*/
	final public function selectSlaver() {
		$db=$this->getAdapter();
		Return $db->setMode('s-m-m');
	}
}




