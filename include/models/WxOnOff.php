<?php

class WxOnOff extends My_EcSysTable {

    public $_name = 'wx_on_off';
    public $_primarykey = 'userid';

    function prepareData($para) {
        $data = array();
        $this->fill_int($para, $data, 'userid');
        $this->fill_int($para, $data, 'wewebsite_imagebg');
        $this->fill_int($para, $data, 'wewebsite_share');
        $this->fill_int($para, $data, 'wewebsite_menu');
        $this->fill_int($para, $data, 'wewebsite_topmenu');
        $this->fill_int($para, $data, 'wewebsite_place');
        $this->fill_int($para, $data, 'signature');
        $this->fill_int($para, $data, 'signatureId');
        $this->fill_int($para, $data, 'music_flag');
        $this->fill_int($para, $data, 'translate_flag');
        $this->fill_int($para, $data, 'weather_flag');
        $this->fill_int($para, $data, 'joke_flag');
        $this->fill_int($para, $data, 'kuaidi_flag');
        $this->fill_int($para, $data, 'train_flag');
        $this->fill_int($para, $data, 'auto_reply');
        $this->fill_int($para, $data, 'app_flag');
        $this->fill_int($para, $data, 'wegame_scratch');
        $this->fill_int($para, $data, 'wegame_egg');
        $this->fill_int($para, $data, 'wegame_bigwheel');
        $this->fill_int($para, $data, 'wegame_star');
        $this->fill_int($para, $data, 'wegame_scratch_date');
        $this->fill_int($para, $data, 'wegame_bigwheel_date');
        $this->fill_int($para, $data, 'noteonoff');
        $this->fill_int($para, $data, 'printonff');
        $this->fill_int($para, $data, 'printyldonoff');
        $this->fill_int($para, $data, 'car_baoyang');
        $this->fill_int($para, $data, 'car_repair');
        $this->fill_int($para, $data, 'car_shijia');
        $this->fill_int($para, $data, 'car_gujia');
        $this->fill_int($para, $data, 'car_zixun');
        $this->fill_int($para, $data, 'car_paipai');
        $this->fill_int($para, $data, 'car_chekuan');
        $this->fill_int($para, $data, 'car_cheku');
        $this->fill_int($para, $data, 'car_weizhang');
        $this->fill_int($para, $data, 'car_jisuanqi');
        $this->fill_int($para, $data, 'car_chekuang');
        $this->fill_int($para, $data, 'car_map');
        $this->fill_int($para, $data, 'car_baoxian');
        $this->fill_int($para, $data, 'car_cxzixun');
        $this->fill_str($para, $data, 'wmsite'); //外卖小票机域名
        $this->fill_str($para, $data, 'tcsite'); //堂吃小票机域名
        $this->fill_int($para, $data, 'xp_type'); //小票机器类型
        $this->fill_str($para, $data, 'hdfk_list');
        Return $data;
    }

    public function insert_data($data) {
        return $this->insert($data);
    }

    public function update_data($data, $where) {
        $uid = get_uid();
        if ($uid) {
            $key = CacheKey::get_user_onoff_key($uid);
            mc_unset($key);
        }
        return $this->update($data, $where);
    }

    public function update_row($data, $id) {
        if (!$id)
            return;
        $return = $this->update_data($data, " where  " . $this->_primarykey . "=" . $id);
        $this->clean_cache($id);
        return $return;
    }

    public function delete_data($id) {
        $data["status"] = -1;
        $ret = $this->update_data($data);
        $this->clean_cache($id);
        return $ret;
    }

    public function get_data($id) {
        if (!$id || !is_numeric($id))
            return;
        return $this->scalar("*", " where " . $this->_primarykey . "=" . $id);
    }

    public function clean_cache($id) {
        $key = CacheKey::get_user_onoff_key($id);
        mc_unset($key);
    }

    function get_filter_array() {
        $filter_data[] = array('key' => 'status', 'js' => '=', 'data_type' => 'int');
        $filter_data[] = array('key' => 'username', 'js' => 'like', 'data_type' => 'string', "fields" => "username");
        $filter_data[] = array('key' => 'realname', 'js' => 'like', 'data_type' => 'string', "fields" => "realname");

        return $filter_data;
    }

    //根据条件查询多条数据  @author xukan
    public function get_data_all($select = '*', $where) {
        $sql = "select $select from wx_on_off " . $where;
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    //根据条件查询1条数据  @author xukan
    public function get_data_one($select = '*', $where) {
        return $this->scalar($select, $where);
    }

    /**
     * @author sunshichun@dodoca.com
     * $uid 用户编号
     * @return array   整条记录
     */
    public function get_row_byuid($uid) {
        if (!$uid)
            return;
        $key = CacheKey::get_user_onoff_key($uid);
        $data = mc_get($key);
        if (!$data) {
            $data = $this->scalar("*", " where userid=$uid");
            if ($data) {
                mc_set($key, $data, 7200);
            }
        }
        return $data;
    }

    public function m_get_data($uid = null) {
        if ($uid == null)
            $uid = get_uid();

        $res = $this->scalar("*", "WHERE userid=" . $uid);
        return $res;
    }

}

?>
