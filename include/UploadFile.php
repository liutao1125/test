<?php
/**
 * 图片上传处理
 */
class UploadFile
{
		public  $src_mw;   	//magickwand 对象
		private $src_file; 		//源文件路径
		private $src_width; 	//源文件高度
		private $src_height; 	//源文件宽度
		private $src_ext;		//源文件扩展名
		private $src_format;	//图片格式
		private $is_img;		//源文件是否存在
		private $pic_base_url;  //nsf图片路径
		public $md5_file_name; //文件md5值
		public $file_ext;     // 文件扩展名

		function __construct($src_file) {
			$this->src_file = $src_file;
			$this->src_mw   = NewMagickWand();
			$this->is_img=MagickReadImage($this->src_mw,$src_file);
			$this->format = MagickGetImageFormat($this->src_mw);
			$this->src_width =MagickGetImageWidth($this->src_mw);
			$this->src_height =MagickGetImageHeight($this->src_mw);
			$this->pic_base_url=$_SERVER["SINASRV_UPLOAD"];
			if($this->is_img)
			{
				$this->md5_file_name=md5_file($this->src_file);
			}
			else
			{
				$this->md5_file_name='';
			}
			$this->init_ext();
			DestroyMagickWand($this->src_mw);
		}
		/**
		 * 裁图
		 * $pix->裁剪什么图片  midd / thum / small
		 * $Width ->缩略图片宽度
		 * $Width ->缩略图片高度
		 * $bgcolor->背景色
		 * $watermark->水印图片
   		 * $padding_left-> 水印图片左边距离
   		 * $padding_top->  水印图片顶部距离
		 */
		function create_imge($pix,$Width, $Height, $bgcolor = "#FFFFFF", $watermark = "", $padding_left = 20,$padding_top=20) {
		   $return_info['result']=0;
		   if (!$this->is_img)
		   {
		   	  $this->write_log('is_img->没有文件');
		      return $return_info;
		   }
		
		   $mymagickwand= NewMagickWand();
		   MagickReadImage($mymagickwand,$this->src_file);

		   $srcW = MagickGetImageWidth($mymagickwand);
		   $srcH = MagickGetImageHeight($mymagickwand);
		   $srcR = $this->src_width/$this->src_height;
		   $dstR = $Width/$Height;
		   if ($Width<=0 || $Height <=0)
		   {
		     $newW = $srcW; //新图片高度
		     $newH = $srcH; //新图片宽度
		   }
		   else
		   {
		     if($Width > $srcW)
		     {
		       if ($Height > $srcH)
		       {
		         $newW = $srcW;
		         $newH = $srcH;
		       }
		       else
		       {
		         $newH = $Height;
		         $newW = round($newH*$srcR);
		       }
		     }
		     else
		     {
		       if ($dstR > $srcR)
		       {
		         $newH = $Height;
		         $newW = round($newH*$srcR);
		       }
		       else
		       {
		         $newW = $Width;
		         $newH = round($newW/$srcR);
		       }
		     }
		   }
		
		   //图片类型
			if($this->file_ext=='')
			{
		   		$this->write_log('图片格式不对->'.$this->file_ext);
		    	return $return_info;
		   	}
		   	
		   //生成背景图
		   $bgmagickwand = NewMagickWand();
		   //MagickNewImage($bgmagickwand,$Width,$Height,$bgcolor);
		   MagickNewImage($bgmagickwand,$newW,$newH,$bgcolor);
		  
		   MagickSetFormat($bgmagickwand,$this->format);
		
		   //缩放原图并合并到背景图上
		   MagickScaleImage($mymagickwand, $newW, $newH);
		   MagickCompositeImage($bgmagickwand, $mymagickwand, MW_OverCompositeOp, 0, 0);
		  
		   //处理水印图
		   if ($watermark && is_file($watermark))
		   {
			     MagickRemoveImage($mymagickwand);
			     $padding = intval($padding);
			     if (MagickReadImage($mymagickwand, $watermark))
			     {
			         $wmL = $newW-$padding_left;
			         $wmT = $newH-$padding_top;
			         MagickCompositeImage($bgmagickwand,$mymagickwand, MW_OverCompositeOp, $wmL,$wmT);
			     }
		   }
		   
		   $arr=$this->explode_md5();
		   $nfs_url= '/'.$pix.'/'.$arr[1].'/'.$arr[2].'/'.$arr[3].'/'.$arr[4].'/';
		
		   $full_url=$this->pic_base_url.$nfs_url;//文件完整路径
		   $this->make_dir($full_url);//创建目录
		  
		   $dstFilename = $full_url.$arr[5].".".$this->file_ext;//完整文件名称

		   MagickWriteImage($bgmagickwand, $dstFilename);
		   DestroyMagickWand($bgmagickwand);
		   DestroyMagickWand($mymagickwand);
		   $return_info['result']=1;
		   $return_info["img_url"] = str_replace($this->pic_base_url,'',$dstFilename);
		   
		   return $return_info;
		}
		
		/**
		 * 初始化扩展名
		 */
		function init_ext()
		{
			$srcT =$this->format;
			if ($srcT == "JPEG")
			{
				$this->file_ext = "jpg";
			}
			elseif ($srcT == "GIF")
			{
				$this->file_ext = "gif";
			}
			elseif ($srcT == "PNG")
			{
				$this->file_ext = "png";
			}
			elseif ($srcT == "BMP")
			{
				$this->file_ext = "bmp";
			}
			else
			{
				$this->file_ext = "";
			}
		}
		/**
		 * 拷贝原图
		 */
		function copy_org()
		{
			if(!$this->is_img)return false;
			if(!$this->md5_file_name)return false;
			$arr=$this->explode_md5();
			$dir=$this->pic_base_url.'/org/'.$arr[1].'/'.$arr[2].'/'.$arr[3].'/'.$arr[4].'/';
			if(!is_dir($dir))//原图目录不存在
			{
				$this->make_dir($dir);
			}
			$df=$dir.$arr[5].'.'.$this->file_ext;
			$up_status=0;
			if(is_uploaded_file($this->src_file))//http
			{
				$up_status=move_uploaded_file($this->src_file,$df);
			}
			else
			{
				$up_status=copy($this->src_file,$df);
			}
			if(!$up_status){
				$return_info['result']=0;
				$this->write_log('copy_org->上传原图失败(src:'.$this->src_file.',df->'.$df.')');
			}
			else
			{
				$return_info['result']=1;
				$return_info["img_url"] = str_replace($this->pic_base_url,'',$df);
			}
			return $return_info;
		}
		
		//水印图
		function get_water($width=600,$height=450)
		{
			return $this->create_imge('midd',$width, $height,  "#FFFFFF",  "water.png",130,60);
		}
		//中图
		function get_thum($width=400,$height=250)
		{
			return $this->create_imge('thum',$width, $height);
		}
		//小区（缩略图）
		function get_small($width=100,$height=74)
		{
			return $this->create_imge('small',$width, $height);
		}
		
		/**
		 * $pix: org / midd /thum /small
		 */
		function check_file_exists($pix)
		{
			$file=$this->get_url_by_md5($pix);
			if (is_file($file))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		/**
		 * 获取图片全路径
		 */
		function get_url_by_md5($pix)
		{
			if(!$this->md5_file_name)return false;
			$arr=$this->explode_md5();
			return $this->pic_base_url.'/'.$pix.'/'.$arr[1].'/'.$arr[2].'/'.$arr[3].'/'.$arr[4].'/'.$arr[5].'.'.$this->file_ext;
		}
		/**
		 * 拆分md5字符串
		 */
		function explode_md5()
		{
			if(!$this->md5_file_name)return false;
			$arr[1]=substr($this->md5_file_name, 0, 1);
			$arr[2]=substr($this->md5_file_name, 1, 2);
			$arr[3]=substr($this->md5_file_name, 3, 3);
			$arr[4]=substr($this->md5_file_name, 6, 4);
			$arr[5]=substr($this->md5_file_name, 10);
			return $arr;
		}
		
	
		
		/**
		 * 创建目录
		 */
		function make_dir($dir='')
		{
			return is_dir($dir) or ($this->make_dir(dirname($dir)) and mkdir($dir, 0777));
		}
		
		/**
		 * 写日志
		 */
		function write_log($txt,$log_file_name='uploadpic')
		{
		    PubFun::save_log($txt,$log_file_name);
		}
		
		/**
		 * 清除临时文件
		 */
		public function destroy()
		{
			if(is_file($this->src_file)) @unlink($this->src_file);
		}
}



?>