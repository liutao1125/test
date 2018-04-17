<?php

/*

	注意：
	对于临时缓存，同域名下的的key值相通，不同域名下的的key值不相通，
	对于持久缓存key值均相通
*/

//==========================================================================
//-----Memcache 内存临时缓存---------
define('MEMCACHE_HANDLE','__memcached__');
function mc_init($host='') {
	$key = MEMCACHE_HANDLE.$host;
	if( !(isset($GLOBALS[$key]) && is_object($GLOBALS[$key])) ){
		$name = "SINASRV_MEMCACHED_SERVERS". ( !empty($host) ? ":".str_replace('.','_',$host) : '');
		$servers = isset($_SERVER[$name]) ? $_SERVER[$name] : $_SERVER['SINASRV_MEMCACHED_SERVERS'];

		$cache = new Memcache();
		$servers=str_replace(' ',',',$servers);

		$servers = explode(",", $servers);
		foreach($servers as $val)	{
			$conf = explode(":", $val);
			$cache->addServer($conf[0], $conf[1]);

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
	return '';//缓存统一
	//Return isset($prefix)  ? $prefix : strtolower($_SERVER['HTTP_HOST']);
	$perfix = $_SERVER['SINASRV_MEMCACHED_KEY_PREFIX'];
	if(empty($perfix)){
		return '';
	}
	return $perfix;

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
	$cache->set(mc_prefix($prefix).$name, null, 0);
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



?>