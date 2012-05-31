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

	/**
	 * Get File Information
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return NULL
	 */

	public function file_view_get($identifier)
	{
		//Check user is valid
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('files_model');
			
			//Check file exists
			if($file = $this->files_model->file_get_details($identifier))
			{
				//Check user has permission to files project
				//if ($this->access->user_has_project_permission($user, $file['project'], 'write'))
				//{
					$this->load->model('projects_model');
					$response->permissions = $this->projects_model->get_permissions_project_user($user, $file['project']);
				
					//CHECK FOR CREATE FILE PERMISSION
	
					if ($file['visibility'] === 'public')
					{
						$response->status = TRUE;
						$response->file = $this->files_model->file_get_details_public($identifier);
						$this->response($response, 200);
					}
					else
					{
						if ($this->access->valid_user(array('projects')))
						{
							$response->status = TRUE;
							$response->file = $this->files_model->file_get_details($identifier);
							$this->response($response, 200);
						}
					}
				//}
			}
		}
	}
	

	/**
	 * Get File Set Information
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return NULL
	 */

	public function file_set_view_get($identifier)
	{
		//Check user is valid
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('files_model');
			
			//Check file exists
			if($file = $this->files_model->file_set_get_details($identifier))
			{
				//Check user has permission to files project
				//if ($this->access->user_has_project_permission($user, $file['project'], 'write'))
				//{
				
					$this->load->model('projects_model');
					$response->permissions = $this->projects_model->get_permissions_project_user($user, $file['project']);
				
					//CHECK FOR CREATE FILE PERMISSION
	
					if ($file['visibility'] === 'public')
					{
						$response->status = TRUE;
						$response->file_set = $this->files_model->file_set_get_details_public($identifier);
						$response->archive_files = $this->files_model->file_set_get_files_public($identifier);
						$this->response($response, 200);
					}
					else
					{
						$response->status = TRUE;
						$response->file_set = $this->files_model->file_set_get_details($identifier);
						$response->archive_files = $this->files_model->file_set_get_files($identifier);
						$this->response($response, 200);
					}
				//}
			}
		}
	}
	
	
	/**
	 * Get Public File Information
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return NULL
	 */

	public function file_view_public_get($identifier)
	{
		$this->load->model('files_model');
		
		//Check file exists
		if($file = $this->files_model->file_get_details($identifier))
		{
			$this->load->model('projects_model');
		
			if ($file['visibility'] === 'public')
			{
				$response->status = TRUE;
				$response->file = $this->files_model->file_get_details_public($identifier);
				$this->response($response, 200);
			}
		}
	}
	
	/**
	 * Get One-Time Download Key
	 *
	 * @param string $identifier The file identifier
	*/
	
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
	
	/**
	 * View Put
	 *
	 * Updates a file
	 *
	 * @param $identifer string The identifier of the file
	 */

	public function file_view_put($identifier)
	{
		//Check for valid user
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('files_model');

			//Check file exists
			if($file = $this->files_model->file_get_details($identifier))
			{
				//CHANGE TO CHECK FOR FILE PERMISSIONS
				//if ($this->access->user_has_project_permission($user, $identifier, 'write'))
				//{				
					if ($file = $this->files_model->update_file($identifier, $this->put('name'), $this->put('default_licence'), $this->put('public_view')))
					{
						$response->file = $file;
						$response->status = TRUE;
						$this->response($response, 200); // 200 being the HTTP response code
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'An unspecified error occurred in updating the file.';
						$this->response($response, 400);
					}
				//}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'The specified file does not exist.';
				$this->response($response, 404);
			}
		}
	}

	
	/**
	 * Download File
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return NULL
	*/
	
	public function download_get($identifier)
	{
		$this->load->model('files_model');
		
		if ($this->files_model->validate_otk($this->get('otk'), $identifier))
		{
			$file = $this->files_model->file_get_details($identifier);
			$expires = time() + 60;
			$path = '/v1/MossoCloudFS_e4c5ab67-0b7a-4095-999c-32aaf03a6886/project:' . $file['project'] . '/'. $identifier .'.'. $file['extension'];
			$key = hash_hmac('sha1', "GET\n{$expires}\n{$path}", $_SERVER['RACKSPACE_METADATAKEY']);
			$this->output->set_header('Location: ' . 'https://storage101.lon3.clouddrive.com' . $path . '?temp_url_sig=' . $key . '&temp_url_expires=' . $expires);

		}
	}
	
	/**
	 * Process Download Queue
	 *
	 * @return NULL
	*/
	
	function process_queue_get()
	{
		if ($queued_files = $this->db
			->where('file_upload_status', 'staged')
			->or_where('file_upload_status', 'uploading')
			->order_by('file_uploaded_timestamp')
			->get('archive_files'))
		{
			$in_queue = $queued_files->num_rows();
			echo '<p>' . $in_queue . ' files in queue.</p>';
			if ($in_queue > 0)
			{
				$queued_file = $queued_files->row();
				
				echo '<p>Next file is ' . $queued_file->file_id . ', currently ' . $queued_file->file_upload_status . '</p>';
				
				if ($queued_file->file_upload_status === 'staged')
				{
			
					$queued_file = $queued_files->row();
					$this->load->library('storage/storage_rackspacecloud');
					$this->load->model('files_model');
					$this->files_model->set_file_status($queued_file->file_id, 'uploading');
					
					//Upload
					if ($this->storage_rackspacecloud->save($this->config->item('orbital_storage_directory') . '/' . $queued_file->file_id . '.' . $queued_file->file_extension, $queued_file->file_id . '.' . $queued_file->file_extension, array(), $queued_file->file_project))
					{
					
						//Delete local copy
						
						unlink($this->config->item('orbital_storage_directory') . '/' . $queued_file->file_id . '.' . $queued_file->file_extension);
						echo '<p>File uploaded successfully.</p>';				
												
						$this->files_model->set_file_status($queued_file->file_id, 'uploaded');			
					}
					else
					{
						$this->files_model->set_file_status($queued_file->file_id, 'upload_error_hard');
						echo '<p>Upload file ' . $queued_file->file_id . '.' . $queued_file->file_extension . ' failed.</p>';
					}
				}
				else
				{
					echo '<p>Next file in queue is already being uploaded.</p>';
				}
			}
			else
			{
				echo '<p>No actionable files in queue.</p>';
			}		
		}
		else
		{
			$this->files_model->set_file_status($queued_file->file_id, 'upload_error_soft');
			echo 'Database query failed';
		}
				
	}
}

// End of file files.php
// Location: ./controllers/files.php
