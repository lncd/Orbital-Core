<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Licences Model
 *
 * Allows interaction with licence data.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
*/

class Licences_model extends CI_Model {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * List all
	 *
	 * Lists all licences.
	 *
	 * @return ARRAY
	 */
	
	function list_all()
	{
		if ($licences = $this->db->order_by('licence_name_full')->get('licences'))
		{
			$output = array();
			foreach ($licences->result() as $licence)
			{
			
				if ($this->db->where('file_licence', $licence->licence_id)->count_all_results('archive_files') > 0 OR $this->db->where('dset_licence', $licence->licence_id)->count_all_results('datasets') > 0 OR $this->db->where('project_default_licence', $licence->licence_id)->count_all_results('projects') > 0)
				{
					$in_use = TRUE;
				}
				else
				{
					$in_use = FALSE;
				}
			
				$output[] = array(
					'id' => $licence->licence_id,
					'short_name' => $licence->licence_name_short,
					'name' => $licence->licence_name_full,
					'uri' => $licence->licence_summary_uri,
					'enabled' => (bool) $licence->licence_enabled,
					'in_use' => $in_use
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
	 * List all
	 *
	 * Lists all available licences.
	 *
	 * @return ARRAY
	 */
	
	function list_all_available()
	{
		if ($licences = $this->db->order_by('licence_name_full')->where('licence_enabled', TRUE)->get('licences'))
		{
			$output = array();
			foreach ($licences->result() as $licence)
			{			
				$output[] = array(
					'id' => $licence->licence_id,
					'short_name' => $licence->licence_name_short,
					'name' => $licence->licence_name_full,
					'uri' => $licence->licence_summary_uri
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
	 * Get licence
	 *
	 * Gets a specific licence.
	 *
	 * @param $id string The identifier of the licence
	 * @return ARRAY
	 */
	
	function get_licence($id)
	{
		if ($licence = $this->db->where('licence_id', $id)->get('licences'))
		{
			if ($licence->num_rows() === 1)
			{
				$licence = $licence->row();
				
				if ($this->db->where('file_licence', $licence->licence_id)->count_all_results('archive_files') > 0 OR $this->db->where('dset_licence', $licence->licence_id)->count_all_results('datasets') > 0 OR $this->db->where('project_default_licence', $licence->licence_id)->count_all_results('projects') > 0)
				{
					$in_use = TRUE;
				}
				else
				{
					$in_use = FALSE;
				}
				
				return array(
					'id' => $licence->licence_id,
					'short_name' => $licence->licence_name_short,
					'name' => $licence->licence_name_full,
					'uri' => $licence->licence_summary_uri,
					'enabled' => (bool) $licence->licence_enabled,
					'summary' => $licence->licence_summary,
					'allow_list' => $licence->licence_allow_list,
					'forbid_list' => $licence->licence_forbid_list,
					'condition_list' => $licence->licence_condition_list,
					'in_use' => $in_use
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
	 * Create licence
	 *
	 * Creates a new licence.
	 *
	 * @param $name string                The full name of the licence
	 * @param $name_short string          The short name of the licence
	 * @param $licence_summary_uri string The uri of the licence
	 * @return ARRAY
	 */
	
	function create_licence($name, $name_short, $licence_summary_uri)
	{
			
		$insert = array(
			'licence_name_short' => $name_short,
			'licence_name_full' => $name,
			'licence_summary_uri' => $licence_summary_uri
		);
		
		// Attempt insert
		
		if ($this->db->insert('licences', $insert))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Update licence
	 *
	 * Updates a licence.
	 *
	 * @param $id string        The licence identifier
	 * @param $name string      The full name of the licence
	 * @param $shortname string The short name of the licence
	 * @param $uri string       The full name of the licence
	 * @param $enable bool      If the licence is enabled
	 * @return ARRAY
	 */
	
	function update_licence($id, $name, $shortname, $uri, $enable = FALSE)
	{
		
		if ($this->get_licence($id))
		{
		
			$update = array(
				'licence_name_full' => $name,
				'licence_name_short' => $shortname,
				'licence_summary_uri' => $uri,
				'licence_enabled' => (bool) $enable
			);
		
			if ($this->db
				->where('licence_id', $id)
				->update('licences', $update))
			{
				return TRUE;
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

// End of file licences.php