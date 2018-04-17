<?php
/**
 * 验证类
 */
class Validate
{

	/**
	 * 各种验证规则
	 * @var <array>
	 */
	private static $_rules = array(
		'email'    => '/^[a-z0-9]+[._\-\+]*@([a-z0-9]+[-a-z0-9]*\.)+[a-z0-9]+$/',
		'url'      => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
		'currency' => '/^\d+(\.\d+)?$/',
		'mobile'   => '/^1[3458]\d{9}$/',
		'hd_mobile'=>'/^1[1-9]\d{2}[x\d][x\d]\d{5}$/'
	);

	/**
	 * @todo	是否字母加数字
	 * @param <type> $value
	 * @return <type>
	 */
	public static function is_alnum( $value )
    {
        return ctype_alnum( $value );
    }

   /**
    * @todo	是否字母
    * @param <type> $value
    * @return <type>
    */
    public static function is_alpha( $value )
    {
        return ctype_alpha( $value );
    }

	/**
	 * @todo	是否数字(必须是字符串类型)ctype_digit('42')为true，ctype_digit(42)为false
	 * @param <type> $value
	 * @return <type>
	 */
	public static function is_digits( $value )
    {
        return ctype_digit( $value );
    }

	/**
	 * @todo	是否数字is_num('42')和is_num(42)均返回true
	 * @param <type> $value
	 * @param <type> $max
	 * @return <type>
	 */
	public static function is_num( $value, $max = null )
    {
    	$regexp = $max ? '/^\d{1,'.$max.'}$/' : '/^\d+$/';
    	return preg_match($regexp, $value);
    }

	/**
	 * 判断是否id的格式
	 * @param <int> $value
	 * @return <boolean>
	 */
	public static function is_id( $value )
	{
		$is_num = self::is_num( $value );
		if( $is_num )
		{
			return $value > 0;
		}
		return false;
	}

	/**
	 * @todo	是否email
	 * @param <type> $value
	 * @return <type>
	 */
	public static function is_email( $value )
	{
		return self::regx( $value, 'email' );
	}

	/**
	 * @todo	是否货币
	 * @param <type> $value
	 * @return <type>
	 */
	public static function is_currency( $value )
	{
		return self::regx( $value, 'currency' );
	}

	/**
	 * @todo	是否url
	 * @param <type> $value
	 * @return <type>
	 */
	public static function is_url( $value )
	{
		return self::regx( $value, 'url' );
	}

	/**
	 * @todo	是否手机号
	 * @param <type> $value
	 * @return <type>
	 */
	public static function is_mobile( $value )
	{
		return self::regx( $value, 'mobile' );
	}

	/**
	 * 是否有效数据（不为空即为有效）
	 * @param <string> $value
	 * @return boolean
	 */
	public static function is_effective( $value )
	{
		return !empty($value);
	}

	/**
	 * 是否不是空字符串
	 *
	 * @param string $value
	 * @return boolean
	 */
	public static function no_empty_str($value)
	{
	    return ('' != $value);
	}

	/**
	 * 是否大于0
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public static function great_zero($value)
	{
	    return ($value > 0);
	}

	/**
	 * @todo	通用验证
	 * @param <type> $value
	 * @param <type> $type
	 * @return <type>
	 */
	public static function regx( $value, $type )
	{
		$v_type  = strtolower( $type );
		$pattern = empty(self::$_rules[$v_type]) ? $type : self::$_rules[$v_type] ;
		if(!preg_match("/^\/.*?\/$/", $pattern))
		{
			return false;
		}
		$num = preg_match( $pattern, $value );
		if (false === $num || 0 === $num)
		{
			return false;
		} else
		{
		    return true;
		}
	}

	/**
	 * @todo	验证email
	 * @param string $email
	 * @return bool
	 */
	public static function new_is_email($email)
	{
		return preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/", $email);
	}

	/**
	 * @ignore
	 */
	public function  __call($name, $arguments)
	{
		return 'unkown method';	//错误信息不可修改
	}

	/*
		恒大特殊项目
	*/
	public function hd_mobile($value) {
		return self::regx( $value, 'hd_mobile' );
	}
}