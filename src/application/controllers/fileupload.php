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

class Fileupload extends CI_Controller {
	
	private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){
    
    	parent::__construct();   
       
    }
    
	function index()
	{
	
		$this->load->library('session');
	
		if ($this->input->get('token') AND $this->input->get('licence'))
		{
		
			$this->load->model('files_model');
		
			if ($token = $this->session->userdata('form_' . $this->input->get('token')))
			{

				$this->load->helper('fileupload');
				
				// list of valid extensions, ex. array("jpeg", "xml", "bmp")
				$allowedExtensions = array();
				// max file size in bytes
				$sizeLimit = 524288000;
				
				$file_id = $this->files_model->get_file_id();
				
				$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
				$result = $uploader->handleUpload($this->config->item('orbital_storage_directory') . '/', $file_id);
				
				if ($this->input->get('public') === 'public')
				{
					$file_visibility = 'public';
				}
				else
				{
					$file_visibility = 'private';
				}
				
				if ($result['success'] === TRUE)
				{
					$this->load->helper('file');
					$this->files_model->add_file(
							$file_id,
							$this->input->get('qqfile'),
							strtolower(substr(strrchr($this->input->get('qqfile'), '.'), 1)),
							get_mime_by_extension($this->input->get('qqfile')),
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
			else
			{
				$this->output->set_output(json_encode(array('error' => 'Unable to validate upload token.')));
			}
		}
		else
		{
			$this->output->set_output(json_encode(array('error' => 'Missing required elements.')));
		}
	}
	
	function form()
	{
	
		if ($this->input->get('token') && $this->input->get('licence'))
		{
		
			$this->load->model('files_model');
	
			if ($tokendata = $this->files_model->validate_upload_token($this->input->get('token')))
			{
			
				$this->load->library('session');
				$formtoken = random_string('alnum', 16);
				$this->session->set_userdata('form_' . $formtoken, $tokendata);
			
				$data['token'] = $formtoken;
				$data['licence'] = (int) $this->input->get('licence');
				$this->load->view('uploader', $data);
			}
			else
			{
				show_404();
			}
		}
		else
		{
			echo 'Missing parameter.';
		}
	}
				
}

// End of file projects.php
// Location: ./controllers/projects.php