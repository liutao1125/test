<?php
/**
 * @author sunshichun@dodoca.com
 * 处理微信数据
 */
class WeiXin
{
	private $Token;
	private $AppId;
	private $AppSecret;
	private $uid;
	private $from_type;
	private $openid;
	private $dev_user_name;
	private $fans_id;
	private $userinfo;
	private $wxopen;
	private $is_auth=false; //是否是授权公众号
	private $authorizer_access_token; //是否是授权公众号
	private $authorizer_refresh_token;//刷新令牌
	public function __construct($uid,$from_type='1',$token=''){
		$this->uid=$uid;
		$this->from_type=$from_type;
		$info=User::get_user_info($uid);
		if($info["status"]=='-1' || $info["over_time"]<date("Y-m-d"))//过期或者被删除
		{
			PubFun::save_log('uid->'.$this->uid.',info_uid->'.$info["uid"],'wx_over_time');
			exit;
		}
		$this->userinfo=$info;
		if(!$token)
		{
			$this->Token=$this->userinfo["userkey"];
		}
		else
		{
			$this->Token=$token;
		}
		$this->init_account($uid,$from_type);
	}
	
	public function init_account($uid,$from_type)
	{
		$wxaccount = new WxUserAccount();
		$accountinfo = $wxaccount->get_acc_type($uid,$from_type);
		if($accountinfo["is_auth"]==1){
			$this->is_auth = true;
			$this->authorizer_refresh_token = $accountinfo["authorizer_refresh_token"];
			$this->AppId=$accountinfo["appid"];
			$this->AppSecret=$accountinfo["appsecret"];
			$this->wxopen = new Wxopen("justdecrypt");
		}else{
			$wx=new WxUser();
			$data=$wx->get_user_account($uid,$from_type);
			if($data)
			{
				$this->AppId=$data["appid"];
				$this->AppSecret=$data["appsecret"];
			}
		}
	}
	
	/**
	 * 初始粉丝数据
	 */
	public function init_fans()
	{
		$wei=new WxUserFans();
		$rs=$wei->get_row_byusername($this->uid,$this->openid);
		if($rs)
		{
			$this->fans_id=$rs["id"];
		}
		else
		{
			$this->fans_id=0;
		}
		$wei->close();
		unset($wei);
	}
	
	/**
	 * 绑定验证
	 */
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}
	
	/**
	 * 临时写订阅数据
	 */
	public function tmp_subscribe($x='',$y='')
	{
			if(!$this->openid || !$this->uid)return;
			$fans=new WxUserFans();
			$ext=$fans->scalar("id,userid"," where userid=".$this->uid." and weixin_user='".$this->openid."'");
			if(!$ext)
			{
				$u_d["userid"]=$this->uid;
				$u_d["weixin_user"]=$this->openid;
				$u_d["status"]=1;
				$u_d["sub_date"]=time();
				$u_d["fans_type"]=$this->from_type;
				$fans->insert_data($u_d);
			}

			$fans->close();
			unset($fans);
	}
	
	/**
	 * 微信请求响应地址
	 */
	public function responseMsg()
	{
		if(isset($_GET["echostr"]))//验证提交
		{
			$this->valid();
		}
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
			$log=new WxMsg();
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = trim($postObj->FromUserName);
            $data["fromusername"]=str_replace("'","",$fromUsername);
            $data["tousername"] = trim($postObj->ToUserName);
            $keyword = $postObj->Content?$postObj->Content:'';
            $data["content"]=trim($keyword);
            $data["msgtype"]=trim($postObj->MsgType);
            $data["picurl"]=trim($postObj->PicUrl );
            $data["event"] = trim($postObj->Event?$postObj->Event:'');
            $data["eventkey"] = trim($postObj->EventKey?$postObj->EventKey:'');
            $data["createtime"]=trim($postObj->CreateTime);
            $data["msgid"]=trim($postObj->MsgId);

            //PubFun::save_log('uid->'.$this->uid.',msg->'.$data["content"].',event->'.$data["event"] .',eventkey->'.$data["eventkey"],'3397_log');
           
            $call_msg='';
            $msgid=$data["msgid"];
			
            $this->openid=$data["fromusername"];
            $this->dev_user_name=$data["tousername"];
            //PubFun::save_log('uid->'.$this->uid.',msg->'.$data["content"].',event->'.$data["event"] .',eventkey->'.$data["eventkey"],'meme_log');
            //临时收集用户数据
            $this->tmp_subscribe();
            $this->init_fans();
            $WxUserFans = new WxUserFans();
            $WxUserFans->update(array('end_call'=>time()), "where id=".$this->fans_id);
//             $DzDebugLog = new DzDebugLog();
//             $DzDebugLog->insert(array(
//                 'descript' => '欢迎词调试',
//                 'jsondata' => $data["msgtype"].'===='.$data["event"],
//                 'createtime' => SYS_TIME
//             ));
            switch($data["msgtype"])
            {
            	case "text"://推送文本消息
            		$data["userid"]=$this->uid;
		            $data["fans_id"]=$this->fans_id;
            		$ret=$this->get_key_msg($data["content"]);

            		$kg=new WxOnOff();
            		$info=$kg->get_row_byuid($this->uid);
                        
                        $WxKefuReply  =new WxKefuReply();
                        $is_open = $WxKefuReply->scalar("is_open", "where userid={$this->uid}");
            		if($ret && isset($ret["not_match"])){//未匹配到数据
                            if($ret["not_match"]==2){
                                if($is_open == 1){  //微客服
                                    //写入chat,custom,custom_log   智能客服调试！！！！！！！！
                                    $this->write_log($data);
                                    //return false;
                                    //end 
                                }
                            }
            			if(!empty($info) && $info["auto_reply"]){//已开通智能客服
                                    $data["msg_type"]=1;
            			}else{//关闭智能客服
                                    $data["msg_type"]=2;
            			}
            		}
                    if($is_open == 1){ //客服评价
                        	$WxKefuReply = new WxKefuReply();
                        	$is_judge = $WxKefuReply->scalar("is_judge", "where userid={$this->uid}");
                        	if($is_judge==1){
	                            if(in_array($data['content'],array('1','2','3','4'))){
	                                $WxKefuCustomLog = new WxKefuCustomLog();
	                                $xtime = time()-600;
	                                $kefu_pl = $WxKefuCustomLog->fetchAll("select count(*) as num from wx_kefu_custom_log where endtime>{$xtime} and fans_id={$data['fans_id']} and degree=0");
	                                if($kefu_pl[0]['num'] > 0){
	                                    $this->write_log($data);
	                                }
	                            }
                        	}
                        }
            		$data["content"]=str_replace("'","", $data["content"]);
            		$data["msg_from"]=$this->from_type;//1->微信，2->易信
            		$log->insert_data($data);
            		$kg->close();
            		unset($kg);
            		$log->close();
            		unset($log);
            		if($ret && isset($ret["not_match"])){
            			//$this->call_service();
            		}
            	
            		$this->to_msg($ret);
            		break;
            	case "image":
            		if($data["picurl"])
            		{
            			$mc_val=mc_get($msgid.$this->fans_id);
            			if($mc_val)//防止微信重复提交
            			{
            				exit;
            			}
            			mc_set($msgid.$this->fans_id,'1');

            			$pic=new PicData();
            			$pic_info=$pic->down_img($data["picurl"]);
            			if(!$pic_info["id"])
            			{
            				$pic_info=$pic->down_img($data["picurl"]);
            			}
            			$pic->close();
            			unset($pic);
            			if($pic_info["id"] && $pic_info["org"])
            			{
            					$photo=new PhotoCategory();
            					$p_data["pic_id"]=$pic_info["id"];
            					$p_data["userid"]=$this->uid;
            					$p_data["status"]="0";
            					$p_data["from_type"]="2";
            					$pic_id=$photo->insert_data($p_data);
     
            					$onoff=new WxOnOff();
								$printonff=$onoff->get_data_one("printonff,printyldonoff","where userid=".$this->uid);
            					if($printonff['printonff'] == 1){//进入打印处理
            						$WxPrintLog = new WxPrintLog();
            						$WxPrintInfo = new WxPrintInfo();
            						$macinfo = $WxPrintInfo->scalar("*","where status>-1 and userid=".$this->uid);
            						if($printonff['printyldonoff']){
            							$macinfo['mac_type'] = 2;
            						}
            						if($macinfo){
            							$plid = $WxPrintLog->insert_data(array("userid"=>$this->uid,"img_url"=>$pic_info["id"],"fansid"=>$this->fans_id));
            							if($plid){
            								if($macinfo['mac_type']=='1'){
            										$WxPrint = new WxPrint();
	            									$infostr = $WxPrint->dataSend($postStr);
	            									$this->call_msg($infostr);
		            								exit;
            								}elseif($macinfo['mac_type']=='2'){
            										$WxPrint = new WxPrint();
	            									$infostr = $WxPrint->dataSendYin($this->uid,$postStr);
	            									$this->call_msg($infostr);
		            								exit;
            								}else{
            										$this->call_msg("图片我们已经收到，如需剪切，请点击<a href='".WHB_DOMAIN."/jietuphone/jtimg?id=".$plid."'> 【剪切】</a> 如需添加文字，请回复 #文字 ；例如：#值得纪念的一天完成以上定制需求后请输入 @消费码 进行打印，输入格式：@1111");
	            									exit;
            								}
            							}
            						}
            					}
            				$photo->close();
            				$this->call_msg('您的图片我们已经收到，谢谢！');
            			}
            			else
            			{
            				mc_set($msgid,'0');
            				$this->call_msg('发送图片失败！');
            				exit;
            			}
            		}
            		break;
            	case "event"://事件推送

            		//PubFun::save_log('uid->'.$this->uid.',X->'. $data["Latitude"].',Y->'.$data["Longitude"].',event->'.$data["event"],'location');
            		if($data["event"]=="CLICK")
            		{
            			$eventkey=trim($data["eventkey"]);
            			$ret=null;
            			if($eventkey)
            			{
            				$arr=explode('_',$eventkey);
            				$id=$arr[2];
            				
            				if($id)
            				{
            					$menu=new WxFootMenu();
            					$rs=$menu->get_row_byid($id);
            					if($rs)
            					{
            						$from_type=0;
            						if($rs["menutype"]=='1')//文字
            						{
            							$from_type=1;
            						}
            						else if($rs["menutype"]=='2')//单图文
            						{
            							$from_type=2;
            						}
            						else if($rs["menutype"]=='5')//多图文
            						{
            							$from_type=3;
            						}
            						if($from_type)
            						{
            							$ret=$this->get_data_byid($rs["pk_id"],$from_type);
//             							$DzDebugLog = new DzDebugLog();
//             							$DzDebugLog->insert(array('descript'=>'eventkey=>'.eventkey,'jsondata'=>json_encode($data)));
//             							$DzDebugLog->insert(array('descript'=>'pk_id=>'.$rs["pk_id"],'jsondata'=>json_encode($ret)));
            							$this->save_view_log($from_type,$rs["pk_id"]);
            							
//             							$tjmenu = new TjPvuvMenu();
//             							$tjmenu->insert_data(array('id' => $id, 'uid' => $this->uid));             //底部菜单统计
//             							$tjmenu->close();
//             							unset($tjmenu);
            						}
            					}
            					$menu->close();
            					unset($menu);
            				}
            			}
            			if($ret)
            			{
            				$this->to_msg($ret);
            			}
            			else
            			{
            				$ret=$this->get_default_msg($this->uid,1);
            				$this->to_msg($ret);
            			}
            		}
            		else if($data["event"]=="subscribe")//关注
            		{
//             		    $DzDebugLog = new DzDebugLog();
//             		    $DzDebugLog->insert(array(
//             		        'descript' => '欢迎词调试',
//             		        'jsondata' => '欢迎词调试成功',
//             		        'createtime' => SYS_TIME
            		    
//             		    ));
            			$wei=new WxUserFans();
            			$this->fans_id=$wei->subscribe($data["fromusername"],$this->uid,$this->from_type);
//             			$ccbstatus=VindaApi::registermember($this->fans_id);//扫码注册
            			$wei->close();
            			unset($wei);
            			//修改粉丝卡状态
//             			FansCard::alter_fanscard_flag(1,$this->fans_id);

//             			PubFun::save_log('这里开始调用欢迎词','sxzx_welcome_data');//记录欢迎词调用
            			$ret=$this->get_default_msg($this->uid,2);//欢迎词
//             			PubFun::save_log($ret,'sxzx_welcome_data');//记录欢迎词调用
//             			$DzDebugLog->insert(array(
//             			    'descript' => '欢迎词调试',
//             			    'jsondata' => json_encode($ret),
//             			    'createtime' => SYS_TIME
//             			));
            			$this->to_msg($ret);
            		}
            		else if($data["event"]=="SCAN")//扫描二维码
            		{
            			if($data["eventkey"])//有场景值
            			{
            				if($data["eventkey"]=='99996')//注册场景二维码
            				{
            					$this->reg();
            				}
            				else if($data["eventkey"]=='99998')//未验证, 扫描二维码绑定
            				{
            					$this->band_scan();
            				}
            				else if($data["eventkey"]=='99997')//找回密码
            				{
            					$this->get_pwd();
            				}
            				else if($data["eventkey"]=='99995')//扫描二维码获取登录授权码(指尖海报)
            				{
            					$u=new WxFreeUser();
            					$this->get_acc_code($u);
            				}
            				else if($data["eventkey"]=='99993')//扫描二维码获取登录授权码(点客排版)
            				{
            					$u=new WxFreeUserFont();
            					$this->get_acc_code($u);
            				}
            				else
            				{
	            				$u_data["userid"]=$this->uid;
	            				$w=new WxQrcode();
	            				$t=$w->scalar("*"," where userid=".$this->uid." and scene=".$data["eventkey"]);
	            				if($t)
	            				{
	            					$mc_key=$this->uid.'_scan_'.$data["eventkey"];
	            					$mc_data=mc_get($mc_key);
	            					if(!$mc_data)
	            					{
	            						mc_set($mc_key,'1',30);//设置缓存30秒，房租重复提交
	            						
		            					$u_data["qrcodeid"]=$t["id"];
		            					$fans=new WxUserFans();
		            					$fan_data=$fans->scalar("*"," where userid=".$this->uid." and weixin_user='".$data["fromusername"]."'");
		            					if($fan_data)
		            					{
		            						$u_data["fansid"]=$fan_data["id"];
		            					}
		            					else
		            					{
		            						$u_data["fansid"]=$fans->subscribe($data["fromusername"],$this->uid);
		            					}
		            					if($u_data["fansid"])
		            					{
		            						$u_data["scanningtime"]=time();
		            						$detail=new WxQrcodeDetail();
		            						$detail->insert_data($u_data);//记录扫描日志
		            						$detail->close();
		            						unset($detail);
		            						if($t["stairone"] || $t["stairtwo"])//有模块
		            						{
			            						//处理回复数据
			            						$call_msg=$GLOBALS['module_category'][$t["stairone"]]["name"];
			            						if($call_msg)
			            						{ 
			            							if($t["stairone"]=='18' && $this->userinfo["ver_type"]=='4')//店铺、电商
			            							{
			            								$call_msg='微电商';
			            							}
				            						$link_url=str_replace('FANS_ID',$this->fans_id,$t["url"]);
													if(strpos($link_url,'OPEN_ID_E')!==false)
													{
														$tmp_open_id=PubFun::encrypt($this->fans_id,'E');
														$tmp_open_id=base64_encode($tmp_open_id);
														$link_url=str_replace('OPEN_ID_E',$tmp_open_id,$link_url);
														unset($tmp_open_id);
													} 
													if(strpos($link_url,'USER_ACC_KEY')!==false)
													{
														$wxt=new WxCardAccTime();
														$rand_key=$wxt->get_acc_key();
														if($rand_key)
														{
															$link_url=str_replace('USER_ACC_KEY',$rand_key,$link_url);
														}
														$wxt->close();
													}
			            							$call_msg="<a href='".$link_url."'>".$call_msg."</a>";
			            							$this->call_msg($call_msg);
			            						}
		            						}
		            					}
		            					$fans->close();
		            					unset($fans);
	            					}
	            				}
	            				$w->close();
	            				unset($w);
            				}
            			}
            		}
            		else if($data["event"]=="unsubscribe")//取消订阅
            		{
            			$wei=new WxUserFans();
            			$wei->unsubscribe($data["fromusername"],$this->uid);
            			$wei->close();
            			unset($wei);
            			//修改粉丝卡状态
            			FansCard::alter_fanscard_flag(2,$this->fans_id);
            		}
            		else if($data["event"]=='MASSSENDJOBFINISH')//群发结束
            		{
            			//$qf=new WxQunFa();
            			$msgid = trim($postObj->MsgID);
            			$massend = new WxMassSend();
            			$uu_data["send_err"]=trim($postObj->Status);
            			$uu_data["filtercount "]=trim($postObj->FilterCount );
            			$uu_data["sentcount"]=trim($postObj->SentCount );
            			$uu_data["errorcount"]=trim($postObj->ErrorCount );
            			$massend->update_data($uu_data," where msgid='".$msgid."'");
            			//$qf->update_data($uu_data," where msgid='".$msgid."'");
            		}
            		else if($data["event"]=='card_pass_check')//卡券通过审核
            		{
            		    $coupon = new CouponsInfo();
            			$card_id = trim($postObj->CardId);
            			$coupon->update(array("audit_status"=>3)," where `card_id`='{$card_id}'");
            			unset($coupon);
    
            		}
            		else if($data["event"]=='card_not_pass_check')//卡券未通过审核
            		{
            			$coupon = new CouponsInfo();
            			$card_id = trim($postObj->CardId);
            			$coupon->update(array("audit_status"=>2)," where `card_id`='{$card_id}'");
            			unset($coupon);

            		}
            		else if($data["event"]=='user_get_card')//用户领取卡券
            		{
            			
            			$coupon = new CouponsInfo();
            			$wxuserfans = new WxUserFans();
            			$coupons_subtabulation = new CouponsSubtabulation();
            			$card_id = trim($postObj->CardId);
            			$coupon_info = $coupon->scalar("id,start_end_type,start_date,end_date", " where `card_id`='{$card_id}'");
            			if(!empty($coupon_info["id"])){
	            			$openid = $wxuserfans->scalar("id", " where `weixin_user`='".$this->openid."'");
	            			$data = array();
	            			//处理有效期
	            			if($coupon_info["start_end_type"]==1){
	            				$data["start_datetimes"] = $coupon_info["start_date"];
	            				$data["end_datetimes"] = $coupon_info["end_date"];
	            			}
	            			if($coupon_info["start_end_type"]==2){
	            				$data["start_datetimes"] = time()+$coupon_info["start_date"]*24*3600;
	            				$data["end_datetimes"] = time()+$coupon_info["end_date"]*24*3600;
	            			}
	            			$data["coupons_info_id"] = $coupon_info["id"];
	            			$data["coupons_code"] = "微信：".$postObj->UserCardCode;
	            			$data["datetimes"] = time();
	            			$data["code_id"] = $postObj->UserCardCode;
	            			$data["status"] = 1;
	            			$data["openid"] = $openid;
	            			$data["userid"] = $this->uid;
	            			$coupons_subtabulation->insert_data($data);
            			}
            			
            		}
            		else if($data["event"]=='user_del_card')//用户删除卡券
            		{
            			//PubFun::save_log(json_encode($postStr),"wxcardevent");
            			//todo，由於code非点客自定义，所以暂时无法实现删除卡券的同步
            		}
            		break;
            	case "location":

            		/*$loc=new WxLocation();
            		$shop_list=$loc->get_near_location(trim($postObj->Location_X),trim($postObj->Location_Y),$this->uid);
            		if($shop_list)
            		{
            			$msg=$shop_list[0]["title"].":\r\n".$shop_list[0]["description"];
            			$this->call_msg($msg);
            		}
            		else
            		{
            			$this->call_msg('附近没有您要找的信息！');
            		}
            		$loc->close();
            		unset($loc);*/
            		
            		/******************liuxiaofa,2014-12-22*******************/
            		
            		$str=$_SERVER['SERVER_NAME'];
            		$in=strstr($str,'t.');
            		if(empty($in)){
            			$sername="www.dodoca.com";
            		}else{
            			$sername="t.new.dodoca.com";
            		}
            		$wxLbsSet = new WxLbsSet();
            		$PicData = new PicData();
            		$wxLbsStore = new WxLbsStore();
            		$lbsset=$wxLbsSet->scalar('*', "where userid={$this->uid}");
            		if(empty($lbsset)){
            			$pic='http://'.$sername.'/www/images/mrlbs.jpg';
            			$metre='10000';
            		}else{
            			$metre=$lbsset['metre'];
            			if(empty($lbsset['image'])){
            				$pic='http://'.$sername.'/www/images/mrlbs.jpg';
            			}else{
            				$image=$lbsset['image'];
            				$picrs=$PicData->get_row_byid($image);
            				$pic= $picrs['org'];
            			}
            		}
            		
            		$rs=$wxLbsStore->get_near_store(trim($postObj->Location_Y), trim($postObj->Location_X), $metre, $this->uid);
            		if (count($rs) == 0){
            			$this->call_msg('您附近暂无该商家！');
            		}else{
            			$shopArray = array();
            			$lx=trim($postObj->Location_Y);
            			$ly=trim($postObj->Location_X);
            			$shopArray[] = array("Title"=>"点击查看周边所有商家", "Description"=>"", "PicUrl"=>$pic, "Url"=>$sername."/lbsstorephone/indexphone?uid={$this->uid}&lx={$lx}&ly={$ly}");
            			for ($i = 0; $i < count($rs); $i++) {
            				$id=$rs[$i]['id'];
            				$distance=$rs[$i]['distance'];
            				$jump_type=$rs[$i]['jump_type'];
            				$imgcover=$rs[$i]['imgcover'];
            				$picurlrs=$PicData->get_row_byid($imgcover);
            				$picurl= $picurlrs['org'];
            				if($jump_type==1){
            					$url=$sername."/lbsstorephone/storephone?id={$id}&distance={$distance}&uid={$this->uid}";
            				}elseif($jump_type==2){
            					$url=$rs[$i]['jump_url'];
            				}elseif($jump_type==3){
            					$url=$rs[$i]['jump_cateurl'];
            				}else{
            					$url=$sername."/lbsstorephone/msginfophone?id={$id}";
            				}
            				$shopArray[] = array(
            						"Title"=>"".$rs[$i]['name']." ".$rs[$i]['summary']." ".$rs[$i]['distance']."米",
            						"Description"=>"",
            						"PicUrl"=>$picurl,
            						"Url"=>$url
            				);
            			}
            			$this->call_lbsnews($shopArray);
           			}
           			$wxLbsSet->close();
            		$PicData->close();
            		$wxLbsStore->close();
            		unset($wxLbsStore,$lbsset,$PicData);
            		/******************liuxiaofa,2014-12-22*******************/
            		break;
            	case "voice":
            		$this->init_fans();
            		if($this->uid=="102")
            		{
            			$ret=$this->get_data_byid('6353','2');//WeMedi 做活动
            			$this->to_msg($ret);
            		}
            		break;
            }  
            exit;
		}else {
			echo "no post data!";
			exit;
		}
	}
	
	/**
	 * @author liuxiaofa@dodoca.com
	 * 返回图文信息(用于lbs)
	 */
	public function call_lbsnews($arr){
		if(!is_array($arr)){
			return;
		}
		$itemTpl = "    
		<item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>";
		$item_str = "";
		foreach ($arr as $item){
			$item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
		}
		$newsTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[news]]></MsgType><Content><![CDATA[]]></Content><ArticleCount>%s</ArticleCount><Articles>{$item_str}</Articles></xml>";
		
		$result = sprintf($newsTpl, $this->openid, $this->dev_user_name, time(), count($arr));
		if($this->is_auth){
			echo $this->wxopen->get_cryptmsg($result);
		}else{
			echo $result;
		}
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 回复微信
	 * $ret 数组
	 */
	public  function to_msg($ret)
	{
		if($ret)
		{
			if(isset($ret["msg_type"]) && $ret["msg_type"]=='1')//文字回复
			{
				$this->call_msg($ret["data"]["reply_msg"]);
			}
			if(isset($ret["msg_type"]) &&  $ret["msg_type"]=='4')//纯文字
			{
				$this->call_msg($ret["data"]);
			}
			else//图文
			{
				$this->call_news($ret["data"]);
			}
		}
	}
	

	/**
	 * 返回文本信息
	 */
	public function call_msg( $keyword='', $msgType='text')
	{
		
		if($keyword)
		{
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>
						</xml>";
			$time = time();
			$msgType = "text";
			$resultStr = sprintf($textTpl, $this->openid, $this->dev_user_name, $time, $msgType, $keyword);
			if($this->is_auth){
				echo $this->wxopen->get_cryptmsg($resultStr);
			}else{
				echo $resultStr;
			}
		}
	}
	
	/**
	 * 转接客服
	 */
	public function call_service()
	{
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[transfer_customer_service]]></MsgType>
						</xml>";
			$time = time();
			$resultStr = sprintf($textTpl, $this->openid, $this->dev_user_name, $time);
			if($this->is_auth){
				echo $this->wxopen->get_cryptmsg($resultStr);
			}else{
				echo $resultStr;
			}
	}
	
	//返回图文信息
	public function call_news($news_list)
	{
		if($news_list && is_array($news_list))
		{ 
			$time = time();
			$textTpl = "<xml>
						<ToUserName><![CDATA[$this->openid]]></ToUserName>
						<FromUserName><![CDATA[$this->dev_user_name]]></FromUserName>
						<CreateTime>$time</CreateTime>
						<MsgType><![CDATA[news]]></MsgType>
						<ArticleCount>".count($news_list)."</ArticleCount>
						<Articles>";
			foreach($news_list as $k=>$v)
			{
				$img_url=isset($v["img_url"])?$v["img_url"]:'';
				$link_url=str_replace('FANS_ID',$this->fans_id,$v["hid_link_url"]);
				if(strpos($link_url,'OPEN_ID_E')!==false)
				{
					$tmp_open_id=PubFun::encrypt($this->fans_id,'E');
					$tmp_open_id=base64_encode($tmp_open_id);
					$link_url=str_replace('OPEN_ID_E',$tmp_open_id,$link_url);
					unset($tmp_open_id);
				} 
				//if(strpos($link_url,'USER_ACC_KEY')!==false)
				//{
					//$wxt=new WxCardAccTime();
					//$rand_key=$wxt->get_acc_key();
					//if($rand_key)
					//{
					//	$link_url=str_replace('USER_ACC_KEY',$rand_key,$link_url);
					//}
					//$wxt->close();
				//}
				$textTpl.="<item>";
				$textTpl.="<Title><![CDATA[".(isset($v["title"])?$v["title"]:$v["summary"])."]]></Title>";
				$textTpl.="<Description><![CDATA[".(isset($v["summary"])?$v["summary"]:'')."]]></Description>";
				$textTpl.="<PicUrl><![CDATA[".$img_url."]]></PicUrl>";
				$textTpl.="<Url><![CDATA[".$link_url."]]></Url>";
				$textTpl.="</item>";
			}
			$textTpl.="</Articles>";
			$textTpl.="</xml>";
		    if($this->is_auth){
				echo $this->wxopen->get_cryptmsg($textTpl);
			}else{
				echo $textTpl;
			}
		}
	}
	
	//扫描二维码获取授权码(指尖海报)
	function get_acc_code($tab_obj)
	{
		if(!$tab_obj || !$this->uid || !$this->fans_id)
		{
			$this->call_msg('初始化信息失败，请重新再试！');
		}
		else
		{
			$info=$tab_obj->scalar("*"," where fans_id=".$this->fans_id);
			if($info)
			{
				$tab_obj->close();
				$this->call_msg('您的登录授权码是：'.$info["fans_code"].' ,请妥善保管！');
			}
			else
			{
				$code=rand(10000000,99999999);
				$r=$tab_obj->scalar("id"," where fans_code=$code");
				if($r)
				{
					$code=rand(10000000,99999999);
				}
				$wxuser=new WxUser();
				$rand_user["username"]=$this->fans_id.'_'.rand(100000,999999);
				$rand_user["from_type"]=4;
				$uid=$wxuser->insert_data($rand_user);
				$wxuser->close();
				if(!$uid)
				{
					$this->call_msg('初始化账号失败，请重新再试！');
				}
				else
				{
					$data["fans_id"]=$this->fans_id;
					$data["userid"]=$uid;
					$data["fans_code"]=$code;
					$id=$tab_obj->insert_data($data);
					$tab_obj->close();
					if(!$id)
					{
						$this->call_msg('获取授权码失败，请重新再试！');
					}
					else
					{
						$this->call_msg('您的登录授权码是：'.$code.' ,请妥善保管好！');
					}
				}
			}
		}
		exit;
	}
	
	/**
	 * 注册扫描二维码
	 */
	public function reg()
	{
		if(!$this->fans_id)
		{
			$this->call_msg('获取验证码失败，请重新再试！');
			exit;
		}
		$mc_key=$this->uid.'_regss'.$this->fans_id;
		$mc_data=mc_get($mc_key);
		$code='';
		$reg=new WxReg();
		$code_info=$reg->scalar("*"," where fans_id=".$this->fans_id);
		if($code_info)
		{
			if($code_info["is_reg"]=='1')
			{
				$this->call_msg('您已经注册过！');
				exit;
			}
			else
			{
				if(!$mc_data)
				{
					$code=$code_info["rand_code_reg"];
					mc_set($mc_key,'1',30);
				}
				else//拒绝重复提交
				{
					die();
				}
			}
		}
		else
		{
			$mc_data=mc_get($mc_key);
			if(!$mc_data)
			{
				$id=$reg->insert_data(array("fans_id"=>$this->fans_id));
				if($id)
				{
					$code=128888+$id;//产生唯一的code
					$reg->update_data(array("rand_code_reg"=>$code)," where id=".$id);
				}
				mc_set($mc_key,'1',30);
			}
			else//拒绝重复提交
			{
				die();
			}
		}
		if($code)
		{
			$this->call_msg('您的注册验证码是：'.$code.' ,请妥善保存！');
		}
		else
		{
			$this->call_msg('获取验证码失败，请重新再试！');
		}
	}
	
	/**
	 * 未验证扫描二维码
	 */
	function band_scan()
	{
		//PubFun::save_log("fans_id->".$this->fans_id.",id->".$this->uid,'wx_scan');
		$code='';
		$reg=new WxReg();
		$code_info=$reg->scalar("*"," where fans_id=".$this->fans_id);
		if($code_info)
		{
			$code=$code_info["rand_code_reg"];
		}
		else
		{
			$id=$reg->insert_data(array("fans_id"=>$this->fans_id));
			if($id)
			{
				$code=128888+$id;//产生唯一的code
				$reg->update_data(array("rand_code_reg"=>$code)," where id=".$id);
			}
		}
		
		if($code)
		{
			$this->call_msg('您的验证码是：'.$code.' ,请妥善保存！');
		}
		else
		{
			$this->call_msg('获取验证码失败，请重新再试！');
		}
	}
	
	/**
	 * 找回秘密
	 */
	function get_pwd()
	{
		$code='';
		$reg=new WxReg();
		$code_info=$reg->scalar("*"," where fans_id=".$this->fans_id);
		if(!$code_info)
		{
			$this->call_msg('您还未注册或者未绑定我们点点客官方微信号！');
		}
		else if(!$code_info["userid"])
		{
			$this->call_msg('您账号不能使用二维码找回密码！');
		}
		else
		{
			$code=rand(100000,999999); //产生随机的code
			$reg->update_data(array("rand_code_pwd"=>$code)," where fans_id=".$this->fans_id);
		}
		//PubFun::save_log('code->'.$code.",fans_id->".$this->fans_id,'wx_log');
		if($code)
		{
			$this->call_msg('您的验证码是：'.$code.' ,请妥善保存！');
		}
		else
		{
			$this->call_msg('获取登录密码失败，请重新再试！');
		}
	}
	
	public function reply_msg($msg)
	{
		$Access_Token=$this->get_access_token();
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$Access_Token;
		$rt=$this->sub_curl($url,$msg);
		$rt=json_decode($rt,true);
		if($rt["errcode"]=='40001')
		{
			$Access_Token=$this->get_access_token(true);
			$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$Access_Token;
			$rt=$this->sub_curl($url,$msg);
			$rt=json_decode($rt,true);
		}
		return 	$rt["errcode"]===0?"1":'0';
	}
        
        
	/**
	 * 客服回复
	 */
	public function get_reply_msg($openid,$msg,$msg_type='text')
	{
		$textTpl = '{
					    "touser":"%s",
					    "msgtype":"%s",
					    "%s":
					    {
					         "content":"%s"
					    }
					}';
		$textTpl = sprintf($textTpl, $openid, $msg_type, $msg_type, $msg);
		return $textTpl;
	}

	
	/**
	 * 获取二维码
	 */
	public function get_qrcode($scene_id,$time=0)
	{
		$time=intval($time);
		$img_url='';
		$ticket=$this->get_ticket($scene_id,$time);
		if($ticket)
		{
			
			$url="https://mp.weixin.qq.com/cgi-bin/showqrcode";
			$para["ticket"]=$ticket;
			$rt=$this->sub_curl($url,$para,0);
			$file_name=$this->uid.'_'.$scene_id.".jpg";
			$file_url=$_SERVER["SINASRV_UPLOAD"].'/'.$file_name;
			$rs=file_put_contents($file_url,$rt);
			if(is_file($file_url))
			{
				$pic=new PicData();
				$pic_info=$pic->post_file($file_url);
				if(!$pic_info["id"])
				{
					$pic_info=$pic->post_file($file_url);
				}
				$img_url=isset($pic_info["org"])?$pic_info["org"]:'';
				//PubFun::save_log('url->'.$img_url.",file_url->".$file_url,'erweima');
				$pic->close();
				unset($pic);
			}
		}
		return $img_url;	
	}
	
	/**
	 * 获取用户基本信息
	 * $open_id
	 */
	public function get_user_info($open_id)
	{
		if(!$this->uid)return;
		$tmp_mc_key=$this->uid.'get_user_info_1';
		$is_pass=mc_get($tmp_mc_key);
		if($is_pass=='not pass')return;//该账号不允许抓取数据
		
		$rt='';
		$Access_Token=$this->get_access_token();
		if(!$Access_Token)
		{
			$Access_Token=$this->get_access_token(true);
		}
		if($Access_Token)
		{
			$url="https://api.weixin.qq.com/cgi-bin/user/info";
			$para["access_token"]=$Access_Token;
			$para["openid"]=$open_id;
			$para["lang"]='zh_CN';
			$rt_info=$this->sub_curl($url,$para,0);
			$rt=json_decode($rt_info,true);
			if($rt["errcode"])//接口返回错误
			{
				PubFun::save_log('$rt->'.$rt_info.',uid->'.$this->uid.',openid->'.$open_id,'get_user_info');
			}
			if($rt["errcode"]=='48001')//API未授权
			{
				$tu=new WxUserAccount();
				$tu->update(array("account_attribute"=>"3")," where userid=".$this->uid." and account_type=1");
				$tu->close();
				mc_set($tmp_mc_key,'not pass',86400);
				return;
			}
			if($rt["errcode"]=='40003')//openid非法
			{
				$w=new WxUserFans();
				$w->update(array("status"=>"0")," where userid=".$this->uid." and weixin_user='".$open_id."'");
				$w->close();
				unset($w);
				return;
			}
			if($rt["errcode"]=='40001')
			{
				$Access_Token=$this->get_access_token(true);
				$para["access_token"]=$Access_Token;
				$url="https://api.weixin.qq.com/cgi-bin/user/info";
				$rt=$this->sub_curl($url,$para,0);
				$rt=json_decode($rt,true);
			}
		}
		//更新时间
		if($rt["nickname"] ||  $rt["headimgurl"])
		{
			$u_d["nick_name"]=$rt["nickname"];
			$u_d["sex"]=$rt["sex"];
			$u_d["province"]=$rt["province"];
			$u_d["city"]=$rt["city"];
			$u_d["sub_date"]=$rt["subscribe_time"];
			if($rt["headimgurl"])
			{
				$pic=new PicData();
				$img_info=$pic->down_img($rt["headimgurl"]);
				if($img_info && $img_info["id"])
				{
					$u_d["head_img"]=$img_info["id"];
				}
				$pic->close();
				unset($pic);
			}
			$wei_w=new WxUserFans();
			$rs=$wei_w->update_data($u_d,"where userid=".$this->uid." and weixin_user='".$open_id."'");
			$wei_w->close();
			unset($wei_w);
			unset($u_d);
		}
		else
		{
			if($rt["openid"]==$open_id && $rt["subscribe"]==0)
			{
				$u_d["status"]=0;
				$wei_w=new WxUserFans();
				$wei_w->update_data($u_d,"where userid=".$this->uid." and weixin_user='".$open_id."'");
				$wei_w->close();
				unset($wei_w);
			}
		}
		return $rt["nickname"]?$rt:array();
	}
	
	/**
	 * 获取公众账号用户列表
	 */
	function get_user_list($next_id='')
	{
		$rt='';
		$url="https://api.weixin.qq.com/cgi-bin/user/get";
		$Access_Token=$this->get_access_token();
		$para=array();
		if($next_id)
		{
			$para["next_openid"]=$next_id;
		}
		if($Access_Token)
		{
			$para["access_token"]=$Access_Token;
			$rt=$this->sub_curl($url,$para,0);
			$rt=json_decode($rt,true);
			if($rt["errcode"]=='40001')
			{
				$Access_Token=$this->get_access_token(true);
				$para["access_token"]=$Access_Token;
				$url="https://api.weixin.qq.com/cgi-bin/user/info";
				$rt=$this->sub_curl($url,$para,0);
				$rt=json_decode($rt,true);
			}
		}
		return $rt;
	}
	
	/**
	 * 获取ticket
	 */
	public function get_ticket($scene_id,$time=0)
	{
		$json_data=$this->get_ticket_data($scene_id,$time);
		$Access_Token=$this->get_access_token();
		$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$Access_Token;
		$rt=$this->sub_curl($url,$json_data); 	
		$rt=json_decode($rt,true);
		if(isset($rt["errcode"]) && $rt["errcode"]=='40001')
		{
			$Access_Token=$this->get_access_token(true);
			$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$Access_Token;
			$rt=$this->sub_curl($url,$json_data);
			//PubFun::save_log('nocache->get_ticket->'.$rt.',uid->'.$this->uid,'wx_log');
			$rt=json_decode($rt,true);
		}
		return 	$rt["ticket"]?$rt["ticket"]:'';
	}
	
	/**
	 * 获取凭证
	 * $from_cache 是否从缓存获取数据
	 */
	public function get_access_token($from_cache=false)
	{
		if($this->from_type == '1'){//微信
			$url="https://api.weixin.qq.com/cgi-bin/token";
			$mc_key=$this->uid."_sxzx_mc_acc_token";
		}elseif($this->from_type == '2'){//易信
			$url="https://api.yixin.im/cgi-bin/token";
			//$mc_key=$this->uid."_mc_acc_yixin_token";
		}
		$par["grant_type"]="client_credential";
		$par["appid"]=$this->AppId;
		$par["secret"]=$this->AppSecret;
		$access_token=mc_get($mc_key);
		PubFun::save_log('sxzx get token by time :'.date('Y-m-d G:i:s').' AppSecret : '.$this->AppSecret 
		    . 'token = '.$access_token.'uid ='.$this->uid,'sxzx_get_access_token');//记录token
// 		echo 'cache token : '.$access_token.'<br>';
		if($from_cache)
		{
			$access_token=false;
		}
		if(!$access_token)
		{
			if($this->is_auth && $this->AppSecret==''){
				PubFun::save_log('auth->uid->'.$this->uid,'get_access_token');//记录token
				$authorizer_refresh_token=$this->authorizer_refresh_token;
				$authorizer_token = $this->wxopen->api_authorizer_token($this->AppId,$authorizer_refresh_token);
				
			    if($authorizer_token){
			    	$wxuseraccount = new WxUserAccount();
			    	if($authorizer_token["authorizer_access_token"])
			    	{
				    	$access_token=$authorizer_token["authorizer_access_token"];
// 				    	echo 'authorizer_access_token : '.$access_token.'<br>';
				    	mc_set($mc_key,$access_token,$authorizer_token["expires_in"]-1200);
				    	$tokendata["authorizer_access_token"] = $authorizer_token["authorizer_access_token"];
				    	$tokendata["authorizer_refresh_token"] = $authorizer_token["authorizer_refresh_token"];
				    	$wxuseraccount->update_data($tokendata," where  userid=".$this->uid." and account_type=1");
			    	}
			    	else if($authorizer_token["errcode"]=='40001')//AppSecret 错误
			    	{
			    		$wxuseraccount->update_data(array("appsecret"=>"")," where  userid=".$this->uid." and account_type=1");
			    		//添加缓存，防止抓取数据
			    		$tmp_mc_key=$this->uid.'get_user_info_1';
			    		mc_set($tmp_mc_key,'not pass',86400);
			    	}
			    	$wxuseraccount->close();
			    }
			}else{
				PubFun::save_log('sxzx'.$this->uid,'get_access_token');//记录token
			    $data=$this->sub_curl($url,$par,0);
				if($data)
				{
					$data=json_decode($data,true);
					if(is_array($data) && isset($data["access_token"]) && $data["access_token"])
					{
						$access_token=$data["access_token"];
// 						echo 'authorizer_access_token : '.$access_token.'<br>';
						mc_set($mc_key,$access_token,$data["expires_in"]-1200);
					}
					if($data["errcode"] && $data["errcode"]=='40001' )//AppSecret 错误
					{
						$wxuseraccount = new WxUserAccount();
						$wxuseraccount->update_data(array("appsecret"=>"")," where  userid=".$this->uid." and account_type=1");
						$wxuseraccount->close();
						
						$tmp_mc_key=$this->uid.'get_user_info_1';
						mc_set($tmp_mc_key,'not pass',86400);
					}
				}
			}
		}
		return $access_token;
	}
	//JSSDK 
	public function getSignPackage($url='') {
		$jsapiTicket = $this->getJsApiTicket();
		$url = $url ? $url : "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
	
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		$signature = sha1($string);
		$signPackage = array(
				"appId"     => $this->AppId,
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"rawString" => $string
		);
		return $signPackage;
	}
	//JSSDK
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	//JSSDK 获取ticket
	public function getJsApiTicket() {
		
		if(!$this->uid)return;
		$mc_ticket=$this->uid. __FUNCTION__.'1';
		$ticket=mc_get($mc_ticket);
		if(!$ticket)
		{
			$accessToken = $this->get_access_token();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";
			$par["type"]="jsapi";
			$par["appid"]=$this->AppId;
			$par["secret"]=$this->AppSecret;
			$par["access_token"]=$accessToken;
			$rs=$this->sub_curl($url,$par,0);
			$data=json_decode($rs,true);
			if ($data["ticket"]) {
				$ticket=$data["ticket"];
				mc_set($mc_ticket,$ticket,7000);
			}
			else
			{
				PubFun::save_log('$rs->'.$rs.',uid->'.$this->uid,'jssdk');
			}
		}
		return $ticket;
	}
	
	/**
	 * $scene_id 场景值
	 * $time 过期时间 单位秒 (临时二维码)
	 */
	public function get_ticket_data($scene_id,$time=0)
	{
		$str='';
		if($time)
		{
			$str='{"expire_seconds": '.$time.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
		}
		else
		{			
			$str='{"action_name": "QR_LIMIT_SCENE","action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
		}
		return $str;
	}
	
	/**
	 * 创建菜单
	 * @return bool
	 */
	public function create_menu()
	{
		$access_token=$this->get_access_token();//初试化验证票据
		$menu=$this->get_menu_info();
		$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		$info=$this->sub_curl($url,$menu);
		$info=json_decode($info,true);
		//写日志
		if($info["errcode"]=='40001')
		{
			$access_token=$this->get_access_token(true);//初试化验证票据
			$menu=$this->get_menu_info();
			$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
			$info=$this->sub_curl($url,$menu);
			$info=json_decode($info,true);
		}
		if($this->is_auth){
			$authorizer_token = $this->wxopen->api_authorizer_token($this->AppId,$this->authorizer_refresh_token);
			$info["errmsg"] = $authorizer_token;
		}
		return 	$info;
	}
	
	/**
	 * 易信菜单
	 * @return bool
	 */
	public function create_yixin_menu()
	{
		$access_token=$this->get_access_token();//初试化验证票据
		$menu=$this->get_menu_info();
		$url="https://api.yixin.im/cgi-bin/menu/create?access_token=".$access_token;
		$info=$this->sub_curl($url,$menu);
		$info=json_decode($info,true);
		//写日志
		//PubFun::save_log('errcode->'.$info["errcode"].",acc_token->'.$access_token.',msg->".$info["errmsg"].',uid->'.$this->uid.",menu_data->".$menu,'wx_menu');
		if($info["errcode"]=='40001')
		{
			$access_token=$this->get_access_token(true);//初试化验证票据
			$menu=$this->get_menu_info();
			$url="https://api.yixin.im/cgi-bin/menu/create?access_token=".$access_token;
			$info=$this->sub_curl($url,$menu);
			$info=json_decode($info,true);
			//PubFun::save_log('errcode->'.$info["errcode"].",acc_token->'.$access_token.',msg->".$info["errmsg"].',uid->'.$this->uid.",menu_data->".$menu,'wx_menu');
		}
		return 	$info["errcode"]=='0'?true:false;
	}
	
	/**
	 * 获取菜单数据
	 */
	public function get_menu_info()
	{
		$str='';
		$str.='{';
		$str.='"button":[';		
		$menu=new WxFootMenu();
		$menu_data=$menu->fetchAll("select * from ".$menu->_name." where userid=".$this->uid." and status=1 and parent_id=0 order by sorts desc");//父菜单
		
		if($menu_data)
		{
				foreach($menu_data as $k=>$v)
				{
					$child_list=$menu->fetchAll("select * from ".$menu->_name." where userid=".$this->uid." and menutype>0 and status=1 and parent_id=".$v["id"]." order by sorts desc");//子菜单
					if($child_list)
					{
						$tmp='{';
						$tmp.='"name":"'.$v["menuname"].'",';
						$tmp.='"sub_button":[';
						foreach($child_list as $key=>$val)
						{
								if($val["menutype"]=='1' || $val["menutype"]=='2' || $val["menutype"]=='5')//文字、图片
								{
									$tmp.='{"type":"click","name":"'.$val["menuname"].'","key":"'.$val["hid_link_url"].'"}';
								}
								else if($val["menutype"]=='7')
								{
									$tmp.='{"type":"scancode_push","name":"'.$val["menuname"].'","key":"scancode_push"}';
								}
								else if($val["menutype"]=='8')
								{
									$tmp.='{"type":"pic_photo_or_album","name":"'.$val["menuname"].'","key":"pic_photo_or_album"}';
								}
								else
								{
									$tmp.='{"type":"view","name":"'.$val["menuname"].'","url":"'.$val["hid_link_url"].'"}';
								}
								if((count($child_list)-1)>$key)
								{
									$tmp.=',';
								}
						}	
						$tmp.=']';
						$tmp.='},';
						
						$str.=$tmp;
						$tmp='';
					}
					else
					{
						$val=$v;
						if($val["menutype"]=='1' || $val["menutype"]=='2' || $val["menutype"]=='5')//文字、图片
						{
							$str.='{"type":"click","name":"'.$val["menuname"].'","key":"'.$val["hid_link_url"].'"},';
						}
						else if($val["hid_link_url"]!='')
						{
							$str.='{"type":"view","name":"'.$val["menuname"].'","url":"'.$val["hid_link_url"].'"},';
						}
					}
				}
		}
		if($this->from_type == '2'){//易信去掉最后一个逗号
			$str = substr($str,0,strlen($str)-1);
		}
		$str.=']';
		$str.='}';
		return $str;
	}
	
	public function sub_curl($url,$data,$is_post=1)
	{
		$ch = curl_init();
		if(!$is_post)//get 请求
		{
			$url =  $url.'?'.http_build_query($data);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $is_post);
		if($is_post)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$info = curl_exec($ch);
		$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		//PubFun::save_log('$url->'.$url.',uid->'.$this->uid.',code->'.$code,'wx_log');
		curl_close($ch);
		return $info;
	}
	
	/**
	 * 验证
	 */
	private function checkSignature()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = $this->Token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取默认回复、欢迎词
	 * $uid 用户id
	 * $type  1->默认回复、2->欢迎词
	 */
	public  function get_default_msg($uid,$type)
	{
		if(!$uid || !$type)return ;
		$default=new WxReplyDefault();
		$data=$default->scalar("*"," where userid=$uid and reply_type=$type");
		$ret=array();
		if($data)
		{
			$this->save_view_log($data["from_type"],$data["pk_id"]);
			$ret=$this->get_data_byid($data["pk_id"],$data["from_type"]);
		}
		return $ret;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 通过关键词获取回复内容
	 * $key 关键词
	 * @return array  
	 */
	public function get_key_msg($key)
	{
		$ret["msg_type"]='4';//纯文字  
		$key=trim($key);
		$onoff=new WxOnOff();
		$onoff_info=$onoff->get_row_byuid($this->uid);//趣味互动开关
		$tmp=mb_substr($key,0,2,'utf-8') ;
		$tmp2=mb_substr($key,0,1,'utf-8') ;
		$last_char=mb_substr($key,mb_strlen($key,'utf-8')-1,1,'utf-8') ;
		if($tmp=='笑话' && $onoff_info["joke_flag"]){
			$ret["data"]=HappyHud::GetJoke();
		}elseif($tmp=="点歌" && $onoff_info["music_flag"]){
			$music_name='';
			$music_gs='';
			$str=str_replace("点歌",'',$key);	
			if(strpos($str,'@')!==false){
				$arr=explode('@',$str);
				$music_name=trim($arr[0]);
				$music_gs=trim($arr[1]);
			}else{
				$music_name=trim($str);
			}
			$ret["data"]=HappyHud::GetMusic($music_name,$music_gs);
		}elseif($tmp=="天气"  && $onoff_info["weather_flag"]){
			$key=trim(str_replace('天气','',$key));
			$ret["data"]=HappyHud::GetWeather($key);
		}elseif($tmp=="翻译"  && $onoff_info["translate_flag"]){
			$key=trim(str_replace('翻译','',$key));
			if(strpos($key,"@")!==false){//日文翻译
				$key=trim(str_replace('@','',$key));
				$ret["data"]=HappyHud::GetTranslate('jp',$key);
			}else{//英文
				$ret["data"]=HappyHud::GetTranslate('en',$key);
			}
		}elseif($tmp=="快递"  && $onoff_info["kuaidi_flag"]){
			$key=trim(str_replace('快递','',$key));
			$arr=explode('@',$key);
			if(!$arr[0] || !$arr[1]){
				$ret["data"]="数据格式不对！,正确格式：快递申通@868273943254";
			}else{
				$ret["data"]=HappyHud::GetKuaidi($arr[0],$arr[1]);
			}
		}elseif($tmp=="车次" && $onoff_info["train_flag"]){
			$key=trim(str_replace('车次','',$key));
			$ret["data"]=HappyHud::GetTrainInfo($key);
		}elseif($tmp=="列车" && $onoff_info["train_flag"]){
			$key=trim(str_replace('列车','',$key));
			$type=substr($key,0,1);
			$key=substr($key,1);
			$arr=explode('@',$key);
			if(!$arr[0] || !$arr[1]){
				$ret["data"]="对不起，您提交的信息格式不对，例如：列车G上海@苏州, 列车类型字母代码:\n G-高速动车\n K-快速 \n T-空调特快 \n D-动车组\n Z-直达特快 \n Q-其他";
			}else{
				$ret["data"]=HappyHud::GetTrainList($arr[0],$arr[1],$type);
			}
		}
		else if($tmp2=='#' && $last_char=='#' )
		{
			$str='';
			$w=new WeixinwebsiteKey();
			$rs=$w->fetchAll("select b.id,title from weixin_wewebsite_key as a inner join weixin_wewebsitey as b on a.wid=b.id and  news_key='$key'");
			if(!$rs)
			{
				$str='D码输入错误，请重新再输！';
			}
			else
			{
				$str="点击进入 <a href='".WHB_DOMAIN."/phone/websitey?id=".$rs[0]["id"]."#mp.weixin.qq.com'>'".$rs[0]["title"]."'</a> 指尖海报，即刻分享至朋友圈和微信群。\n\r点点客,只做移动社交营销\n股票代码：430177";
			}
			$this->call_msg($str);
			exit;
		}
		elseif($tmp2=="#" && $onoff_info["printonff"]){//打印文字
			$key=trim(str_replace('#','',$key));
			$WxPrintLog = new WxPrintLog();
			$WxPrintInfo = new WxPrintInfo();
			$picloginfo = $WxPrintLog->scalar("*","where consume_code is null and fansid=".$this->fans_id." order by create_time desc ");
			$macinfo = $WxPrintInfo->scalar("*","where status>-1 and userid=".$this->uid);
			$onoff=new WxOnOff();
			$printyldonoff = $onoff->get_data_one("printyldonoff","where userid=".$this->uid);
			if($printyldonoff){
				$macinfo['mac_type'] = 2;
			}
			if(!$picloginfo){
				$ret["data"]="请先上传图片";
			}else{
				$WxPrintLog->update(array("showtext"=>$key),"where id=".$picloginfo['id']);
				if($macinfo['mac_type']==1){
					$WxPrint = new WxPrint();
					$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
					$infostr = $WxPrint->dataSend($postStr);
					$ret["data"] = $infostr;
				}elseif($macinfo['mac_type']==2){
					$WxPrint = new WxPrint();
					$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
					$infostr = $WxPrint->dataSendYin($this->uid,$postStr);
					$ret["data"] = $infostr;
				}else{
					$ret["data"]="文字添加成功，请输入消费码，输入格式：@1111";
				}
			}
		}elseif($tmp2=="@" && $onoff_info["printonff"]){//消费码验证
			$key=trim(str_replace('@','',$key));
			$WxPrintLog = new WxPrintLog();
			$WxPrintInfo = new WxPrintInfo();
			$picloginfo = $WxPrintLog->scalar("*","where consume_code is null and fansid=".$this->fans_id." order by create_time desc ");
			$macinfo = $WxPrintInfo->scalar("*","where status>-1 and userid=".$this->uid);
			$onoff=new WxOnOff();
			$printyldonoff = $onoff->get_data_one("printyldonoff","where userid=".$this->uid);
			if($printyldonoff){
				$macinfo['mac_type'] = 2;
			}
			if(!$picloginfo){
				$ret["data"]="请先上传图片";
			}else{
				$WxPrintCode = new WxPrintCode();
				if($macinfo['mac_type']==1){
					$WxPrint = new WxPrint();
					$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
					$infostr = $WxPrint->dataSend($postStr);
					$ret["data"]=$infostr;
					if(strstr($infostr,"正在为您打印照片")){
						$checkcodeid = $WxPrintCode->scalar("id","where code='$key' and status=0 and type=0 and userid=".$this->uid." order by id desc");
						if($checkcodeid){
							$WxPrintCode->update(array("status"=>1,"consume_time"=>time()),"where id=$checkcodeid");
						}else{
							$WxPrintCode->insert_data(array("code"=>$key,"status"=>1,"consume_time"=>time(),"type"=>1,"userid"=>$this->uid));
						}
						$WxPrintLog->update(array("consume_code"=>$key),"where id=".$picloginfo['id']);
					}
				}elseif($macinfo['mac_type']==2){
					$WxPrint = new WxPrint();
					$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
					if(!$picloginfo['showtext']){//自动完成第二步
						$strxl = explode("@".$key, $postStr);
						$xmlStr = $strxl[0].$strxl[1];
						$WxPrint->dataSendYin($this->uid,$xmlStr);
					}
					$infostr = $WxPrint->dataSendYin($this->uid,$postStr);
					$ret["data"]=$infostr;
					/*if(strstr($infostr,"就可以拿到照片了")){
						$checkcodeid = $WxPrintCode->scalar("id","where code='$key' and status=0 and type=0 and userid=".$this->uid." order by id desc");
						if($checkcodeid){
							$WxPrintCode->update(array("status"=>1,"consume_time"=>time()),"where id=$checkcodeid");
						}else{
							$WxPrintCode->insert_data(array("code"=>$key,"status"=>1,"consume_time"=>time(),"type"=>1,"userid"=>$this->uid));
						}
						$WxPrintLog->update(array("consume_code"=>$key),"where id=".$picloginfo['id']);
					}*/
				}else{
					$codeinfo = $WxPrintCode->scalar("*","where code='$key'");
					$is_dy = 1;
					if($codeinfo){
						$jqinfo = $WxPrintInfo->scalar("*","where id=".$codeinfo['terid']);
						if($jqinfo['pernum']>0){
							$logcount = $WxPrintLog->getCount("where consume_code!='' and DATE(FROM_UNIXTIME(create_time))=DATE(NOW()) and fansid='".$this->fans_id."' and userid=".$this->uid." and terid='".$codeinfo['terid']."'");
							if($logcount>=$jqinfo['pernum']){
								$ret["data"]='每天只能打印'.$jqinfo['pernum'].'次，请明天再来！';
								$is_dy = 0;
							}
						}
						if($is_dy){
							if($codeinfo['status']==1){
								$ret["data"]="消费码已被使用";
							}else{
								$showtext = $picloginfo['showtext'];
								$WxPrintInfo = new WxPrintInfo();
								$pic=new PicData();
								$qrCodeid = $WxPrintInfo->scalar("logo","where id=".$codeinfo['terid']);
								if($qrCodeid){
									$qrinfo = $pic->get_row_byid($qrCodeid);
									$qrCode = $qrinfo['org'];
								}
								if($picloginfo['jqimg']){
									$imgurl = $picloginfo['jqimg'];
								}else{
									//自动摆正
									$dyurl = $picloginfo['img_url'];
									$picinfo = $pic->get_row_byid($dyurl);
									$src = $picinfo['org'];
									$i = new ImgExif();
									$info = $i->getImgInfo($src,"All","1");
									$Orientation = $info['Orientation'];
									$back = imagecreatefromjpeg($src);
									$white = imagecolorallocate($back,255,255,255);
									$newimg = imagerotate($back,$Orientation,$white);
									//$dyurl=$_SERVER["SINASRV_UPLOAD"].'/mergepic/'.time().".jpg";
									$dyurl = $_SERVER['SINASRV_DATA_TMP'] . "printpic_dy" .time() . ".jpg";
									imagejpeg($newimg,$dyurl);
									$dyurlinfo = $pic->post_file($dyurl);
									//自动剪切
									$src = $dyurlinfo['org'];
									$whinfo = getimagesize($src);
									$yswidth = $whinfo[0];
									$ysheight = $whinfo[1];
									if($yswidth>$ysheight){
										$cjheight = $ysheight;
										$cjwidth = $ysheight;
										$x = ($yswidth-$cjheight)/2;
										$y = 0;
									}else{
										$cjheight = $yswidth;
										$cjwidth = $yswidth;
										$x = 0;
										$y = ($ysheight-$cjwidth)/2;
									}
									$hzmstr = end(explode(".",$src));
									strtolower($hzmstr);
									if($hzmstr=='jpg'||$hzmstr=='jpeg'){
										$img_r = imagecreatefromjpeg($src);
									}elseif($hzmstr=='gif'){
										$img_r = imagecreatefromgif($src);
									}elseif($hzmstr=='png'){
										$img_r = imagecreatefrompng($src);
									}else{
										show_msg("图片限制jpg，gif，png格式");
										exit;
									}
									$targ_w = 800;
									$targ_h = 800;
									$dst_r = ImageCreateTrueColor($targ_w, $targ_h);
									imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$cjwidth,$cjheight);
									//$imgurl = $_SERVER["SINASRV_UPLOAD"].'/mergepic/'.time().".jpg";
									$imgurl = $_SERVER['SINASRV_DATA_TMP'] . "printpic_dyend" .time() . ".jpg";
									imagejpeg($dst_r , $imgurl);
									imagedestroy($dst_r);
									$dyendurlinfo = $pic->post_file($imgurl);
									$imgurl = $dyendurlinfo['org'];
								}
								$zjinfo = $pic->merge_pic($imgurl,$qrCode,$showtext);
								$WxPrintLog->update(array("pjimg"=>$zjinfo['img_url'],"consume_code"=>$key,"terid"=>$codeinfo['terid']),"where id=".$picloginfo['id']);
								$WxPrint = new WxPrint();
								$WxPrint->printPic($zjinfo['img_url'],$key);
								$ret["data"]="正在为您打印照片，请在机器处等取照片。";
							}
						}
					}else{
						$ret["data"]="消费码错误";
					}
				}
			}
		}
		else//取用户关键词
		{
				$wk=new WxKeywordColl();
				$key_info=$wk->fetchAll("select * from ".$wk->_name."  where userid=".$this->uid." and  binary keywords like '%$key%' and status=1 order by udate desc");
				if($key_info)//匹配到文本、单图文、多图文
				{
					$tmp_pk_id=0;
					$tmp_from_type=0;
					$is_exist=0;
					foreach($key_info as $tmp_key=>$tmp_val)
					{
						if(!$is_exist)
						{
							$tmp_arr=explode(',',$tmp_val["keywords"]);
							if(is_array($tmp_arr))
							{
								foreach($tmp_arr as $kk=>$vv)
								{
									if(trim($vv)==$key)
									{
										$tmp_pk_id=$tmp_val["pk_id"];
										$tmp_from_type=$tmp_val["from_type"];
										$is_exist++;
										break;
									}
								}
							}
						}
					}
					$this->save_view_log($tmp_from_type,$tmp_pk_id);
					if($tmp_pk_id>0)
					{
						$ret=$this->get_data_byid($tmp_pk_id,$tmp_from_type);
					}
					else
					{
						$ret=$this->get_default_msg($this->uid,1);
						$ret["not_match"]='1';
					}
				}
				else//未匹配到
				{
					$WxKefuReply  =new WxKefuReply();
					$is_open = $WxKefuReply->scalar("is_open", "where userid={$this->uid}");
					if($is_open==1){
						$ret["not_match"]='2';
					}else{
						$ret=$this->get_default_msg($this->uid,1);
						$ret["not_match"]='1';
					}
				}
		}

		return $ret;
	}
	
	/*
	 * @author sunshichun@dodoca.com
	 * $id 主键
	 * $from_type 来源 1->文字  2->单图文  3->多图文
	 * 
	 */
	function get_data_byid($id,$from_type)
	{
		if(!$id || !$from_type)return ;
		$obj=null;
		$ret["msg_type"]=$from_type;
		switch ($from_type)
		{
			case "1"://文字
				$obj=new WxReplyMsg();
				$ret["data"]=$obj->get_row_byid($id);
				break;
			case "2"://单图文
				$obj=new WxReplyPic();
				$ret["data"][0]=$obj->get_row_byid($id);
				break;
			case "3"://多图文
				$obj=new WxReplyPicMores();
				$ret["data"]=$obj->get_row_byid($id);
				break;
			default:
				$ret["msg_type"]=0;
				break;
		}
		if($obj)
		{
			$obj->close();
			unset($obj);
		}
		return $ret;
	}
	
	/**
	 * 记录日志
	 */
	function save_view_log($log_type,$pk_id)
	{
		if(!$log_type || !$pk_id)return;
		$data["log_type"]=$log_type;
		$data["pk_id"]=$pk_id;
		$data["fans_id"]=$this->fans_id;
		$data["userid"]=$this->uid;
		$log=new WxViewLog();
		$log->insert_data($data);
		$log->close();
		unset($log);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 处理分词
	 * $key 被分词的文字
	 */
	function get_keywords($key)
	{
		return false;
	}

	/**
	 * 上传多媒体
	 * $filename 文件在磁盘上的绝对路径
	 */
	function uploadpic($filename)
	{
		$access_token=$this->get_access_token();//初试化验证票据
		//s($access_token);
		$url="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=image";
		$fields['media'] = '@'.$filename;
		$fields['phpcallback'] = true;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_POST, true );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
		$content =  curl_exec( $ch );
		curl_close($ch);
		$rs=json_decode($content,true);
		if(isset($rs["media_id"]) && $rs["media_id"])
		{
			return $rs["media_id"];
		}
		else
		{
			PubFun::save_log('uploadpic->'.$content,'wx_qunfa');
			return false;
		}
	}
	
	/**
	 * 上传图文素材
	 * $array 多维数组
	 */
	function uploadnew($array)
	{
		$data=$this->get_uploadnew($array);
		$access_token=$this->get_access_token();//初试化验证票据
		$url='https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$access_token;
		$content=$this->sub_curl($url,$data,1);
		$info=json_decode($content,true);
		if(isset($info["media_id"]) && $info["media_id"])
		{
			return $info["media_id"];
		}
		else
		{
			PubFun::save_log('uploadnew->'.$content,'wx_qunfa');
			return false;
		}
	}
	
	//组织图文素材
	function get_uploadnew($array)
	{
		if(!is_array($array) || count($array)<=0)return;
		$str='{';
		$str.='"articles": [';
		foreach($array as $k=>$v)
		{
			$str.='{';
			$str.='"thumb_media_id":"'.$v["media_id"].'",';
	        $str.=' "author":"'.$v["author"].'",';
			$str.='"title":"'.$v["title"].'",';
			$str.='"content_source_url":"'.$v["link_url"].'",';
			$str.='"content":"'.$v["content"].'",';
			$str.='"digest":"'.$v["digest"].'"';
			$str.=' },';
		}
		$str=trim($str,',');
		$str.=']';
		$str.='}';
		return $str;
	}
	
	//群发
	function send_news($media_id)
	{
		$data=$this->get_new_data($media_id);
		$access_token=$this->get_access_token();//初试化验证票据
		$url='https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
		$content=$this->sub_curl($url,$data,1);
		$info=json_decode($content,true);
		if(isset($info["errcode"]) && $info["errcode"]=='0')
		{
			PubFun::save_log('send_news->'.$content.',msgid->'.$info["msg_id"],'wx_qunfa');
			return $info["msg_id"];
		}
		else
		{
			PubFun::save_log('send_news_fail->'.$content.',get_uid->'.get_uid(),'wx_qunfa');
			return false;
		}
	}
	
	/**
	 * 获取群发图片数据
	 */
	function get_new_data($media_id)
	{
		$str='{';
		$str.='"filter":{';
		$str.='"group_id":"0"';
		$str.='},';
		$str.='"mpnews":{
			"media_id":"'.$media_id.'"
			},';
		$str.='"msgtype":"mpnews"';
		return $str;
	}
	
	/**
	 * 发送图文
	 * $id 单图文、多图文表主键
	 * $from_type 类型   2->单图文  3->多图文
	 */
	function send_pic_msg($id,$from_type)
	{
		$result["code"]=0;
		$result["msg"]='';
		if(!$id || ($from_type!='2' && $from_type!='3')){
			$result["msg"]='参数不对!';
			return $result;
		}
		$data=array();
		$ret=$this->get_data_byid($id,$from_type);
		$fail_count=0;
		if(is_array($ret))
		{
			foreach($ret["data"] as $k=>$v)
			{
				$img_url=trim($v["img_url"]);
				if($img_url)
				{
					$pic=new PicData();
					$img_url=$pic->local_img($img_url);
					$pic->close();
					//$img_url=str_replace(IMG_DOMAIN,$_SERVER["SINASRV_UPLOAD"],$img_url);
					//$img_url=str_replace(IMG_DOMAIN_NEW,$_SERVER["SINASRV_UPLOAD"],$img_url);
					
					$pic_type=strrchr($img_url,'.');
					if($pic_type!='.jpg')
					{
						$new_img=str_replace($pic_type,'.jpg',$img_url);//强制改扩展名称
						copy($img_url,$new_img);
						$img_url=$new_img;
					}
					$media_id=$this->uploadpic($img_url);
					if($media_id)
					{
						$t["media_id"]=$media_id;
						//$t["author"]='作者';
						$t["title"]=$v["title"];
						$t["link_url"]=str_replace('FANS_ID',$this->fans_id,$v["hid_link_url"]);
						//$t["content"]=$v["content"];
						$t["digest"]=$v["summary"];
						$data[]=$t;
					}
					else
					{
						$fail_count++;
					}
				}
				unset($t);
			}
		}
		if($fail_count)
		{
			$result["msg"]="有 $fail_count 张图片上传到微信失败，请重新提交！";
		}
		else
		{
			if($data)
			{
				$new_media_id=$this->uploadnew($data);
				if($new_media_id)
				{
					$msg_id=$this->send_news($new_media_id);
					if($msg_id)
					{
						$qf=new WxQunFa();
						$u_data["pk_id"]=$id;
						$u_data["from_type"]=$from_type;
						$u_data["userid"]=$this->uid;
						$u_data["msgid"]=trim($msg_id);
						$u_data["statdate"]=date("Ym");
						$qf->insert_data($u_data);
						$qf->close();
						unset($qf);
						
						PubFun::save_log('send_pk_id->'.$id.',from_type->'.$from_type.',get_uid->'.get_uid(),'wx_qunfa');
						
						$result["code"]=1;
						$result["msg"]='群发消息成功!';
						//社交平台钩子同步设置
					}
					else
					{
						$result["msg"]='群发消息失败!';
					}
				}
				else
				{
					$result["msg"]='上传图文消息素材失败!';
				}
			}
			else
			{
				$result["msg"]='缺少图文数据';
			}
		}
		return $result;
	}
	
        //get图文模板
        function get_reply_pic_msg($id,$from_type,$openid,$msg_type='news')
	{
		$result["code"]=0;
		$result["msg"]='';
		if(!$id || ($from_type!='2' && $from_type!='3')){
			$result["msg"]='参数不对!';
			return $result;
		}
		$data=array();
		$ret=$this->get_data_byid($id,$from_type);
                if(is_array($ret)){
                    $textTpl = '{
                                "touser":"%s",
                                "msgtype":"%s",
                                "%s":{
                                    "articles": %s
                                }
                            }';
                    foreach($ret['data'] as $key=>$value){
                        $link_url=str_replace('FANS_ID',$this->fans_id,$value["hid_link_url"]);
                        if(strpos($link_url,'OPEN_ID_E')!==false)
                        {
                                $tmp_open_id=PubFun::encrypt($this->fans_id,'E');
                                $tmp_open_id=base64_encode($tmp_open_id);
                                $link_url=str_replace('OPEN_ID_E',$tmp_open_id,$link_url);
                                unset($tmp_open_id);
                        } 
                        if(strpos($link_url,'USER_ACC_KEY')!==false)
                        {
                                $wxt=new WxCardAccTime();
                                $rand_key=$wxt->get_acc_key();
                                if($rand_key)
                                {
                                        $link_url=str_replace('USER_ACC_KEY',$rand_key,$link_url);
                                }
                                $wxt->close();
                        }
                        $data[$key]['title'] = urlencode($value['title']);
                        $data[$key]['description'] = urlencode($value['summary']);
                        $data[$key]['url'] = urlencode($link_url);
                        $data[$key]['picurl'] = urlencode($value['img_url']);
                    }
                    $msg = urldecode(json_encode($data)); 
                    
                    //$msg = json_encode($data);
                    $textTpl = sprintf($textTpl, $openid, $msg_type, $msg_type, $msg);
                    return $textTpl;
                }   
	}
       
       //客服聊天日志
         public function write_log($data){
            //操作表
            $WxKefuChat = new WxKefuChat();
            $WxKefuCustom = new WxKefuCustom();
            $WxKefuCustomLog = new WxKefuCustomLog();
            if(!empty($data)){
                //十分钟之内的客服评价
                $time = time() -600;
                $judge_sql = "select custom_id from wx_kefu_custom_log where endtime>{$time} and fans_id={$data['fans_id']} and degree=0 order by endtime desc";
                $res = $WxKefuCustomLog->fetchAll($judge_sql);
                if(!empty($res)){
                    $judge_arr = array('1','2','3','4');
                    if(in_array($data['content'],$judge_arr)){
                        $jdata['degree'] = $data['content'];
                        $where = "where custom_id={$res[0]['custom_id']}";
                        $WxKefuCustomLog->fetchAll("update wx_kefu_custom_log set degree={$data['content']} where custom_id={$res[0]['custom_id']}");
                        return false;
                    }
                }
                //根据结束时间判断是否新建会话
                $xtime = time()-172800;
                $check_sql = "select kcl.custom_id,kc.kefu_id from wx_kefu_custom_log kcl left join wx_kefu_custom kc on kcl.custom_id=kc.id where kcl.fans_id={$data['fans_id']} and kcl.endtime=0 and kcl.starttime>{$xtime}";
                $res = $WxKefuCustomLog->fetchAll($check_sql);
                if(!empty($res)){
                    $chat_data['custom_id'] = $res[0]['custom_id'];
                    $chat_data['kefu_id'] = $res[0]['kefu_id'];
                    $chat_data['userid'] = $this->uid;
                    $chat_data['fans_id'] = $data['fans_id'];
                    $chat_data['type'] = 1;
                    $chat_data['content'] = $data['content'];
                    $WxKefuChat->insert_data($chat_data);
                }else{
                   
                	$WxUser = new WxUser();
                	$WxKefuUser = new WxKefuUser();
                	$uid = $this->uid;
                	$userdata = $WxKefuUser->fetchAll("select * from wx_kefu_user where userid={$uid} and status=1");
                	foreach ($userdata as $key=>$value){
                		$kefudata .= $value['id'].",";
                	}
                	$kefudata = substr($kefudata,0,strlen($kefudata)-1);
                	$now = time();
                	$today = mktime(0,0,0,date('m',time()),date('d',time()),date('y',time()));
                	$WxKefuOnlineLog = new WxKefuOnlineLog();
                	$logdata = $WxKefuOnlineLog->fetchAll("select * from wx_kefu_online_log where kefu_id in ({$kefudata}) and starttime>{$today}");
                	foreach ($logdata as $key=>$value){
                		if($value['endtime']==0){
                			$is_online = 1;
                		}
                	}
                	if($is_online==1){
                		$is_online=1;
                	}else{
                		$is_online=0;
                	}
                	$result = $WxUser->get_row_byid($uid);
                	$this->from_type=1;
                	$this->Token=$result['userkey'];
                	$WxUserFans = new WxUserFans();
                	$openid = $WxUserFans->scalar("weixin_user", "where id={$data['fans_id']}");
                	$WxKefuReply = new WxKefuReply();
                	$autoreply = $WxKefuReply->scalar("*", "where userid={$uid}");
                	$ret=$this->get_key_msg($data["content"]);
                	if($ret["not_match"]==2){
	                	if($is_online==1){
	                		$msg_tpl = $this->get_reply_msg($openid,$autoreply['wait_reply'],$msg_type='text');
	                		$suc = $this->reply_msg($msg_tpl);
	                	}else{
	                		$msg_tpl = $this->get_reply_msg($openid,$autoreply['outline_reply'],$msg_type='text');
	                		$suc = $this->reply_msg($msg_tpl);
	                	}
                	}
                	if($is_online==1){
	                    $custom_data['userid'] = $this->uid;
	                    $custom_data['fans_id'] = $data['fans_id'];
	                    $custom_id = $WxKefuCustom->insert_data($custom_data);
	                    
	                    $log_data['userid'] = $custom_data['userid'];
	                    $log_data['fans_id'] = $data['fans_id'];
	                    $log_data['custom_id'] = $custom_id;
	                    $log_data['starttime'] = time();
	                    $WxKefuCustomLog->insert_data($log_data);
	
	                    $chat_data['userid'] = $custom_data['userid'];
	                    $chat_data['fans_id'] = $data['fans_id'];
	                    $chat_data['custom_id'] = $custom_id;
	                    $chat_data['type'] = 1;
	                    $chat_data['content_type'] = 1;
	                    $chat_data['content'] = $data['content'];
	                    $WxKefuChat->insert_data($chat_data);
                	}
                    
                }
                return false;
            }else{
                return false;
            }
        }
	
 
	//创建分组
	public function create_groups($name){
		$access_token = $this->get_access_token();//初试化验证票据
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=".$access_token;
		$data = '{"group":{"name":"'.$name.'"}}';
		$content = $this->sub_curl($url,$data,1);
		$rt = json_decode($content,true);
		if($rt['group']['id']){
			return $rt['group'];
		}else{
			return 0;
		}
	}
	
	
	//查询所有分组
	public function select_groups(){
		$access_token = $this->get_access_token();//初试化验证票据
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=".$access_token;
		$content = $this->sub_curl($url,$data,1);
		$rt = json_decode($content,true);
		if(isset($rt['errcode']) && $rt['errcode']=='40013'){
			return 0;
		}else{
			return $rt['groups'];
		}
	}
	
	//查询所在分组
	public function select_dqgroup($openid){
		$access_token = $this->get_access_token();//初试化验证票据
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=".$access_token;
		$data = '{"openid":"'.$openid.'"}';
		$content = $this->sub_curl($url,$data,1);
		$rt = json_decode($content,true);
		if(isset($rt['errcode']) && $rt['errcode']=='40003'){
			return 0;
		}else{
			return $rt['groupid'];
		}
	}
	
	//修改分组名
	public function update_group($group_id,$newname){
		$access_token = $this->get_access_token();//初试化验证票据
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token=".$access_token;
		$data = '{"group":{"id":'.$group_id.',"name":"'.$newname.'"}}';
		$content = $this->sub_curl($url,$data,1);
		$rt = json_decode($content,true);
		if($rt['errcode']=='40013'){
			return 0;
		}else{
			return 'ok';
		}
	}
	
	//移动用户分组
	public function yd_group($group_id,$openid){
		$access_token = $this->get_access_token();//初试化验证票据
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=".$access_token;
		$data = '{"openid":"'.$openid.'","to_groupid":'.$group_id.'}';
		$content = $this->sub_curl($url,$data,1);
		$rt = json_decode($content,true);
		if(isset($rt['errcode']) && $rt['errcode']=='40013'){
			return 0;
		}else{
			return 'ok';
		}
	}
	//用户分析数据接口
	public function fans_analysis($begin_date,$end_date){
		$access_token = $this->get_access_token();//初试化验证票据
		$url = "https://api.weixin.qq.com/datacube/getusersummary?access_token=".$access_token;
		$data = array("begin_date"=>$begin_date,"end_date"=>$end_date);
		$content = $this->sub_curl($url,json_encode($data),1);
		$rt = json_decode($content,true);
		return $rt;
	}
	
	/*
	获取精确群发数据
	 
	function get_jqnew_data($openid,$data,$msgtype)
	{
		if($msgtype == 'mpnews'){
			$str='{';
			$str.='"touser":['.$openid.'],';
			$str.='"mpnews":{"media_id":"'.$data.'"},';
			$str.='"msgtype":"mpnews"}';
		}else{
			$str='{';
			$str.='"touser":['.$openid.'],';
			$str.='"msgtype":"text",';
			$str.='"text":{"content":"'.$data.'"}}';
		}
		return $str;
	}
	
	*/
	/**
	 * 微信发送模板信息
	 * @author zcc zhangchangchun@dodoca.com
	 * @createtime 2015.4.17
	 * @parram data = array(
	 'template_id'	=>	'8tGVAiwqc5f1hjrvC_Xrvz_scfMeZGYjDltsp_xc0iE',	//微信公众平台的模板ID
	 'openid'		=>	'',		//用户openid
	 'url'			=>	'',		//跳转url 不设置（ios跳转到空页面，安卓不跳转）
	 'first'			=>	'',		//头部
	 'remark'		=>	'',		//尾部
	 'body'			=>	array(	//主体逗号分隔，键名-键值
	 'key1'	=>	'val1',
	 'key1'	=>	'val2',
	 'key1'	=>	'val3',
	 ),
	 );
	 */
	public function sendMessTemplate($data) {
	    
// 	    $DzDebugLog = new DzDebugLog();
// 	    $DzDebugLog->insert(array('descript'=>'发送开始','jsondata'=>json_encode($data)));
	    if(!is_array($data)) { return false; }
	    if(!$data['template_id']) {
	        return array('state'=>2,'cont'=>'模板ID不能为空！'); exit;
	    }
	    if(!$data['openid']) {
	        return array('state'=>2,'cont'=>'用户openid不能为空！'); exit;
	    }
	    $tem_url = isset($data['url']) ? $data['url'] : '';
	    $first = isset($data['first']) ? $data['first'] : '';
	    $remark = isset($data['remark']) ? $data['remark'] : '';
	    $body = '';
	    if($data['body']) {
	        foreach($data['body'] as $key => $v) {
	            $body .= '"'.$key.'": {
                       "value":"'.$v.'",
                       "color":"#173177"
                   },';
	        }
	    }
	    $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->get_access_token();
	    $str = ' {
           "touser":"'.$data['openid'].'",
           "template_id":"'.$data['template_id'].'",
           "url":"'.$tem_url.'",
           "topcolor":"#FF0000",
           "data":{
                   "first": {
                       "value":"'.$first.'",
                       "color":"#173177"
                   },'.$body.'
                   "remark":{
                       "value":"'.$remark.'",
                       "color":"#173177"
                   }
           }
       }';
	    
	    $culres = $this->sub_curl($url,$str,1);
	    $res = json_decode($culres,true);
// 	    $DzDebugLog->insert(array('descript'=>'发送结果','jsondata'=>json_encode($res)));
	    if($res['errcode']==0 && $res['errmsg']=='ok') {	//提交成功
	        return array('state'=>1,'cont'=>$res['msgid']);
	    } else {
	        return array('state'=>2,'errcode'=>$res['errcode'],'errmsg'=>$res['errmsg']);
	    }
	}
	
}
?>
