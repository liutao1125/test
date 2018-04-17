<?php
/*
 * 志愿表
 * @author wanghuan
 */
class DzZhiyuan extends My_EcArrayTable
{
    public $_name ='dz_zhiyuan';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                  //ID
        $this->fill_int($para,$data,'province');            //省份（'1'=>'湖北','2'=>'江西','3'=>'河南','4'=>'安徽','5'=>'江苏'）
        $this->fill_int($para,$data,'batch');               //批次
        $this->fill_int($para,$data,'project');             //文理科  1=>文科   2=>理科
        $this->fill_int($para,$data,'no');                  //排序
        $this->fill_str($para,$data,'name');                //院校名称
        $this->fill_str($para,$data,'address');             //院校地址
        $this->fill_int($para,$data,'wc');                  //
        $this->fill_int($para,$data,'xc');                  //
        Return $data;
    }

    //获取批次，搜索学校
    function getDatas($provinceid, $project,$fraction,$step, $dosubmit="", $batch="", $keyword=""){
        if($dosubmit =='search'){
            $keyword = trim(preg_replace("/[A-Za-z]/", '', $keyword));
            if(!empty($provinceid) && !empty($batch) && !empty($project)){
                //$projects = array("1"=>"wenke","2"=>"like");
                if(empty($keyword)){
                    $res = $this->fetchAll("SELECT * FROM ".$this->getTable()." WHERE project='$project' AND batch='$batch' AND province='$provinceid' limit 0,5");
                }else{
                    $res = $this->fetchAll("SELECT * FROM ".$this->getTable()." WHERE project='$project' AND province='$provinceid' AND name LIKE '%".$keyword."%' limit 0,5");
                }
                return $res;
            }else{
                die();
            }
        }
        if($dosubmit =="dosubmit"){
            $province_arr = array('1'=>'湖北','2'=>'江西','3'=>'河南','4'=>'安徽','5'=>'江苏');
            $province = $province_arr[$provinceid];
            $lineObject = new DzLine();
            $yuanxiaoObject = new DzYuanxiao();
            $line = $lineObject->getOne(array('province'=>$province));
            if($step==1){
                //文科
                if($project == 1){
                    $num = array($fraction,$line['one_w'],$line['two_w'],$line['three_w'],$line['four_w']);
                    $nu = $yuanxiaoObject->bubbleSort($num);
                    if($nu[0] == $fraction){//文科一本
                        $title = "第一批本科";$batch = 1;
                        switch ($provinceid){
                            case 1:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 3:
                                $arr=array('平行志愿A','平行志愿B','平行志愿C','平行志愿D','平行志愿E','平行志愿F');
                                break;
                            case 4:
                                $arr=array('志愿A','志愿B','志愿C','志愿D');
                                break;
                            case 5:
                                $arr=array('志愿A','志愿B','志愿C');
                                break;
                            default:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }elseif($nu[1] == $fraction){//文科2本
                        $title = "第二批本科";$batch = 2;
                        switch ($provinceid){
                            case 1:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 3:
                                $arr=array('平行志愿A','平行志愿B','平行志愿C','平行志愿D','平行志愿E','平行志愿F');
                                break;
                            case 4:
                                $arr=array('志愿A','志愿B','志愿C','志愿D');
                                break;
                            case 5:
                                $arr=array('志愿A','志愿B','志愿C');
                                break;
                            default:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }elseif($nu[2] == $fraction){//理科三本
                        $title = "第三批本科";$batch = 3;
                        switch ($provinceid){
                            case 1:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 3:
                                $arr=array('第一志愿');
                                break;
                            case 4:
                                $arr=array('志愿A','志愿B','志愿C','志愿D');
                                break;
                            case 5:
                                $arr=array('志愿A','志愿B','志愿C');
                                break;
                            default:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }elseif($nu[3] == $fraction){//文科专科
                        $batch = 4;
                        switch ($provinceid){
                            case 1:
                                $title = '高职高专批次';
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $title = '高职（专科）批次';
                                $arr=array('第一志愿');
                                break;
                            case 3:
                                $title = '高职高专批';
                                $arr=array('第一志愿');
                                break;
                            case 4:
                                $title = '高职(专科)批次';
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 5:
                                $title = '第四批高职（专科）';
                                $arr=array('志愿A','志愿B','志愿C','志愿E','志愿F');
                                break;
                            default:
                                $title = "高职(专科)";
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }
                }elseif($project == 2){
                    $num = array($fraction,$line['one_l'],$line['two_l'],$line['three_l'],$line['four_l']);
                    $nu = $yuanxiaoObject->bubbleSort($num);
                    if($nu[0] == $fraction){//理科一本
                        $title = "第一批本科";$batch = 1;
                        switch ($provinceid){
                            case 1:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 3:
                                $arr=array('平行志愿A','平行志愿B','平行志愿C','平行志愿D','平行志愿E','平行志愿F');
                                break;
                            case 4:
                                $arr=array('志愿A','志愿B','志愿C','志愿D');
                                break;
                            case 5:
                                $arr=array('志愿A','志愿B','志愿C');
                                break;
                            default:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }elseif($nu[1] == $fraction){//理科二本
                        $title = "第二批本科";$batch = 2;
                        switch ($provinceid){
                            case 1:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 3:
                                $arr=array('平行志愿1','平行志愿2','平行志愿3','平行志愿4','平行志愿5','平行志愿6');
                                break;
                            case 4:
                                $arr=array('志愿A','志愿B','志愿C','志愿D');
                                break;
                            case 5:
                                $arr=array('志愿A','志愿B','志愿C');
                                break;
                            default:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }elseif($nu[2] == $fraction){//理科三本
                        $title = "第三批本科";$batch = 3;
                        switch ($provinceid){
                            case 1:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 3:
                                $arr=array('第一志愿');
                                break;
                            case 4:
                                $arr=array('志愿A','志愿B','志愿C','志愿D');
                                break;
                            case 5:
                                $arr=array('志愿A','志愿B','志愿C');
                                break;
                            default:
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }elseif($nu[3] == $fraction){//理科专科
                        $batch = 4;
                        switch ($provinceid){
                            case 1:
                                $title = '高职高专批次';
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 2:
                                $title = '高职（专科）批次';
                                $arr=array('志愿A');
                                break;
                            case 3:
                                $title = '高职高专批';
                                $arr=array('第一志愿');
                                break;
                            case 4:
                                $title = '高职(专科)批次';
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                                break;
                            case 5:
                                $title = '第四批高职（专科）';
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E');
                                break;
                            default:
                                $title = "高职(专科)";
                                $arr=array('志愿A','志愿B','志愿C','志愿D','志愿E','志愿F');
                        }
                    }
                }
                $form = array();
                $form[] = '升学在线'.$province.'省普通文理类'.$title.'<br>志愿草表';
                $form[] = $batch;
                foreach($arr as $key=>$val){
                    $form[] = $val;
                }
                return $form;
            }
        }
    }

    function step2($provinceid,$project,$fraction,$batch,$v){
        //省份
        $arr = array('1'=>'湖北','2'=>'江西','3'=>'河南','4'=>'安徽','5'=>'江苏');
        $province = $arr[$provinceid];
        $lineObject = new DzLine();
        $studentfswcObject = new DzStudentFsWc();
        $yuanxiaoObject = new DzYuanxiao();
        //志愿
        $volunteer = array();
        $volunteers = array();
        $volunteers[] = $v['volunteer1'];
        $volunteers[] = $v['volunteer2'];
        $volunteers[] = $v['volunteer3'];
        $volunteers[] = $v['volunteer4'];
        $volunteers[] = $v['volunteer5'];
        $volunteers[] = $v['volunteer6'];
        $n=0;
        foreach($volunteers as $key=>$val){
            if(empty($val)){
                unset($volunteer[$key]);
            }else{
                $volunteer[$n] = $val;
                $n++;
            }
        }
        $line = $lineObject->getOne(array('province'=>$province));//得到省份列表，用于页面显示
        if($province == "湖北"){
            if($project == 1){
                for($i=$fraction;$i>=$line['four_w'];$i--){
                    $weici = $studentfswcObject->getOne(array('province'=>$province,'wk_fs'=>$i));
                    if(is_array($weici) && !empty($weici)){
                        $weici = $weici['wk_wc'];
                        break;
                    }
                }
                $num = array($fraction,$line['one_w'],$line['two_w'],$line['three_w'],$line['four_w']);
                $nu = $yuanxiaoObject->bubbleSort($num);
                if($nu[0] == $fraction){
                    $batch = 1;
                }elseif($nu[1] == $fraction){
                    $batch = 2;
                }elseif($nu[2] == $fraction){
                    $batch = 3;
                    $xiancha = $fraction - $line['three_w'];
                }elseif($nu[3] == $fraction){
                    $batch = 4;
                    $xiancha = $fraction - $line['four_w'];
                }
            }elseif($project == 2){
                for($i=$fraction;$i>=$line['four_l'];$i--){
                    $weici = $studentfswcObject->getOne(array('province'=>$province,'lk_fs'=>$i));
                    if(is_array($weici)){
                        $weici = $weici['lk_wc'];
                        break;
                    }
                }
                $num = array($fraction,$line['one_l'],$line['two_l'],$line['three_l'],$line['four_l']);
                $nu = $yuanxiaoObject->bubbleSort($num);
                if($nu[0] == $fraction){
                    $batch = 1;
                }elseif($nu[1] == $fraction){
                    $batch = 2;
                }elseif($nu[2] == $fraction){
                    $batch = 3;
                    $xiancha = $fraction - $line['three_l'];
                }elseif($nu[3] == $fraction){
                    $batch = 4;
                    $xiancha = $fraction - $line['four_l'];
                }
            }
            //$this->db->table_name = 'zhiyuan';
            $result = array();
            $result['cyc'] = array();
            $result['wyw'] = array();
            $result['byb'] = array();
            $result['yaxian'] = array();
            //$str_volunteers = implode(",",$volunteers);
            //$order_school = $this->db->select("province='$provinceid' AND project='$project' AND name on($str_volunteers)");
            if($batch == 1 || $batch == 2){
                $where1 = "province='$provinceid' AND project='$project' AND wc<$weici";
                $where2 = "province='$provinceid' AND project='$project' AND wc>=$weici";
            }else{
                $where1 = "province='$provinceid' AND project='$project' AND xc>$xiancha  AND batch=$batch";
                $where2 = "province='$provinceid' AND project='$project' AND xc<=$xiancha  AND batch=$batch";
            }
            $res1 = $this->select($where1,"*",'0,20','id DESC');
            $res1 = array_reverse($res1);
            $res2 = $this->select($where2,"*",'0,30','id ASC');
            foreach( $res1 as $key => $val ) {
                if($key <= count($res1)-5-1){
                    $result['cyc'][] = $val['name'];
                }else{
                    $result['wyw'][] = $val['name'];
                }
            }
            foreach( $res2 as $key => $val ) {
                if($key <= 9){
                    $result['wyw'][] = $val['name'];
                }else{
                    $result['byb'][] = $val['name'];
                    //$order[$val['id']] = $val['name'];
                }
            }
            //sort($order);
            $kk=0;
            foreach($volunteer as $val){
                $order_school = $this->getOne(array('province'=>$provinceid,'project'=>$project,'batch' => $batch,'name'=>$val));
                if(is_array($order_school) && !empty($order_school)){
                    $weici_volunteer = $order_school['wc'];//位次，1，2批次使用
                    $xiancha_volunteer = $order_school['xc'];//线差，3，4批次使用
                    if(in_array($val,$result['cyc'])){
                        $order[$order_school['id']] = $order_school['name'];
                        $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>略高于你的分数，可作为冲一冲的院校放前面。';
                    }elseif(in_array($val,$result['wyw'])){
                        $order[$order_school['id']] = $order_school['name'];
                        $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                    }elseif(in_array($val,$result['byb'])){
                        $order[$order_school['id']] = $order_school['name'];
                        $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>略低于你的分数，可作为保一保的院校放后面。';
                    }else{
                        if($batch == 1 || $batch == 2){//1,2批次，用wc
                            if($weici < $weici_volunteer){
                                $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>低于你的分数太多，<span class="red">不建议选择</span>。';
                            }elseif($weici > $weici_volunteer){
                                $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>高于你的分数太多，<span class="red">不建议选择</span>。';
                            }else{
                                $order[$order_school['id']] = $order_school['name'];
                                $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                            }
                        }else{//3,4批次
                            if($xiancha > $xiancha_volunteer){
                                $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>低于你的分数太多，<span class="red">不建议选择</span>。';
                            }elseif($xiancha < $xiancha_volunteer){
                                $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>高于你的分数太多，<span class="red">不建议选择</span>。';
                            }else{
                                $order[$order_school['id']] = $order_school['name'];
                                $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                            }
                        }
                        $kk++;
                    }
                }else{
                    $res[]['msg'] = '你所填报的<span class="green">'.$val.'</span>不在该批次招生或院校名称输入有误。';
                    $kk++;
                }
            }
            if($kk>=3){
                return $res;
            }else{
                ksort($order);
                $i=0;
                foreach($order as $key=>$val){
                    $res[$i]['school'] = $val;
                    $i++;
                }
                //print_r($order);exit;
                return $res;
            }
        }elseif($province == "江苏"){
            for($i=0;$i<count($volunteer);$i++){
                $get_fencha = $this->getOne(array('name'=>$volunteer[$i],'province'=>$provinceid,'batch' => $batch,'project'=>$project));
                $fencha[$i] = $get_fencha['xc'];
            }
            if($batch == 1 && $project == 1){
                $xiancha = $fraction - $line['one_w'];
            }elseif($batch == 2 && $project == 1){
                $xiancha = $fraction - $line['two_w'];
            }elseif($batch == 3 && $project == 1){
                $xiancha = $fraction - $line['three_w'];
            }elseif($batch == 4 && $project == 1){
                $xiancha = $fraction - $line['four_w'];
            }elseif($batch == 1 && $project == 2){
                $xiancha = $fraction - $line['one_l'];
            }elseif($batch == 2 && $project == 2){
                $xiancha = $fraction - $line['two_l'];
            }elseif($batch == 3 && $project == 2){
                $xiancha = $fraction - $line['three_l'];
            }elseif($batch == 4 && $project == 2){
                $xiancha = $fraction - $line['four_l'];
            }
            $arr = array($xiancha+3,$xiancha,$xiancha-2,$xiancha-5);
            $res = $this->sorting2($arr,$fencha,$volunteer);
            return $res;
        }elseif($project == 1){//文科
            if($batch == 1 || $batch == 2){
                $paiming = $studentfswcObject->getOne(array('province'=>$province,'wk_fs'=>$fraction));
                $paiming = $paiming['wk_wc'];
                for($i=0;$i<count($volunteer);$i++){
                    $get_quanzhong = $this->getOne(array('name'=>$volunteer[$i],'province'=>$provinceid,'batch' => $batch,'project'=>$project));
                    $quanzhongs[$i] = $get_quanzhong['wc'];
                }
                if($batch == 1){
                    if($province == "湖北"){
                        $arr = array($paiming-500,$paiming,$paiming+500,$paiming+1000);
                    }elseif($province == "江西"){
                        $arr = array($paiming-500,$paiming,$paiming+500,$paiming+1000);
                    }elseif($province == "安徽"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                    }elseif($province == "河南"){
                        $arr = array($paiming-500,$paiming,$paiming+1000,$paiming+1500);
                    }
                }elseif($batch == 2){
                    if($province == "湖北"){
                        $arr = array($paiming-1500,$paiming,$paiming+1500,$paiming+3000);
                    }elseif($province == "江西"){
                        $arr = array($paiming-1000,$paiming,$paiming+500,$paiming+1000);
                    }elseif($province == "安徽"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                    }elseif($province == "河南"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                    }
                }
                $res = $this->sorting($arr,$quanzhongs,$volunteer);
                return $res;
                //print_r($res);exit;
            }elseif($batch == 3 || $batch == 4){
                for($i=0;$i<count($volunteer);$i++){
                    $get_fencha = $this->getOne(array('name'=>$volunteer[$i],'province'=>$provinceid,'batch' => $batch,'project'=>$project));
                    $fencha[$i] = $get_fencha['xc'];
                }
                if($batch == 3){
                    $xiancha = $fraction - $line['three_w'];
                    if($province == "湖北"){
                        $arr = array($xiancha+5,$xiancha,$xiancha-3,$xiancha-8);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "江西"){
                        $arr = array($xiancha+6,$xiancha,$xiancha-3,$xiancha-9);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "安徽"){
                        $arr = array($xiancha+5,$xiancha,$xiancha-3,$xiancha-8);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "河南"){
                        $arr = array($xiancha+5,$xiancha-12);
                        $res = $this->sorting4($arr,$fencha,$volunteer);
                    }
                    echo json_encode($res);
                }elseif($batch == 4){
                    $xiancha = $fraction - $line['four_w'];
                    if($province == "湖北"){
                        $arr = array($xiancha+20,$xiancha,$xiancha-10);
                        $byb = array(
                            '0'=>'湖北职业技术学院',
                            '1'=>'湖北大学知行学院',
                            '2'=>'湖北工业职业技术学院',
                            '3'=>'襄阳职业技术学院',
                            '4'=>'武汉工程职业技术学院',
                            '5'=>'荆州职业技术学院',
                            '6'=>'湖北工业职业技术学院',
                            '7'=>'荆楚理工学院',
                            '8'=>'武汉城市职业学院',
                            '9'=>'信阳农林学院',
                            '10'=>'黄河水利职业技术学院',
                            '11'=>'河南机电高等专科学校',
                            '12'=>'郑州师范学院',
                            '13'=>'郑州轻工业学院',
                            '14'=>'南昌工程学院',
                            '15'=>'天津医学高等专科学校'
                        );
                        $res = $this->sorting3($arr,$fencha,$volunteer,$byb);
                    }elseif($province == "江西"){
                        $arr = array($xiancha+25,$xiancha,$xiancha-10);
                        $byb = array(
                            '0'=>'江西工业职业技术学院',
                            '1'=>'江西电力职业技术学院',
                            '2'=>'江西信息应用职业技术学院',
                            '3'=>'江西交通职业技术学院',
                            '4'=>'江西机电职业技术学院',
                            '5'=>'江西科技职业学院',
                            '6'=>'九江职业大学',
                            '7'=>'江西财经职业学院',
                            '8'=>'江西陶瓷工艺美术职业技术学院',
                            '9'=>'江西工业工程职业技术学院',
                            '10'=>'江西应用工程职业学院',
                            '11'=>'赣西科技职业学院',
                            '12'=>'鹰潭职业技术学院',
                            '13'=>'江西环境工程职业学院',
                            '14'=>'江西应用技术职业学院',
                            '15'=>'湖北水利水电职业技术学院',
                            '16'=>'武汉商贸职业学院',
                            '17'=>'咸宁职业技术学院',
                            '18'=>'湖北生物科技职业学院',
                            '19'=>'荆州理工职业学院',
                            '20'=>'湖北国土资源职业学院',
                            '21'=>'湖北科技职业学院',
                            '22'=>'武汉城市职业学院',
                            '23'=>'武汉工业职业技术学院',
                            '24'=>'湖北轻工职业技术学院',
                            '25'=>'商丘医学高等专科学校',
                            '26'=>'黄河水利职业技术学院',
                            '27'=>'安徽机电职业技术学院'
                        );
                        $res = $this->sorting3($arr,$fencha,$volunteer,$byb);
                    }elseif($province == "安徽"){
                        $arr = array($xiancha+20,$xiancha,$xiancha-10,$xiancha-15);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "河南"){
                        $arr = array($xiancha+10,$xiancha-10);
                        $res = $this->sorting4($arr,$fencha,$volunteer);
                    }
                    return $res;
                }
            }
        }elseif($project == 2){//理科
            if($batch == 1 || $batch == 2){
                $paiming = $studentfswcObject->getOne(array('province'=>$province,'lk_fs'=>$fraction));
                $paiming = $paiming['lk_wc'];
                for($i=0;$i<count($volunteer);$i++){
                    $get_quanzhong = $this->getOne(array('name'=>$volunteer[$i],'province'=>$provinceid,'batch' => $batch,'project'=>$project));
                    $quanzhongs[$i] = $get_quanzhong['wc'];
                }
                if($batch == 1){
                    if($province == "湖北"){
                        $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }elseif($province == "江西"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }elseif($province == "安徽"){
                        $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }elseif($province == "河南"){
                        $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }
                    return $res;
                }elseif($batch == 2){
                    if($province == "湖北"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }elseif($province == "江西"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+2000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }elseif($province == "安徽"){
                        $arr = array($paiming-2000,$paiming,$paiming+2000,$paiming+4000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }elseif($province == "河南"){
                        $arr = array($paiming-1000,$paiming,$paiming+1000,$paiming+3000);
                        $res = $this->sorting($arr,$quanzhongs,$volunteer);
                    }
                    return $res;
                }
            }elseif($batch == 3 || $batch == 4){
                for($i=0;$i<count($volunteer);$i++){
                    $get_fencha = $this->getOne(array('name'=>$volunteer[$i],'province'=>$provinceid,'batch' => $batch,'project'=>$project));
                    $fencha[$i] = $get_fencha['xc'];
                }
                if($batch == 3){
                    $xiancha = $fraction - $line['three_l'];
                    if($province == "湖北"){
                        $arr = array($xiancha+5,$xiancha,$xiancha-5,$xiancha-10);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "江西"){
                        $arr = array($xiancha+8,$xiancha,$xiancha-5,$xiancha-10);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "安徽"){
                        $arr = array($xiancha+5,$xiancha,$xiancha-5,$xiancha-10);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "河南"){
                        $arr = array($xiancha+5,$xiancha-12);
                        $res = $this->sorting4($arr,$fencha,$volunteer);
                    }
                    return $res;
                }elseif($batch == 4){
                    $xiancha = $fraction - $line['four_l'];
                    if($province == "湖北"){
                        $arr = array($xiancha+20,$xiancha,$xiancha-10);
                        $byb = array(
                            '0'=>'湖北职业技术学院',
                            '1'=>'湖北大学知行学院',
                            '2'=>'湖北工业职业技术学院',
                            '3'=>'襄阳职业技术学院',
                            '4'=>'武汉工程职业技术学院',
                            '5'=>'荆州职业技术学院',
                            '6'=>'湖北工业职业技术学院',
                            '7'=>'荆楚理工学院',
                            '8'=>'武汉城市职业学院',
                            '9'=>'信阳农林学院',
                            '10'=>'黄河水利职业技术学院',
                            '11'=>'河南机电高等专科学校',
                            '12'=>'郑州师范学院',
                            '13'=>'郑州轻工业学院',
                            '14'=>'南昌工程学院',
                            '15'=>'天津医学高等专科学校'
                        );
                        $res = $this->sorting3($arr,$fencha,$volunteer,$byb);
                    }elseif($province == "江西"){
                        $arr = array($xiancha+25,$xiancha,$xiancha-10);
                        $byb = array(
                            '0'=>'江西工业职业技术学院',
                            '1'=>'江西电力职业技术学院',
                            '2'=>'江西信息应用职业技术学院',
                            '3'=>'江西交通职业技术学院',
                            '4'=>'江西机电职业技术学院',
                            '5'=>'江西科技职业学院',
                            '6'=>'九江职业大学',
                            '7'=>'江西财经职业学院',
                            '8'=>'江西陶瓷工艺美术职业技术学院',
                            '9'=>'江西工业工程职业技术学院',
                            '10'=>'江西应用工程职业学院',
                            '11'=>'赣西科技职业学院',
                            '12'=>'鹰潭职业技术学院',
                            '13'=>'江西环境工程职业学院',
                            '14'=>'江西应用技术职业学院',
                            '15'=>'湖北水利水电职业技术学院',
                            '16'=>'武汉商贸职业学院',
                            '17'=>'咸宁职业技术学院',
                            '18'=>'湖北生物科技职业学院',
                            '19'=>'荆州理工职业学院',
                            '20'=>'湖北国土资源职业学院',
                            '21'=>'湖北科技职业学院',
                            '22'=>'武汉城市职业学院',
                            '23'=>'武汉工业职业技术学院',
                            '24'=>'湖北轻工职业技术学院',
                            '25'=>'商丘医学高等专科学校',
                            '26'=>'黄河水利职业技术学院',
                            '27'=>'安徽机电职业技术学院'
                        );
                        $res = $this->sorting3($arr,$fencha,$volunteer,$byb);
                    }elseif($province == "安徽"){
                        $arr = array($xiancha+20,$xiancha,$xiancha-10,$xiancha-15);
                        $res = $this->sorting2($arr,$fencha,$volunteer);
                    }elseif($province == "河南"){
                        $arr = array($xiancha+10,$xiancha-10);
                        $res = $this->sorting4($arr,$fencha,$volunteer);
                    }
                    return $res;
                }
            }
        }
    }

    /**
     * 获取单条数据
     * @author wanghuan
     * @datetime 2015/9/23
     */
    public function getOne($where, $data = array())
    {
        return $this->find($where, $data);
    }


    //冒泡函数-一二批次数组处理
    function sorting($arr,$quanzhongs,$volunteer){
        $result = array();
        $new_arr= $arr;
        $kk = 0;
        for($n=0;$n<count($quanzhongs);$n++){
            $new_arr[] = $quanzhongs[$n];
            $numbers = $new_arr;
            $lenght=count($numbers);
            for($h=0;$h<$lenght-1;$h++){
                for($i=0;$i<$lenght-$h-1;$i++){
                    if($numbers[$i]>$numbers[$i+1]){
                        $kong=$numbers[$i+1];
                        $numbers[$i+1]=$numbers[$i];
                        $numbers[$i]=$kong;
                    }
                }
            }

            if($quanzhongs[$n] == ''){
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }elseif($numbers[2] == $quanzhongs[$n]){//稳一稳
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                $order[$n]['order'] = $quanzhongs[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[1] == $quanzhongs[$n]){//冲一冲
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>略高于你的分数，可作为冲一冲的院校放前面。';
                $order[$n]['order'] = $quanzhongs[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[0] == $quanzhongs[$n]){ //冲一冲左侧，够不着
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>高于你的分数太多，<span class="red">不建议选择</span>。';
                $order[$n]['order'] = $quanzhongs[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }elseif($numbers[3] == $quanzhongs[$n]){//保一保
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>略低于你的分数，可作为保一保的院校放后面。';
                $order[$n]['order'] = $quanzhongs[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[4] == $quanzhongs[$n]){//保一保右侧
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>低于你的分数太多，<span class="red">不建议选择</span>。';
                $order[$n]['order'] = $quanzhongs[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }else{
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }

            $new_arr = $arr;
        }
        if($kk >= 3){
            return $result;
        }else{
            foreach ($order as $key => $val){
                $vals1[$key] = $val['order'];
                $vals2[$key] = $val['school'];
            }
            array_multisort($vals1, SORT_ASC, $order);
            for($i=0;$i<count($result);$i++){
                $result[$i]['school'] = $order[$i]['school'];
            }
            return $result;
        }

    }

    //冒泡函数-三四批次数组处理
    function sorting2($arr,$fencha,$volunteer){
        $result = array();
        $new_arr= $arr;
        $kk = 0;
        for($n=0;$n<count($fencha);$n++){
            $new_arr[] = $fencha[$n];
            $numbers = $new_arr;
            $lenght=count($numbers);
            for($h=0;$h<$lenght-1;$h++){
                for($i=0;$i<$lenght-$h-1;$i++){
                    if($numbers[$i]<$numbers[$i+1]){
                        $kong=$numbers[$i+1];
                        $numbers[$i+1]=$numbers[$i];
                        $numbers[$i]=$kong;
                    }
                }
            }
            if($fencha[$n] == ''){
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }elseif($numbers[2] == $fencha[$n]){//稳一稳
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[1] == $fencha[$n]){//冲一冲
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>略高于你的分数，可作为冲一冲的院校放前面。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[0] == $fencha[$n]){ //冲一冲左侧，够不着
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>高于你的分数太多，<span class="red">不建议选择</span>。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }elseif($numbers[3] == $fencha[$n]){//保一保
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>略低于你的分数，可作为保一保的院校放后面。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[4] == $fencha[$n]){//保一保右侧
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>低于你的分数太多，<span class="red">不建议选择</span>。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }else{
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }

            $new_arr = $arr;
        }
        if($kk >= 3){
            return $result;
        }else{
            foreach ($order as $key => $val){
                $vals1[$key] = $val['order'];
                $vals2[$key] = $val['school'];
            }
            array_multisort($vals1, SORT_DESC, $order);
            for($i=0;$i<count($result);$i++){
                $result[$i]['school'] = $order[$i]['school'];
            }
            return $result;
        }
    }

    //冒泡函数-三四批次数组处理  ,保一保为手动设置
    function sorting3($arr,$fencha,$volunteer,$byb){
        $result = array();
        $new_arr= $arr;
        $kk = 0;
        for($n=0;$n<count($fencha);$n++){
            $new_arr[] = $fencha[$n];
            $numbers = $new_arr;
            $lenght=count($numbers);
            for($h=0;$h<$lenght-1;$h++){
                for($i=0;$i<$lenght-$h-1;$i++){
                    if($numbers[$i]<$numbers[$i+1]){
                        $kong=$numbers[$i+1];
                        $numbers[$i+1]=$numbers[$i];
                        $numbers[$i]=$kong;
                    }
                }
            }
            if(in_array($volunteer[$n],$byb)){//保一保
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>略低于你的分数，可作为保一保的院校放后面。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($fencha[$n] == ''){//不在
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }elseif($numbers[2] == $fencha[$n]){//稳一稳
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[1] == $fencha[$n]){//冲一冲
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>略高于你的分数，可作为冲一冲的院校放前面。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[0] == $fencha[$n]){ //冲一冲左侧，够不着
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>高于你的分数太多，<span class="red">不建议选择</span>';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }else{
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }

            $new_arr = $arr;
        }
        if($kk >= 3){
            return $result;
        }else{
            foreach ($order as $key => $val){
                $vals1[$key] = $val['order'];
                $vals2[$key] = $val['school'];
            }
            array_multisort($vals1, SORT_DESC, $order);
            for($i=0;$i<count($result);$i++){
                $result[$i]['school'] = $order[$i]['school'];
            }
            return $result;
        }
    }

    //冒泡函数-三四批次数组处理  ,河南三四批专用
    function sorting4($arr,$fencha,$volunteer){
        $result = array();
        $new_arr= $arr;
        $kk=0;
        for($n=0;$n<count($fencha);$n++){
            $new_arr[] = $fencha[$n];
            $numbers = $new_arr;
            $lenght=count($numbers);
            for($h=0;$h<$lenght-1;$h++){
                for($i=0;$i<$lenght-$h-1;$i++){
                    if($numbers[$i]<$numbers[$i+1]){
                        $kong=$numbers[$i+1];
                        $numbers[$i+1]=$numbers[$i];
                        $numbers[$i]=$kong;
                    }
                }
            }
            if($fencha[$n] == ''){
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }elseif($numbers[1] == $fencha[$n]){
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>与你的分数较吻合，可作为最合适的院校放中间。';
                $order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
            }elseif($numbers[0] == $fencha[$n]){ //左侧，够不着
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在可选范围内，<span class="red">不建议选择</span>';
                //$order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }elseif($numbers[2] == $fencha[$n]){//右侧，稳一稳
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在可选范围内，<span class="red">不建议选择</span>';
                //$order[$n]['order'] = $fencha[$n];$order[$n]['school'] = $volunteer[$n];
                $kk++;
            }else{
                $result[]['msg'] = '你所填报的<span class="green">'.$volunteer[$n].'</span>不在这个批次招生。';
                $kk++;
            }
            $new_arr = $arr;
        }
        if($kk >= 3){
            return $result;
        }else{
            foreach ($order as $key => $val){
                $vals1[$key] = $val['order'];
                $vals2[$key] = $val['school'];
            }
            array_multisort($vals1, SORT_DESC, $order);
            for($i=0;$i<count($result);$i++){
                $result[$i]['school'] = $order[$i]['school'];
            }
            return $result;
        }
    }
}
?>