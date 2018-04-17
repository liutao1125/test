<?php

/*
session默认使用file作为存储，因为IO关系会影响性能，所以使用Memory内存作为缓存处理
*/

function mc_ses_open($path, $name)
{
	 return true;
}

function mc_ses_close()
{	
	//$expire = (int) session_cache_expire()*60;
	//mc_set(mc_ses_get_id(session_id()), $GLOBALS['__session'], $expire);
	//var_dump($GLOBALS['__session']);
	return true;
}

function mc_ses_read($id)
{		
	$id=mc_ses_get_id($id);
	//s($id);
	Return  mc_get($id) ;
}

function mc_ses_write($id, $data)
{	
	//$GLOBALS['__session'] = $data;
	//show(session_cache_expire());
	$id=mc_ses_get_id($id);
	$expire = (int) session_cache_expire()*60;
	mc_set($id, $data, $expire);
	//var_dump($id,$data,$expire);	
	Return true;
}

function mc_ses_destroy($id)
{	
	$id=mc_ses_get_id($id);
	mc_unset($id);
	Return true;
}

function mc_ses_get_id($id)
{
	return $id;
}

function mc_ses_gc($maxlt)
{
	// garbage collection is done on the memcached server, no need to do it here... 
}

if (!session_set_save_handler ('mc_ses_open', 'mc_ses_close' , 'mc_ses_read', 'mc_ses_write', 'mc_ses_destroy', 'mc_ses_gc'))
{
	die('Set handling for Memory sessioning failed...');
}

?>
