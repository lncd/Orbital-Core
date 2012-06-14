<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Archive Files Model
 *
 * Allows interaction with archive files.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Files_model extends CI_Model {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get OTK
	 *
	 * Generates a one-time key to access a file.
	 *
	 * @param string $identifier Identifier of file.
	 *
	 * @return string|false The key, or FALSE if the file does not exist.
	 */

	function get_otk($identifier)
	{
		$otk = random_string('alnum', 64);
		$insert = array(
			'otk_token' => $otk,
			'otk_file' => $identifier,
			'otk_expires' => date('Y-m-d H:i:s', time() + 60)
		);
	
		if ($this->db->insert('archive_otks', $insert))
		{
			return $otk;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Validate OTK
	 *
	 * Validates an OTK against a file, and marks it as used.
	 *
	 * @param string $key        One-time key.
	 * @param string $identifier Identifier of file.
	 *
	 * @return bool TRUE if key is valid, FALSE if not.
	 */

	function validate_otk($key, $identifier)
	{
		if ($this->db->where('otk_token', $key)
				->where('otk_file', $identifier)
				->where('otk_expires >', date('Y-m-d H:i:s', time()))
				->get('archive_otks'))
		{
			$this->db->where('otk_token', $key)->delete('archive_otks');
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get Upload Token
	 *
	 * Generates a one-time token to upload to a project.
	 *
	 * @param string $project Identifier of project.
	 * @param string $user    Identifier of user.
	 *
	 * @return string|false The token, or FALSE if the file does not exist.
	 */

	function get_upload_token($project, $user)
	{
		$token = random_string('alnum', 64);
		$insert = array(
			'aut_token' => $token,
			'aut_user' => $user,
			'aut_project' => $project,
			'aut_expires' => date('Y-m-d H:i:s', time() + 300)
		);
	
		$this->db->where('aut_user', $user)->where('aut_project', $project)->delete('archive_upload_tokens');
	
		if ($this->db->insert('archive_upload_tokens', $insert))
		{
			return $token;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Validate Upload Token
	 *
	 * Validates an upload token and marks it as used.
	 *
	 * @param string $token Token to validate.
	 *
	 * @return bool TRUE if key is valid, FALSE if not.
	 */

	function validate_upload_token($token)
	{
		$token_data = $this->db
			->where('aut_token', $token)
			->where('aut_expires >', date('Y-m-d H:i:s', time()))
			->get('archive_upload_tokens');
		
		if ($token_data->num_rows() === 1)
		{
			$token_data = $token_data->row();
			$this->db->where('aut_token', $token)->delete('archive_upload_tokens');
			return array(
				'project' => $token_data->aut_project,
				'user' => $token_data->aut_user
			);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * List for Project
	 *
	 * Lists all files for project and their upload status.
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return ARRAY
	 */

	function list_for_project($identifier, $limit = 5)
	{
		if ($archive_files = $this->db
			->where('file_project', $identifier)
			->join('licences', 'licence_id = file_licence')
			->order_by('file_title')
			->limit($limit)
			->get('archive_files'))
		{
			$output = array();

			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = array
				(
					'id' => $archive_file->file_id,
					'title' => $archive_file->file_title,
					'size' => $archive_file->file_size,
					'original_name' => $archive_file->file_original_name,
					'uploaded' => $archive_file->file_uploaded_timestamp,
					'visibility' => $archive_file->file_visibility,
					'status' => $archive_file->file_upload_status,
					'licence' => $archive_file->licence_name_short
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Create file set
	 *
	 * Creates a new file set.
	 *
	 * @param string $identifier The project identifier
	 * @param string $identifier The file set name
	 * @param string $identifier The file set description
	 *
	 * @return ARRAY
	 */

	function create_file_set($project_identifier, $name, $description)
	{
		$identifier = uniqid($this->config->item('orbital_cluster_sn'));

		$insert = array(
			'set_id' => $identifier,
			'set_project' => $project_identifier,
			'set_name' => $name,
			'set_description' => $description,
			'set_visibility' => 'private'
		);

		// Attempt create

		if ($this->db->insert('archive_file_sets', $insert))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
	
	
	/**
	 * Set file status
	 *
	 * Sets the status of a file.
	 *
	 * @param string $identifier Identifier of file.
	 * @param string $status     Status of file.
	 *
	 * @return bool TRUE if key is valid, FALSE if not.
	 */
	
	function set_file_status($identifier, $status)
	{
		if ($this->db->where('file_id', $identifier)
			->update('archive_files', array('file_upload_status' => $status)))
			
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * List public files for Project
	 *
	 * Lists all files for public project and their upload status.
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return ARRAY
	 */

	function list_public_for_project($identifier, $limit = 5)
	{
		if ($archive_files = $this->db
			->where('file_project', $identifier)
			->join('licences', 'licence_id = file_licence')
			->order_by('file_title')
			->where('file_visibility', 'public')
			->limit($limit)
			->get('archive_files'))
		{
			$output = array();

			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = array
				(
					'id' => $archive_file->file_id,
					'title' => $archive_file->file_title,
					'size' => $archive_file->file_size,
					'original_name' => $archive_file->file_original_name,
					'uploaded' => $archive_file->file_uploaded_timestamp,
					'visibility' => $archive_file->file_visibility,
					'status' => $archive_file->file_upload_status,
					'licence' => $archive_file->licence_name_short
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * List File sets
	 *
	 * Lists all files sets.
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return ARRAY
	 */

	function list_file_sets($identifier, $limit = 5)
	{
		if ($file_sets = $this->db
			->where('set_project', $identifier)
			->order_by('set_name')
			->limit($limit)
			->get('archive_file_sets'))
		{
			$output = array();

			foreach ($file_sets->result() as $file_set)
			{
				$output[] = array
				(
					'file_set_id' => $file_set->set_id,
					'file_set_name' => $file_set->set_name,
					'file_set_visibility' => $file_set->set_visibility,
					'file_set_description' => $file_set->set_description
					
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
		
	/**
	 * List file sets file belongs to
	 *
	 * Lists all file sets a file belongs to.
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return ARRAY
	 */

	function file_file_set_list($identifier)
	{
		if ($file_sets = $this->db
			->where('fslink_file', $identifier)
			->join('archive_file_sets', 'set_id = fslink_set')
			->get('archive_file_set_links'))
		{
			$output = array();

			foreach ($file_sets->result() as $file_set)
			{
				$output[] = array
				(
					'file_set_id' => $file_set->set_id,
					'file_set_name' => $file_set->set_name,
					'file_set_visibility' => $file_set->set_visibility,
					'file_set_description' => $file_set->set_description
					
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * List public for Project
	 *
	 * Lists all files for public project and their upload status.
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return ARRAY
	 */

	function list_public_file_sets($identifier, $limit = 5)
	{
		if ($file_sets = $this->db
			->where('set_project', $identifier)
			->where('set_visibility', 'public')
			->order_by('set_name')
			->limit($limit)
			->get('archive_file_sets'))
		{
			$output = array();

			foreach ($file_sets->result() as $file_set)
			{
				$output[] = array
				(
					'file_set_id' => $file_set->set_id,
					'file_set_name' => $file_set->set_name,
					'file_set_visibility' => $file_set->set_visibility,
					'file_set_description' => $file_set->set_description
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * File get details
	 *
	 * Lists a files details.
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return ARRAY
	 */

	function file_get_details($identifier)
	{
		if ($archive_file = $this->db
			->where('file_id', $identifier)
			->join('projects', 'project_id = file_project')
			->join('licences', 'licence_id = file_licence')
			->get('archive_files'))
		{
			$archive_file = $archive_file->row();

			return array
			(
				'id' => $archive_file->file_id,
				'original_name' => $archive_file->file_original_name,
				'title' => $archive_file->file_title,
				'extension' => $archive_file->file_extension,
				'mimetype' => $archive_file->file_mimetype,
				'project' => $archive_file->file_project,
				'size' => $archive_file->file_size,
				'licence' => $archive_file->file_licence,
				'visibility' => $archive_file->file_visibility,
				'status' => $archive_file->file_upload_status,
				'uploaded_by' => $archive_file->file_uploaded_by,
				'timestamp' => $archive_file->file_uploaded_timestamp,
				'project' => $archive_file->project_id,
				'project_name' => $archive_file->project_name,
				'project_public_view' => $archive_file->project_public_view,
				'licence_name' => $archive_file->licence_name_full,
				'licence_uri' => $archive_file->licence_summary_uri
			);
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * File Set get details
	 *
	 * Lists a file sets details.
	 *
	 * @param string $identifier The file set identifier
	 *
	 * @return ARRAY
	 */

	function file_set_get_details($identifier)
	{
		if ($archive_file_set = $this->db
			->where('set_id', $identifier)
			->join('projects', 'project_id = set_project')
			->get('archive_file_sets'))
		{
			$archive_file_set = $archive_file_set->row();

			return array
			(
				'id' => $archive_file_set->set_id,
				'title' => $archive_file_set->set_name,
				'description' => $archive_file_set->set_description,
				'visibility' => $archive_file_set->set_visibility,
				'project' => $archive_file_set->project_id,
				'project_name' => $archive_file_set->project_name,
				'project_public_view' => $archive_file_set->project_public_view
			);
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * File Set get details public
	 *
	 * Lists a public file sets details.
	 *
	 * @param string $identifier The file set identifier
	 *
	 * @return ARRAY
	 */

	function file_set_get_details_public($identifier)
	{
		if ($archive_file_set = $this->db
			->where('set_id', $identifier)
			->where('set_visibility', 'public')
			->join('projects', 'project_id = set_project')
			->get('archive_file_sets'))
		{
			$archive_file_set = $archive_file_set->row();

			return array
			(
				'id' => $archive_file_set->set_id,
				'title' => $archive_file_set->set_name,
				'description' => $archive_file_set->set_description,
				'visibility' => $archive_file_set->set_visibility,
				'project' => $archive_file_set->project_id,
				'project_name' => $archive_file_set->project_name,
				'project_public_view' => $archive_file_set->project_public_view
			);
		}
		else
		{
			return FALSE;
		}
	}


	/**
	 * File Set get files
	 *
	 * Lists a file sets files.
	 *
	 * @param string $identifier The file set identifier
	 *
	 * @return ARRAY
	 */

	function file_set_get_files($identifier)
	{
		if ($archive_files = $this->db
			->where('fslink_set', $identifier)
			->join('archive_files', 'file_id = fslink_file')
			->join('licences', 'licence_id = file_licence')
			->get('archive_file_set_links'))
		{
			$output = array();

			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = array
				(
					'id' => $archive_file->file_id,
					'original_name' => $archive_file->file_original_name,
					'title' => $archive_file->file_title,
					'licence' => $archive_file->licence_name_short,
					'uploaded' => $archive_file->file_uploaded_timestamp,
					'size' => $archive_file->file_size,
					'visibility' => $archive_file->file_visibility,
					'status' => $archive_file->file_upload_status
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * File Set get files public
	 *
	 * Lists a public file sets files.
	 *
	 * @param string $identifier The file set identifier
	 *
	 * @return ARRAY
	 */

	function file_set_get_files_public($identifier)
	{
		if ($archive_file_set = $this->db
			->where('fslink_set', $identifier)
			->where('set_visibility', 'public')
			->join('archive_files', 'file_id = fslink_file')
			->get('archive_file_set_links'))
		{
			$output = array();

			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = array
				(
					'id' => $archive_file->file_id,
					'original_name' => $archive_file->file_original_name,
					'licence' => $archive_file->file_licence,
					'uploaded' => $archive_file->file_uploaded_timestamp,
					'size' => $archive_file->file_size,
					'visibility' => $archive_file->file_visibility,
					'status' => $archive_file->file_upload_status
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	

	/**
	 * File get details public
	 *
	 * Lists all files for a public project and their upload status.
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return ARRAY
	 */

	function file_get_details_public($identifier)
	{
		if ($archive_file = $this->db
			->where('file_id', $identifier)
			->where('file_visibility', 'public')
			->join('projects', 'project_id = file_project')
			->join('licences', 'licence_id = file_licence')
			->get('archive_files'))
		{
			$archive_file = $archive_file->row();

			return array
			(
				'id' => $archive_file->file_id,
				'original_name' => $archive_file->file_original_name,
				'title' => $archive_file->file_title,
				'extension' => $archive_file->file_extension,
				'mimetype' => $archive_file->file_mimetype,
				'project' => $archive_file->file_project,
				'size' => $archive_file->file_size,
				'status' => $archive_file->file_upload_status,
				'licence' => $archive_file->file_licence,
				'project_name' => $archive_file->project_name,
				'licence_name' => $archive_file->licence_name_full,
				'licence_uri' => $archive_file->licence_summary_uri
			);
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get file id
	 *
	 * Generates a files id.
	 *
	 * @return STRING
	 */
	
	function get_file_id()
	{
		return random_string('alnum', 16);
	}
	
	/**
	 * Add file
	 *
	 * Adds file to upload queue.
	 *
	 * @param string $identifier   The file identifier
	 * @param string $originalname The files original name
	 * @param string $extension    The file extension
	 * @param string $mimetype     The mimetype of the file
	 * @param int    $size         The size (in bytes) of the file
	 * @param string $project      The project the file belongs to
	 * @param string $licence      The licence the file should be under
	 * @param bool   $visibility   If the file is public or private
	 * @param string $status       The upload status of the file
	 * @param string $uploader     Who the file is uploaded by
	 *
	 * @return ARRAY
	 */
	
	function add_file($identifier, $originalname, $extension, $mimetype, $size, $project, $licence, $visibility, $status, $uploader)
	{
		$insert = array(
			'file_id' => $identifier,
			'file_original_name' => $originalname,
			'file_title' => $originalname . ' (' . date('d/m/y H:i') . ')',
			'file_extension' => $extension,
			'file_mimetype' => $mimetype,
			'file_size' => (int) $size,
			'file_project' => $project,
			'file_licence' => $licence,
			'file_visibility' => $visibility,
			'file_upload_status' => $status,
			'file_uploaded_by' => $uploader
		);
		
		if ($this->db->insert('archive_files', $insert))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Update File
	 *
	 * Updates a files details.
	 *
	 * @param string $identifier      The file identifier
	 * @param string $name            The file name
	 * @param string $default_licence The file default_licence
	 * @param array $other            Other information
	 *
	 * @return $identifier.
	 */

	function update_file($identifier, $name, $default_licence, $public_view,  $other = array())
	{
		$update = array(
			'file_title' => $name,
			'file_licence' => $default_licence,
			'file_visibility' => $public_view
		);

		foreach($other as $name => $value)
		{
			$this->db->set($name, $value);
		}

		// Attempt update

		if ($this->db->where('file_id', $identifier) -> update('archive_files', $update))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
	

	/**
	 * Update File file set
	 *
	 * Updates a file sets details.
	 *
	 * @param string $identifier  The file identifier
	 * @param string $name        The file name
	 * @param string $description The file description
	 * @param array $other        Other information
	 *
	 * @return $identifier.
	 */

	function update_file_set($identifier, $name, $description, $file_set_public)
	{
		$update = array(
			'set_name' => $name,
			'set_description' => $description,
			'set_visibility' => $file_set_public
		);

		// Attempt update

		if ($this->db->where('set_id', $identifier) -> update('archive_file_sets', $update))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Update File set files
	 *
	 * Updates a file sets files.
	 *
	 * @param string $identifier  The file set identifier
	 * @param string $file        The file
	 * @param string $action      Add or remove the file
	 *
	 * @return $identifier.
	 */

	function update_file_set_files($identifier, $file, $action)
	{
		$update = array(
				'fslink_file' => $file,
				'fslink_set' => $identifier
			);
			
		if ($action === 'add')
		{
			$check_existing_records = $this->db->where('fslink_set', $identifier) -> where('fslink_file', $file) -> get('archive_file_set_links');
			if ($check_existing_records->num_rows() === 0)
			{
				if ($this->db->insert('archive_file_set_links', $update))
				{
					return $identifier;
				}
			}
		}
		else if ($action === 'remove')
		{
			if ($this->db->where('fslink_set', $identifier) -> where('fslink_file', $file) -> get('archive_file_set_links'))
			{
				if ($this->db->where('fslink_set', $identifier) -> where('fslink_file', $file) -> delete('archive_file_set_links'))
				{				
					return $identifier;
				}
			}			
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Update File file sets
	 *
	 * Updates a files file sets.
	 *
	 * @param string $identifier  The file identifier
	 * @param string $file        The file set
	 * @param string $action      Add or remove the file
	 *
	 * @return $identifier.
	 */

	function update_file_file_sets($identifier, $file_set, $action)
	{
		$update = array(
				'fslink_file' => $identifier,
				'fslink_set' => $file_set
			);
			
		if ($action === 'add')
		{
			$check_existing_records = $this->db->where('fslink_set', $file_set) -> where('fslink_file', $identifier) -> get('archive_file_set_links');
			
			if ($check_existing_records->num_rows() === 0)
			{
				if ($this->db->insert('archive_file_set_links', $update))
				{
					return $identifier;
				}
			}
		}
		else if ($action === 'remove')
		{
			if ($this->db->where('fslink_set', $file_set) -> where('fslink_file', $identifier) -> get('archive_file_set_links'))
			{
				if ($this->db->where('fslink_set', $file_set) -> where('fslink_file', $identifier) -> delete('archive_file_set_links'))
				{				
					return $identifier;
				}
			}			
		}
		else
		{
			return FALSE;
		}
	}
}




// End of file projects.php