<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * archive storage
 *
 * Stores files in archive.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @link       https://github.com/lncd/Orbital-Core
 */

class Archive extends Orbital_Controller
{
	public function form()
	{
		$this->load->helper('form');
		$this->load->view('archive_upload');
	}

	/**
	 * Upload file
	 *
	 * Uploads file to archive
	 *
	 * @access public
	 */

	public function upload()
	{
		if ($user = $this->access->valid_user(array('archivefiles_write')))
		{
			if($this->input->post('project_id'))
			{
				$this->load->model('projects_model');

				//Check project exists
				if($this->projects_model->get_project($this->input->post('project_id')))
				{
					//Check user has permissions
					if ( $this->access->user_has_permission($user, 'project', 'archivefiles_write', $this->input->post('project_id')))
					{
						$response->user = $user;
						$response->post = $this->input->post();
						$response->file = $_FILES['userfile'];
						$config['upload_path'] = $this->config->item('orbital_storage_directory');
						$config['allowed_types'] = 'gif|jpg|png';
						$config['encrypt_name'] = TRUE;

						$this->load->library('upload', $config);

						if ( ! $this->upload->do_upload())
						{
							//Upload Error
							$response->error = array('error' => $this->upload->display_errors());

							$this->response($response, 500); // 500 being the HTTP response code
						}
						else
						{
							//Upload Success
							$response->data = array('upload_data' => $this->upload->data());

							$this->response($response, 200); // 200 being the HTTP response code
						}
					}
				}
				else
				{
					//If project does not exist
					show_404();
				}
			}
			else
			{
				//If parameters are missing
				$this->output
				->set_status_header('400')
				->set_output(json_encode(array(
							'error' => 'missing_parameters',
							'error_description' => 'This request is missing required parameters.'
						)));
			}
		}
	}
	
	/**
	 * Upload file via jquery
	 *
	 * Uploads file to archive via jquery
	 *
	 * @access public
	 */
	
	function jqueryupload()
	{
		$this->load->library('Uploadhandler');
		$upload_handler = new UploadHandler(array('upload_dir' => $this->config->item('orbital_storage_directory') . '/'));
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Content-Disposition: inline; filename="files.json"');
		header('X-Content-Type-Options: nosniff');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

		switch ($_SERVER['REQUEST_METHOD']) {
		case 'OPTIONS':
			break;
		case 'HEAD':
		case 'GET':
			$upload_handler->get();
			break;
		case 'POST':
			if (isset($_REQUEST['_method']) AND $_REQUEST['_method'] === 'DELETE') {
				$upload_handler->delete();
			} else {
				$upload_handler->post();
			}
			break;
		case 'DELETE':
			$upload_handler->delete();
			break;
		default:
			header('HTTP/1.1 405 Method Not Allowed');
		}
	}
}

// End of file archive.php