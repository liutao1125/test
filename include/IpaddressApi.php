<?php

/**
 * @author liqipeng
 * IP 地址获取
 */
class IpaddressApi {

    private static $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=';

    function __construct() {
        //  self::$url = $this->url;
    }

    /*
     * param   $queryIP  input ipaddress
     * return  province  ===>   id
     */

    static function getIPLoc_sina($queryIP) {
        $url = self::$url . $queryIP;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_ENCODING, 'utf8');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回 
        $location = curl_exec($ch);
        $location = json_decode($location);
        curl_close($ch);
        $loc = "";
        if ($location === FALSE)
            return "";
        if (empty($location->desc)) {
            $loc = $location->province;
        } else {
            $loc = $location->desc;
        }
        $loc = $location->province;
        return $loc;
    }

    //  输入的ip  eg  127.0.0.1
    // 返回0 代表 不成功   返回大于0的 int类型  代表 省份id
    function getProvinceId($ip = '') {

	
        if (trim($ip) && trim($ip) !='127.0.0.1') {

            $province_array = &$GLOBALS['province'];
            $province_name = self::getIPLoc_sina($ip);
            if (!empty($province_name)) {
                if (isset($province_array) && is_array($province_array)) {
                    $_area_id = array_search($province_name, $province_array);
                }
            }
        }
        return isset($_area_id) && $_area_id > 0 ? $_area_id : 0;
    }

}

?>
