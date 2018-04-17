<?php

/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-25
 * Time: 上午10:31
 */
namespace baidu\bce\http;

class HttpRequest {
	private $http_method;
	private $host;
	private $uri;
	private $query_string;
	private $headers;
	private $input_stream;
	
	function __construct() {
		$this->headers = array ();
	}
	
	/**
	 *
	 * @param mixed $host        	
	 */
	public function setHost($host) {
		$this->host = $host;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getHost() {
		return $this->host;
	}
	
	/**
	 *
	 * @param mixed $query_string        	
	 */
	public function setQueryString($query_string) {
		$this->query_string = $query_string;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getQueryString() {
		return $this->query_string;
	}
	
	/**
	 *
	 * @param mixed $uri        	
	 */
	public function setUri($uri) {
		$this->uri = $uri;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getUri() {
		return $this->uri;
	}
	
	/**
	 *
	 * @param mixed $headers        	
	 */
	public function setHeaders($headers) {
		$this->headers = $headers;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getHeaders() {
		return $this->headers;
	}
	
	/**
	 *
	 * @param mixed $input_stream        	
	 */
	public function setInputStream($input_stream) {
		$this->input_stream = $input_stream;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getInputStream() {
		return $this->input_stream;
	}
	
	/**
	 *
	 * @param mixed $http_method        	
	 */
	public function setHttpMethod($http_method) {
		$this->http_method = $http_method;
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function getHttpMethod() {
		return $this->http_method;
	}
	public function addHttpHeader($key, $data) {
		// array_push($this->headers, sprintf("%s:%s", $key, $data));
		$this->headers [$key] = $data;
	}
} 