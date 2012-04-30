<?php defined('BASEPATH') or exit('No direct script access allowed');

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
	 * @param string $key   One-time key.
	 * @param string $identifier Identifier of file.
	 *
	 * @return bool TRUE if key is valid, FALSE if not.
	 */

	function validate_otk($key, $identifier)
	{
		if ($this->db->where('otk_token', $key)->where('otk_file', $identifier)->where('otk_expires >', date('Y-m-d H:i:s', time())))
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
	
		$this->db->where('aut_user', $user)->where('aut_project', $project)->delete('archive_otks');
	
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
		$token = $this->db
			->where('aut_token', $token)
			->where('otk_expires >', date('Y-m-d H:i:s', time()))
			->get('archive_upload_tokens');
			
		if ($token->num_rows() === 1)
		{
			$this->db->where('aut_token', $token)->delete('archive_upload_tokens');
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function list_for_project($identifier)
	{
		if ($archive_files = $this->db
			->where('file_project', $identifier)
			->get('archive_files'))
		{
			$output = array();

			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = array
				(
					'id' => $archive_file->file_id,
					'original_name' => $archive_file->file_original_name,
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

	function list_public_for_project($identifier)
	{
		if ($archive_files = $this->db
			->where('file_project', $identifier)
			->where('file_visibility', 'public')
			->where('file_upload_status', 'uploaded')
			->get('archive_files'))
		{
			$output = array();

			foreach ($archive_files->result() as $archive_file)
			{
				$output[] = array
				(
					'id' => $archive_file->file_id,
					'original_name' => $archive_file->file_original_name
				);
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}

	function file_get_details($identifier)
	{
		if ($archive_file = $this->db
			->where('file_id', $identifier)
			->join('projects', 'project_id = file_project')
			->get('archive_files'))
		{
			$archive_file = $archive_file->row();

			return array
			(
				'id' => $archive_file->file_id,
				'original_name' => $archive_file->file_original_name,
				'extension' => $archive_file->file_extension,
				'mimetype' => $archive_file->file_mimetype,
				'project' => $archive_file->file_project,
				'licence' => $archive_file->file_licence,
				'visibility' => $archive_file->file_visibility,
				'status' => $archive_file->file_upload_status,
				'uploaded_by' => $archive_file->file_uploaded_by,
				'timestamp' => $archive_file->file_uploaded_timestamp,
				'project' => $archive_file->project_id
			);
		}
		else
		{
			return FALSE;
		}
	}


	function file_get_details_public($identifier)
	{
		if ($archive_file = $this->db
			->where('file_id', $identifier)
			->get('archive_files'))
		{
			$archive_file = $archive_file->row();

			return array
			(
				'id' => $archive_file->file_id,
				'original_name' => $archive_file->file_original_name,
				'extension' => $archive_file->file_extension,
				'mimetype' => $archive_file->file_mimetype,
				'project' => $archive_file->file_project,
				'licence' => $archive_file->file_licence
			);
		}
		else
		{
			return FALSE;
		}
	}

}

// End of file projects.php