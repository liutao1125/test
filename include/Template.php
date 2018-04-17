<?php
//会员卡模板
$GLOBALS['template_card']=array(
			"1" => "VIP会员卡",
			"2" => "VIP银卡",
			"3" => "VIP金卡",
			"4" => "VIP钻卡",
		);

//优惠券模板
$GLOBALS['template_ticket']=array(
			"1" => "兑换券模板1",
			"2" => "兑换券模板2",
			"3" => "兑换券模板3",
		);

//会务模板样式
$GLOBALS['template_hwys']=array(
	"0"	=>	array(
		'id'	=>	'1',
		'title'	=>	'商业会务',
		'pic'	=>	'/www/images/micro_business/mb01.jpg'
	),
	"1"	=>	array(
		'id'	=>	'2',
		'title'	=>	'多彩炫丽',
		'pic'	=>	'/www/images/micro_business/mb02.jpg'
	),
	"2"	=>	array(
		'id'	=>	'3',
		'title'	=>	'隆重庆典',
		'pic'	=>	'/www/images/micro_business/mb03.jpg'
	),
	"3"	=>	array(
		'id'	=>	'4',
		'title'	=>	'欢庆婚礼',
		'pic'	=>	'/www/images/micro_business/mb04.jpg'
	),
	"4"	=>	array(
		'id'	=>	'5',
		'title'	=>	'点点客',
		'pic'	=>	'/www/images/micro_business/mb05.jpg'
	),
);

//粉丝卡金点交易状态
$GLOBALS['golden_type']=array(
	"1"=>"开卡获得",
	"2"=>"每日签到(稳健型)",
	"3"=>"完善信息",
	"4"=>"推荐好友",	
	"5"=>"连续签到",
	"6"=>"充值",
	"7"=>"消费",
	"8"=>"每日签到(冒险型)",
	"9"=>"推荐人",
	"10"=>"单笔消费送",
	"11"=>"单笔充值送",
	"12"=>"pos机充值",
	"13"=>"pos机充值送",
	"14"=>"后台操作",
	"15"=>"店铺消费",
	"16"=>"团购消费",
	"17"=>"限时购消费",
	"18"=>"秒杀消费",	
	"19"=>"竞拍消费",	
	"20"=>"商城消费",
	"21"=>"子账号后台操作",
	"22"=>"餐饮外卖支付",
	"23"=>"餐饮堂吃支付",
	"24"=>"外送支付",
	"-100"=>"未知消费"
);

//粉丝卡积分交易状态
$GLOBALS['integral_type']=array(
	"1"=>"开卡获得",
	"2"=>"每日签到(稳健型)",
	"3"=>"完善信息",
	"4"=>"推荐好友",	
	"5"=>"连续签到",
	"6"=>"单笔充值送",
	"7"=>"单笔消费送",
	"8"=>"升级粉丝卡",
	"9"=>"换取兑换券",
	"10"=>"每日签到(冒险型)",
	"11"=>"初始卡积分",
	"12"=>"后台操作",
	"13"=>"子账号后台操作",
	'14'=>"商户定期清除"
);

//微网站自定义开场动画
$GLOBALS['open_cartoon']=array(
	"0"	=>	array(
		'id'	=>	'1',
		'title'	=>	'从右往左'
	),
	"1"	=>	array(
		'id'	=>	'2',
		'title'	=>	'从左向右'
	),
	"2"	=>	array(
		'id'	=>	'3',
		'title'	=>	'淡入渐显'
	),
	"3"	=>	array(
		'id'	=>	'4',
		'title'	=>	'翻转'
	),
);

//内部音乐库
$GLOBALS['music_library']=array(
	"0"	=>	array(
		'id'	=>	'1',
		'title'	=>	'An Unfinished Life',
		'url'=>'http://pic.dodoca.com/music/1.mp3',
		//'audition'=>'http://music.163.com/#/song?id=26902557'
	),
	"1"	=>	array(
		'id'	=>	'2',
		'title'	=>	'I Heard Goodbye',
		'url'=>'http://pic.dodoca.com/music/2.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28378029'
	),
	"2"	=>	array(
		'id'	=>	'3',
		'title'	=>	'假如',
		'url'=>'http://pic.dodoca.com/music/3.mp3',
		//'audition'=>'http://music.163.com/#/song?id=26026963'
	),
	"3"	=>	array(
		'id'	=>	'4',
		'title'	=>	'一番の宝物',
		'url'=>'http://pic.dodoca.com/music/4.mp3',
		//'audition'=>'http://music.163.com/#/song?id=471987'
	),
	"4"	=>	array(
		'id'	=>	'5',
		'title'	=>	'爱的勇气',
		'url'=>'http://pic.dodoca.com/music/5.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28850746'
	),
	"5"	=>	array(
		'id'	=>	'6',
		'title'	=>	'More Than Words',
		'url'=>'http://pic.dodoca.com/music/6.mp3',
		//'audition'=>'http://music.163.com/#/song?id=17567262'
	),
	"6"	=>	array(
		'id'	=>	'7',
		'title'	=>	'Just Yesterday',
		'url'=>'http://pic.dodoca.com/music/7.mp3',
		//'audition'=>'http://music.163.com/#/song?id=18100203'
	),
	"7"	=>	array(
		'id'	=>	'8',
		'title'	=>	'At My Most Beautiful',
		'url'=>'http://pic.dodoca.com/music/8.mp3',
		//'audition'=>'http://music.163.com/#/song?id=20283082'
	),
	"8"	=>	array(
		'id'	=>	'9',
		'title'	=>	'夜空中最亮的星',
		'url'=>'http://pic.dodoca.com/music/9.mp3',
		//'audition'=>'http://music.163.com/#/song?id=25706282'
	),
	"9"	=>	array(
		'id'	=>	'10',
		'title'	=>	'陌生城市的早晨',
		'url'=>'http://pic.dodoca.com/music/10.mp3',
		//'audition'=>'http://music.163.com/#/song?id=375656'
	),
	"10"	=>	array(
		'id'	=>	'11',
		'title'	=>	'Long, Long Way To Go',
		'url'=>'http://pic.dodoca.com/music/11.mp3',
		//'audition'=>'http://music.163.com/#/song?id=2416600'
	),
	"11"	=>	array(
		'id'	=>	'12',
		'title'	=>	'红莲の弓矢',
		'url'=>'http://pic.dodoca.com/music/12.mp3',
		//'audition'=>'http://music.163.com/#/song?id=26608719'
	),
	"12"	=>	array(
		'id'	=>	'13',
		'title'	=>	'Bang Bang Bang',
		'url'=>'http://pic.dodoca.com/music/13.mp3',
		//'audition'=>'http://music.163.com/#/song?id=19192332'
	),
	"13"	=>	array(
		'id'	=>	'14',
		'title'	=>	'Do It, Again',
		'url'=>'http://pic.dodoca.com/music/14.mp3',
		//'audition'=>'http://music.163.com/#/song?id=19932838'
	),
	"14"	=>	array(
		'id'	=>	'15',
		'title'	=>	'The Fox',
		'url'=>'http://pic.dodoca.com/music/15.mp3',
		//'audition'=>'http://music.163.com/#/song?id=27706564'
	),
	"15"	=>	array(
		'id'	=>	'16',
		'title'	=>	'Moves Like Jagger',
		'url'=>'http://pic.dodoca.com/music/16.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21253966'
	),
	"16"	=>	array(
		'id'	=>	'17',
		'title'	=>	'Waiting',
		'url'=>'http://pic.dodoca.com/music/17.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28201231'
	),
	"17"	=>	array(
		'id'	=>	'18',
		'title'	=>	'All of Me',
		'url'=>'http://pic.dodoca.com/music/18.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28371425'
	),
	"18"	=>	array(
		'id'	=>	'19',
		'title'	=>	'Runaway',
		'url'=>'http://pic.dodoca.com/music/19.mp3',
		//'audition'=>'http://music.163.com/#/song?id=18969328'
	),
	"19"	=>	array(
		'id'	=>	'20',
		'title'	=>	'恋人歌歌',
		'url'=>'http://pic.dodoca.com/music/20.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28748202'
	),
	"20"	=>	array(
		'id'	=>	'21',
		'title'	=>	'江湖再见',
		'url'=>'http://pic.dodoca.com/music/21.mp3',
		//'audition'=>'http://music.163.com/#/song?id=27514900'
	),
	"21"	=>	array(
		'id'	=>	'22',
		'title'	=>	'南山忆',
		'url'=>'http://pic.dodoca.com/music/22.mp3',
		//'audition'=>'http://music.163.com/#/song?id=167786'
	),
	"22"	=>	array(
		'id'	=>	'23',
		'title'	=>	'Kiss the rain',
		'url'=>'http://pic.dodoca.com/music/23.mp3',
		//'audition'=>'http://music.163.com/#/song?id=5312668'
	),
	"23"	=>	array(
		'id'	=>	'24',
		'title'	=>	'Drama',
		'url'=>'http://pic.dodoca.com/music/24.mp3',
		//'audition'=>'http://music.163.com/#/song?id=2529461'
	),
	"24"	=>	array(
		'id'	=>	'25',
		'title'	=>	'달빛이 저무는 밤',
		'url'=>'http://pic.dodoca.com/music/25.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28713834'
	),
	"25"	=>	array(
		'id'	=>	'26',
		'title'	=>	'Bamboo',
		'url'=>'http://pic.dodoca.com/music/26.mp3',
		//'audition'=>'http://music.163.com/#/song?id=801827'
	),
	"26"	=>	array(
		'id'	=>	'27',
		'title'	=>	'操场上的夏天',
		'url'=>'http://pic.dodoca.com/music/27.mp3',
		//'audition'=>'http://music.163.com/#/song?id=27583087'
	),
	"27"	=>	array(
		'id'	=>	'28',
		'title'	=>	'월의 눈동자를 지닌 소녀',
		'url'=>'http://pic.dodoca.com/music/28.mp3',
		//'audition'=>'http://music.163.com/#/song?id=28838936'
	),
	"28"	=>	array(
		'id'	=>	'29',
		'title'	=>	'MO1',
		'url'=>'http://pic.dodoca.com/music/29.mp3',
		//'audition'=>'http://music.163.com/#/song?id=591797'
	),
	"29"	=>	array(
		'id'	=>	'30',
		'title'	=>	'Dreamscape',
		'url'=>'http://pic.dodoca.com/music/30.mp3',
		//'audition'=>'http://music.163.com/#/song?id=4442686'
	),
	"30"	=>	array(
		'id'	=>	'31',
		'title'	=>	'The Dawn',
		'url'=>'http://pic.dodoca.com/music/31.mp3',
		//'audition'=>'http://music.163.com/#/song?id=4017240'
	),
	"31"	=>	array(
		'id'	=>	'32',
		'title'	=>	'Puppy',
		'url'=>'http://pic.dodoca.com/music/32.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	//喜帖
	"32"	=>	array(
		'id'	=>	'33',
		'title'	=>	'My Soul',
		'url'=>'http://pic.dodoca.com/music/33.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"33"	=>	array(
		'id'	=>	'34',
		'title'	=>	'爱就一个字',
		'url'=>'http://pic.dodoca.com/music/34.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"34"	=>	array(
		'id'	=>	'35',
		'title'	=>	'纯音乐',
		'url'=>'http://pic.dodoca.com/music/35.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"35"	=>	array(
		'id'	=>	'36',
		'title'	=>	'婚礼进行曲',
		'url'=>'http://pic.dodoca.com/music/36.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"36"	=>	array(
		'id'	=>	'37',
		'title'	=>	'今天你要嫁给我',
		'url'=>'http://pic.dodoca.com/music/37.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"37"	=>	array(
		'id'	=>	'38',
		'title'	=>	'梦中的婚礼',
		'url'=>'http://pic.dodoca.com/music/38.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"38"	=>	array(
		'id'	=>	'39',
		'title'	=>	'明天我要嫁给你',
		'url'=>'http://pic.dodoca.com/music/39.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"39"	=>	array(
		'id'	=>	'40',
		'title'	=>	'瓦妮莎的微笑',
		'url'=>'http://pic.dodoca.com/music/40.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),
	"40"	=>	array(
		'id'	=>	'41',
		'title'	=>	'圣诞歌-铃儿响叮当',
		'url'=>'http://pic.dodoca.com/music/41.mp3',
		//'audition'=>'http://music.163.com/#/song?id=21270643'
	),

);

//新版互动墙展示墙动画效果
$GLOBALS['newwall_cartoon'] = array(
    "0"=>"无效果",
    "1"=>"淡入淡出",
    "2"=>"从上往下覆盖",
    "3"=>"从右往左覆盖",
    "4"=>"从下往上覆盖",
    "5"=>"从左往右覆盖",
    "6"=>"从右往左滚动",
    "7"=>"从左往右滚动"
);

//圣诞派分数增加文字
$GLOBALS['christmas_add'] = array(
    '1'=>'二话不说，慷慨解囊。当场掏出一把大票。',
	'2'=>'嘴角上扬，露出迷人的微笑。',
	'3'=>'翻遍全身口袋，哆哆嗦嗦递过来几张。',
	'4'=>'一弯腰，从鞋垫下面抽出几张大票。',
	'5'=>'趁人不备，偷偷从募集箱里抽走了几张。',
	'6'=>'脸上挂上了诡异的笑容。你懂得！',
	'7'=>'大喊一声“见面分一半。”',
	'8'=>'突然想起您之前欠他的饭钱。'
);


//微喜帖-封面模板
$GLOBALS['invitation_template'] = array(
	'1'=>array(
		'id'=>1,
		'title'=>'默认',
		'image'=>'mbs01.jpg'
	),
	'2'=>array(
		'id'=>2,
		'title'=>'默认',
		'image'=>'mbs02.jpg'
	),
	'3'=>array(
		'id'=>3,
		'title'=>'默认',
		'image'=>'mbs03.jpg'
	),
	'4'=>array(
		'id'=>4,
		'title'=>'默认',
		'image'=>'mbs04.jpg'
	),
);


//微喜帖-宣言模板
$GLOBALS['manifesto_template'] = array(
    "1" => array(
                'manifesto_bridegroom' => '我会承担起一个丈夫的责任，<br/>'. '我会承担起一个父亲的责任，<br/>'.
                                        '我会做好家庭和事业的平衡，<br/>'. '为你和家人挡风遮雨。',
                'manifesto_bride' => '你即将在婚礼上，<br/>'.'单膝跪地对我说：让我们牵手走完这一辈子吧？<br/>'.
                                    '我将说：我愿意。<br/>'.'我会坚守妻子的本分，以家庭为重，<br/>'.
                                    '教育好我们的孩子，孝顺好我们的父母，<br/>'.'与你一起承担未来的路上的一切。',
                ),
    "2" => array(
        'manifesto_bridegroom' => '从相识到相知相爱，风风雨雨我们一<br/>'. '起走过了七个年头，有人说这是七年之痒<br/>'.
                                '的时候，我们却把它作为幸福起点的时刻。<br/>'.'因为有你，生活不再孤单。<br/>'.
                                '因为有你，未来充满期盼。<br/>'.'因为有你，身边洋溢温暖。<br/>'.
                                '相信我，我会用自己坚实的臂膀扛起<br/>'.'家庭这份幸福的责任，做一件人生最浪漫<br/>'.
                                '的事情，就是陪你一起慢慢变老。',
        'manifesto_bride' => '那一年，我们偶然相遇，没想到世界<br/>'.'这么大，两个小小的心却从此被系在一起。<br/>'.
                            '在这段爱情长跑中，经历过风风雨<br/>'.'雨，今朝终于修成正果。而在这个美丽的<br/>'.
                            '日子里 ，我们决定让幸福延续。<br/>'.'想说；这一路走来，实属不易。<br/>'.
                            '相信在未来的日子里，我们还能这样<br/>'.'不离不弃，陪伴彼此左右。',
        ),
    "3" => array(
        'manifesto_bridegroom' => '你是我一直以来的梦想，<br/>'.'如今这个梦想即将变为现实，<br/>'.
                                '我的内心充满激动与感激，<br/>'.'感谢上天能给我如此的荣幸，<br/>'.
                                '让我能有机会关心你，照顾你，呵护你。',
        'manifesto_bride' => '从你牵起我手的那一刻，<br/>'.'我就已经认定了，<br/>'.
                            '你，就是我一生的归宿，<br/>'.'我愿意，除了你心里，哪里都不去，<br/>'.
                            '我也愿意，将最好的自己交给你。',
        ),
    "4" => array(
        'manifesto_bridegroom' => '把你的情 记在心底<br/>'. '直到沧海桑田 直到地久天长<br/>'.
                                '不管我在哪方 无论我在哪时<br/>'.'漫漫长途 拥有着我不变的心',
        'manifesto_bride' => '爱 是千山 是万水<br/>'.'是我们跨越重重困难的守候<br/>'.
                            '爱 是豆蔻 是耄耋<br/>'.'是我们将要走过的岁月相守',
        ),
    "5" => array(
        'manifesto_bridegroom' => '我承诺永远对你忠实，<br/>'.'我承诺我将爱你、带领你、保护你直到白头偕老，<br/>'.
                                '我承诺我将努力让你看见我的爱，<br/>'.'只因为，我们是合而为一的。',
        'manifesto_bride' => '爱情很简单，<br/>'.'因为每个人都会说：“我爱你，会为你付出一切!”<br/>'.
                            '爱情也很难，<br/>'.'因为没有多少人做到了他的承诺，<br/>'.
                            '但你给我的诺言，我愿意无条件的相信。',
        )
);


//外卖行业版-外卖模板
$GLOBALS['takeout_template'] = array(
		'0'=>array(
				'id'=>1,
				'title'=>'默认模板',
				'image'=>'wmmod01.jpg'
		),
		'1'=>array(
				'id'=>2,
				'title'=>'双排小图',
				'image'=>'wmmod02.jpg'
		)
);

//酒店行业版-最晚到店时间
$GLOBALS['hotel_lasttime'] = array(
	'0'=>array('id'=>1,'title'=>'16:00'),
	'1'=>array('id'=>2,'title'=>'17:00'),
	'2'=>array('id'=>3,'title'=>'18:00'),
	'3'=>array('id'=>4,'title'=>'19:00'),
	'4'=>array('id'=>5,'title'=>'20:00'),
	'5'=>array('id'=>6,'title'=>'21:00'),
	'6'=>array('id'=>7,'title'=>'22:00'),
	'7'=>array('id'=>8,'title'=>'23:00'),
	'8'=>array('id'=>9,'title'=>'次日0:00'),
	'9'=>array('id'=>10,'title'=>'次日1:00'),
	'10'=>array('id'=>11,'title'=>'次日2:00'),
	'11'=>array('id'=>12,'title'=>'次日3:00'),
	'12'=>array('id'=>13,'title'=>'次日4:00'),
	'13'=>array('id'=>14,'title'=>'次日5:00')
);

//酒店行业版-证件类型
$GLOBALS['hotel_cardtype'] = array(
	'0'=>array('id'=>1,'title'=>'身份证'),
	'1'=>array('id'=>2,'title'=>'军官证'),
	'2'=>array('id'=>3,'title'=>'警官证'),
	'3'=>array('id'=>4,'title'=>'士兵证'),
	'4'=>array('id'=>5,'title'=>'护照'),
	'5'=>array('id'=>6,'title'=>'其他')
);

//新大转盘模板
$GLOBALS['bigwheel_template'] = array(
		'0'=>array(
				'id'=>1,
				'title'=>'模板1',
				'image'=>'tpl_ico1.jpg'
		),
		'1'=>array(
				'id'=>2,
				'title'=>'模板2',
				'image'=>'tpl_ico2.jpg'
		),
		'2'=>array(
				'id'=>3,
				'title'=>'模板3',
				'image'=>'tpl_ico3.jpg'
		)
);




?>
