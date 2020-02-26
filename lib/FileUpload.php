<?php 

namespace JWorkman\SecureFileUpload;

class FileUpload {

	public $name;
	public $target;
	private $_file;
	private $_allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls'];

	public static function init($file, $name) { return new self($file, $name); }

	public function __construct($file, $name)
	{
		$this->_file = $file;
		$this->name = $name;
	}

	public function upload()
	{
		$target_file = SECURE_FILE_UPLOADS_PATH . basename($this->_file["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		$unique_target = SECURE_FILE_UPLOADS_PATH . '/' . md5(time() . rand(0,99999999) . basename($this->_file["name"])) . '.' . $imageFileType;
		if (!in_array($imageFileType, $this->_allowed)) { return 'File extension "' . $imageFileType . '" not allowed'; }
		if (file_exists($unique_target)) { return 'File already exists!'; }
		if ($this->_file['size'] > 21000000) { return 'File size is too large! Please upload a smaller file.'; }
		if (move_uploaded_file($this->_file['tmp_name'], $unique_target) === true) {
			$this->target = basename($unique_target);
			return true;
		}
		return 'Sorry! Your file could not be uploaded at this time.';
	}
}
