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
	
	function create_licence($name, $name_short, $licence_summary_uri)
	{
			
		$insert = array(
			'licence_name_short' => $name_short,
			'licence_name' => $name,
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
}

// End of file licences.php