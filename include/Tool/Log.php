<?php
class Tool_Log {
	const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages
	static public function getInstance(){
		static $logger;
		if(!($logger instanceof Zend_Log)) {
            $file = self::getLogFileName();
			$writer = new Zend_Log_Writer_Stream($file);
			$logger = new Zend_Log($writer);
		}
		return $logger;
	}

    /**
     * 设置日志文件目录
     * @return string
     */
	static public function getLogFileName()
	{
		$dir = $_SERVER['SINASRV_LOG_DIR'];
		$dir = is_dir($dir) ? $dir : $_SERVER['TEMP'];
		$dir .= PHP_SAPI == 'cli' ? '/cli/' : '/php/';
		$dir .= date('Ymd', SYS_TIME);

		if(!is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}
		if(PHP_SAPI == 'cli')
		{
			$dir .= isset($_SERVER['argv'][1]) ? sprintf('/%s.php.log',strtolower($_SERVER['argv'][1])) : '/php.log';;
			return $dir;
		}
		else
		{
			return $dir . sprintf('/%s.log', date('H', SYS_TIME));
		}

	}

	/**
	 * 写日志
	 * @param $msg
	 * @param $type
	 * @throws Zend_Log_Exception
	 */
	static function write($msg, $type){
		return self::getInstance()->log($msg, $type);
	}
	static public function info($msg){
		return self::write($msg, self::INFO);
	}
	static public function err($msg){
		return self::write($msg, self::ERR);
	}
	static public function debug($msg){
		return self::write($msg, self::DEBUG);
	}
}
