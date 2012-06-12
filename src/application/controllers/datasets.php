<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Datasets
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

class Datasets extends Orbital_Controller {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Dataset Create Post
	 *
	 * Creates a dataset
	 */

	public function dataset_create_post()
	{
		if ($user = $this->access->valid_user(array('create_projects')))
		{
			if ($this->access->user_has_permission($user, 'project_create'))
			{
				$this->load->model('datasets_model');

				if ($dataset = $this->datasets_model->create_dataset($this->input->post('project_identifier'), $this->input->post('dataset_name'), $this->input->post('dataset_description')))
				{
					$response->dataset_id = $dataset;
					$this->output->set_header('Location: ' . site_url('collection/' . $dataset));

					$response->status = TRUE;
					$response->message = 'Dataset created.';
					$this->response($response, 201);
				}
			}
		}
	}
}

// End of file datasets.php
// Location: ./controllers/datasets.php
