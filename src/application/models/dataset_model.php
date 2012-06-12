<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dynamic Dataset Model
 *
 * Allows interaction with dynamic datasets.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Dataset_model extends CI_Model {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * List Project Datasets
	 *
	 * List dynamic datasets for the given project.
	 *
	 * @param string $project    The project identifier
	 * @param bool   $publiconly Should the list only include public datasets?
	 *
	 * @return ARRAY
	 */

	function list_project_datasets($project, $limit = 5, $publiconly = FALSE)
	{
		
		$this->db->where('dset_project', $project);
			
		if ($publiconly === TRUE)
		{
			$this->db->where('dset_visibility', 'public');
		}
		
		$datasets = $this->db
			->limit($limit)
			->get('datasets');
		
		$output = array();
		
		foreach ($datasets->result() as $dataset)
		{
			$output[] = array(
				'id' => $dataset->dset_id,
				'name' => $dataset->dset_name,
				'visibility' => $dataset->dset_visibility
			);
		}
		
		return $output;
		
	}
	
}

// End of file dataset_model.php