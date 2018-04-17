<?php

/*
 * 学生关注论坛表
 * @author liutao
 */

class DzForumAttention extends My_EcArrayTable {
    public $_name = 'dz_forum_attention';
    public $_primarykey = 'id';

    function prepareData($para) {
        $data = array();
        $this->fill_int($para, $data, 'id');                   //ID
        $this->fill_int($para, $data, 'uid');                  //用户id
        $this->fill_int($para, $data, 'type');                 //类型：1=>关注论坛，2=>关注专家
        $this->fill_int($para, $data, 'forumid');              //被关注论坛ID
        $this->fill_int($para, $data, 'expertid');             //专家id
//         $this->fill_int($para,$data,'num_count');         //被关注的数量（被关注时加一）
        $this->fill_int($para, $data, 'status');               //状态0=>取消关注，1=>关注
        $this->fill_int($para, $data, 'createtime');           //关注时间
        Return $data;
    }


    /**
     * author wanghuan<wanghuan@dodoca.net>
     * 获取列表
     * @param array $where
     * @param array $data
     * @param string $order
     * @param int $page
     * @param int $pagesize
     * @param string $group
     * @return array
     */
    public function lists($where = array(), $data = array(), $order = '', $page = 1, $pagesize = 50, $group = '') {
        $rs = $this->listPage($where, $data, $order, $page, $pagesize, $group);
        return $rs;
    }


    /**
     * 查询用户所有关注的论坛Id
     * author wanghuan<wanghuan@dodoca.net>
     * @return array
     */
    public function getAllThreadid($uid) {
        $where = 'where status = 1 and uid=' . $uid;
        $sql = "SELECT forumid FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    
    
    /**
     * 查询用户所有关注的论坛Id
     * author wanghuan<wanghuan@dodoca.net>
     * @return array
     */
    public function getAttentionForumInfo($idArr,$page = 1,$pagesize = 10) {
        if (! is_array($idArr)) {
            return false;
        }
        $str = join(',',$idArr);
        $offset = ($page-1)*$pagesize;
        $DzSchoolModel = new DzSchool();
        $sql = "SELECT 
                        a.id as forumid,a.schoolid,a.website_name,b.id,b.name,b.profile,b.logo,b.province,b.category,b.sort,b.is_hide
               FROM  
                        dz_forum a 
               LEFT JOIN  
                        $DzSchoolModel->_name  b 
               ON   
                        a.schoolid = b.id
               WHERE 
                        a.status = 1 AND a.type=1 AND b.status = 1 AND b.is_hide = 0 AND a.id in ($str) 
               ORDER BY 
                        b.sort ASC
               LIMIT 
                        $offset, $pagesize;";
        $ForumAttentionInfo = $this->fetchAll($sql);
        return $ForumAttentionInfo;
    }

    /**
     *
     *   权限验证，是否有权限发帖，回复
     *
     * @method canpost
     * @param param
     * @author wanghuan<wanghuan@dodoca.net>
     *
     */

    public function canpost() {
        $userInfo = get_session('mobile_info') != "" ? get_session('mobile_info') : unserialize($_COOKIE['mobile_info']);
        if (!$userInfo) {
            return array();
        }
        $res = $this->getAllThreadid($userInfo['uid']);
        if (!$res) {
            return array();
        }
        if (is_array($res) && !empty($res)) {
            foreach ($res as $v) {
                $r[] = $v['forumid'];
            }
        }
        return $r;
    }


    /**
     *
     *   判断是否已关注某个论坛
     *
     * @method canpost
     * @param param
     * @author wanghuan<wanghuan@dodoca.net>
     *
     */

    public function isAttentionForum($forumid, $uid) {
        if (!$uid) {
            return false;
        }
        return $this->find(array('forumid' => $forumid, 'uid' => $uid));
    }

    /**
     *   根据论坛ID查询关注用户信息
     * @param $forumid 论坛ID
     * @param $province 省份
     * @param $city  城市
     * @param $grade 年级
     * @param $role  角色
     * @author luoqin<luoqin@dodoca.com>
     * @datetime 2016-03-23
     */
    public function getUserById($forumid, $province, $city, $grade, $role, $page = 1, $pagesize = 15) {
        if (empty($forumid)) {
            return false;
        }
        $DzUserModel = new DzUser();
        $sql = "SELECT 
                    t1.createtime, t2.name,t2.province_id,t2.city_id,t2.middle_school_id,t2.school_grade,t2.school_class,t2.role_id 
                FROM " .
            $this->_name . " t1
                LEFT JOIN " .
            $DzUserModel->_name . " t2
                ON 
                     t1.uid = t2.uid " .
            "WHERE
                    t1.type = 1 AND t1.forumid = " . $forumid . " AND t1.status = 1 AND t2.status = 1 AND t2.role_id in (9,10)";

        if (!empty($province)) {
            $sql .= " AND t2.province_id = " . $province;
        }
        if (!empty($city)) {
            $sql .= " AND t2.city_id = " . $city;
        }
        if (!empty($grade)) {
            $sql .= " AND t2.school_grade = " . $grade;
        }
        if (!empty($role)) {
            $sql .= " AND t2.role_id = " . $role;
        }
        $limit = ($page - 1) * $pagesize;
        $sql .= " ORDER BY t1.id DESC LIMIT " . $limit . " , " . $pagesize;
        $res = $this->fetchAll($sql);
        return $res;
    }

    /**
     *   根据论坛ID查询关注用户总数
     * @param $forumid 论坛ID
     * @param $province 省份
     * @param $city  城市
     * @param $grade 年级
     * @param $role  角色
     * @author luoqin<luoqin@dodoca.com>
     * @datetime 2016-03-23
     */
    public function getCountById($forumid, $province, $city, $grade, $role) {
        if (empty($forumid)) {
            return false;
        }
        $DzUserModel = new DzUser();
        $sql = "SELECT count(t1.id) as count FROM " . $this->_name .
            " t1 LEFT JOIN " . $DzUserModel->_name . " t2 ON t1.uid = t2.uid" .
            " WHERE t1.type = 1 AND t1.forumid = " . $forumid .
            " AND t1.status = 1 AND t2.status = 1 AND t2.role_id in (9,10)";
        if (!empty($province)) {
            $sql .= " AND t2.province_id = " . $province;
        }
        if (!empty($city)) {
            $sql .= " AND t2.city_id = " . $city;
        }
        if (!empty($grade)) {
            $sql .= " AND t2.school_grade = " . $grade;
        }
        if (!empty($role)) {
            $sql .= " AND t2.role_id = " . $role;
        }
        $count = $this->fetchAll($sql);
        return $count ? $count[0]['count'] : 0;
    }

    /**
     *   按照省份根据论坛ID统计关注用户总数
     * @param $forumid 论坛ID
     * @param $gradeArr 年级字符串
     * @author luoqin<luoqin@dodoca.com>
     * @datetime 2016-03-23
     */
    public function getProvinceCount($forumid) {
        if (empty($forumid)) {
            return false;
        }
        $DzUserModel = new DzUser();
        $sql = "SELECT count(t1.id) as count,t2.province_id FROM " . $this->_name .
            " t1 LEFT JOIN " . $DzUserModel->_name . " t2 ON t1.uid = t2.uid" .
            " WHERE t1.type = 1 AND t1.forumid = " . $forumid .
            " AND t1.status = 1 AND t2.status = 1 GROUP BY t2.province_id";
        $ForumAttentionInfo = $this->fetchAll($sql);
        $result = array();
        if (!empty($ForumAttentionInfo)) {
            foreach ($ForumAttentionInfo as $k => $v) {
                $result[$v['province_id']] = $v['count'];
            }
        }
        return $result;
    }


    /**
     *   按照省份根据论坛ID统计关注用户总数
     * @param $forumid 论坛ID
     * @param $province 省份
     * @param $gradeArr 年级字符串
     * @author luoqin<luoqin@dodoca.com>
     * @datetime 2016-03-23
     */
    public function getRoleCount($forumid,$province,$grade){
        if (empty($forumid) || empty($province)) {
            return false;
        }
        $DzUserModel = new DzUser();
        $sql = "SELECT 
                    t2.school_grade,t2.role_id 
               FROM " . 
                    $this->_name ." t1 
               LEFT JOIN " . $DzUserModel->_name . " t2 
               ON   
                   t1.uid = t2.uid" .
            " WHERE 
                t1.type = 1 AND t1.forumid = " . $forumid .
            " AND t1.status = 1 AND t2.status = 1 AND t2.province_id = " . $province;
        $ForumAttentionInfo = $this->fetchAll($sql);
        $result = array();
        $temp = array();
        if (!empty($ForumAttentionInfo)) {
            foreach ($ForumAttentionInfo as $k => $v) {
                if ($v['school_grade'] == '') {
                     $result['empty'][] = $v;
                     $temp['empty'][$v['role_id']][] = $v;
                }else if ($v['school_grade'] < $grade) {
                     $result['graduate'][] = $v;
                     $temp['graduate'][$v['role_id']][] = $v;
                }else{
                    $result[$v['school_grade']][] = $v;
                    $temp[$v['school_grade']][$v['role_id']][] = $v;
                }
            }
        }
        $count = $ForumAttentionInfo ? count($ForumAttentionInfo) : 0;
        return array('result'=>$result,'temp'=>$temp,'total_count'=>$count);
    }

}

?>