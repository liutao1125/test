<?php	

/*

	注意：
	对于临时缓存，同域名下的的key值相通，不同域名下的的key值不相通，
	对于持久缓存key值均相通
*/

//==========================================================================
//-----Memcache 内存临时缓存---------
define('MEMCACHE_HANDLE','__memcache__');
function mc_init($host='') {
	$key = MEMCACHE_HANDLE.$host;
	if( !(isset($GLOBALS[$key]) && is_object($GLOBALS[$key])) ){		
		
		$name = "SINASRV_MEMCACHED_SERVERS". ( !empty($host) ? ":".str_replace('.','_',$host) : '');
		$servers = isset($_SERVER[$name]) ? $_SERVER[$name] : $_SERVER['SINASRV_MEMCACHED_SERVERS'];

		$cache = new Memcache();
		$servers = explode(",", $servers);
		foreach($servers as $val)
		{
			$v = explode(":", $val);
			$cache->addServer($v[0], $v[1]);
		}
		$GLOBALS[$key] = $cache;
	}
	return $GLOBALS[$key];
}
/*
	关闭缓存连接
*/
function mc_close(){
	$key = MEMCACHE_HANDLE;
	if( (isset($GLOBALS[$key]) && is_object($GLOBALS[$key])) ){		
		$cache=$GLOBALS[$key];
		$cache->close();
		unset($GLOBALS[$key]);
	}
}
/*
	设置缓存前缀
*/
function mc_prefix($prefix=null) {
	Return isset($prefix)  ? $prefix : strtolower($_SERVER['HTTP_HOST']);
}
/*
	写入缓存
*/
function mc_set($name, $value , $ttl = 3600) {
	if(empty($name))return;
	$cache= mc_init();	
	Return	$cache->set(mc_prefix().$name, $value, false, $ttl);
}
/*
	读取缓存
*/
function mc_get($name, $default=null) {
	if(empty($name))return;
	$cache= mc_init();	
	$ret=$cache->get(mc_prefix().$name);
	Return $ret?$ret:$default;
}
/*
	清除缓存,允许跨域清除，但不允许跨域读写
	todo:  缓存服务器分布部署时会存在问题
*/
function mc_unset($name, $prefix = null) {
	if(empty($name))return;
	$cache= mc_init($prefix);	
	$cache->set(mc_prefix($prefix).$name, null, false, 0);
	$cache->delete(mc_prefix($prefix).$name);
}
function mc_isset() {
	//未用，暂不实现
}

function mc_inc($name,$val=1) {
	if(empty($name))return;
	$cache= mc_init();	
	$ret = $cache->increment(mc_prefix().$name,$val);
	if($ret===false)	$cache->set(mc_prefix().$name,$val);
	Return $ret;
}
function mc_dec($name,$val=1 ) {
	if(empty($name))return;
	$cache= mc_init();	
	Return	$cache->decrement(mc_prefix().$name,$val);
}
 




//================================pc+dc==========================================
//----TokyoTyrant 持久缓存---
define('TYRANT_HANDLE','__tyrant__');
define('TYRANT_PREFIX','');
function pc_init($name) {
	
	$servers = explode(",", $_SERVER["SINASRV_TYRANT_SERVERS"]);	
	 
	$index = hexdec(substr(  md5($name) , 0,2)); 
	$index = $index % count($servers) ;
	$key = TYRANT_HANDLE.$index;	 

	if( !(isset($GLOBALS[$key]) && is_object($GLOBALS[$key])) ){	
		require_once('MyTT.php');
		$cache = new MyTT();	
		
		//1.连接指定服务器
		$svr = $servers[$index];		
		$v = explode(":", $svr);
		$succ=$cache->addServer($v[0], $v[1]);

		//2.检查指定服务器，如失败则连接其他服务器
		if(!$succ){
			unset($servers[$index]);	//移除指定服务器
			if(is_array($servers)){
				foreach($servers as $svr){
					$v = explode(":", $svr);
					$succ=$cache->addServer($v[0], $v[1]);
				}
			}
		}
		
		$GLOBALS[$key] = $cache;
	}
	return $GLOBALS[$key];
}
/*
	关闭缓存连接
*/
function pc_close(){
	$count = substr_count($_SERVER["SINASRV_TYRANT_SERVERS"], ','); 
	for($i=0;$i<=$count;$i++){
		$key = TYRANT_HANDLE.$i;
		if( (isset($GLOBALS[$key]) && is_object($GLOBALS[$key])) ){		
			$cache=$GLOBALS[$key];
			$cache->close();
			unset($GLOBALS[$key]);
		}
	}
}
/*
*	设置持久缓存：$name键名,$value键值,$dc是否同时在数据缓存中备份
*	$dc 为true时，键名长度限定为25。
*/
function pc_set($name, $value ,$dc=false ) {
	if(empty($name))return;
	$cache = pc_init($name);	
	if($dc) dc_set($name, $value);
	Return	$cache->set(TYRANT_PREFIX.$name, $value, false, 0);
}
/*
*	获取持久缓存：$name键名(长度50),$dc是否检查数据缓存备份
*	$dc 为true时，键名长度限定为50。
*/
function pc_get($name,$dc=false) {
	if(empty($name))return;
	$cache = pc_init($name);	
	$ret=$cache->get(TYRANT_PREFIX.$name);	
	
	if($dc){
		if(!$ret){
			//如缓存未命中，则直接从库中读取，并同时设置该缓存
			$ret = dc_get($name,null);
			if($ret) pc_set($name, $ret ,false);
		}
		Return $ret;
	}else{
		Return $ret?$ret:null;
	}
}
function pc_unset($name,$dc=false) {
	if(empty($name))return;	 
	$cache = pc_init($name);	
	$cache->set(TYRANT_PREFIX.$name, null, false, 0);
	$cache->delete(TYRANT_PREFIX.$name);
	if($dc) dc_unset($name);
}
function pc_isset() {
	//未用，暂不实现
}

function pc_inc($name,$val=1) {
	//if(empty($name))return;
	$cache = pc_init($name);	
	$ret = $cache->increment(TYRANT_PREFIX.$name,$val);
	//if($ret===false)	$GLOBALS[TYRANT_HANDLE]->set(TYRANT_PREFIX.$name,$val);
	Return $ret;
}
function pc_dec($name,$val=1) {
	//if(empty($name))return;
	$cache = pc_init($name);		
	Return	$cache->decrement(TYRANT_PREFIX.$name,$val);
}




//==========================================================================
//------DB 缓存 性能较差，慎用！！！-------------
function dc_get($name, $default=null) { 
  $name=dc_name($name);
  $table=new SysVariable(); 
  $var=$table->scalar('value',"where name='$name'");
  unset($table);
  return isset($var) ? unserialize($var) : $default;
}
function dc_set($name, $value) {	
  $name=dc_name($name);
  $table=new SysVariable();
  //$db->query("DELETE FROM variable WHERE name = '$name'; INSERT INTO variable (name, value) VALUES ('$name', '".serialize($value)."')" );  
  $table->execute("REPLACE INTO {$table->_name} (name, value) VALUES ('$name', '".serialize($value)."')");
  unset($table);
}
function dc_unset($name) {	
  $name=dc_name($name);
  $table=new SysVariable(); 
  $table->execute("DELETE FROM {$table->_name} WHERE name = '{$name}' ");
  unset($table);
}
//检查name varchar(50)字段长度
function dc_name($name) {
	if(strlen($name)>50)$name=md5($name);
	return $name;
}



/*
	程序结束时注册关闭缓存连接
*/
/*
function mc_pc_close() {
	mc_close();
	pc_close();
	return true;
}
//Memcache::addServer() will be automatically closed at the end of script execution
//register_shutdown_function('mc_pc_close');
*/

/*

//==========================================================================
//----MC+DB持久缓存---
//$name 25个 英文字符，默认一天
function pc2_set($name, $value , $ttl = 86400 ) {	
	mc_set( $name,  $value , $ttl);
	dc_set($name, $value);
}
function pc2_get($name, $default=null  ) {	
	$value=mc_get($name);
	if(!$value){
		$value=dc_get($name, $default);
		mc_set( $name,  $value );
	}
	Return $value;
}








//==========================================================================
//------用户标识 持久缓存--------
function user_get($name, $default=null,$uid=null) { 
  return pc_get(($uid?$uid:get_uid()).":".$name, $default=null);
}

function user_set($name, $value,$uid=null) {
   pc_set(($uid?$uid:get_uid()).":".$name, $value);
}
*/


//----end 变量缓存 -------------------------






?>