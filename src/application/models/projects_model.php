<?php defined('BASEPATH') or exit('No direct script access allowed');

class Projects_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get Project Details
	 *
	 * Returns entire user information object for given address.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return object|false The project object, or FALSE if project does not exist.
	 */

	function get_project($identifier)
	{
		if ($project = $this->mongo_db->where(array('_id' => $identifier))->get('projects'))
		{
			if (count($project) === 1)
			{
				return $project[0];
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	function create_project($name, $user)
	{
		$identifier = uniqid($this->config->item('orbital_cluster_sn'));

		$insert = array(
			'_id' => $identifier,
			'name' => $name,
			'project_created' => time()
		);

		// Attempt create

		if ($this->mongo_db->insert('projects', $insert))
		{	$this->load->model('permissions');
			$this->permissions->create_permission($user, 'project', array('read', 'write', 'delete', 'archivefiles_write', 'archivefiles_read', 'sharedworkspace_read', 'dataset_create'), $identifier);
		
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
}

// End of file projects.php