<?php 

namespace JWorkman\SecureFileUpload;


class Response {
	public $code = 500;
	public $msg = "Sorry we could not upload your file at this time. Please try another file.";
	public $files = [];
	public function set($code, $msg) {
		$this->code = $code;
		$this->msg = $msg;
		return $this;
	}
}
