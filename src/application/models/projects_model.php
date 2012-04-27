<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Projects Model
 *
 * Allows interaction with projects data.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
*/

class Projects_model extends CI_Model {

	/**
	 * Constructor
	*/

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
		if ($project = $this->db->where('project_id', $identifier)->get('projects'))
		{ 
			if ($project->num_rows() === 1)
			{
				$project = $project->row();
					return array(
					'identifier' => $project->project_id,
					'name' => $project->project_name,
					'abstract' => $project->project_abstract,
					'start_date' => $project->project_start,
					'end_date' => $project->project_end,
					'research_group' => $project->project_research_group,
					'public_view' => $project->project_public_view,
					'default_licence' => $project->project_default_licence
					); 
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
	
	function list_public($limit = 20)
	{
		if ($projects = $this->db->where('project_public_view', 'visible')->limit($limit)->get('projects'))
		{
			$output = array();
			
			foreach ($projects->result() as $project)
			{
				$output[] = $project->project_id;
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
	function list_public_archive_files($identifier)
	{
		if ($archive_files = $this->db->where('file_project', $identifier)->where('file_visibility', 'public')->get('archive_files'))
		{
			$output = array();
			
			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = $archive_file->file_original_name;
			}
			return $output;
		}
		else
		{
			return FALSE;
		}		
	}
	
	function list_archive_files($identifier)
	{
		if ($archive_files = $this->db->where('file_project', $identifier)->get('archive_files'))
		{
			$output = array();
			
			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = $archive_file->file_original_name;
			}
			return $output;
		}
		else
		{
			return FALSE;
		}		
	}
	
	function list_public_files($identifier)
	{
		if ($handle = opendir($this->config->item('orbital_storage_directory')/* . '/' . $identifier)*/))
		{
			$output = array();
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != "..")
				{
					$output[] = $entry;
				}
			}
			closedir($handle);
			return $output;
		}
	}
	
	function list_user($user, $limit = 20)
	{
		if ($projects = $this->db->join('permissions_projects', 'p_proj_project = project_id')->where('p_proj_user', $user)->where('p_proj_read', TRUE)->limit($limit)->get('projects'))
		{
			$output = array();
			
			foreach ($projects->result() as $project)
			{
				$output[] = $project->project_id;
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}

	function create_project($name, $abstract, $user)
	{
		$identifier = uniqid($this->config->item('orbital_cluster_sn'));

		$insert = array(
			'project_id' => $identifier,
			'project_name' => $name,
			'project_abstract' => $abstract
		);

		// Attempt create

		if ($this->db->insert('projects', $insert))
			{ $this->load->model('permissions');
			$this->add_permission($identifier, $user, TRUE, TRUE, TRUE, TRUE, TRUE);

			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
	
	function add_permission($project_id, $user, $read = TRUE, $write = FALSE, $delete = FALSE, $archive_read = TRUE, $archive_write = FALSE, $workspace = TRUE, $manage_users = FALSE)
	{
		$insert = array(
		'p_proj_project' => $project_id,
		'p_proj_user' => $user,
		'p_proj_read' => $read,
		'p_proj_write' => $write,
		'p_proj_delete' => $delete,
		'p_proj_archive_read' => $archive_read,
		'p_proj_archive_write' => $archive_write,
		'p_proj_workspace' => $workspace,
		'p_proj_manage_users' => $manage_users
		);
		$this->db->insert('permissions_projects', $insert);
	}
	
	function get_permissions_project_user($user, $project)
	{
		if ($permissions = $this->db->where('p_proj_user', $user) -> where('p_proj_project', $project) -> get('permissions_projects'))
		{
			if ($permissions->num_rows() === 1)
			{
				$permissions = $permissions->row();
				return array(
				'read' => (bool)$permissions->p_proj_read,
				'write' => (bool)$permissions->p_proj_write,
				'delete' => (bool)$permissions->p_proj_delete,
				'archive_read' => (bool)$permissions->p_proj_archive_read,
				'archive_write' => (bool)$permissions->p_proj_archive_write,
				'sharedworkspace_read' => (bool)$permissions->p_proj_workspace,
				'manage_users' => (bool) $permissions->p_proj_manage_users,
				'dataset_create' => (bool)$permissions->p_proj_dataset_create
				);
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	function get_project_users($project)
	{
		if ($permissions = $this->db->where('p_proj_project', $project) -> get('permissions_projects'))
		{
			if ($permissions->num_rows() > 0)
			{
				$output = array();
				foreach($permissions -> result() as $permission)
				{
					$output[$permission->p_proj_user] = array(
					'read' => (bool) $permission->p_proj_read,
					'write' => (bool) $permission->p_proj_write,
					'delete' => (bool) $permission->p_proj_delete,
					'archive_read' => (bool) $permission->p_proj_archive_read,
					'archive_write' => (bool) $permission->p_proj_archive_write,
					'sharedworkspace_read' => (bool) $permission->p_proj_workspace,
					'manage_users' => (bool) $permission->p_proj_manage_users,
					'dataset_create' => (bool) $permission->p_proj_dataset_create
					);
				}
				return $output;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function update_project($identifier, $name, $abstract, $research_group, $start_date, $end_date, $default_licence, $other = array())
	{
		$update = array(
			'project_name' => $name,
			'project_abstract' => $abstract,
			'project_research_group' => $research_group,
			'project_start' => $start_date,
			'project_end' => $end_date,
			'project_default_licence' => $default_licence
		);

		foreach($other as $name => $value)
		{
			$this->db->set($name, $value);
		}

		// Attempt create

		if ($this->db->where('project_id', $identifier) -> update('projects', $update))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}

	function delete_project($identifier)
	{
		// Attempt delete
		if ($this->db->where('project_id', $identifier)->delete('projects'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}

// End of file projects.php