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
	*/

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
	
	/**
	 * Get One-Time Download Key
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
	 * Download File
	*/
	
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
	
	/**
	 * Process Download Queue
	*/
	
	function process_queue_get()
	{
		if ($queued_files = $this->db
			->where('file_upload_status', 'staged')
			->or_where('file_upload_status', 'uploading')
			->order_by('file_uploaded_timestamp')
			->get('archive_files'))
			{
			if ($queued_files->num_rows() > 0)
			{
				$queued_file = $queued_files->row();
				
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
							
							//unlink($this->config->item('orbital_storage_directory') . '/' . $queued_file->file_id . '.' . $queued_file->file_extension);
							echo 'OK';			
							
							$this->files_model->set_file_status($queued_file->file_id, 'uploaded');			
						}
						else
						{
							$this->files_model->set_file_status($queued_file->file_id, 'upload_error_hard');
							echo 'Upload file ' . $queued_file->file_id . '.' . $queued_file->file_extension . ' Failed';
						}
					}
					else
					{
						echo 'No files to process';
					}
				}
				else
				{
					echo 'Next file in queue is already being uploaded.';
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