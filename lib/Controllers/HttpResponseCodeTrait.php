<?php
namespace CLMVC\Controllers;

/**
 * Class HttpResponseCodeTrait
 * @package CLMVC\Controllers
 */
trait HttpResponseCodeTrait {
	/**
	 * @var int
	 */
	private $http_response_code = 200;
	/**
	 * @return int
	 */
	public function getHttpResponseCode() {
		return $this->http_response_code;
	}

	/**
	 * @param int $http_response_code
	 */
	public function setHttpResponseCode( $http_response_code ) {
		$this->http_response_code = $http_response_code;
	}
}