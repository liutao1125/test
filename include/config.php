<?php

$GLOBALS['sys_module']=array(
			"wx"=>array(
					"domain"=>array('sxzx.dodoca.dev','t.sxzx.dodoca.com','admin.sx985.com','wap.sx985.com','t.data.sxzx.dodoca.com','data.sx985.com','sxzx.dodoca.com'),
					"ver"=>array('test'=>'2.0','sc'=>'2.0','loc'=>'2.0')
			),
			"admin"=>array(
					"domain"=>array(''),
					"ver"=>array('test'=>'','sc'=>'','loc'=>'')
			),
			"data"=>array(
					"domain"=>array(''),
					"ver"=>array('test'=>'','sc'=>'','loc'=>'')
			),
		);
// $GLOBALS['public_gzh']=array("AppID"=>"wx8b5b253d0b6cea04","AppSecret"=>"8f7c043c87aeec64eb2f527629b6dc61","SysUid"=>"48332");

// $GLOBALS['public_gzh']=array("AppID"=>"wx8e9ca32821b7279b","AppSecret"=>"6c97576d7a3d100ec08b5b2b02b03b93","SysUid"=>"48332");
$GLOBALS['public_gzh']=array("AppID"=>"wx865dd371dc62d676","AppSecret"=>"7c5976ddd7327040a1b425cbd4fbb867","SysUid"=>"48332");

$GLOBALS['score_upload_path'] = './www/upload/marks/';
//公众号服务授权登陆
$GLOBALS['Component_Config']=array( 
    //测试环境
//     'test' => array(
//         'token'    => 'dodocaweixin2014001t',
//         'encodingAesKey' => 'dodocaweixin2014001diandianke0020030040068t',
//         'component_appid'   => 'wx20572709b5226162',
//         'component_appsecret'   => '0c79e1fa963cd80cc0be99b20a18faeb',
//     ),
//     'test' => array(
//         'token'    => '21232f297a57a5a743894a0e4a801fc3',
//         'encodingAesKey' => 'a1WdoLFQogxDRshuTFOouV0o7GT580H1mXvJwtth827',
//         'component_appid'   => 'wx8e9ca32821b7279b',
//         'component_appsecret'   => '6c97576d7a3d100ec08b5b2b02b03b93',
//     ),
    'test' => array(
        'token'    => 'fdff0bde65e55705b7062520ac6e72b1',
        'encodingAesKey' => '5YA9QLCESj0Yvcmh36AK00ojrn4EoIs4NX9Vnv7wBqK',
        'component_appid'   => 'wx865dd371dc62d676',
        'component_appsecret'   => '7c5976ddd7327040a1b425cbd4fbb867',
    ),
    //生产环境
    'sc' => array(
        'token'    => 'dodocaweixin2015001',
        'encodingAesKey' => 'dodocaweixin2015001diandianke00200300400688',
        'component_appid'   => 'wx6364363cf7a3a99d',
        'component_appsecret'   => '0c79e1fa963cd80cc0be99b20a18faeb',
    )
);
//科目
$GLOBALS['default_subject']=array(
    1=>'语文',
    2=>'数学',
    3=>'英语',
    4=>'物理',
    5=>'化学',
    6=>'生物',
    7=>'政治',
    8=>'历史',
    9=>'地理',
    10=>'信息技术',
    11=>'音乐',
    12=>'体育',
    13=>'美术',
    14=>'其它'
);


//校讯通接收对象
$GLOBALS['message_type']=array(
    1=>'所有角色',
    2=>'高中管理员',
    3=>'高校管理员',
    4=>'所有高校校长',
    5=>'全省高中校长',
    6=>'全省任课老师、班主任',
    7=>'全省校长、任课老师、班主任',
    8=>'全校师生',
    9=>'全校学生 ',
    10=>'全校班主任、任课老师',
    11=>'学生（班级）',
    12=>'学生（年级）',
    13=>'班主任',
    14=>'任课老师',
    15=>'学生',
    16=>'家长',
    17=>'学生及家长'
);
//专家类型
$GLOBALS['expert_type']=array(
    1=>'学习辅导',
    2=>'心理辅导',
    3=>'家长课堂',
    4=>'职业规划',
    5=>'志愿填报'
);

//二级标题
$GLOBALS['second_title']=array(
    1=>'试卷评析',
    2=>'解题思路',
    3=>'教学心得',
    4=>'优秀课件',
);
//高校类型
$GLOBALS['height_school_type']=array(
    1=>'重点本科院校',
    2=>'普通本科院校',
    3=>'高职高专院校'
);

//发帖班级年级分类
$GLOBALS['class_thread_category']=array(
    1=>'通知',
    2=>'分享',
    3=>'闲聊',
    4=>'求助'
);

//发帖专家分类
$GLOBALS['expert_thread_category']=array(
    5=>'专栏文章',
    6=>'答疑专区'
);

$GLOBALS['high_school_thread_category']=array(
    7=>'报考指南',
    8=>'招生快讯',
    9=>'院校风采',
    10=>'就业情况',
    11=>'线上咨询'
);


$GLOBALS['left_menu']=array(
    'school' => '学校管理',
    'college' => '高校展示',
    'register'=>'高校推荐',
    'grade' => '年级管理',
    'class' => '班级管理',
    'account' => '账号管理',
    'score' => '成绩管理',
    'schedule' => '课程表管理',
    'news' => '新闻管理',
    'main-site' => '升学微网广告管理',
    'message' => '校讯通',
    'forum' => '社区管理',
    'fans-statistics'=>'粉丝统计'
);

$GLOBALS['mobile_right']=array(
    'mobile-score',
    'mobile-message',
    'mobile-forum',
    'mobile-center',
//     'mobile-colleges',
//     'mobile-expert',
    'moble-schedule'
);

$GLOBALS['filter_keyword'] = array('他妈的','操','法轮功','法论大法好');



//角色ID(1=>站长，2=>高校省长，3=>高校校长，4=>高中省长，5=>高中校长，6=>班主任，7=>任课老师，8=>专家，9=>学生，10=>家长，11=>编辑)
$GLOBALS['role_right']=array(
    1 => array( //角色ID 站长 权限：学校删除 升学微网新闻编辑 社区全部
        'account' => array(
            'account-list',
            'web-add',
            'account-mass-add',
            'account-mass-save',
            'download-account-excel',
            'ajax-web-save',
            'ajax-schoolmaster-save',
            'ajax-change-status',
            'ajax-change-sort',
            'ajax-delete-all-account',
            'ajax-check-username',
            'ajax-check-mobile',
            'ajax-check-school',
            'ajax-check-province',
            'ajax-get-school',
            'ajax-get-school-by-area',
            'ajax-get-city',
            'ajax-get-area',
            'ajax-get-city-name',
            'ajax-get-class'
        ),
        'school' => array(
            'school-list',
            'school-add',
            'school-mass-add',
            'school-mass-save',
            'download-school-excel',
            'ajax-change-status',
            'ajax-change-is-hide',
            'ajax-change-sort',
            'ajax-change-updatetime',
            'ajax-delete-all-school',
            'ajax-school-save',
            'template-edit',
            'save-template'
        ),
        'news' => array(
            'news-list',
            'news-edit',
            'ajax-news-save',
            'ajax-get-title',
            'ajax-change-status',
            'ajax-delete-all-news'
        ),
        //二期功能
        'message' => array(
            'message-list',
            'message-send',
            'ajax-message-send-save',
            'message-detail',
            'message-content',
            'refresh-token'
        ),
        'college' => array(
            'college-list',
            'college-save'
        ),
        'register' => array(
            'register-list',
            'register-show-save',
            'ajax-change-sort',
            'college-sort-list'
        ),
        'main-site' => array(
            'main-site-list',
            'save-template'
        ),
        //二期功能
        'forum' => array(
            'forum-list',
            'forum-is-top',
            'forum-is-audit',
            'forum-delete',
            'forum-restart',
            'forum-delete-all',
            'post-thread',
            'forum-save',
            'forum-threads-reply',
            'forum-threads-reply-list',
            'forum-threads-reply-delete',
            'forum-threads-reply-restart',
            'forum-threads-reply-delete-all'
        ),
//         'fans-statistics' => array(
//             'fans-list',
//             'bar-char',
//             'province-map',
//             'role-bar'
//         )
    ),
    2 => array( //角色ID 高校省长
        'account' => array(
            'account-list'
        ),
        'school' => array(
            'school-list'
        ),
       'message' => array(
           'message-list',
           'message-send',
           'ajax-message-send-save',
           'message-detail',
           'message-content'
       )
    ),
    3 => array( //角色ID 高校校长
        'school' => array(
            'school-list',
            'school-add',
            'ajax-change-status',
            'ajax-delete-all-school',
            'ajax-school-save',
            'template-edit',
            'save-template'
        ),
        //二期功能
       'forum' => array(
           'forum-list',
           'forum-is-top',
           'forum-is-audit',
           'forum-delete',
           'forum-restart',
           'forum-delete-all',
           'post-thread',
           'forum-save',
           'forum-threads-reply-list',
           'forum-threads-reply'

       ),
        'fans-statistics' => array(
            'fans-list',
            'bar-char',
            'province-map',
            'role-bar'
        )
    ),
    4 => array( //角色ID 高中省长
        'account' => array(
            'account-list'
        ),
        'school' => array(
            'school-list'
        ),
        'message' => array(
            'message-list',
            'message-send',
            'ajax-message-send-save',
            'message-content',
            'message-detail'
        )
    ),
    5 => array( //角色ID 高中校长
        'school' => array(
            'school-list',
            'school-add',
            'ajax-school-save',
            'template-edit',
            'save-template'
        ),

        'grade' => array(
            'grade-list',
            'grade-add',
            'validat',
            'grade-save',
            'grade-delete',
            'grade-restart',
            'grade-delete-all'
        ),
        'class' => array(
            'class-list',
            'class-add',
            'class-bulk-add',
            'validat',
            'class-save',
            'class-bulk-save',
            'input_csv',
            'download-excel-model',
            'class-delete',
            'class-restart',
            'class-delete-all',
            'class-invite-code',
//             'create-invite-code',
//             'class-invite-code-delete',
//             'class-invite-code-delete-all',
            'invite-code-export'
        ),
        'account' => array(
            'account-list',
            'web-add',
            'ajax-web-save',
            'ajax-change-status',
            'ajax-delete-all-account',
            'ajax-schoolmaster-save',
            'ajax-check-username',
            'ajax-check-mobile',
            'ajax-check-school',
            'ajax-check-province',
            'ajax-get-school',
            'ajax-get-school-by-area',
            'ajax-get-city',
            'ajax-get-area',
            'ajax-get-city-name',
            'ajax-get-class'
        ),
        //二期功能
        'score' => array(
            'score-list',
            'score-bulk-add',
            'score-bulk-add-step',
            'exam-bulk-save',
            'scores-bulk-save',
            'judge_bulk_data',
            'score-delete',
            'score-restart',
            'score-delete-all',
            'score-show',
            'download-excel-model'
        ),
        'news' => array(
            'news-list',
            'news-edit',
            'ajax-news-save',
            'ajax-get-title',
            'ajax-change-status',
            'ajax-delete-all-news'
        ),
        //二期功能
        'message' => array(
            'message-list',
            'message-send',
            'ajax-message-send-save',
            'message-content',
            'message-detail'
        ),
        //二期功能
        'forum' => array(
            'forum-list',
            'forum-is-top',
            'forum-is-audit',
            'forum-delete',
            'forum-restart',
            'forum-delete-all',
            'post-thread',
            'forum-save',
            'forum-threads-reply-list',
            'forum-threads-reply'
        )
    ),
    6 => array( //角色ID 高中班主任
        'account' => array(
            'account-list',
            'web-add',
            'ajax-delete-all-account',
            'ajax-change-status',
            'ajax-classmaster-save'
        ),
//         'class' => array(
//             'class-list',
//             'validat',
//             'create-invite-code',
//             'class-invite-code',
//             'class-invite-code-delete',
//             'class-invite-code-delete-all',
//             'invite-code-export',
//             'upload-students',
//             'download-students-excel-model',
//             'class-create-invite-code'
//         ),
        //二期功能
        'score' => array(
            'score-list',
            'score-add',
            'score-bulk-add',
            'score-add-step',
            'score-bulk-add-step',
            'exam-save',
            'exam-bulk-save',
            'scores-save',
            'scores-bulk-save',
            'scores-save-teacher',
            'input_csv',
            'judge_data',
            'judge_bulk_data',
            'judge_teacher_data',
            'score-delete',
            'score-restart',
            'score-delete-all',
            'score-show',
            'download-excel-model'
        ),
       // 二期功能
        'schedule'=> array(
            'schedule-list',
            'schedule-add',
            'schedule-save'
        ),
//         'news' => array(
//             'news-list',
//             'news-edit',
//             'ajax-news-save',
//             'ajax-get-title',
//             'ajax-change-status',
//             'ajax-delete-all-news'
//         ),
        //二期功能
        'message' => array(
            'message-list',
            'message-send',
            'ajax-message-send-save',
            'message-content',
            'message-detail'
        ),
        'forum' => array(
            'forum-list',
            'forum-is-top',
            'forum-is-audit',
            'forum-delete',
            'forum-restart',
            'forum-delete-all',
            'post-thread',
            'forum-save',
            'forum-threads-reply-list',
            'forum-threads-reply'
        ),
        'filemanager' =>array(
            'file-list',
            'pdf-show'
        )
    ),
    7 => array( //角色ID 任课老师
        'account' => array(
            'account-list',
            'web-add',
            'ajax-classmaster-save'
        ),
       'score' => array(
           'score-list',
           'score-add',
           'score-add-step',
           'exam-save',
           'scores-save',
           'scores-save-teacher',
           'input_csv',
           'judge_data',
           'judge_teacher_data',
           'score-delete',
           'score-restart',
           'score-delete-all',
           'score-show',
           'download-excel-model'
       ),
        'news' => array(
            'news-list',
            'news-edit',
            'ajax-news-save',
            'ajax-get-title',
            'ajax-change-status',
            'ajax-delete-all-news'
        )
    ),
    8 => array( //角色ID 专家
        'account' => array(
            'account-list',
            'web-add',
            'ajax-classmaster-save'
        ),
        'forum' => array(
            'forum-list',
            'forum-is-top',
            'forum-is-audit',
            'forum-delete',
            'forum-restart',
            'forum-delete-all',
            'post-thread',
            'forum-save',
            'forum-threads-reply-list',
            'forum-threads-reply'
        ),
        'news' => array(
            'news-list',
            'news-edit',
            'ajax-news-save',
            'ajax-get-title',
            'ajax-change-status',
            'ajax-delete-all-news'
        ),
    ),
    11 => array( //角色ID 编辑
        'news' => array(
            'news-list',
            'news-edit',
            'ajax-news-save',
            'ajax-get-title',
            'ajax-change-status',
            'ajax-delete-all-news'
        ),
        'college' => array(
            'college-list',
            'college-save'
        )
    ),
);






$GLOBALS['SYS_QRCODE']=array(
			"test"=>array("qrcode_img"=>"/www/weibot.jpg","band_img"=>"/www/band_weibot.jpg","pwd_img"=>"/www/pwd_weibot.jpg","scan_img"=>"/www/scan_test.jpg","scan_font"=>"/www/font_test.jpg"),
			"sc"=>array("qrcode_img"=>"/www/reg.jpg","band_img"=>"/www/band_ddk.jpg","pwd_img"=>"/www/pwd_ddk.jpg","scan_img"=>"/www/scan_sc.jpg","scan_font"=>"/www/font_sc.jpg")
		);

//模块归类
$GLOBALS['module_category'] = array(
	1 => array ('name' => '全景展厅', 'child' => false, 'url' => '/mobilepanorama/showhouse'),
	2 => array ('name' => '微相册', 'child' => false, 'url' => '/mobilephoto/mobilephoto'),
	3 => array ('name' => '微网站', 'child' => false, 'url' => '/phonewebsite/website'),
    //33 => array ('name' => '指尖海报', 'child' => false, 'url' => '/phone/website'),
	33 => array ('name' => '点点客海报', 'child' => false, 'url' => '/mobilehandbill/index'),
	49 => array ('name' => '微酒店', 'child' => array(
											1 => array('name' => '酒店预订', 'url' => '/phoneaccess/index'), 
											2 => array('name' => '预约订单', 'url' => '/phoneaccess/index'), 
	)),
	34 => array ('name' => '微网站(自定义)', 'child' => false, 'url' => '/phonewebsitet/websitet'),
	29=> array ('name' => '微餐饮', 'child' => array(
											1 => array('name' => '微外卖', 'url' => '/phoneaccess/index'), 
											2 => array('name' => '微订座', 'url' => '/phoneaccess/index'), 
											4 => array('name' => '微点菜', 'url' => '/phoneaccess/index'),
											3 => array('name' => '餐饮图库', 'url' => '/mobilerepastpic/login'), 
	)),
	37=> array ('name' => '微餐饮', 'child' => array(
											1 => array('name' => '微订座', 'url' => '/phoneaccess/index'), 
											2 => array('name' => '微外卖', 'url' => '/phoneaccess/index'), 
											3 => array('name' => '微点菜', 'url' => '/phoneaccess/index'),
											4 => array('name' => '门店信息', 'url' => '/phoneaccess/index'),
											5 => array('name' => '餐饮图库', 'url' => '/mobilerepastspic/login'), 
	)),
	43 => array ('name' => '微外送', 'child' => false, 'url' => '/phoneaccess/index'),
	4 => array ('name' => '微预约', 'child' => false, 'url' => '/mobilemicroapp/index'),
//	4 => array ('name' => '微预约', 'child' => false, 'url' => '/mobilemicroappointment/mobilemicroappointment'),
	53 => array ('name' => '优惠券', 'child' => false, 'url' => ''),
	54 => array ('name' => '优惠券包', 'child' => false, 'url' => '/mobilecoupons/couponspacket'),
	52 => array ('name' => '刮刮乐', 'child' => false, 'url' => '/phoneactivityggk/index'),
	55 => array ('name' => '福袋', 'child' => false, 'url' => '/phoneluckybag/index'),
	60 => array ('name' => '大转盘','child' => false, 'url' => '/phoneactivitybigwheel/index'),
	64 => array ('name' => '魔法星星','child' => false, 'url' => '/phoneactivitystar/index'),
	65 => array ('name' => '砸金蛋','child' => false, 'url' => '/phoneactivityegg/index'),
	5 => array ('name' => '旧版优惠券', 'child' => false, 'url' => '/mobilecoupon/show'),
	6 => array ('name' => '微游戏', 'child' => array(
//											1 => array('name' => '刮刮乐', 'url' => '/phone/newshowscratch'), 
//											2 => array('name' => '大转盘', 'url' => '/phone/newshowbigwheel'), 
//											3 => array('name' => '砸金蛋', 'url' => '/phone/newshowegg'), 
//											4 => array('name' => '魔法星星', 'url' => '/phone/newshowstar'),
											5 => array('name' => '愤怒的汽水', 'url' => '/phoneshake/shake'),
											6 => array('name' => '小猪快跑', 'url' => '/phonepig/pig'),
                                            7 => array('name' => '嫦娥去哪了', 'url' => '/catchchange/index'),
											8 => array('name' => '全民挖宝', 'url' => '/wabaophone/index'),
											)),
	/* 7 => array ('name' => '微年会', 'child' => array(
											1 => array('name' => '年会绑定', 'url' => '/meeting/binding'), 
											2 => array('name' => '年会点赞', 'url' => '/meeting/praise'))), */
	8 => array ('name' => '抢红包', 'child' => false, 'url' => '/phoneredpacketqiang/redpacketphone'),
	59 => array ('name' => '摇红包','child' => false, 'url' => '/phoneredpacketz/redpacketphone'),
	9 => array ('name' => '互动墙', 'child' => false, 'url' => '/wallPhone/walllist'),
	11 => array ('name' => '微投票', 'child' => false, 'url' => '/votephone/tptzphone'),
	12 => array ('name' => '微排队', 'child' => false, 'url' => '/mobilelineup/diningcall'),
//	13=> array ('name' => '微签到', 'child' => false, 'url' => '/phone/welcome'),
	14=> array ('name' => '微教育', 'child' => array(
											1 => array('name' => '成绩查询', 'url' => '/mobilecourse/scorequery'), 
											2 => array('name' => '课程报名', 'url' => '/mobileCourse/apply'), 
	)),
	15=> array ('name' => '微资讯', 'child' => false, 'url' => '/phoneconsult/information'),
	16=> array ('name' => '微名片', 'child' => false, 'url' => '/mobilebusiness/index'),
	17=> array ('name' => '粉丝卡', 'child' => false, 'url' => '/phoneaccess/index'),
	18=> array ('name' => '微店铺', 'child' => false, 'url' => '/phoneaccess/index'),
	19=> array ('name' => '微团购', 'child' => false, 'url' => '/phoneaccess/index'),
	20=> array ('name' => '限时购', 'child' => false, 'url' => '/phoneaccess/index'),
	21=> array ('name' => '微秒杀', 'child' => false, 'url' => '/phoneaccess/index'),
	22=> array ('name' => '微竞拍', 'child' => false, 'url' => '/phoneaccess/index'),
	23=> array ('name' => '微楼书', 'child' => false, 'url' => '/phoneblockbook/index'),
	//24=> array ('name' => '世界杯竞猜', 'child' => false, 'url' => '/mobileworldcup/index'),
	25=> array ('name' => '微景点', 'child' => false, 'url' => '/phonemicroscenic/index'),
	26=> array ('name' => '微会务', 'child' => false, 'url' => '/mobileconference/show'),
	27=> array ('name' => '房贷计算器', 'child' => false, 'url' => '/calculator/tools'),
	28=> array ('name' => '游戏库', 'child' => array(
											1 => array('name' => '国旗之旅', 'url' => 'http://m.edianyou.com/h5game/flags.html'), 
											2 => array('name' => '宠物连连看', 'url' => 'http://m.edianyou.com/h5game/petlink.html'),
											3 => array('name' => '爱星座', 'url' => 'http://infoapp.3g.qq.com/g/s?g_f=22207&aid=astro#home'),
											4 => array('name' => '推箱子', 'url' => 'http://m.edianyou.com/h5game/bokoban.html'),
											5 => array('name' => '魔力方块', 'url' => 'http://m.edianyou.com/h5game/mayamojo.htm'),
											6 => array('name' => '像素小鸟', 'url' => 'http://game.9g.com/xsxn/wx.html?f=zf&from=singlemessage&isappinstalled=0'),
											7 => array('name' => '密室逃生', 'url' => 'http://game.9g.com/msts/wx.html?f=9g'),
											8 => array('name' => '青蛙过河', 'url' => 'http://game.9g.com/qingwa/wx.html?f=9g'),
											9 => array('name' => '最强眼力', 'url' => 'http://game.9g.com/zqyl/wx.html?f=9g'),
											10 => array('name' => '一个都不能死', 'url' => 'http://game.9g.com/bns/game.html?f=wx'),
											11 => array('name' => '我要飞的更高', 'url' => 'http://game.9g.com/ttt/wx.html?f=9g'),
											12 => array('name' => '算数达人', 'url' => 'http://game.9g.com/jiafa/wx.html?f=9g'),
											13 => array('name' => '围住神经猫', 'url' => 'http://218.244.142.3/game/mao/index.htm'),
											14 => array('name' => '看你有多色', 'url' => 'http://game.9g.com/ssss/game.html'),
	)),
	30=> array ('name' => '微问卷', 'child' => false, 'url' => '/mobilequestion/index'),
	31=> array ('name' => '生活圈', 'child' => false, 'url' => '/businesscirclephone/index'),
	32=> array ('name' => '活动报名', 'child' => false, 'url' => '/mobileenroll/index'),
	35=> array ('name' => '工具库', 'child' => array(
			1 => array('name' => '天气预报', 'url' => 'http://map.baidu.com/mobile/webapp/third/weather/force=superman/?third_party=hao123'),
			2 => array('name' => '万年历', 'url' => 'http://baidu365.duapp.com/uc/Calendar.html'),
			3 => array('name' => '公交地铁', 'url' => 'http://map.baidu.com/mobile/webapp/third/transit/force=superman/?third_party=hao123'),
			4 => array('name' => '列车查询', 'url' => 'http://touch.qunar.com/h5/train/'),
			5 => array('name' => '团购', 'url' => 'http://m.tuan800.com/?vt=8'),
			6 => array('name' => '地图', 'url' => 'http://map.baidu.com/mobile/webapp/index/index/foo=bar/vt=map&traffic=on'),
			7 => array('name' => '路况查询', 'url' => 'http://map.baidu.com/mobile/webapp/third/traffic/force=superman&city='),
			8 => array('name' => '生活服务', 'url' => 'http://map.baidu.com/mobile/webapp/index/more/?third_party=hao123'),
			9 => array('name' => '航班查询', 'url' => 'http://touch.qunar.com/h5/flight/?bd_source=baiduhao123'),
			10 => array('name' => '违章查询', 'url' => 'http://m.46644.com/tool/illegal/?width=320'),
			11 => array('name' => '酒店预订', 'url' => 'http://map.baidu.com/mobile/webapp/place/hotelzt/da_src=indexnearbypg.nearby'),
			12 => array('name' => '汇率查询', 'url' => 'http://m.46644.com/tool/exchange/?tpltype=weixin'),
			13 => array('name' => '公积金计算', 'url' => 'http://dp.sina.cn/dpool/tools/money/single.php?city_id=1&flag=house_per&pos=63&vt=4'),
			14 => array('name' => '房贷计算', 'url' => 'http://m.rong360.com/calc/?type=fangdai&utm_source=baidu&utm_medium=lightapp&utm_campaign=fangdaijisuanqi&bd_source_light=2120155'),
			15 => array('name' => '车贷计算', 'url' => 'http://car.m.yiche.com/qichedaikuanjisuanqi/'),
			16 => array('name' => '快递查询', 'url' => 'http://m.kuaidi100.com/'),
			17 => array('name' => '邮编区号', 'url' => 'http://wap.ip138.com/post.html'),
			18 => array('name' => '身份证查询', 'url' => 'http://wap.ip138.com/id.html'),
			19 => array('name' => '常用电话', 'url' => 'http://m.hao123.com/n/v/dianhua'),
			20 => array('name' => '手机归属地', 'url' => 'http://wap.ip138.com/sim.html'),
			21 => array('name' => '单位换算', 'url' => 'http://m.46644.com/tool/unitconvert/?tpltype=weixin'),
			22 => array('name' => '最新影讯', 'url' => 'http://m.mtime.cn'),
			23 => array('name' => '景点门票', 'url' => 'http://wap.yikuaiqu.com/?s=46644'),
			24 => array('name' => '个税计算', 'url' => 'http://brccalc.duapp.com/tools/geshui.php'),
			25 => array('name' => 'IT产品速查', 'url' => 'http://wap.zol.com.cn/index.html'),
			26 => array('name' => '短信祝福', 'url' => 'http://m.46644.com/duanxin/'),
			27 => array('name' => '股票助手', 'url' => 'http://wap.eastmoney.com/3g/center/default.shtml'),
			28 => array('name' => '心理测试', 'url' => 'http://dp.sina.cn/dpool/astro/top/index.php?type=1&pos=63&vt=4'),
			29 => array('name' => '百科全书', 'url' => 'http://wapbaike.baidu.com'),
			30 => array('name' => '周边打车', 'url' => 'http://taxi.map.baidu.com'),
			31 => array('name' => '菜谱美食', 'url' => 'http://m.xiachufang.com'),
			32 => array('name' => '移动营业厅', 'url' => 'http://wap.10086.cn/'),
			33 => array('name' => '电信营业厅', 'url' => 'http://wapzt.189.cn'),
			34 => array('name' => '联通营业厅', 'url' => 'http://wap.10010.com'),
			35 => array('name' => '租房信息', 'url' => 'http://m.fang.com/main.d?m=index&city=sh&sf_source=qqbrowser_mz'),
			36 => array('name' => '数字大写转换', 'url' => 'http://m.46644.com/tool/lowerupper.php?tpltype=weixin'),
			37 => array('name' => '网上挂号', 'url' => 'http://m.guahao.com/mobile'),
			38 => array('name' => '星座运势', 'url' => 'http://3g.d1xz.net/yunshi/today/'),
			39 => array('name' => '在线取名', 'url' => 'http://xm.xingzuopei.com/Ming2.aspx'),
			40 => array('name' => '房贷计算器', 'url' => 'http://m.fang.com/tools/'),
	)),
//    36 => array('name' => '微展厅', 'child' => false, 'url' => '/phonecarshow/index'),
    36 => array('name' => '微汽车', 'child' => array(
        1 => array('name' => '微展厅','url' => '/phonecarshow/index'),
        2 => array('name' => '车型报价','url' => '/carphone/index'),
        3 => array('name' => '车型对比','url' => '/carphone/pk'),
        4 => array('name' => '车型图库','url' => '/carphone/index?isxc=1'),
        5 => array('name' => '预约保养','url' => '/carphone/phoneorder?type=1'),
        6 => array('name' => '预约维修','url' => '/carphone/phoneorder?type=2'),
        7 => array('name' => '预约试驾','url' => '/carphone/phoneorder?type=3'),
        8 => array('name' => '二手车估价','url' => '/carphone/phoneorder?type=4'),
        9 => array('name' => '最低价格咨询','url' => '/carphone/phoneorder?type=5'),
        10 => array('name' => '预约拍牌','url' => '/carphone/phoneorder?type=6'),
        11 => array('name' => '尊享预约','url' => '/carphone/indexorder'),
        12 => array('name' => '汽车工具箱','url' => '/carphone/cartools'),
        13 => array('name' => '联系我们','url' => '/carphone/linkus'),
    )),
    38 => array ('name' => '查询中奖信息', 'child' => false, 'url' => '/wegamesselect/index'),
    
    40=> array ('name' => '微喜帖', 'child' => false, 'url' => '/phonewedding/cover'),
    41=> array ('name' => '喜帖管理', 'child' => false, 'url' => '/phoneweddingaudit/index'),
    42 => array ('name' => '新版互动墙', 'child' => false, 'url' => '/newwallphone/signin'),
    //43被微外送占用
    44 => array ('name' => '汽车品牌展示', 'child' => false, 'url' => '/carphone/index'),
    45 => array ('name' => '车系展示', 'child' => false, 'url' => '/carphone/cxlist'),
	46 => array('name' => '微房产', 'child' => array(
        1 => array('name' => '经纪人平台','url' => '/housephone/index'),
        2 => array('name' => '案场经理','url' => '/housecase/caselogin/?type=acjl'),
		3 => array('name' => '置业顾问','url' => '/housecase/caselogin/?type=zygw'),
    )),
	47 => array ('name' => '楼盘展示', 'child' => false, 'url' => '/housephone/lpdetail'),
	//48 => array ('name' => '微助力', 'child' => false, 'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize'),
	50 => array ('name' => 'WiFiSong', 'child' => false, 'url' => '/wifisong/request'),
	51 => array ('name' => '圣诞派', 'child' => false, 'url' =>  '/christmasphone/jumpa'),
	56 => array ('name' => '博博乐', 'child' => false, 'url' => '/newchristmasphone/jumpa'),
	57 => array ('name' => '滴滴打车','child' => false, 'url' => '/mobiledache/didi'),
	58 => array ('name' => '新版微助力','child' => false, 'url' => '/phoneactivityz/getcode'),
	61 => array('name' => '微政务', 'child' => array(
        1 => array('name' => '微政厅','url' => '/mobilegovernment/index'),
        2 => array('name' => '政务简介','url' => '/mobilegovernment/intro'),
		3 => array('name' => '咨询投诉','url' => '/mobilegoveradvisory/list'),
		4 => array('name' => '政策法规','url' => '/mobilegoverpolicy/list'),
		5 => array('name' => '信息公开','url' => '/mobilegoverinformation/list'),
		6 => array('name' => '便民问答','url' => '/mobilegoveranswers/list'),
    )),
    62 => array ('name' => '办事预约','child' => false, 'url' => '/mobilegovmicroapp/list'),
    63 => array ('name' => '办事窗口','child' => false, 'url' => '/mobilegovernment/departmentlist'),
    66 => array ('name' => '爱贴贴', 'child' => false, 'url' => '/aitietiephone/index'),
    68 => array ('name' => '有声贺卡', 'child' => false, 'url' => '/greetcardphone/index'),
	//下面新增请从 69开始
);


//省份
$GLOBALS['province']=array(
		"1"=>"北京",
		"2"=>"安徽",
		"3"=>"福建",
		"4"=>"甘肃",
		"5"=>"广东",
		"6"=>"广西",
		"7"=>"贵州",
		"8"=>"海南",
		"9"=>"河北",
		"10"=>"河南",
		"11"=>"黑龙江",
		"12"=>"湖北",
		"13"=>"湖南",
		"14"=>"吉林",
		"15"=>"江苏",
		"16"=>"江西",
		"17"=>"辽宁",
		"18"=>"内蒙古",
		"19"=>"宁夏",
		"20"=>"青海",
		"21"=>"山东",
		"22"=>"山西",
		"23"=>"陕西",
		"24"=>"上海",
		"25"=>"四川",
		"26"=>"天津",
		"27"=>"西藏",
		"28"=>"新疆",
		"29"=>"云南",
		"30"=>"浙江",
		"31"=>"重庆",
		"32"=>"香港",
		"33"=>"澳门",
		"34"=>"台湾",
		"35"=>"其他"
		);
?>
