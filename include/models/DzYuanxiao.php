<?php
/*
 * 院校表
 * @author wanghuan
 */
class DzYuanxiao extends My_EcArrayTable
{
    public $_name ='dz_yuanxiao';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                  //ID
        $this->fill_int($para,$data,'province');            //省份（'1'=>'湖北','2'=>'江西','3'=>'河南','4'=>'安徽','5'=>'江苏'）
        $this->fill_int($para,$data,'batch');               //批次
        $this->fill_int($para,$data,'project');             //文理科  1=>文科   2=>理科
        $this->fill_int($para,$data,'no');                  //编号
        $this->fill_str($para,$data,'name');                //院校名称
        $this->fill_str($para,$data,'address');             //院校地址
        $this->fill_int($para,$data,'wc');                  //
        $this->fill_int($para,$data,'xc');                  //
        Return $data;
    }

    /**
     * 根据省份ID,文理科，分数获取可选志愿学校
     * @author wanghuan
     * @datetime 2015/9/23
     */
    function getYuanxiao($province, $project, $fraction){
        //初始化
        $result = array();
        $result['cyc'] = array();//冲一冲
        $result['wyw'] = array();//稳一稳
        $result['byb'] = array();//保一保
        $result['yaxian'] = array();//压线
        $arr = array('1'=>'湖北','2'=>'江西','3'=>'河南','4'=>'安徽','5'=>'江苏');
        $line = new DzLine();
        $studentfswc = new DzStudentFsWc();
        $sql = "SELECT * FROM %s %s";
        $lineArr = $line->getOne(array('province'=>$arr[$province]));

        if($arr[$province]=="湖北") {
            switch ($project) {
                case 1:
                    for ($i = $fraction; $i >= $lineArr['four_w']; $i--) {
                        $weici = $studentfswc->getOne(array('province' => $arr[$province], 'wk_fs' => $i));
                        if (is_array($weici) && !empty($weici)) {
                            $weici = $weici['wk_wc'];
                            break;
                        }
                    }
                    $num = array($fraction, $lineArr['one_w'], $lineArr['two_w'], $lineArr['three_w'], $lineArr['four_w']);
                    $nu = $this->bubbleSort($num);
                    if($nu[0] == $fraction){
                        $batch = 1;
                        $yaxian = $fraction - $lineArr['one_w'] <= 3 ? 1 : '';
                    }elseif($nu[1] == $fraction){
                        $batch = 2;
                        $yaxian = $fraction - $lineArr['two_w'] <= 3 ? 1 : '';
                    }elseif($nu[2] == $fraction){
                        $batch = 3;
                        $yaxian = $fraction - $lineArr['three_w'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['three_w'];
                    }elseif($nu[3] == $fraction){
                        $batch = 4;
                        $yaxian = $fraction - $lineArr['four_w'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['four_w'];
                    }
                    break;
                case 2:
                    for ($i = $fraction; $i >= $lineArr['four_l']; $i--) {
                        $weici = $studentfswc->getOne(array('province' => $arr[$province], 'lk_fs' => $i));
                        if (is_array($weici)) {
                            $weici = $weici['lk_wc'];
                            break;
                        }
                    }
                    $num = array($fraction, $lineArr['one_l'], $lineArr['two_l'], $lineArr['three_l'], $lineArr['four_l']);
                    $nu = $this->bubbleSort($num);
                    if ($nu[0] == $fraction) {
                        $batch = 1;
                        $yaxian = $fraction - $lineArr['one_l'] <= 3 ? 1 : '';
                    } elseif ($nu[1] == $fraction) {
                        $batch = 2;
                        $yaxian = $fraction - $lineArr['two_l'] <= 3 ? 1 : '';
                    } elseif ($nu[2] == $fraction) {
                        $batch = 3;
                        $yaxian = $fraction - $lineArr['three_l'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['three_l'];
                    } elseif ($nu[3] == $fraction) {
                        $batch = 4;
                        $yaxian = $fraction - $lineArr['four_l'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['four_l'];
                    }
            }
            if ($batch == 1 || $batch == 2) {
                $where1 = "WHERE province='$province' AND project='$project' AND wc<$weici  AND batch=$batch ORDER BY id DESC limit 0,20";
                $where2 = "WHERE province='$province' AND project='$project' AND wc>=$weici  AND batch=$batch ORDER BY id ASC limit 0,30";
                $res1 = $this->fetchAll(sprintf($sql, $this->_name, $where1));
                $res1 = array_reverse($res1);
                $res2 = $this->fetchAll(sprintf($sql, $this->_name, $where2));
                foreach ($res1 as $key => $val) {
                    if ($key <= count($res1)-5-1) {
                        $result['cyc'][] = $val;//冲一冲
                    } else {
                        $result['wyw'][] = $val;//稳一稳
                    }
                }
                foreach ($res2 as $key => $val) {
                    if ($key <= 9) $result['wyw'][] = $val;
                    else $result['byb'][] = $val; //保一保
                }
            } else {
                $where1 = "WHERE province='$province' AND project='$project' AND xc>$xiancha  AND batch=$batch ORDER BY id DESC limit 0,20";
                $where2 = "WHERE province='$province' AND project='$project' AND xc<=$xiancha  AND batch=$batch ORDER BY id ASC limit 0,30";
                $res1 = $this->fetchAll(sprintf($sql, $this->_name, $where1));
                $res1 = array_reverse($res1);
                $res2 = $this->fetchAll(sprintf($sql, $this->_name, $where2));
                foreach ($res1 as $key => $val) {
                    if ($key <= 14) $result['cyc'][] = $val;
                    else $result['wyw'][] = $val;
                }
                foreach ($res2 as $key => $val) {
                    if ($key <= 9) $result['wyw'][] = $val;
                    else $result['byb'][] = $val;
                }
            }
            if (!empty($yaxian)) {
                $yaxian_batch = $batch + 1;
                $where = "WHERE province='$province' AND project='$project' AND batch='$yaxian_batch' ORDER BY wc ASC limit 0,20";
                $sql = sprintf($sql, $this->_name, $where);
                $result['yaxian'] = $this->fetchAll($sql);
            } else {
                $result['yaxian'] = '';
            }
            $res = $result;
        }elseif($arr[$province]=="江苏") {
            switch ($project) {
                case 1:
                    $num = array($fraction, $lineArr['one_w'], $lineArr['two_w'], $lineArr['three_w'], $lineArr['four_w']);
                    $nu = $this->bubbleSort($num);
                    if ($nu[0] == $fraction) {
                        $batch = 1;
                        $yaxian = $fraction - $lineArr['one_w'] <= 3 ? 1 : '';
                        $xiancha = $fraction - $lineArr['one_w'];
                    } elseif ($nu[1] == $fraction) {
                        $batch = 2;
                        $yaxian = $fraction - $lineArr['two_w'] <= 3 ? 1 : '';
                        $xiancha = $fraction - $lineArr['two_w'];
                    } elseif ($nu[2] == $fraction) {
                        $batch = 3;
                        $yaxian = $fraction - $lineArr['three_w'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['three_w'];
                    } elseif ($nu[3] == $fraction) {
                        $batch = 4;
                        $yaxian = $fraction - $lineArr['four_w'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['four_w'];
                    }
                    break;
                case 2:
                    $num = array($fraction, $lineArr['one_l'], $lineArr['two_l'], $lineArr['three_l'], $lineArr['four_l']);
                    $nu = $this->bubbleSort($num);
                    if ($nu[0] == $fraction) {
                        $batch = 1;
                        $yaxian = $fraction - $lineArr['one_l'] <= 3 ? 1 : '';
                        $xiancha = $fraction - $lineArr['one_l'];
                    } elseif ($nu[1] == $fraction) {
                        $batch = 2;
                        $yaxian = $fraction - $lineArr['two_l'] <= 3 ? 1 : '';
                        $xiancha = $fraction - $lineArr['two_l'];
                    } elseif ($nu[2] == $fraction) {
                        $batch = 3;
                        $yaxian = $fraction - $lineArr['three_l'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['three_l'];
                    } elseif ($nu[3] == $fraction) {
                        $batch = 4;
                        $yaxian = $fraction - $lineArr['four_l'] <= 5 ? 1 : '';
                        $xiancha = $fraction - $lineArr['four_l'];
                    }
                    break;
            }
            $arr = array($xiancha + 3, $xiancha, $xiancha - 2, $xiancha - 5);
            $res = $this->yxsx_sorting($province, $project, $batch, $arr, '', $yaxian);
        }elseif($project==1){
            $num = array($fraction,$lineArr['one_w'],$lineArr['two_w'],$lineArr['three_w'],$lineArr['four_w']);
            $nu = $this->bubbleSort($num);
            if($nu[0] == $fraction || $nu[1] == $fraction){
                $paiming = $studentfswc->getOne(array('province'=>$arr[$province],'wk_fs'=>$fraction));
                $paiming = $paiming['wk_wc'];
                if($nu[0] == $fraction){
                    //第一批次
                    $batch = 1;
                    $yaxian = $fraction - $lineArr['one_w'] <= 3 ? 1 : '';
                    if($arr[$province] == "湖北"){
                        $arr = array($paiming-500,$paiming,$paiming+500,$paiming+1000);
                    }elseif($arr[$province] == "江西"){
                        $arr = array($paiming-500,$paiming,$paiming+500,$paiming+1000);
                    }elseif($arr[$province] == "安徽"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                    }elseif($arr[$province] == "河南"){
                        $arr = array($paiming-500,$paiming,$paiming+1000,$paiming+1500);
                    }
                }elseif($nu[1] == $fraction){
                    //第二批次
                    $yaxian = $fraction - $lineArr['two_w'] <= 3 ? 1 : '';
                    $batch = 2;
                    if($arr[$province] == "湖北"){
                        $arr = array($paiming-1500,$paiming,$paiming+1500,$paiming+3000);
                    }elseif($arr[$province] == "江西"){
                        $arr = array($paiming-1000,$paiming,$paiming+500,$paiming+1000);
                    }elseif($arr[$province] == "安徽"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                    }elseif($arr[$province] == "河南"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                    }
                }
                $res = $this->yxsx_sorting($province,$project,$batch,$arr,'',$yaxian);
            }elseif($nu[2] == $fraction || $nu[3] == $fraction) {
                if ($nu[2] == $fraction) {
                    //第三批次
                    $batch = 3;
                    $yaxian = $fraction - $lineArr['three_w'] <= 5 ? 1 : '';
                    $xiancha = $fraction - $lineArr['three_w'];

                    if ($arr[$province] == "湖北") {
                        $arr = array($xiancha + 5, $xiancha, $xiancha - 3, $xiancha - 8);
                    } elseif ($arr[$province] == "江西") {
                        $arr = array($xiancha + 6, $xiancha, $xiancha - 3, $xiancha - 9);
                    } elseif ($arr[$province] == "安徽") {
                        $arr = array($xiancha + 5, $xiancha, $xiancha - 3, $xiancha - 8);
                    } elseif ($arr[$province] == "河南") {
                        $arr = array($xiancha + 5, $xiancha, $xiancha - 12);
                    }
                    $res = $this->yxsx_sorting($province, $project, $batch, $arr, '', $yaxian);
                } elseif ($nu[3] == $fraction) {
                    //第四批次
                    $batch = 4;
                    $yaxian = $fraction - $lineArr['four_w'] <= 5 ? 1 : '';
                    $xiancha = $fraction - $lineArr['four_w'];
                    if ($arr[$province] == "湖北") {
                        $arr = array($xiancha + 20, $xiancha, $xiancha - 10);
                        $byb = array(
                            '0' => array('no' => '5296', 'name' => '湖北职业技术学院'),
                            '1' => array('no' => '2197', 'name' => '湖北大学知行学院'),
                            '2' => array('no' => '7180', 'name' => '湖北工业职业技术学院'),
                            '3' => array('no' => '8310', 'name' => '襄阳职业技术学院'),
                            '4' => array('no' => '8330', 'name' => '武汉工程职业技术学院'),
                            '5' => array('no' => '8380', 'name' => '荆州职业技术学院'),
                            '6' => array('no' => '8837', 'name' => '湖北工业职业技术学院'),
                            '7' => array('no' => '7502', 'name' => '荆楚理工学院'),
                            '8' => array('no' => '8721', 'name' => '武汉城市职业学院'),
                            '9' => array('no' => '4970', 'name' => '信阳农林学院'),
                            '10' => array('no' => '4489', 'name' => '黄河水利职业技术学院'),
                            '11' => array('no' => '7990', 'name' => '河南机电高等专科学校'),
                            '12' => array('no' => '9183', 'name' => '郑州师范学院'),
                            '13' => array('no' => '3670', 'name' => '郑州轻工业学院'),
                            '14' => array('no' => '7540', 'name' => '南昌工程学院'),
                            '15' => array('no' => '5041', 'name' => '天津医学高等专科学校')
                        );
                    } elseif ($arr[$province] == "江西") {
                        $arr = array($xiancha + 25, $xiancha, $xiancha - 10);
                        $byb = array(
                            '0' => array('no' => '8601', 'name' => '江西工业职业技术学院'),
                            '1' => array('no' => '8603', 'name' => '江西电力职业技术学院'),
                            '2' => array('no' => '8606', 'name' => '江西信息应用职业技术学院'),
                            '3' => array('no' => '8607', 'name' => '江西交通职业技术学院'),
                            '4' => array('no' => '8609', 'name' => '江西机电职业技术学院'),
                            '5' => array('no' => '9046', 'name' => '江西科技职业学院'),
                            '6' => array('no' => '7370', 'name' => '九江职业大学'),
                            '7' => array('no' => '7600', 'name' => '江西财经职业学院'),
                            '8' => array('no' => '9340', 'name' => '江西陶瓷工艺美术职业技术学院'),
                            '9' => array('no' => '9260', 'name' => '江西工业工程职业技术学院'),
                            '10' => array('no' => '9304', 'name' => '江西应用工程职业学院'),
                            '11' => array('no' => '9378', 'name' => '赣西科技职业学院'),
                            '12' => array('no' => '9034', 'name' => '鹰潭职业技术学院'),
                            '13' => array('no' => '9112', 'name' => '江西环境工程职业学院'),
                            '14' => array('no' => '8634', 'name' => '江西应用技术职业学院'),
                            '15' => array('no' => '9540', 'name' => '湖北水利水电职业技术学院'),
                            '16' => array('no' => '9510', 'name' => '武汉商贸职业学院'),
                            '17' => array('no' => '7280', 'name' => '咸宁职业技术学院'),
                            '18' => array('no' => '9491', 'name' => '湖北生物科技职业学院'),
                            '19' => array('no' => '7200', 'name' => '荆州理工职业学院'),
                            '20' => array('no' => '8480', 'name' => '湖北国土资源职业学院'),
                            '21' => array('no' => '7157', 'name' => '湖北科技职业学院'),
                            '22' => array('no' => '8092', 'name' => '武汉城市职业学院'),
                            '23' => array('no' => '8500', 'name' => '武汉工业职业技术学院'),
                            '24' => array('no' => '8180', 'name' => '湖北轻工职业技术学院'),
                            '25' => array('no' => '5520', 'name' => '商丘医学高等专科学校'),
                            '26' => array('no' => '7860', 'name' => '黄河水利职业技术学院'),
                            '27' => array('no' => '6381', 'name' => '安徽机电职业技术学院')
                        );
                    } elseif ($arr[$province] == "安徽") {
                        $arr = array($xiancha + 20, $xiancha, $xiancha - 10, $xiancha - 15);
                        $byb = '';
                    } elseif ($arr[$province] == "河南") {
                        $byb = '';
                        $arr = array($xiancha + 10, $xiancha, $xiancha - 10);
                    }
                    $res = $this->yxsx_sorting($province, $project, $batch, $arr, $byb, $yaxian);
                }
            }
        }elseif($project==2){
                        $num = array($fraction,$lineArr['one_l'],$lineArr['two_l'],$lineArr['three_l'],$lineArr['four_l']);
                        $nu = $this->bubbleSort($num);
                        if($nu[0] == $fraction || $nu[1] == $fraction){
                            $paiming = $studentfswc->getOne(array('province'=>$arr[$province],'lk_fs'=>$fraction));
                            $paiming = $paiming['lk_wc'];
                            if($nu[0] == $fraction){
                                //第一批次
                                $batch = 1;
                                $yaxian = $fraction - $lineArr['one_l'] <= 3 ? 1 : '';
                                if($arr[$province] == "湖北"){
                                    $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                                }elseif($arr[$province] == "江西"){
                                    $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                                }elseif($arr[$province] == "安徽"){
                                    $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                                }elseif($arr[$province] == "河南"){
                                    $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                                }
                            }elseif($nu[1] == $fraction){
                                //第二批次
                                $batch = 2;
                                $yaxian = $fraction - $lineArr['two_l'] <= 3 ? 1 : '';
                                if($arr[$province] == "湖北"){
                                    $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                                }elseif($arr[$province] == "江西"){
                                    $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                                }elseif($arr[$province] == "安徽"){
                                    $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                                }elseif($arr[$province] == "河南"){
                                    $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+3000);
                                }
                            }
                            $res = $this->yxsx_sorting($province,$project,$batch,$arr,'',$yaxian);
                        }elseif($nu[2] == $fraction || $nu[3] == $fraction){
                            if($nu[2] == $fraction){
                                //第三批次
                                $batch = 3;
                                $yaxian = $fraction - $lineArr['three_l'] <= 5 ? 1 : '';
                                $xiancha = $fraction - $lineArr['three_l'];

                                if($arr[$province] == "湖北"){
                                    $arr = array($xiancha+5,$xiancha,$xiancha-5,$xiancha-10);
                                }elseif($arr[$province] == "江西"){
                                    $arr = array($xiancha+8,$xiancha,$xiancha-5,$xiancha-10);
                                }elseif($arr[$province] == "安徽"){
                                    $arr = array($xiancha+5,$xiancha,$xiancha-5,$xiancha-10);
                                }elseif($arr[$province] == "河南"){
                                    $arr = array($xiancha+5,$xiancha,$xiancha-12);
                                }
                                $res = $this->yxsx_sorting($province,$project,$batch,$arr,'',$yaxian);
                            }elseif($nu[3] == $fraction){
                                //第四批次
                                $batch = 4;
                                $yaxian = $fraction - $lineArr['four_l'] <= 5 ? 1 : '';
                                $xiancha = $fraction - $lineArr['four_l'];
                                if($arr[$province] == "湖北"){
                                    $arr = array($xiancha+20,$xiancha,$xiancha-10);
                                    $byb = array(
                                        '0'=>array('no'=>'7170','name'=>'湖北职业技术学院'),
                                        '1'=>array('no'=>'2197','name'=>'湖北大学知行学院'),
                                        '2'=>array('no'=>'7180','name'=>'湖北工业职业技术学院'),
                                        '3'=>array('no'=>'8310','name'=>'襄阳职业技术学院'),
                                        '4'=>array('no'=>'8330','name'=>'武汉工程职业技术学院'),
                                        '5'=>array('no'=>'8380','name'=>'荆州职业技术学院'),
                                        '6'=>array('no'=>'7180','name'=>'湖北工业职业技术学院'),
                                        '7'=>array('no'=>'7230','name'=>'荆楚理工学院'),
                                        '8'=>array('no'=>'8092','name'=>'武汉城市职业学院'),
                                        '9'=>array('no'=>'7130','name'=>'信阳农林学院'),
                                        '10'=>array('no'=>'7860','name'=>'黄河水利职业技术学院'),
                                        '11'=>array('no'=>'6032','name'=>'河南机电高等专科学校'),
                                        '12'=>array('no'=>'9183','name'=>'郑州师范学院'),
                                        '13'=>array('no'=>'3670','name'=>'郑州轻工业学院'),
                                        '14'=>array('no'=>'8218','name'=>'南昌工程学院'),
                                        '15'=>array('no'=>'7630','name'=>'天津医学高等专科学校')
                                    );
                                }elseif($arr[$province] == "江西"){
                                    $arr = array($xiancha+25,$xiancha,$xiancha-10);
                                    $byb = array(
                                        '0'=>array('no'=>'8601','name'=>'江西工业职业技术学院'),
                                        '1'=>array('no'=>'8603','name'=>'江西电力职业技术学院'),
                                        '2'=>array('no'=>'8810','name'=>'江西信息应用职业技术学院'),
                                        '3'=>array('no'=>'9095','name'=>'江西交通职业技术学院'),
                                        '4'=>array('no'=>'8609','name'=>'江西机电职业技术学院'),
                                        '5'=>array('no'=>'9046','name'=>'江西科技职业学院'),
                                        '6'=>array('no'=>'7370','name'=>'九江职业大学'),
                                        '7'=>array('no'=>'7600','name'=>'江西财经职业学院'),
                                        '8'=>array('no'=>'9340','name'=>'江西陶瓷工艺美术职业技术学院'),
                                        '9'=>array('no'=>'9260','name'=>'江西工业工程职业技术学院'),
                                        '10'=>array('no'=>'9304','name'=>'江西应用工程职业学院'),
                                        '11'=>array('no'=>'9378','name'=>'赣西科技职业学院'),
                                        '12'=>array('no'=>'9034','name'=>'鹰潭职业技术学院'),
                                        '13'=>array('no'=>'9112','name'=>'江西环境工程职业学院'),
                                        '14'=>array('no'=>'8634','name'=>'江西应用技术职业学院'),
                                        '15'=>array('no'=>'9540','name'=>'湖北水利水电职业技术学院'),
                                        '16'=>array('no'=>'9510','name'=>'武汉商贸职业学院'),
                                        '17'=>array('no'=>'7280','name'=>'咸宁职业技术学院'),
                                        '18'=>array('no'=>'9491','name'=>'湖北生物科技职业学院'),
                                        '19'=>array('no'=>'7200','name'=>'荆州理工职业学院'),
                                        '20'=>array('no'=>'8480','name'=>'湖北国土资源职业学院'),
                                        '21'=>array('no'=>'7157','name'=>'湖北科技职业学院'),
                                        '22'=>array('no'=>'8092','name'=>'武汉城市职业学院'),
                                        '23'=>array('no'=>'8500','name'=>'武汉工业职业技术学院'),
                                        '24'=>array('no'=>'8180','name'=>'湖北轻工职业技术学院'),
                                        '25'=>array('no'=>'5520','name'=>'商丘医学高等专科学校'),
                                        '26'=>array('no'=>'7860','name'=>'黄河水利职业技术学院'),
                                        '27'=>array('no'=>'6381','name'=>'安徽机电职业技术学院')
                                    );
                                }elseif($arr[$province] == "安徽"){
                                    $arr = array($xiancha+20,$xiancha,$xiancha-10,$xiancha-15);
                                    $byb = '';
                                }elseif($arr[$province] == "河南"){
                                    $byb = '';
                                    $arr = array($xiancha+10,$xiancha,$xiancha-10);
                                }
                                $res = $this->yxsx_sorting($province,$project,$batch,$arr,$byb,$yaxian);
                            }
                        }
        }
        return $res;

    }

    /**
     * 院校筛选
     * @author wanghuan
     * @datetime 2015/9/23
     */
    function yxsx_sorting($provinceid,$project,$batch,$arr,$byb='',$yaxian=''){
        $result = array();
        $sql = "SELECT * FROM %s %s";
        if($provinceid == 3 && ($batch == 3 || $batch == 4)){ //河南三四批专用
            $where = "WHERE province='$provinceid' AND project='$project' AND batch='$batch' AND xc<=$arr[0] AND xc>=$arr[1]";
            $res['hnzy'] = $this->fetchAll(sprintf($sql, $this->_name, $where));
            return $res;
        }else{
            $cyc = array();
            $wyw = array();
            if($provinceid == 5){
                if(!empty($yaxian)){
                    $yaxian_batch = $batch+1;
                    $where = "WHERE province='$provinceid' AND project='$project' AND batch='$yaxian_batch' ORDER BY wc ASC limit 0,20";
                    $yaxian = $this->fetchAll(sprintf($sql, $this->_name, $where));
                }else{
                    $yaxian = '';
                }
                $byb = array();
                $where = "WHERE province='$provinceid' AND project='$project' AND batch='$batch' AND xc<=$arr[0] AND xc>=$arr[3]";
                $res = $this->fetchAll(sprintf($sql, $this->_name, $where));
                foreach($res as $val){
                    if($val['xc'] <= $arr[0] && $val['xc'] > $arr[1] && count($cyc)<20){
                        $cyc[] = $val;
                    }elseif($val['xc'] <= $arr[1] && $val['xc'] >= $arr[2] && count($wyw)<20){
                        $wyw[] = $val;
                    }elseif($val['xc'] < $arr[2] && $val['xc'] >= $arr[3] && count($byb)<20){
                        $byb[] = $val;
                    }
                }
            }elseif($batch == 1 || $batch == 2){
                if(!empty($yaxian)){
                    $yaxian_batch = $batch+1;
                    $where = "WHERE province='$provinceid' AND project='$project' AND batch='$yaxian_batch' ORDER BY wc ASC limit 0,20";
                    $yaxian = $this->fetchAll(sprintf($sql, $this->_name, $where));
                }else{
                    $yaxian = '';
                }
                $byb = array();
                $where = "WHERE province='$provinceid' AND project='$project' AND batch='$batch' AND wc>=$arr[0] AND wc<=$arr[3]";
                $res = $this->fetchAll(sprintf($sql, $this->_name, $where));
                foreach($res as $val){
                    if($val['wc'] >= $arr[0] && $val['wc'] < $arr[1] && count($cyc)<20){
                        $cyc[] = $val;
                    }elseif($val['wc'] >= $arr[1] && $val['wc'] <= $arr[2] && count($wyw)<20){
                        $wyw[] = $val;
                    }elseif($val['wc'] > $arr[2] && $val['wc'] <= $arr[3] && count($byb)<20){
                        $byb[] = $val;
                    }
                }
            }elseif(($batch == 3 || $batch == 4) && empty($byb)){
                if(!empty($yaxian)){
                    $yaxian_batch = $batch+1;
                    $where = "WHERE province='$provinceid' AND project='$project' AND batch='$yaxian_batch'  ORDER BY wc DESC limit 0,20";
                    $yaxian = $this->fetchAll(sprintf($sql, $this->_name, $where));
                }else{
                    $yaxian = '';
                }
                $byb = array();
                $where = "WHERE province='$provinceid' AND project='$project' AND batch='$batch' AND xc<=$arr[0] AND xc>=$arr[3]";
                $res = $this->fetchAll(sprintf($sql, $this->_name, $where));
                foreach($res as $val){
                    if($val['xc'] <= $arr[0] && $val['xc'] > $arr[1] && count($cyc)<20){
                        $cyc[] = $val;
                    }elseif($val['xc'] <= $arr[1] && $val['xc'] >= $arr[2] && count($wyw)<20){
                        $wyw[] = $val;
                    }elseif($val['xc'] < $arr[2] && $val['xc'] >= $arr[3] && count($byb)<20){
                        $byb[] = $val;
                    }
                }
            }elseif(($batch == 3 || $batch == 4) && !empty($byb)){
                if(!empty($yaxian)){
                    $yaxian_batch = $batch+1;
                    $where = "province='$provinceid' AND project='$project' AND batch='$yaxian_batch' ORDER BY wc ASC limit 0,20";
                    $yaxian = $this->fetchAll(sprintf($sql, $this->_name, $where));
                }else{
                    $yaxian = '';
                }
                $where = "WHERE province='$provinceid' AND project='$project' AND batch='$batch' AND xc<=$arr[0] AND xc>=$arr[2]";
                $res = $this->fetchAll(sprintf($sql, $this->_name, $where));
                foreach($res as $val){
                    if($val['xc'] <= $arr[0] && $val['xc'] > $arr[1] && count($cyc)<20){
                        $cyc[] = $val;
                    }elseif($val['xc'] <= $arr[1] && $val['xc'] >= $arr[2] && count($wyw)<20){
                        $wyw[] = $val;
                    }
                }

            }
            $result['cyc'] = $cyc;
            $result['wyw'] = $wyw;
            $result['byb'] = $byb;
            $result['yaxian'] = $yaxian;
            return $result;
        }
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
    public function lists($where = array(), $data = array(), $order = '', $page = 1, $pagesize = 15, $group = '')
    {
        $rs = $this->listPage($where, $data, $order, $page, $pagesize, $group);
        return $rs;
    }

    //冒泡函数
    function bubbleSort($numbers){
        $cnt= count($numbers);
        for($i=0;$i<$cnt-1;$i++){//循环比较
            for($j=$i+1;$j<$cnt;$j++){
                if($numbers[$j]>$numbers[$i]){//执行交换
                    $temp=$numbers[$i];
                    $numbers[$i]=$numbers[$j];
                    $numbers[$j]=$temp;
                }
            }
        }
        return $numbers;
    }
}
?>