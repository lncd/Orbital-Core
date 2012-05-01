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

class Files extends Orbital_Controller {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	public function file_view_get($identifier)
	{
		$this->load->model('files_model');
		
		$file = $this->files_model->file_get_details($identifier);

		if ($file['visibility'] === 'public')
		{
			$response->status = TRUE;
			$response->file = $this->files_model->file_get_details_public($identifier);
			$this->response($response, 200);
		}
		else
		{
			if ($user = $this->access->valid_user(array('projects')))
			{
				$response->status = TRUE;
				$response->file = $this->files_model->file_get_details($identifier);
				$this->response($response, 200);
			}
		}
	}
	
	public function get_otk_get($identifier)
	{
		$this->load->model('files_model');
		$this->load->model('projects_model');
		
		$file = $this->files_model->file_get_details($identifier);

		if ($file['visibility'] === 'public')
		{
			$response->status = TRUE;
			$response->otk = $this->files_model->get_otk($identifier);
			$this->response($response, 200);
		}
		else
		{
			if ($user = $this->access->valid_user(array('archivefiles_read')))
			{
				if ($this->projects_model->get_permissions_project_user($user, $file['project']))
				{
					$response->status = TRUE;
					$response->otk = $this->files_model->get_otk($identifier);
					$this->response($response, 200);
				}
			}
		}
	}
	
	public function download_get($identifier)
	{
		$this->load->model('files_model');
		
		if ($this->files_model->validate_otk($this->get('otk'), $identifier))
		{
			$file = $this->files_model->file_get_details($identifier);
			$expires = time() + 60;
			$path = '/v1/MossoCloudFS_e4c5ab67-0b7a-4095-999c-32aaf03a6886/project:' . $file['project'] . '/'. $identifier .'.'. $file['extension'];
			$key = hash_hmac('sha1', "GET\n$expires\n$path", $_SERVER['RACKSPACE_METADATAKEY']);
			$this->output->set_header('Location: ' . 'https://storage101.lon3.clouddrive.com' . $path . '?temp_url_sig=' . $key . '&temp_url_expires=' . $expires);

		}
	}
	
	function upload_post()
	{
	
		if ($this->post('upload_token') AND $this->post('return_uri') AND $this->post('licence'))
		{
		
			$this->load->model('files_model');
		
			if ($token = $this->files_model->validate_upload_token($this->post('upload_token')))
			{
	
				$allowed_types = array(
					'csv',
					'doc',
					'docx',
					'gif',
					'jpg',
					'md',
					'pdf',
					'png',
					'rar',
					'sql',
					'txt',
					'xls',
					'xlsx',
					'xml',
					'zip',
				);
				
				$file_id = $this->files_model->get_file_id();
			
				$config['upload_path'] = $this->config->item('orbital_storage_directory') . '/';
				$config['allowed_types'] = implode('|', $allowed_types);
				$config['max_size']	= '204800';
				$config['file_name'] = $file_id;
		
				$this->load->library('upload', $config);
		
				if ($this->upload->do_upload('file'))
				{
					
					$file_data = $this->upload->data();
					
					if ($this->post('public') === 'public')
					{
						$file_visibility = 'public';
					}
					else
					{
						$file_visibility = 'private';
					}
					
					if ($this->files_model->add_file(
						$file_id,
						$file_data['client_name'],
						substr($file_data['file_ext'], 1),
						$file_data['file_type'],
						$token['project'],
						(int) $this->post('licence'),
						$file_visibility,
						'staged',
						$token['user']
						
					))
					{
						$this->output->set_header('Location: ' . $this->post('return_uri') . '?message=Upload%20successful.');
					}
					else
					{
						$this->output->set_header('Location: ' . $this->post('return_uri') . '?error=Something%20went%20wrong.');
					}
				}
				else
				{
					$this->output->set_header('Location: ' . $this->post('return_uri') . '?error=' . urlencode($this->upload->display_errors()));
				}
			}
			else
			{
				$response->status = FALSE;
				$response->message = 'An invalid upload token was presented.';
				$this->response($response, 401);
			}
		}
		else
		{
			$response->status = FALSE;
			$response->message = 'No upload token was presented.';
			$this->response($response, 401);
		}
	}
	
	function process_queue()
	{
		if ($queued_files = $this->db
			->where('file_upload_status', 'staged')
			->order_by('staged')
			->get('archive_files'))
			{
				foreach($queued_files as $queued_file)
				{
					$this->load->library('storage/storage_rackspacecloud');
					
					//Upload
					if ($this->storage_rackspacecloud->save($this->config->item('orbital_storage_directory') . $queued_file['file_id'] . '.' . $queued_files['file_extension'], $queued_files['file_project'], '', $queued_file['file_project']))
					{
						//Delete local copy
						unlink($this->config->item('orbital_storage_directory') . $queued_file['file_id'] . '.' . $queued_files['file_extension']);
						return TRUE;						
					}
				
				}
			}
	
		// Connect
		$auth = new CF_Authentication($USERNAME, $KEY);
		$auth->authenticate();
		$conn = new CF_Connection($auth);
		
		
	}
}

// End of file projects.php
// Location: ./controllers/projects.php