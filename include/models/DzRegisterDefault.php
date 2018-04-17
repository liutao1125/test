<?php
/*
 * 注册过程默认关注高校和专家
 * Created by zendstudio.
 * User: zhangle@dodoca.com
 */
class DzRegisterDefault extends My_EcArrayTable
{
    public $_name ='dz_register_default';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_str($para,$data,'province');        //省份id
        $this->fill_str($para,$data,'schoolid');        //字符串，高校id用逗号链接的字符串
        $this->fill_int($para,$data,'kind');            //高校批次对应分数区间1=>重点本科，2=>普通本科，3=>高职高专
        $this->fill_int($para,$data,'type');            //1=>高校推荐（根据省和分数区间），=>2根据省份添加默认推荐高校
        Return $data;
    }

    /**
     *
     *   获取展示高校信息
     *
     * @method getSchoolstr
     * @date   2015年9月25日 上午10:08:10
     * @author zhangle<zhangle@dodoca.net>
     *
     */
    function getSchoolData($schoolstr) {
        $sql = "SELECT 
                    a.id,a.name,b.id as forumid 
                FROM 
                    `dz_school` AS a
                LEFT JOIN 
                    `dz_forum` AS b ON a.id = b.schoolid
                WHERE 
                   b.status = 1
                AND
                   a.id 
                IN 
                   ($schoolstr)
                ORDER  BY  
                    a.sort ASC,b.id ASC";
        return $this->fetchAll($sql);
    }
    
    
    
    /**
     *
     *   获取展示高校字符串
     *
     * @method getSchoolData
     * @date   2015年9月25日 上午10:08:10
     * @author zhangle<zhangle@dodoca.net>
     *
     */
    function getSchoolStr($province_id, $type = 1, $kind = '') {
        return $this->find(array('province'=>$province_id, 'type'=>$type,'kind'=>$kind), array('schoolid'));
    }

}
?>