<?php

$domain=explode('.',HTTP_HOST);
//ip访问
if(is_numeric($domain[0]) && HTTP_HOST!='120.55.194.5')exit;
//设置系统使用 子域名=》对应module
foreach($GLOBALS['sys_module'] as $subname=>$module){
	foreach($module["domain"] as $key=>$val)
	{
		if(HTTP_HOST==$val){
			define('SYS_MODULE',$subname);
			define("BASE_DOMAIN",'http://'.$val);
			break;
		}
	}
}
define("CACHE_FIX","sxzx");
//model没有定义

if (!defined('SYS_MODULE')) {
	exit;
}
//=检查系统是否已发布
define('SYS_RELEASE', isset($_SERVER["SYS_RELEASE"])?$_SERVER["SYS_RELEASE"]: 0 );	//发布状态 ： 2 在线 ，1 测试 ，0或无 开发
switch(SYS_RELEASE){
		case '2':
			ini_set('display_errors','Off');
			error_reporting(E_ALL ^ E_NOTICE | E_PARSE);
			define("WHB_DOMAIN","http://admin.sx985.com");
			define("IMG_DOMAIN","http://res.dodoca.com");
			define("API_DOMAIN","http://data.sx985.com");
			define("IMG_UPLOAD","http://file.sx985.com");
			define("IMG_DOMAIN_NEW","http://pic.sx985.com");
			define('SYS_VER',$GLOBALS['sys_module'][SYS_MODULE]["ver"]["sc"]);
			define('STATIC_DOMAIN',"http://static.sx985.com");
			break;
		case '1':
			ini_set('display_errors','On');
			error_reporting(E_ALL ^ E_NOTICE);
			define("WHB_DOMAIN","http://t.sxzx.dodoca.com");
			define("IMG_DOMAIN","http://t.res.dodoca.com");
			define("API_DOMAIN","http://t.data.sxzx.dodoca.com");
			define("IMG_UPLOAD","http://t.file.sxzx.dodoca.com");
			define("IMG_DOMAIN_NEW","http://t.pic.sxzx.dodoca.com");
			define('SYS_VER',$GLOBALS['sys_module'][SYS_MODULE]["ver"]["test"]);
			define('STATIC_DOMAIN',"/www");
			break;
		default:
			ini_set('display_errors','On');
			error_reporting(E_ALL ^ E_NOTICE);
			define("WHB_DOMAIN","http://sxzx.dodoca.dev");
			define("IMG_DOMAIN","http://img.dodoca.dev");
			define("API_DOMAIN","http://data.dodoca.dev");
			define("APP_DOMAIN",'http://t.app.dodoca.com');
			define("IMG_UPLOAD","http://file.dodoca.dev");
			define("IMG_DOMAIN_NEW","http://pic.dodoca.dev");
			define('SYS_VER',$GLOBALS['sys_module'][SYS_MODULE]["ver"]["loc"]);
			define('STATIC_DOMAIN',"/www");
			break;
}

unset($tmp_key);
unset($t);

?>