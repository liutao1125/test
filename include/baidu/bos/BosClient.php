<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-19
 * Time: 下午2:11
 */

namespace baidu\bce\bos;

require_once __DIR__ . "/BosClient.php";
require_once __DIR__ . "/util/BosOptions.php";
require_once __DIR__ . "/service/BosHttpClient.php";

require_once dirname(__DIR__) . "/model/stream/BceFileOutputStream.php";
require_once dirname(__DIR__) . "/model/stream/BceFileInputStream.php";
require_once dirname(__DIR__) . "/auth/Auth.php";


use baidu\bce\bos\util\BosOptions;
use baidu\bce\bos\service\BosHttpClient;
use baidu\bce\auth\Auth;
use baidu\bce\http\HttpMethod;
use baidu\bce\bos\model\request\MultipartUploadPartId;
use baidu\bce\exception\BceIllegalArgumentException;
use baidu\bce\model\stream\BceFileOutputStream;
use baidu\bce\model\stream\BceFileInputStream;

class BosClient {
    static function factory(array $config) {
        return new static($config);
    }

    public function putObject(array $options) {
        $response = $this->execute(__FUNCTION__, $options);
        return $response;
    }

    public function getObject(array $options) {
        $response = $this->execute(__FUNCTION__, $options);
        return $response;
    }

    public function deleteObject(array $options) {
        $response = $this->execute(__FUNCTION__, $options);
        return $response;
    }

    public function createBucket(array $options) {
        $response = $this->execute(__FUNCTION__, $options);
        return $response;
    }

    public function deleteBucket(array $options) {
        $response = $this->execute(__FUNCTION__, $options);
        return $response;
    }

    public  function __construct(array $config) {
        $this->ak = $config[BosOptions::ACCESS_KEY_ID];
        $this->sk = $config[BosOptions::ACCESS_KEY_SECRET];
        $this->endpoint = $config[BosOptions::ENDPOINT];

        $this->client_options = $config;
        $this->service_client = new BosHttpClient($config);
    }

    public function lowercaseKeys($array){
        $newArray = array();

        $object_meta_prefix = "x-bce-meta-";
        $object_meta_prefix_size = strlen($object_meta_prefix);

        foreach($array as $key=>$value){
            if("content-length" == strtolower($key)){
                $newArray[strtolower($key)] = $value;
            }if("content-type" == strtolower($key)){
                $newArray[strtolower($key)] = $value;
            }if("content-md5" == strtolower($key)){
                $newArray[strtolower($key)] = $value;
            }if("range" == strtolower($key)){
                $newArray[strtolower($key)] = $value;
            }if("x-bce-content-sha256" == strtolower($key)){
                $newArray[strtolower($key)] = $value;
            }if(strncmp($object_meta_prefix, strtolower($key), $object_meta_prefix_size) == 0){
                $newArray[strtolower($key)] = $value;
            }else{
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }

    protected function execute($method, $options) {
        $options = self::lowercaseKeys($options);

        $command_class_name = ucfirst($method);
        require_once __DIR__ . "/model/request/$command_class_name.php";
        $command_class = 'baidu\\bce\\bos\\model\\request\\'.$command_class_name;
        $command = new $command_class($method);
        $command->setServiceClient($this->service_client);

        $response_class_name = ucfirst($method).'Response';
        require_once __DIR__ . "/model/response/$response_class_name.php";
        $response_class = 'baidu\\bce\\bos\\model\\response\\'.$response_class_name;
        $response = new $response_class($options);

        $command->execute($this->client_options, $options, $response);

        return $response;
    }

    protected $endpoint;
    protected $ak;
    protected $sk;
    protected $charset;

    private $service_client;
}
