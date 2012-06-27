<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Timeline Model
 *
 * Allows interaction with timeline data.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
*/

class Timeline_model extends CI_Model {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}

	function add_item($project, $user, $text, $payload = NULL, $type = 'system')
	{
		$insert = array(
			'tl_project' => $project,
			'tl_user' => $user,
			'tl_text' => $text,
			'tl_payload' => $payload,
			'tl_type' => $type
		);
		
		if ($this->db->insert('timeline', $insert))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function add_event($project, $user, $text, $payload = NULL, $type = 'system', $time_stamp)
	{
		$insert = array(
			'tl_project' => $project,
			'tl_user' => $user,
			'tl_text' => $text,
			'tl_payload' => $payload,
			'tl_type' => $type,
			'tl_timestamp' => $time_stamp
		);
		
		if ($this->db->insert('timeline', $insert))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get Timeline for Project
	 *
	 * Returns entire timeline for a given project.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return array|false Array of timeline items, or FALSE if project does not exist.
	 */

	function get_for_project($identifier)
	{
		if ($project = $this->db->where('project_id', $identifier)->get('projects'))
		{ 
			if ($project->num_rows() === 1)
			{
				$project = $project->row();
				$items = $this->db
					->where('tl_project', $identifier)
					->order_by('tl_timestamp')
					->get('timeline');
					
				$output = array();
					
				foreach ($items->result() as $item)
				{
					$output[] = array(
						'id' => $item->tl_id,
						'text' => $item->tl_text,
						'payload' => $item->tl_payload,
						'timestamp' => $item->tl_timestamp,
						'timestamp_unix' => strtotime($item->tl_timestamp),
						'timestamp_human' => date('D jS M Y \a\t g.ia', strtotime($item->tl_timestamp)),
						'user' => $item->tl_user,
						'visibility' => $item->tl_visibility
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
	 * Get Timeline for Project
	 *
	 * Returns entire timeline for a given project.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return array|false Array of timeline items, or FALSE if project does not exist.
	 */

	function get_public_for_project($identifier)
	{
		if ($project = $this->db->where('project_id', $identifier)->get('projects'))
		{ 
			if ($project->num_rows() === 1)
			{
				$project = $project->row();
				$items = $this->db
					->where('tl_project', $identifier)
					->where('tl_visibility', 'public')
					->order_by('tl_timestamp')
					->get('timeline');
					
				$output = array();
					
				foreach ($items->result() as $item)
				{
					$output[] = array(
						'id' => $item->tl_id,
						'text' => $item->tl_text,
						'payload' => $item->tl_payload,
						'timestamp' => $item->tl_timestamp,
						'timestamp_unix' => strtotime($item->tl_timestamp),
						'timestamp_human' => date('D jS M Y \a\t g.ia', strtotime($item->tl_timestamp)),
						'user' => $item->tl_user,
						'visibility' => $item->tl_visibility
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
	 * Get Timeline for Project
	 *
	 * Returns entire timeline for a given project.
	 *
	 * @param string $identifier Identifier of project.
	 *
	 * @return array|false Array of timeline items, or FALSE if project does not exist.
	 */

	function get_all_projects_activity_for_user($user)
	{
		$items = $this->db
			->where('p_proj_user', $user)
			->where('p_proj_read',  1)
			->join('permissions_projects', 'p_proj_project = tl_project')
			->get('timeline');
			
		$output = array();
				
		foreach ($items->result() as $item)
		{
			$output[] = array(
				'id' => $item->tl_id,
				'text' => $item->tl_text,
				'payload' => $item->tl_payload,
				'timestamp' => $item->tl_timestamp,
				'timestamp_unix' => strtotime($item->tl_timestamp),
				'timestamp_human' => date('D jS M Y \a\t g.ia', strtotime($item->tl_timestamp)),
				'user' => $item->tl_user,
				'visibility' => $item->tl_visibility
			);
		}
		
		return $output;
	}
	
	/**
	 * Get Timeline for User
	 *
	 * Returns entire timeline for a given user.
	 *
	 * @param string $identifier Identifier of user.
	 *
	 * @return array|false Array of timeline items, or FALSE if project does not exist.
	 */

	function get_for_user($identifier)
	{
		if ($users = $this->db->where('user_email', $identifier)->get('users'))
		{ 
			if ($users->num_rows() === 1)
			{
				$user = $users->row();
				$items = $this->db
					->where('tl_user', $identifier)
					->order_by('tl_timestamp')
					->get('timeline');
					
				$output = array();
					
				foreach ($items->result() as $item)
				{
					$output[] = array(
						'id' => $item->tl_id,
						'text' => $item->tl_text,
						'payload' => $item->tl_payload,
						'timestamp' => $item->tl_timestamp,
						'timestamp_unix' => strtotime($item->tl_timestamp),
						'timestamp_human' => date('D jS M Y \a\t g.ia', strtotime($item->tl_timestamp)),
						'project' => $item->tl_project,
						'visibility' => $item->tl_visibility
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
}

// End of file timeline_model.php