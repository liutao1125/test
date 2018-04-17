<?php
/*
 * 注册过程默认关注高校和专家
 * Created by zendstudio.
 * User: zhangle@dodoca.com
 */
class DzScoreScope extends My_EcArrayTable
{
    public $_name ='dz_score_scope';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_str($para,$data,'province');        //省份id
        $this->fill_str($para,$data,'max');             //最大值，超过此值，推荐重点本科(开区间)，两者之间，推荐普通本科
        $this->fill_int($para,$data,'min');             //最小值，小于此值，推荐高职高专（闭区间）
        $this->fill_int($para,$data,'total');           //高考总分
        Return $data;
    }

    /**
     *
     *   查询省份名称
     * @date   2016-03-23
     * @author luoqin<luoqin@dodoca.com>
     *
     */
    function getProvinceName(){
        $DzAreaModel = new DzArea();
        $sql = "SELECT t2.area_id,t2.area_name FROM " . $this->_name .
            " t1 LEFT JOIN " . $DzAreaModel->_name . " t2 ON t1.province = t2.area_id" .
            " WHERE t2.area_type = 1 AND t2.parent_id = 1 ORDER BY t1.id desc";
        $res = $this->fetchAll($sql);
        if(!empty($res)){
            return $res;
        }
        else{
            return false;
        }
    }
    
    /**
     *
     *   根据省份取该省总分
     * @date   2016-03-23
     * @author zhangle<zhangle@dodoca.com>
     *
     */
    function getProvinceScoreTotal($province_id){
        return $this->find(array('province'=>$province_id), array('total'));
    }

}
?>