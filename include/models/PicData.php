<?php
/*
 * 图片存储表
 * @author xukan
 */
class PicData extends My_EcArrayTable
{
	public $_name ='pic_data';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'org');
		$this->fill_str($para,$data,'thum');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'from_type');
		Return $data;
	}

	public function insert_data($data){
		$data["cdate"]=time();
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		$data["udate"]=time();
		$data["uid"]=get_uid();
		return $this->update($data,$where);
	}

	public function delete_data($id){
		$data["status"]=-1;
		$ret=$this->update_data($data);
		$this->clean_cache($id);
		return $ret;
	}

    /**根据id获取图片url
     * @author sunshichun@dodoca.com
     * @datetime 2015-10-12
     */
    public function getPicById($id){
        if(!$id || !is_numeric($id))return false;
        $where['id'] = $id;
        $ret=$this->find($where,'org');
        return $ret;
    }

	/**
	 * @author sunshichun@dodoca.com
	 * 获取图片单条记录 
	 * $id 图片表pic_data主键
	 * 返回值  数组   exa:  array("id"=>"1","org"=>"http://www.xxx.com/xxx.jpg");
	 */
	public function get_row_byid($id)
	{
		if(!$id || !is_numeric($id))return false;
		$key=CacheKey::getpicdatakey($id);
// 		$data=mc_get($key);
		$data = false;
		if(!$data)
		{
			$data=$this->scalar("*"," where id=".$id);
			if($data)
			{
				$pic_fix=IMG_DOMAIN;
				if($data["from_type"]=='1')//新图片上传
				{
					$pic_fix=IMG_DOMAIN_NEW;
				}
				if($data["org"] && strpos($data["org"],'http://')===false)
				{
					$data["org"]=$data["org"]?$pic_fix.$data["org"]:'';
				}
				if($data["thum"] && strpos($data["thum"],'http://')===false)
				{
					$data["thum"]=$data["thum"]?$pic_fix.$data["thum"]:'';
				}
				mc_set($key,$data,7200);
			}
		}
		return $data;
	}
	
	public function clean_cache($id)
	{
		$key=CacheKey::get_photo_key($id);
		mc_unset($key);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	$file_name : 表单中file的name属性
	$is_create_thum : 是否创建缩略图 1->是 ，0->否
	$w : 创建的缩略图宽度
	$h:  创建的缩略图高度
	返回值：数组   array("org"=>org/1/b9/e73/64e7/4bd979d19b34918fc277f8.jpg,'thum'=>'xxx/xxx/xxx.jpg',"id=>"1")
	 */
	public function upload_file($souce_file,$is_create_thum=0,$w=0,$h=0)
	{
		//$souce_file=$_FILES[$file_name]["tmp_name"];
		$upload=new UploadFile($souce_file);
		$data=array();
		
		if($is_create_thum)
		{
			$rr["thum"]=$upload->get_thum($w,$h);
			$data["thum"]=$rr["thum"]["img_url"];
		}
		
		$rr["org"]=$upload->copy_org();
		if($rr["org"]["result"])
		{
			$data["org"]=$rr["org"]["img_url"];
		}
		$upload->destroy();
		$id=$this->insert_data($data);
		if($id)
		{
			$data["id"]=$id;
		}
		return $data;
	}
	
	/**
	 * 远程图片本地化
	 * $img_url 原来图片的路径
	 * $is_thum 是否要生存缩略图 1->是，0->否
	 * $w 缩略图的宽度
	 * $h 缩略图的高度
	 */
	function down_img($img_url,$is_thum=0,$w=120,$h=120)
	{
		$data["img_url"]=$img_url;
		$data["w"]=$w;
		$data["h"]=$h;
		$url=PubFun::get_down_img_url();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$info = curl_exec($ch);
		//$errno=curl_errno ($ch );
		//$error=curl_error ($ch );
		//s($error);
		$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		$info=json_decode($info,true);
		//PubFun::save_log('id->'.$pic_info["id"].',url->'.$pic_info["org"].',down_url->'.$url.',img_url->'.$img_url,'pic');
		return $info;
		/*
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $img_url);
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			$img_data = curl_exec($ch);
			$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
			curl_close($ch);
			if(!$img_data || strpos($img_data,'head')!==false)
			{
				return false;
			}
			$file_name=time().rand(100,99).".jpg";
			$file_url=$_SERVER["SINASRV_UPLOAD"].'/'.$file_name;
			file_put_contents($file_url,$img_data);
			if(is_file($file_url))
			{
				$pic_info=$this->upload_file($file_url,$is_thum,$w,$h);
				if($pic_info["id"])
				{
					return $pic_info;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
			*/
	}
	
	/**
	 * 图片合成
	 * $src_img 原始图片拒绝路径
	 * $water_pic 二维码图片绝对路径
	 * $txt 显示文字
	 * return array
	 * Array(
	    	[result] => 1
	    	[img_url] => http://img.dodoca.dev/midd/c/fa/b8f/fa7a/58e25f7a94e4cd111d037d.jpg
		)
	 */
	public function merge_pic($src_img,$water_pic='',$txt='')
	{
		if(!$src_img)return false;
	    //$img = new Imagick($src_img);
		//return $img->get_water(874,1240,$water_pic,$txt);
		$data["img_url"]=$src_img;
		$data["img_url_water"]=$water_pic;
		$data["img_txt"]=$txt;
		
		$url=PubFun::get_merge_img_url();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content =  curl_exec( $ch );
		curl_close($ch);
		$rs=json_decode($content,true);
		return $rs;
		
	}
	
	/**
	 * 图片、文件上传
	 * $filename ->文件绝对路径 (H:/55.jpg)
	 */
	function post_file($filename)
	{
		$url=PubFun::get_upload_url();
        error_reporting(0);
		$fields['Filedata'] = '@'.$filename;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
		$content =  curl_exec( $ch );
		curl_close($ch);
		
//
		$rs=json_decode($content,true);
		
		@unlink($filename);
		return $rs;
	}
	
	//本地化临时图片
	function local_img($img_url)
	{
		if(!$img_url)return;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $img_url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$img_data = curl_exec($ch);
		$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$img_data || strpos($img_data,'head')!==false)
		{
			return false;
		}
		$file_name=time().rand(100,99).".jpg";
		$dir=$_SERVER["SINASRV_UPLOAD"].'/qunfa';
		if(!is_dir($dir))
		{
			mkdir($dir, 0777);
		}
		$dir=$_SERVER["SINASRV_UPLOAD"].'/qunfa/'.date("Y-m-d");
		if(!is_dir($dir))
		{
			mkdir($dir, 0777);
		}
		$file_url=$dir.'/'.$file_name;
		file_put_contents($file_url,$img_data);
		if(is_file($file_url))
		{
			return $file_url;
		}
		else
		{
			return '';
		}
	}

		
}
?>