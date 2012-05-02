<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Files
 *
 * Gets a list of all projects a user has access to.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class FileUploader extends CI_Controller {
	
	private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){
    
    	parent::__construct();   
       
    }
    
	function index()
	{
	
		if ($this->input->get('upload_token') AND $this->input->get('return_uri') AND $this->input->get('licence'))
		{
		
			$this->load->model('files_model');
		
			if ($token = $this->files_model->validate_upload_token($this->input->get('upload_token')))
			{

				$this->load->helper('fileupload');
				
				// list of valid extensions, ex. array("jpeg", "xml", "bmp")
				$allowedExtensions = array();
				// max file size in bytes
				$sizeLimit = 524288000;
				
				$file_id = $this->files_model->get_file_id();
				
				$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
				$result = $uploader->handleUpload($this->config->item('orbital_storage_directory') . '/', $file_id);
				
				var_dump($result);
				
				if ($result['success'] === TRUE)
				{
					$this->files_model->add_file(
							$file_id,
							$file_data['client_name'],
							substr($file_data['file_ext'], 1),
							$file_data['file_type'],
							$token['project'],
							(int) $this->input->get('licence'),
							$file_visibility,
							'staged',
							$token['user']
						);
				}
				// to pass data through iframe you will need to encode all html tags
				$this->output->set_output(json_encode($result));
			}
			$this->output->set_output(json_encode(array('error' => 'Unable to validate upload token.')));
		}
		$this->output->set_output(json_encode(array('error' => 'Missing required elements.')));
	}
	
	function form()
	{
		$this->load->view('uploader');
	}
				
}

// End of file projects.php
// Location: ./controllers/projects.php