<?php

/**
 * Handle file uploads via XMLHttpRequest
 *
 * PHP Version 5
 *
 * @category   Helper
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @license    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Qquploadedfilexhr
{
	/**
	 * Save the file to the specified path
	 *
	 * @param string $path The path of the file to save
	 *
	 * @return boolean TRUE on success
	 */

	function save($path)
	{
		$input = fopen('php://input', 'r');
		$temp = tmpfile();
		$real_size = stream_copy_to_stream($input, $temp);
		fclose($input);

		if ($real_size !== $this->getSize())
		{
			return FALSE;
		}

		$target = fopen($path, 'w');
		fseek($temp, 0, SEEK_SET);
		stream_copy_to_stream($temp, $target);
		fclose($target);

		return TRUE;
	}

	/**
	 * Gets name of file
	 *
	 * @return string File name
	 */

	function getName()
	{
		return $_GET['qqfile'];
	}
	
	/**
	 * Gets size of file
	 *
	 * @return int
	 * @throws Exception
	 */

	function getSize()
	{
		if (isset($_SERVER['CONTENT_LENGTH'])){
			return (int)$_SERVER['CONTENT_LENGTH'];
		}
		else
		{
			throw new Exception('Getting content length is not supported.');
		}
	}
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 *
 * @category   Helper
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @license    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Qquploadedfileform {

	/**
	 * Save the file to the specified path
	 *
	 * @param string $path the path of the file
	 *
	 * @return boolean TRUE on success
	 */

	function save($path)
	{
		if( ! move_uploaded_file($_FILES['qqfile']['tmp_name'], $path))
		{
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Gets name of file
	 *
	 * @return mixed
	 */

	function getName()
	{
		return $_FILES['qqfile']['name'];
	}

	/**
	 * Gets size of file
	 */

	function getSize()
	{
		return $_FILES['qqfile']['size'];
	}
}

/**
 * Handle file uploads
 *
 * @category   Helper
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @license    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Qqfileuploader {

	/**
	 * Allowed file extensions.
	 *
	 * @var array @allowedExtensions Allowed file extensions.
	 */
	private $allowed_extensions = array();
	
	/**
	 * File size limit.
	 *
	 * @var int @size_limit Allowed file size.
	 */
	private $size_limit = 10485760;

	/**
	 * File to be processed.
	 *
	 * @var string @file File to be processed.
	 */
	private $_file;

	/**
	 * Construct
	 */

	function __construct(array $allowed_extensions = array(), $size_limit = 10485760)
	{
		$allowed_extensions = array_map('strtolower', $allowed_extensions);

		$this->allowedExtensions = $allowed_extensions;
		$this->sizeLimit = $size_limit;

		$this->checkServerSettings();

		if (isset($_GET['qqfile']))
		{
			$this->file = new qqUploadedFileXhr();
		}
		elseif (isset($_FILES['qqfile']))
		{
			$this->file = new qqUploadedFileForm();
		}
		else
		{
			$this->file = FALSE;
		}
	}

	/**
	 * Checks settings on the server
	 *
	 * @return NULL
	 */

	private function checkServerSettings()
	{
		$post_size = $this->toBytes(ini_get('post_max_size'));
		$upload_size = $this->toBytes(ini_get('upload_max_filesize'));

		if ($post_size < $this->sizeLimit OR $upload_size < $this->sizeLimit)
		{
			$size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
			die("{'error':'increase post_max_size AND upload_max_filesize to {$size}'}");
		}
	}

	/**
	 * Convert string to bytes
	 *
	 * @param $str String to convert to bytes
	 * @return $var bytes
	 */

	private function toBytes($str)
	{
		$val = trim($str);
		$last = strtolower($str[strlen($str)-1]);
		switch($last) {
		case 'g': $val *= 1024;
		case 'm': $val *= 1024;
		case 'k': $val *= 1024;
		}
		return $val;
	}

	/**
	 * Returns array('success'=>true) OR array('error'=>'error message')
	 *
	 * @param string  $upload_directory The directory the file is uploaded to
	 * @param string  $file_id          The identifier of the file
	 * @param boolean $replace_old_file If the previous file should be replaced or not
	 *
	 * @return array
	 */
 
	function handleUpload($upload_directory, $file_id, $replace_old_file = FALSE)
	{
		if ( ! is_writable($upload_directory))
		{
			return array('error' => "Server error. Upload directory isn't writable.");
		}

		if ( ! $this->file)
		{
			return array('error' => 'No files were uploaded.');
		}

		$size = $this->file->getSize();

		if ($size === 0)
		{
			return array('error' => 'File is empty');
		}

		if ($size > $this->sizeLimit)
		{
			return array('error' => 'File is too large');
		}

		$pathinfo = pathinfo($this->file->getName());
		$filename = $file_id;
		//$filename = md5(uniqid());
		$ext = $pathinfo['extension'];

		if($this->allowedExtensions AND ! in_array(strtolower($ext), $this->allowedExtensions))
		{
			$these = implode(', ', $this->allowedExtensions);
			return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
		}

		if( ! $replace_old_file)
		{
			/// don't overwrite previous files that were uploaded
			while (file_exists($upload_directory . $filename . '.' . $ext))
			{
				$filename .= rand(10, 99);
			}
		}

		if ($this->file->save($upload_directory . $filename . '.' . $ext))
		{
			return array('success'=>TRUE);
		}
		else
		{
			return array('error'=> 'Could not save uploaded file. The upload was cancelled, OR server error encountered');
		}
	}
}

// End of file fileupload_helper.php