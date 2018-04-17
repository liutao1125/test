<?php

/**
 *   加密基础类库
 *
 * @author      Lin.x
 * @description 加密基础类库
 * @date        2015-5-29 15:46:42
 */
class Tool_Encrypt
{
    const HASHKEY = 'SXZX@DODOCA';  //加密字符串

    /**
     * 数据库md5机密方法
     * @param $string
     * @return string
     */
    public static function dbmd5($string)
    {
        return md5('' . md5($string));
    }

    /**
     *  生成密码
     * @param  string $password
     * @return string
     * @author lin.x
     * @example Tool_Encrypt::password('1234');
     */
    public static function password($password)
    {
        return md5(self::HASHKEY . md5($password));
    }

    /**
     * 校验密码
     * @param $password
     * @param $hash
     * @return bool
     * @author lin.x
     */
    public static function checkPassword($password, $hash)
    {
        return self::password($password) === $hash;
    }

    /**
     * @return string
     * 生产tokenKEY
     */
    public static function createTokenKey()
    {
        return uniqid().rand(10,1000);
    }

    /**
     * 生产token
     * @param $key
     * @return bool
     */
    public function createToken($key)
    {
        if(!isset($_SESSION['token'][$key]))
        {
            $_SESSION['token'][$key] = md5($key);
        }
        return true;
    }

    /**
     * 验证token
     * @param $key
     * @return bool
     */
    public static function checkToken($key)
    {
        if(isset($_SESSION['token'][$key]))
        {
            if($_SESSION['token'][$key] === md5($key))
            {
                $_SESSION['token'][$key] = '';
                return true;

            }
        }
        return false;
    }
	// 对参数进行签名
	public static function sign($data)
	{
		ksort($data);
		$str = self::createLinksString($data);
		return PubFun::encrypt(md5($str), 'E');
	}
	protected static function createLinksString($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
            if(empty($val)) {
                continue;
            }
			$arg .= $key . "=" . $val . "&";
		}
		//去掉最后一个&字符
		$arg = substr($arg, 0, count($arg)-2);

		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

		return $arg;
	}
}
