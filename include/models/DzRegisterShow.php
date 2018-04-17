<?php
/*
 * 注册过程默认关注高校和专家
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/17
 * Time: 10:34
 */
class DzRegisterShow extends My_EcArrayTable
{
    public $_name ='dz_register_show';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'schoolid');          //展示id
        $this->fill_int($para,$data,'sort');             //排序
        Return $data;
    }
    
    
    
    /**
     *
     *   根据类型获取高校
     *
     * @method getSchoolDataByType
     * @date   2015年9月25日 上午10:08:10
     * @author zhangle<zhangle@dodoca.net>
     *
     */
    function getSchoolDataShow() {
        $sql = "SELECT
            b.id,b.name,c.id as forumid
        FROM
            `dz_register_show` AS a
        LEFT JOIN
            `dz_school` AS b ON a.schoolid = b.id
        LEFT JOIN
            `dz_forum` AS c ON a.schoolid = c.schoolid
        ORDER  BY
            a.sort ASC,b.id ASC
        LIMIT 17";
        return $this->fetchAll($sql);
    }


}
?>