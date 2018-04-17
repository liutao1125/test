<?php

/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-25
 * Time: 上午10:40
 */
namespace baidu\bce\http;

require_once dirname(__DIR__) . "/model/stream/BceBaseStream.php";
use baidu\bce\model\stream\BceBaseStream;

class HttpResponse {
	private $http_version;
	private $http_code;
	private $reason_phrase;
	private $http_headers;
	private $output_stream;
	private $has_recv_status_line;
	
	function __construct($output_stream) {
		$this->output_stream = $output_stream;
		$this->has_recv_status_line = false;
		$this->http_headers = array ();
	}
	
	function writeHeader($curl_handle, $raw_header_line) {
		$header_line = trim ( $raw_header_line, "\n\r" );

		if (strlen ( $header_line ) == 0) {
			return strlen ( $raw_header_line );
		}
		if ($this->has_recv_status_line) {
			$pair = explode ( ":", $header_line, 2 );
			$key = trim ($pair [0] );

			$value = trim ($pair [1] );
            $value = trim ($value, "\"" );
			if ($key == "Content-Length" && $this->output_stream instanceof BceBaseStream) {
				$this->output_stream->reserve ( ( int ) $value );
			}

			$this->http_headers [$key] = $value;
		} else {
			$status_line_items = explode ( " ", $header_line, 3 );
			$this->http_version = $status_line_items [0];
			$this->http_code = ( int ) $status_line_items [1];
			$this->reason_phrase = $status_line_items [2];
			if($this->http_code>=200){
				$this->has_recv_status_line = true;
			}
		}
		
		return strlen ( $raw_header_line );
	}
	
	function writeBody($curl_handle, $data) {
		if($this->output_stream instanceof BceBaseStream){
			return $this->output_stream->write ( $data );
		}
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function getHasRecvStatusLine() {
		return $this->has_recv_status_line;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getHttpCode() {
		return $this->http_code;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getHttpHeaders() {
		return $this->http_headers;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getHttpVersion() {
		return $this->http_version;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getOutputStream() {
		return $this->output_stream;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getReasonPhrase() {
		return $this->reason_phrase;
	}
	public function getResponseHeader($key) {
		if (array_key_exists ( $key, $this->http_headers )) {
			return $this->http_headers [$key];
		}
		
		return "";
	}
} 