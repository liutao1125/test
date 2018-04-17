<?php
/**
 * Class String
 * 字符串格式化
 */
class Tool_String
{
    const UTF8 = 'utf-8';
    const GBK = 'gbk';

	static public function random($length = 6)
    {
		$length = abs($length);
		$n = max(1, 32 - $length);
		$n = mt_rand(0, $n);
		return substr(md5(uniqid()), $n, $length);
	}
	
	

	/**
	 * 截取字符串,支持字符编码,默认为utf-8
	 *
	 * @param string $string 要截取的字符串编码
	 * @return string 截取后的字串
	 */
	public static function checkUserName($string)
	{
// 	    return 	preg_match("/^[a-z0-9_\x80-\xff]+[^_]$/",$string);
	    return 	preg_match("/^[a-z0-9_]+[^_]$/",$string);
	}
    /**
     * 截取字符串,支持字符编码,默认为utf-8
     *
     * @param string $string 要截取的字符串编码
     * @param int $start     开始截取
     * @param int $length    截取的长度
     * @param string $charset 原妈编码,默认为UTF8
     * @param boolean $dot    是否显示省略号,默认为false
     * @return string 截取后的字串
     */
    public static function substr($string, $start, $length, $charset = self::UTF8, $dot = true)
    {
        switch (strtolower($charset)) {
            case self::GBK:
                $string = self::substrForGbk($string, $start, $length, $dot);
                break;
            case self::UTF8:
                $string = self::substrForUtf8($string, $start, $length, $dot);
                break;
            default:
                $string = substr($string, $start, $length);
        }
        return $string;
    }

    /**
     * 求取字符串长度
     *
     * @param string $string  要计算的字符串编码
     * @param string $charset 原始编码,默认为UTF8
     * @return int
     */
    public static function strlen($string, $charset = self::UTF8)
    {
        switch (strtolower($charset)) {
            case self::GBK:
                $count = self::strlenForGbk($string);
                break;
            case self::UTF8:
                $count = self::strlenForUtf8($string);
                break;
            default:
                $count = strlen($string);
        }
        return $count;
    }

    /**
     * 将变量的值转换为字符串
     *
     * @param mixed $input   变量
     * @param string $indent 缩进,默认为''
     * @return string
     */
    public static function varToString($input, $indent = '')
    {
        switch (gettype($input)) {
            case 'string':
                return "'" . str_replace(array("\\", "'"), array("\\\\", "\\'"), $input) . "'";
            case 'array':
                $output = "array(\r\n";
                foreach ($input as $key => $value)
                {
                    $output .= $indent . "\t" . self::varToString($key, $indent . "\t") . ' => ' . self::varToString(
                            $value, $indent . "\t");
                    $output .= ",\r\n";
                }
                $output .= $indent . ')';
                return $output;
            case 'boolean':
                return $input ? 'true' : 'false';
            case 'NULL':
                return 'NULL';
            case 'integer':
            case 'double':
            case 'float':
                return "'" . (string) $input . "'";
        }
        return 'NULL';
    }

    /**
     * 以utf8格式截取的字符串编码
     *
     * @param string $string  要截取的字符串编码
     * @param int $start      开始截取
     * @param int $length     截取的长度，默认为null，取字符串的全长
     * @param boolean $dot    是否显示省略号，默认为false
     * @return string
     */
    public static function substrForUtf8($string, $start, $length = null, $dot = false)
    {
        $l = strlen($string);
        $p = $s = 0;
        if (0 !== $start)
        {
            while ($start-- && $p < $l)
            {
                $c = $string[$p];
                if ($c < "\xC0")
                    $p++;
                elseif ($c < "\xE0")
                    $p += 2;
                elseif ($c < "\xF0")
                    $p += 3;
                elseif ($c < "\xF8")
                    $p += 4;
                elseif ($c < "\xFC")
                    $p += 5;
                else
                    $p += 6;
            }
            $s = $p;
        }

        if (empty($length))
        {
            $t = substr($string, $s);
        }
        else
        {
            $i = $length;
            while ($i-- && $p < $l)
            {
                $c = $string[$p];
                if ($c < "\xC0")
                    $p++;
                elseif ($c < "\xE0")
                    $p += 2;
                elseif ($c < "\xF0")
                    $p += 3;
                elseif ($c < "\xF8")
                    $p += 4;
                elseif ($c < "\xFC")
                    $p += 5;
                else
                    $p += 6;
            }
            $t = substr($string, $s, $p - $s);
        }

        $dot && ($p < $l) && $t .= "...";
        return $t;
    }

    /**
     * 以gbk格式截取的字符串编码
     *
     * @param string $string  要截取的字符串编码
     * @param int $start      开始截取
     * @param int $length     截取的长度，默认为null，取字符串的全长
     * @param boolean $dot    是否显示省略号，默认为false
     * @return string
     */
    public static function substrForGbk($string, $start, $length = null, $dot = false)
    {
        $l = strlen($string);
        $p = $s = 0;
        if (0 !== $start) {
            while ($start-- && $p < $l)
            {
                if ($string[$p] > "\x80")
                    $p += 2;
                else
                    $p++;
            }
            $s = $p;
        }

        if (empty($length))
        {
            $t = substr($string, $s);
        }
        else
        {
            $i = $length;
            while ($i-- && $p < $l) {
                if ($string[$p] > "\x80")
                    $p += 2;
                else
                    $p++;
            }
            $t = substr($string, $s, $p - $s);
        }

        $dot && ($p < $l) && $t .= "...";
        return $t;
    }

    /**
     * 以utf8求取字符串长度
     *
     * @param string $string     要计算的字符串编码
     * @return int
     */
    public static function strlenForUtf8($string)
    {
        $l = strlen($string);
        $p = $c = 0;
        while ($p < $l) {
            $a = $string[$p];
            if ($a < "\xC0")
                $p++;
            elseif ($a < "\xE0")
                $p += 2;
            elseif ($a < "\xF0")
                $p += 3;
            elseif ($a < "\xF8")
                $p += 4;
            elseif ($a < "\xFC")
                $p += 5;
            else
                $p += 6;
            $c++;
        }
        return $c;
    }

    /**
     * 以gbk求取字符串长度
     *
     * @param string $string     要计算的字符串编码
     * @return int
     */
    public static function strlenForGbk($string)
    {
        $l = strlen($string);
        $p = $c = 0;
        while ($p < $l)
        {
            if ($string[$p] > "\x80")
                $p += 2;
            else
                $p++;
            $c++;
        }
        return $c;
    }

    /**
     * 将字符串首字母小写
     *
     * @param string $str
     *        	待处理的字符串
     * @return string 返回处理后的字符串
     */
    public static function lcfirst($str)
    {
        $str[0] = strtolower($str[0]);
        return $str;
    }

    /**
     * 获得随机数字符串
     *
     * @param int $length
     *        	随机数的长度
     * @return string 随机获得的字串
     */
    public static function getRandStr($length)
    {
//         $mt_string = 'AzBy0CxDwEv1FuGtHs2IrJqK3pLoM4nNmOlP5kQjRi6ShTgU7fVeW8dXcY9bZa';
        $mt_string = 'yxwvuts2rq3p4nm5kji6hg7fe8dc9ba';
        $randstr = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randstr .= $mt_string[mt_rand(0, 30)];
        }
        return $randstr;
    }

    /**
     * 对字符串中的参数进行替换
     *
     * 该函优化了php strtr()实现, 在进行数组方式的字符替换时支持了两种模式的字符替换:
     *
     * @example <pre>
     *          1. echo Tool_String::strtr("I Love {you}",array('{you}' =>
     *          'lili'));
     *          结果: I Love lili
     *          2. echo Tool_String::strtr("I Love
     *          #0,#1",array('lili','qiong'));
     *          结果: I Love lili,qiong
     *          <pre>
     * @param string $str
     * @param string $from
     * @param string $to
     *        	可选参数,默认值为''
     * @return string
     */
    public static function strtr($str, $from, $to = '')
    {
        if (is_string($from))
        {
            return strtr($str, $from, $to);
        }
        if (isset($from[0]))
        {
            foreach ($from as $key => $value)
            {
                $from['#' . $key] = $value;
                unset($from[$key]);
            }
        }
        return !empty($from) ? strtr($str, $from) : $str;
    }

    public static function filter_emoji($str){
        return Tool_String::filterMutilByteSpecialChar($str);

        // Match Emoticons
//        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u'; // 无法匹配 8字节的 英国国旗 emoji 扩大范围
//        $regexEmoticons = '/[\x{1F1E7}-\x{1F64F}]/u';
        $regexEmoticons = '/[\x{1F000}-\x{1F64F}]/u'; // 继续扩大范围
        $clean_text = preg_replace($regexEmoticons, '', $str);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        //过滤上面未能过滤的多字节字符
        if(!empty($clean_text)){
            $clean_text = Tool_String::filterMutilByteSpecialChar($clean_text);
        }

        return $clean_text;
    }

    /**
     * 加密手机号码，中间使用*号显示数字
     * @param $phone
     * @return mixed
     */
    public static function encodeMobilePhone($phone)
    {
        if($phone)
        {
            return substr_replace($phone,'****',3,4);
        }
        return false;
    }

    /**
     * 过滤多字节字符
     * @param  $string
     * @return string
     */
    public static function filterMutilByteSpecialChar($string) {
        $str = '';
        $array = array();
        $strlen = mb_strlen($string,'UTF-8');
        while ($strlen) {
            $s = mb_substr($string, 0, 1,'UTF-8');
            if(strlen($s) < 4){
                $str .= $s;
            }
            $string = mb_substr($string, 1, $strlen, 'UTF-8');
            $strlen = mb_strlen($string, 'UTF-8');
        }
        $str = trim($str);
        $str = str_replace('　', '', $str);
        return $str;
    }
}