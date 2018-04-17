<?php

/**
 * author yy
 * pos云小票打印机  
 * code — 打印后 返回的json格式 状态:1.打印成功 -1.key校验错误，-2.缺纸，-3时间戳已用不能重复打印
 * $is_child == 1 区分多门店外卖堂吃  == 0 外卖  == 2 //通用版外卖
 * $xp_type == 1 小切（大纸）  == 2 小普（小纸）
 */
class CloudPrintApi {

    public function getprintAction($uid, $is_child) {
        $WxUser = new WxUser();
//        $WxRepastsrestaurant = new WxRepastsrestaurant();
        //$is_child = $WxUser->scalar("is_child", "where uid=$uid"); 
        $StorefrontRepasts = new StorefrontRepasts();
        $WxGprsInfo = new WxGprsInfo();
        $WxGprstotalInfo = new WxGprstotalInfo();
        $RepastTakeout = new RepastTakeout();
        $WxOnOff = new WxOnOff();
        $xpkey = "weixindodoca@2014shanghaixpj";
        $dqdate = date("Y-m-d");

        if ($is_child == 1) {
//            $xp_type = $WxRepastsrestaurant->scalar("xp_type", "where id=$uid");
            $xp_type = $StorefrontRepasts->scalar("xp_type", "where storefront_id=$uid");
        } else {
            $xp_type = $WxOnOff->scalar("xp_type", "where userid=$uid");
        }

        //汇总小票打印
        if ($is_child == 1) {
            $totalinfo = $WxGprstotalInfo->scalar("*", "where restaurant_id=$uid and cdate='$dqdate'");
        } else {
            $totalinfo = $WxGprstotalInfo->scalar("*", "where userid=$uid and cdate='$dqdate'");
        }
        if (!$totalinfo) {
            if ($is_child == 1) { //多门店外卖
                $tjinfo = $RepastTakeout->multistatistics($uid);
                if ($xp_type == 1) {
                    $content = $WxGprstotalInfo->cloudmanygetcontent($uid);
                } elseif ($xp_type == 2) {
                    $content = $WxGprstotalInfo->xzcloudmanygetcontent($uid);
                }
            } elseif ($is_child == '0') { //外卖
                $tjinfo = $RepastTakeout->statistics($uid);
                if ($xp_type == 1) {
                    $content = $WxGprstotalInfo->cloudgetcontent($uid);
                } elseif ($xp_type == 2) {
                    $content = $WxGprstotalInfo->xzcloudgetcontent($uid);
                }
            } else {  //通用版外卖
                $tjinfo = $RepastTakeout->takeout_statistics($uid);
                if ($xp_type == 1) {
                    $content = $WxGprstotalInfo->hycloudgetcontent($uid);
                } elseif ($xp_type == 2) {
                    $content = $WxGprstotalInfo->hyxzcloudgetcontent($uid);
                }
            }

            $endtime = $tjinfo['restaurant_end_datetime'];
            $endtime1 = strtotime('2014-01-01 ' . date("H:i", strtotime("$endtime -30 min")) . ":00");
            $endtime2 = strtotime("2014-01-01 $endtime:00");
            $dqtime = strtotime(date("2014-01-01 H:i:00"));
            if ($dqtime >= $endtime1 && $dqtime <= $endtime2) {
                if (!$totalinfo) {
                    if ($is_child == 1) {
                        $xpsite = $StorefrontRepasts->scalar("wmsite", "where storefront_id=$uid");
//                        $xpsite = $WxRepastsrestaurant->scalar("wmsite", "where id=$uid");
                    } else {
                        $xpsite = $WxOnOff->scalar("wmsite", "where userid=$uid");
                    }
                    $mdkey = $this->EncyptUrl(array('type' => "text", 'content' => $content, 'cdate' => time()), $xpkey);
                    $content = urlencode($content);

                    $url = "http://" . $xpsite . "/service/sendPrintData.php?content={$content}&type=text&key=$mdkey&cdate=" . time();
                    $content = file_get_contents($url);
                    $tjdyinfo = json_decode($content, true);
                    if ($tjdyinfo['code'] == 1) {
                        if ($is_child == 1) {
                            $tjid = $WxGprstotalInfo->insert(array("restaurant_id" => $uid, "status" => 1, "enddate" => $tjinfo['restaurant_end_datetime'], "cdate" => date("Y-m-d")));
                        } else {
                            $tjid = $WxGprstotalInfo->insert(array("userid" => $uid, "status" => 1, "enddate" => $tjinfo['restaurant_end_datetime'], "cdate" => date("Y-m-d")));
                        }
                    }
                }
            }
        }
        //餐饮小票打印
        if ($is_child == 1) {
            $info = $WxGprsInfo->scalar("*", "where restaurant_id=$uid and status=0 order by cdate desc ");
        } else {
            $info = $WxGprsInfo->scalar("*", "where userid=$uid and restaurant_id=0 and status=0 order by cdate desc ");
        }
        if ($info) {
            if ($info['ctype'] == 1 || $info['ctype'] == 3 || $info['ctype'] == 5) {//外卖
                if ($is_child == 1) {
                    $xpsite = $StorefrontRepasts->scalar("wmsite", "where storefront_id=$uid");
//                    $xpsite = $WxRepastsrestaurant->scalar("wmsite", "where id=$uid");
                } else {
                    $xpsite = $WxOnOff->scalar("wmsite", "where userid=$uid");
                }
            } else {//堂吃
                if ($is_child == 1) {
//                    $xpsite = $WxRepastsrestaurant->scalar("tcsite", "where id=$uid");
                    $xpsite = $StorefrontRepasts->scalar("wmsite", "where storefront_id=$uid");
                } else {
                    $xpsite = $WxOnOff->scalar("tcsite", "where userid=$uid");
                }
            }
            if ($info['ctype'] == 1) { //外卖
                $a = $RepastTakeout->orderinfo($info['cid']);
                //multi_print (1:打印1次，2:打印2次，3:打印3次)  默认为 1  
                $content = '';
                for ($i = 0; $i < $a['order']['multi_print']; $i++) {
                    if ($xp_type == 1) {
                        $content .= $WxGprsInfo->cloudgetcontent($info['cid'], $a) . "\n\n";
                    } elseif ($xp_type == 2) {
                        $content .= $WxGprsInfo->xzcloudgetcontent($info['cid'], $a) . "\n\n";
                    }
                }
            } elseif ($info['ctype'] == 2) {  //堂吃
                $a = $RepastTakeout->meal($info['cid'], $uid);
                //multi_print (1:打印1次，2:打印2次，3:打印3次)  默认为 1  
                $content = '';
                for ($i = 0; $i < $a['order']['multi_print']; $i++) {
                    if ($xp_type == 1) {
                        $content .= $WxGprsInfo->cloudgetstcontent($info['cid'], $a) . "\n\n";
                    } elseif ($xp_type == 2) {
                        $content .= $WxGprsInfo->xzcloudgetstcontent($info['cid'], $a) . "\n\n";
                    }
                }
            } elseif ($info['ctype'] == 3) { //多门店外卖
                $a = $RepastTakeout->multiorderinfo($info['cid']);
                $content = '';
                for ($i = 0; $i < $a['order']['multi_print']; $i++) {
                    if ($xp_type == 1) {
                        $content .= $WxGprsInfo->cloudmanygetcontent($info['cid'], $a) . "\n\n";
                    } elseif ($xp_type == 2) {
                        $content .= $WxGprsInfo->xzcloudmanygetcontent($info['cid'], $a) . "\n\n";
                    }
                }
            } elseif ($info['ctype'] == 4) {  //多门店堂吃 
                $a = $RepastTakeout->multimeal($info['cid']);
                $content = '';
                //multi_print (1:打印1次，2:打印2次，3:打印3次)  默认为 1  
                for ($i = 0; $i < $a['order']['multi_print']; $i++) {
                    if ($xp_type == 1) {
                        $content .= $WxGprsInfo->cloudmanygetstcontent($info['cid'], $a) . "\n\n";
                    } elseif ($xp_type == 2) {
                        $content .= $WxGprsInfo->xzcloudmanygetstcontent($info['cid'], $a) . "\n\n";
                    }
                }
            } elseif ($info['ctype'] == 5) {//行业版外卖
                $a = $RepastTakeout->takeout_orderinfo($info['cid']);
                $content = '';
                for ($i = 0; $i < $a['order']['multi_print']; $i++) {
                    if ($xp_type == 1) {
                        $content .= $WxGprsInfo->hycloudgetcontent($info['cid'], $a) . "\n\n";
                    } elseif ($xp_type == 2) {
                        $content .= $WxGprsInfo->hyxzcloudgetcontent($info['cid'], $a) . "\n\n";
                    }
                }
            }
            $mdkey = $this->EncyptUrl(array('type' => "text", 'content' => $content, 'cdate' => time()), $xpkey);
            $content = urlencode($content);
            $url = "http://" . $xpsite . "/service/sendPrintData.php?content={$content}&type=text&key=$mdkey&cdate=" . time();
            $content = file_get_contents($url);
            $dyinfo = json_decode($content, true);
            if ($dyinfo['code'] == 1) {
                $WxGprsInfo->update_data(array("status" => '1'), "where id='" . $info['id'] . "'"); //更新
                if ($info['ctype'] == 1) {
                    $RepastTakeout->update_type($info['cid'], 1, $uid); //1为(下单)打印成功
                } elseif ($info['ctype'] == 3) {
                    $RepastTakeout->multi_update_type($info['cid'], 1); //1为(下单)打印成功
                } elseif ($info['ctype'] == 5) {
                    $RepastTakeout->takeout_update_type($info['cid'], 1, $uid); //1为(下单)打印成功
                }
            } elseif ($_GET['code'] == -1 || $_GET['code'] == -2 || $_GET['code'] == -3) {
                exit;
            }
        }
    }

    //md5加密
    function EncyptUrl($data, $key) {
        ksort($data, SORT_STRING);
        $__av = array_values($data);
        $src_string = implode(",", $__av) . $key;
        $md5 = md5($src_string);
        return $md5;
    }

}

?>