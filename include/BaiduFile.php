<?php
/**
 * 图片上传处理
 */
class BaiduFile
{
		public  $Access_Key_ID;   	
		private $Secret_Access_Key ; 		


		function __construct($src_file) {
			$this->Access_Key_ID='ab1ea31258dc4ff99a8ab19c770ea8d3';
			$this->Secret_Access_Key='e82a56f2b0e64468bc5cf8a8d0fe3683';
		}
		
		function file_upload()
		{
			require_once "/baidu/bos/BosClient.php";
			require_once  "/baidu/bos/util/BosOptions.php";
			//use baidu\bce\bos\util\BosOptions;
            //use baidu\bce\bos\BosClient;
            $client_options = array();
            $client_options[BosOptions::ENDPOINT] = "your.endpoint:8080";
            $client_options[BosOptions::ACCESS_KEY_ID] = $this->Access_Key_ID;
            $client_options[BosOptions::ACCESS_KEY_SECRET] = $this->Secret_Access_Key;
            $bos_client = BosClient::factory($client_options);
		}
		
		function t($bucket_name, $object_name, $file_name)
		{
			//$request = array();
			//$bucket_name = "dodoca-yun";
			//$request[BosOptions::BUCKET] = $bucket_name;
			//$response = $bos_client‐>createBucket($request);
			//return $response;
			
			require_once  "/baidu/model/stream/BceFileInputStream.php";
			//use baidu\model\stream;

            //$options = array();
            //$options[BosOptions::BUCKET] = $bucket_name;
            //$options[BosOptions::OBJECT] = $object_name;
            //$options[BosOptions::OBJECT_CONTENT_STREAM] = new BceFileInputStream(($file_name);
            //$s=$bos_client‐>putObject($options);
            //s($s);

		}
		
}



?>