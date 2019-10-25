<?php
/**
 * Plugin Name:  Secure File Uploads
 * Plugin URI:   https://github.com/jworkman/secure-file-uploads
 * Description:  Secure File Uploads
 * Version:      1.0.1
 * Author:       Justin Workman
 * Author URI:   https://github.com/jworkman
 * License:      GPLv2 or later
 */

require_once __DIR__ . '/lib/Response.php';
require_once __DIR__ . '/lib/FileUpload.php';
use JWorkman\SecureFileUpload\Response;
use JWorkman\SecureFileUpload\FileUpload;

// Only load class if it hasn't already been loaded.
if ( ! class_exists( 'SecureFileUploadPlugin' ) ) {
	class SecureFileUploadPlugin {
		public static function init() { return new self(); }
		
		public function forbidden() 
		{
			return new WP_REST_Response('You are not allowed to access this page.', 403);
		}
		public function upload()
		{
			$response = new Response();
			$files = $this->getUploadedFiles();
			if ( count($files) < 1 ) {
				return $response->set(422, 'No file was specified.');
			}
			foreach ($files as $f) {
				$uploaded = $f->upload();
				if ($uploaded !== true) { return $response->set(422, $uploaded); }
			}
			$response->set(200, 'Success...');
			$response->files = $files;
			return $response;
		}

		public function getUploadedFiles()
		{
			$files = [];
			foreach($_FILES as $name => $file) {
				$f = new FileUpload($file, $name);
				array_push($files, $f);
			}
			return $files;
		}
		public function read() 
		{
			if(!isset($_GET['k']) || $_GET['k'] !== SFU_API_SECURE_KEY || !isset($_GET['f'])) {
				return $this->forbidden();
			}
			$filename = basename($_GET['f']);
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Content-Type: application/octet-stream');
			readfile(SECURE_FILE_UPLOADS_PATH . '/' . $filename);
			exit;
		}
	}

	function sfu_upload_file() { return SecureFileUploadPlugin::init()->upload(); }
	function sfu_get_file() { return SecureFileUploadPlugin::init()->read(); }
}

// Now we add our endpoints
add_action( 'rest_api_init', function () {
	register_rest_route( 'secure-file-uploads/v1', '/upload', array(
		'methods' => 'POST',
		'callback' => 'sfu_upload_file',
	) );
} );

// Now we add our endpoints
add_action( 'rest_api_init', function () {
	register_rest_route( 'secure-file-uploads/v1', '/get', array(
		'methods' => 'GET',
		'callback' => 'sfu_get_file',
	) );
} );

