<?php
/*
 * 升学在线网站主表
 * @author wanghuan
 */
class DzMainWebsite extends My_EcArrayTable
{
    public $_name ='dz_main_website';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                   //主站ＩＤ
        $this->fill_str($para,$data,'website_name');         //网站名称
        $this->fill_str($para,$data,'keywords');             //关键字
        $this->fill_str($para,$data,'content');              //content
        $this->fill_str($para,$data,'bannerids');            //首页图片ids,多种轮播图以,隔开
        Return $data;
    }


    /**
     * @author wanghuan@dodoca.net
     * 获取主站首页banner轮播图
     */
    function getBanner() {
        $key = CacheKey::get_home_bannerid();
        $data = mc_get($key);
        if(!$data)
        {
            
            $where = array('id'=>1);
            $banner = $this->find($where,array("bannerids"));
            if ($banner !=null && $banner!='') {
                $bannerArr = json_decode($banner,true);
                $pic = new PicData();
                //获取图片接口
                if (is_array($bannerArr)) {
                    foreach($bannerArr as $v){
                        if($v['pic_id'] == 0){
                            $data[] = array('id' => 0 ,'org' =>'/www/images/sxzx_mb_top.jpg','link'=>$v['link']);
                        }else{
                            $temp = $pic->get_row_byid((int)$v['pic_id']);
                            $temp['link'] = $v['link'];
                            $data[] = $temp;
                        }
                    }
                }else{
                    $data[] = array('id' => 0 ,'org' =>'/www/images/sxzx_mb_top.jpg','link'=>$v['link']);
                }

            }
            
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }

        return $data;
    }
}
?>