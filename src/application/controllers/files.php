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
				if ($user_has_project_permission($user, $file->project, 'p_proj_archive_read', $softfail = FALSE))
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
			$path = '/v1/MossoCloudFS_e4c5ab67-0b7a-4095-999c-32aaf03a6886/project_' . $file['project'] . '/'. $identifier .'.'. $file['extension'];
			$key = hash_hmac('sha1', "GET\n$expires\n$path", $_SERVER['RACKSPACE_METADATAKEY']);
			$this->output->set_header('Location: ' . 'https://storage101.lon3.clouddrive.com' . $path . '?temp_url_sig=' . $key . '&temp_url_expires=' . $expires);

		}
	}
}

// End of file projects.php
// Location: ./controllers/projects.php