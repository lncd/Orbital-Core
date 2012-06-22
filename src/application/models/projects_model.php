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
		if ($project = $this->db->where('project_id', $identifier)->join('licences', 'licence_id = project_default_licence')->get('projects'))
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
					'created' => $project->project_created,
					'research_group' => $project->project_research_group,
					'public_view' => $project->project_public_view,
					'default_licence' => $project->project_default_licence,
					'default_licence_name' => $project->licence_name_full,
					'google_analytics' => $project->project_google_analytics
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
	
	/**
	 * List public
	 *
	 * Lists all public projects up to the given limit.
	 *
	 * @param int $limit limit for the number of public projects to be listed.
	 *
	 * @return ARRAY The list of public projects.
	 */

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
	
	/**
	 * List public archive files
	 *
	 * Returns list of public archive files for given project.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return ARRAY The list of public archive files.
	 */

	function list_public_archive_files($identifier)
	{
		if ($archive_files = $this->db->where('file_project', $identifier)->where('file_visibility', 'public')->or_where('file_visibility', 'visible')->get('archive_files'))
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
	
	/**
	 * List datasets
	 *
	 * Returns list of datasets for given project.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return ARRAY The list of datasets.
	 */

	function list_datasets($identifier)
	{
		if ($datasets = $this->db->where('dset_project', $identifier)->where('dset_visibility', 'public')->get('datasets'))
		{
			$output = array();
			
			foreach ($datasets->result() as $dataset)
			{
				$output[] = $dataset->file_original_name;
			}
			return $output;
		}
		else
		{
			return FALSE;
		}		
	}
	
	/**
	 * List archive files
	 *
	 * Returns list of archive files for given project.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return ARRAY The list of archive files.
	 */
	
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
	
	/**
	 * List user
	 *
	 * Returns list of projects the user is part of.
	 *
	 * @param string $user  The current user.
	 * @param int    $limit The limit of projects to display.
	 *
	 * @return ARRAY The list of projects.
	 */
	
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

	/**
	 * Create project
	 *
	 * Creates a new project.
	 *
	 * @param string $name     The project title.
	 * @param string $abstract The project abstract
	 * @param string $user     The current user
	 *
	 * @return ARRAY The list of projects.
	 */

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
		{
			$this->load->model('permissions');
			$this->add_permission($identifier, $user, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE);

			$this->timeline_model->add_item($identifier, $user, $name . ' was added to Orbital');
			$this->stream_model->add_item($user, 'created', 'project', $identifier);

			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Add permission
	 *
	 * Adds a permission to the specified user for the specified project.
	 *
	 * @param string $project_id    The specified project.
	 * @param string $user          The specified user.
	 * @param bool   $read          Read permission.
	 * @param bool   $write         Write permission.
	 * @param bool   $delete        Delete permission.
	 * @param bool   $archive_read  Read archive files permission.
	 * @param bool   $archive_write Write archive files permission.
	 * @param bool   $workspace     View workspace permission.
	 * @param bool   $manage_users  Permission to manage users.
	 *
	 * @return ARRAY The list of projects.
	 */
	
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
	
	/**
	 * Get permissions for project user
	 *
	 * Gets the current users permissions for the project.
	 *
	 * @param string $user    The current user
	 * @param string $project The current project
	 *
	 * @return ARRAY The list of permissions.
	 */
	
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
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get project users
	 *
	 * Gets the list of project users.
	 *
	 * @param string $project The current project
	 *
	 * @return ARRAY The list of project users.
	 */
	 
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
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Update Project
	 *
	 * Updates a projects details.
	 *
	 * @param string $identifier      The project identifier
	 * @param string $name            The project name
	 * @param string $abstract        The project abstract
	 * @param string $research_group  The project research_group
	 * @param string $start_date      The project start_date
	 * @param string $end_date        The project end_date
	 * @param string $default_licence The project default_licence
	 * @param array  $other           Other information
	 *
	 * @return ARRAY The list of permissions.
	 */

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
	
	/**
	 * Is Deletable
	 *
	 * Checks if a project is deletable
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return BOOL.
	 */

	function is_deletable($identifier)
	{
		if ($this->get_project($identifier))
		{
			if (count($this->list_archive_files($identifier)) > 0)
			{
				return FALSE;
			}
			if (count($this->list_datasets($identifier)) > 0)
			{
				return FALSE;
			}
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Delete project
	 *
	 * Deletes a project
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return BOOL.
	 */
	
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
	
	function update_project_members($identifier, $user, $read, $write, $delete, $manage_users, $archivefiles_read, $archivefiles_write, $sharedworkspace_read, $dataset_create)
	{	
		$update = array(
		'p_proj_read' => $read,
		'p_proj_write' => $write,
		'p_proj_delete' => $delete,
		'p_proj_manage_users' => $manage_users,
		'p_proj_workspace' => $sharedworkspace_read,
		'p_proj_dataset_create' => $dataset_create,
		'p_proj_archive_read' => $archivefiles_read,
		'p_proj_archive_write' => $archivefiles_write
		);

		// Attempt update
		if ($this->db->where('p_proj_project', $identifier) -> where('p_proj_user', $user) -> count_all_results('permissions_projects') > 0)
		{
			if ($this->db->where('p_proj_project', $identifier) -> where('p_proj_user', $user) -> update('permissions_projects', $update))
			{
				return $identifier;		
			}
			else
			{
				return FALSE;
			}
		}
		//else insert
		else
		{
			if ($this->db->where('user_email', $user) -> count_all_results('users') > 0)
			{
				$update['p_proj_project'] = $identifier;
				$update['p_proj_user'] = $user;
				
				if ($this->db->insert('permissions_projects', $update))
				{
					return $identifier;
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
	}
	
	function delete_project_members($identifier, $user)
	{	
		// Attempt delete
		if ($this->db->where('p_proj_project', $identifier) -> where('p_proj_user', $user) -> delete('permissions_projects'))
		{
			return $identifier;		
		}
		else
		{
			return FALSE;
		}
	}
}

// End of file projects.php