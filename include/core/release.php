<?php	//ANSI

//==============================================


$GLOBALS['cityOpts']=array(


 );

//城市所在机房编号： 空=上海 ， 1=广州 ，2=天津
$GLOBALS['cityRoom']=array(

);
  
//已经发布上线的城市
$GLOBALS['cityReleasedOpts']=$GLOBALS['cityOpts'];

//上线用新图库站点
$GLOBALS['new_pic_system']=array(
		'wx'=>'无锡',

);



$GLOBALS["provice_city"]=array(
	
		);


$GLOBALS['city_ord']=array(

);
$GLOBALS['city_alliance']=array(

);


//==================================================================================

 
$EcName='eju';
//三种发布状态的域名后缀  2 在线 ，1 测试 ，0或无 开发
$Suffix = array(
			'2'=>array($EcName=>'.com',	'baidu'=>'.com','sina'=>'.com.cn','fangyou'=>'.com','resfyou'=>'.com'),
			'1'=>array($EcName=>'.com',	'baidu'=>'.cn','sina'=>'.cn','fangyou'=>'.cn','resfyou'=>'.com'),
			'0'=>array($EcName=>'.dev',	'baidu'=>'.bz','sina'=>'.bz','fangyou'=>'.bz','resfyou'=>'.dev'),
			);


define('SYS_NAME',$EcName);
define('SYS_RELEASE', isset($_SERVER["SINASRV_SYS_RELEASE"])?$_SERVER["SINASRV_SYS_RELEASE"]: 0 );	//发布状态 ： 2 在线 ，1 测试 ，0或无 开发
$server=$_SERVER['HTTP_HOST'];
if(strpos($server,".dev")!==false)//开发
{
	define('SYS_IS_TEST', '1');
	define('BASE_PIC_URL', 'my.image.dev');
}
else if(strpos('/'.$server,"/f.")!==false || strpos($server,"sandbox")!==false || strpos($server,"f.my")!==false || strpos($server,"f.ser")!==false ||   strpos($server,"f.data")!==false)//测试
{
	define('SYS_IS_TEST', '2');
	//define('BASE_PIC_URL', 'my.image.dev');
}
else
{
	define('SYS_IS_TEST', '3');
	define('BASE_PIC_URL', 'timg.fangyou.com');
}
unset($server);
define('DOMAIN_ROOT', 'esf.'.SYS_NAME.$Suffix[SYS_RELEASE][SYS_NAME]);
 

define("DOMAIN_ROOT_FY",	'fangyou'.$Suffix[SYS_RELEASE]['fangyou']);
define("DOMAIN_ROOT_BAIDU",	'baidu'.$Suffix[SYS_RELEASE]['baidu']);
define("DOMAIN_ROOT_SINA",	'sina'.$Suffix[SYS_RELEASE]['sina']);
define("DOMAIN_ROOT_RES",	'resfyou'.$Suffix[SYS_RELEASE]['resfyou']);	//新增2011-02-21
 

define("DOMAIN_ESF_BAIDU",	'esf.'.DOMAIN_ROOT_BAIDU);
define("DOMAIN_ZUF_BAIDU",	'rent.'.DOMAIN_ROOT_BAIDU);
define("DOMAIN_ESF_SINA",	'esf.'.DOMAIN_ROOT_SINA);
define("DOMAIN_ZUF_SINA",	'zufang.'.DOMAIN_ROOT_SINA);


switch(SYS_RELEASE){
	case '2':
		ini_set('display_errors','off');
		break;
	case '1':
	case '0':
	default:
		ini_set('display_errors','On');
		error_reporting(E_ALL ^ E_NOTICE);
		break;
}

//========清除已无用的变量=========
unset($Suffix  );
?>