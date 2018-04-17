<?php
/*
 * 升学在线学校表（含高校和高中）
 * @author liutao
 */
class DzSchool extends My_EcArrayTable
{
    public $_name ='dz_school';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                      //学校id主键
        $this->fill_str($para,$data,'name');                    //学校名称
        $this->fill_int($para,$data,'type');                    //学校类型1=>高中，2=>大学
        $this->fill_int($para,$data,'category');                //高校类型配置$GLOBALS['height_school_type'],1=>985和211,2=>985,3=>211,4=>普通本科,5=>民办本科,6=>高职高专
        $this->fill_int($para,$data,'logo');                    //学校logo
        $this->fill_int($para,$data,'province');                //学校所在省
        $this->fill_int($para,$data,'city');                    //学校所在市
        $this->fill_int($para,$data,'area');                    //学校所在区
        $this->fill_str($para,$data,'address');                 //学校详细地址
        $this->fill_int($para,$data,'templateid');              //高中微网模板id（共4个，分别定义为1,2,3,4参考编辑模板）
        $this->fill_str($para,$data,'bannerid');                //banner图片（id，用逗号隔开）备注：出现０则为默认图片
        $this->fill_str($para,$data,'profile');                 //学校简介
        $this->fill_int($para,$data,'sort');                    //排序序号
        $this->fill_int($para,$data,'updatetime');              //对已经上移的排序进行微调
        $this->fill_int($para,$data,'createtime');              //添加时间
        $this->fill_int($para,$data,'status');                  //状态0=>删除，1=>正常
        $this->fill_int($para,$data,'is_hide');                 //状态0=>不隐藏，1=>隐藏
        Return $data;
    }

    /**
     * 根据ID获取学校name
     * @author liutao@dodoca.net
     * @datetime 2015/9/22
     */
    function getSchoolNameById($id){
        if(!$id || !is_numeric($id))return false;
        $key = CacheKey::get_school_name_key($id);
        $data = mc_get($key);
        if(!$data)
        {
            $where=array(
                'id' => $id,
                'status' => 1
            );
            $data = $this->find($where,'name');
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }

    /**
     * 获取高中数组
     * @author liutao@dodoca.net
     * @datetime 2015/12/7
     */
    function getSchoolArray(){
        $res = array();
        $where = array(
            'status' => 1,
            'type' => 1
        );
        $data = $this->select($where,'id,name');
        foreach((array)$data as $key=>$value)
        {
            $res[$value['name']] = $value['id'];
        }
        return $res;
    }

    /**
     * 根据ID删除学校
     * @author liutao@dodoca.net
     * @datetime 2015/9/23
     */
    function deleteSchoolById($id){
        if(!$id || !is_numeric($id))return false;
        $data = array('status' => 0);
        $where = array(
            'id' => $id
        );
        $res = $this->updateData($data,$where);
        return $res;
    }

    /**
     * 根据数组条件批量删除学校
     * @author liutao@dodoca.net
     * @datetime 2015/9/29
     */
    function deleteSchoolByArray($where){
        $data = array('status' => 0);
        $res = $this->updateData($data,$where);
        return $res;
    }

    /**
     * 获取学校单条记录
     * @author liutao@dodoca.net
     * @datetime 2015/9/23
     * $id dz_school主键
     */
    public function get_row_byid($id)
    {
        if(!$id || !is_numeric($id))return false;
        return $this->scalar("*"," where status=1 and id=".$id);

    }
	
	/**
     * 根据城市ID city获取高中学校信息
     * @author rongxiang
     * @datetime 2015/9/23
     */
    function getSchoolByCityId($id){
        if(!$id || !is_numeric($id))return false;
        $where=array(
            'city' => $id,
            'type' => 1, //type 1为高中 2为高校
            'status' => 1
        );
        $res = $this->select($where,'id,name');
        return $res;
    }

    /**
     * 根据城市下地区ID area获取高中学校信息
     * @author rongxiang
     * @datetime 2016/3/2
     */
    function getSchoolByAreaId($id){
        if(!$id || !is_numeric($id))return false;
        $where=array(
            'area' => $id,
            'type' => 1, //type 1为高中 2为高校
            'status' => 1
        );
        $res = $this->select($where,'id,name');
        return $res;
    }


    /**
     * 根据学校名称模糊查询学校的id集
     * @author rongxiang
     * @datetime 2015/9/24
     */
    function getSchoolByName($name,$type=""){
        $where=array(
            'status' => 1
        );
        if($type!='')$where['type']=$type;
        $where['name']=array('like','%'.$name.'%');
        $res = $this->select($where,'id,name');
        return $res;
    }


    /**
     * @author wanghuan@dodoca.net
     * 获取模板banner轮播图
     */
    function getTemplateBanner($banner) {
        $picArr = "";//初始化
        if($banner!=null && $banner!="") {
            $bannerArr = explode(',', $banner);
            $pic = new PicData();
            if ($bannerArr && is_array($bannerArr)) {
                //获取图片接口
                foreach ($bannerArr as $v) {
                    if($v!=0){
                        $picArr[] = $pic->get_row_byid((int)$v);
                    }else{
                        $picArr[] = array('id' => 0 ,'org' =>'/www/mobile/images/sx_mb_01_ban.png');
                    }
                }
            }
        }
        return $picArr;
    }
    
    
    
    /**
     *
     *   获取默认展示高校和论坛
     *
     * @method getDefaultSchool
     * @date   2015年9月25日 上午10:08:10
     * @author zhangle<zhangle@dodoca.net>
     *
     */
    function getDefaultSchool($schoolid) {
        $sql = "SELECT 
                    a.id,a.name,b.id AS forumid
                FROM 
                    `dz_school` AS a
                LEFT JOIN 
                    `dz_forum` AS b ON a.id = b.schoolid
                WHERE 
                    b.type = 1 AND a.id IN({$schoolid})
                ORDER BY 
                    a.sort ASC,b.id ASC";
        return $this->fetchAll($sql);
    }

}
?>