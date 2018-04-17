<?php
/**
 * 社交平台发布消息接口
 * @author dengjianjun
 *
 */
class SnsApi{
	
	private $snsmodel;//sns模型
	
	public function __construct(){
		$this->snsmodel = new SnsTask();
	}
	/**
	 * 新增任务
	 * @param string $content 内容=标题+','+描述+','+图文消息URL
	 * @param string $imgurl 图片路径 = 图片的绝对路径
	 * @param int $graphicid 新增图文素材成功后返回的其唯一id
	 */
	public function addTask($content,$imgpath,$graphicid){
		$data = array();
		$data["userid"] = get_uid();
		$data["graphicid"] = $graphicid;
		$data["content"] = $content;
		//$data["picpath"] = $this->moveFile($imgpath);//以后图片可能需要moveFile处理
		$data["picpath"] = $imgpath;//以后图片可能需要moveFile处理
		$data["addtime"] = time();
		$data["endtime"] = null;
		$data["status"] = -1;//未处理状态
		$ret = $this->snsmodel->addtask($data);
		if($ret){
			$result = $this->taskFactory($ret);
		    return $result;
		}else{
			return false;
		}
	}
	
	/**
	 * 取出单条任务，处理任务
	 */
	public function taskFactory($id){
		$taskArr = $this->snsmodel->gettask($id);
			$data["content"]=$taskArr[0]["content"];
			$data["picpath"]=$taskArr[0]["picpath"];
			//$data["picpath"]="http://ww2.sinaimg.cn/bmiddle/a93f3bd0jw1ehiexcmqz2j20af0dwgo0.jpg";
			$uid = $taskArr[0]["userid"];
			$msg = $this->snsmodel->snsmass($data,$uid);//执行发送
			$this->snsmodel->updatetask($taskArr[0]["id"],$msg["errcode"],$msg["msg"],$msg["mid"],$msg["user"]["id"]);
			if($msg["msg"]=="ok"){
				return true;
			}else{
				return false;
			}
	}
	/*
	 * 检测授权码是否过期 
	 */
	public function checkOauthToken($type="sina"){
		return $this->snsmodel->checkOauthToken($type);
	}
	/*
	 *发布一条文本微博
	* 参数：
	* $content，微博内容
	* 返回字段：http://open.weibo.com/wiki/2/statuses/update
	*
	*/
	public function statusesUpdate($content,$type="sina"){
		$data = array();
		$data["userid"] = get_uid();
		$data["content"] = $content;
		$data["addtime"] = time();
		$data["endtime"] = null;
		$data["status"] = -1;//未处理状态
		$id = $this->snsmodel->addtask($data);
		
		$ret = $this->snsmodel->statusesUpdate($content);
		if(empty($ret["error"])){
			$this->snsmodel->updatetask($id,0,"ok",$ret["mid"],$ret["user"]["id"]);
			return $ret;
		}else{
			$this->snsmodel->updatetask($id,$ret["error_code"],$ret["error"],"","");
			return $ret;
		}
	}
	/*
	 *转发一条微博
	* 参数：
	* $id，要转发的微博ID。
	* $status，添加的转发文本，必须做URLencode，内容不超过140个汉字，不填则默认为“转发微博”。
	* 返回字段：http://open.weibo.com/wiki/2/statuses/repost
	*
	*/
	public function statusesRepost($id,$status,$type="sina"){
		$ret = $this->snsmodel->statusesRepost($id,$status);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	/*
	 *删除一条微博
	* 参数：
	* $id，要删除的微博ID。
	* 返回字段：http://open.weibo.com/wiki/2/statuses/destroy
	*
	*/
	public function statusesDestory($id,$type="sina"){
		$ret = $this->snsmodel->statusesDestory($id);
		if(empty($ret["error"])){
			return  $ret;
		}else{
			return $ret;
		}
	}
	/*
	 * 根据微博id获取其基本信息
	* 参数：
	* $mid，微博的id
	* 返回字段：http://open.weibo.com/wiki/2/statuses/show
	*/
	public function statusesShow($mid,$type="sina"){
		$ret = $this->snsmodel->statusesShow($mid,$type);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	/*
	 * 根据一条微博ID获取其转发列表
	* 参数：
	* $page,翻页用，每页20条评论
	* 返回字段：http://open.weibo.com/wiki/2/statuses/repost_timeline
	* 注：此接口最多只返回最新的2000条数据；
	*/
	public function statusesRepostTimeline($mid,$page=1,$type="sina"){
		$ret = $this->snsmodel->statusesRepostTimeline($mid,$page,$type);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	/*
	 * 根据一条微博ID获取其评论列表
	* 参数：
	* $page,翻页用，每页20条评论
	* 返回字段：http://open.weibo.com/wiki/2/comments/show
	* 注：此接口最多只返回最新的2000条数据；
	*/
	public function statusesComment($mid,$page=1,$type="sina"){
		$ret = $this->snsmodel->statusesComment($mid,$page,$type);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	/*
	 * 回复一条微博评论
	* 参数：
	* $cid,评论id
	* $comment 评论内容
	* 返回字段：http://open.weibo.com/wiki/2/comments/reply
	*/
	public function commentsReply($mid,$cid,$comment="",$type="sina"){
		$ret = $this->snsmodel->commentsReply($mid,$cid,$comment,$type);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	/*
	 * 删除一条微博评论
	* 参数：
	* $cid,评论id
	* 返回字段：http://open.weibo.com/wiki/2/comments/destroy
	*/
	public function commentsDestroy($cid,$type="sina"){
		$ret = $this->snsmodel->commentsDestroy($cid,$type);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	/*
	 * 获取sina表情
	* 参数：
	* $emotype 表情类别，face：普通表情、ani：魔法表情、cartoon：动漫表情，默认为face。
	* 返回字段：http://open.weibo.com/wiki/2/emotions
	*/
	public function emotions($emotype="face",$type="sina"){
		$ret = $this->snsmodel->emotions($emotype,$type);
		if(empty($ret["error"])){
			return $ret;
		}else{
			return $ret;
		}
	}
	function get_emotions(){ $url="http://api.t.sina.com.cn/emotions.json?source=1362404091"; 
	     return file_get_contents($url); 
	}
	/**
	 *  图片服务器和代码服务器不在同一台服务器上时候需要调用这个函数处理图片路径
	 *  将图片下载到临时目录
	 *  *php实现下载远程图片到本地
	 *	@param $url       string      远程文件地址
	 *	@param $filename  string      保存后的文件名（为空时则为随机生成的文件名，否则为原文件名）
	 *	@param $fileType  array       允许的文件类型
	 *	@param $dirName   string      文件保存的路径（路径其余部分根据时间系统自动生成）
	 *	@param $type      int         远程获取文件的方式
	 *	@return           json        返回文件名、文件的保存路径
	 */
	private function moveFile($url,$type=0){
		$dirName='D:\AMP\www\dodoca\temp\\';
		if($url == ''){return false;}
		//获取文件原文件名
		$defaultFileName = basename($url);
		//获取文件类型
		$suffix = substr(strrchr($url,'.'), 1);
		//设置保存后的文件名
		$filename = time().rand(0,9).'.'.$suffix;
		
		//获取远程文件资源
		if($type){
			$ch = curl_init();
			$timeout = 40;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			ob_start ();
			curl_exec($ch);
			$file = ob_get_contents ();
			ob_end_clean ();
			curl_close($ch);
		}else{
			ob_start();
			readfile($url);
			$file = ob_get_contents();
			ob_end_clean();
		}
		//设置文件保存路径
		$dirName = $dirName.date("Y-m-d")."\\";
		if(!file_exists($dirName)){
			mkdir($dirName, 0777, true);
		}
		//保存文件
		$res = @fopen($dirName.$filename,'a');
		fwrite($res,$file);
		fclose($res);
		return $dirName.$filename;
	}
}









