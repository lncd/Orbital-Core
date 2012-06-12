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
	
	/**
	 * Validate Set Token
	 *
	 * Ensures that the token is valid for the set
	 *
	 * @param string $dataset Identifier of the dataset
	 * @param string $token   Token to validate
	 *
	 * @return BOOL
	 */
	
	function validate_token($dataset, $token)
	{
		if ($this->db
			->where('dset_id', $dataset)
			->where('dset_key', $token)
			->count_all_results('datasets') === 1)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Add Datapoint
	 *
	 * Add a datapoint to a dataset
	 */
	
	function add_datapoint($dataset, $datapoint)
	{
	
		// Extract ID (if necessary)
		if (isset ($datapoint->id) AND is_string($datapoint->id))
		{
			$id = $datapoint->id;
		}
		else
		{
			$id = uniqid($this->config->item('orbital_cluster_sn'));
		}
		
		unset($datapoint->id);
	
		$insert = array(
			'last_update_time' => time(),
			'last_data' => $datapoint,
			'history' => array(
				time() => $datapoint
			)
		);
	
		$this->mongo_db
			->where(array('_id' => $id))
			->set(array(
				'update_time' => time(),
				'data' => $datapoint
			))
			->push('history', array(
				'time' => time(),
				'data' => $datapoint
			))
			->update('dataset_' . $dataset, array('upsert' => TRUE));
	}
}

// End of file dataset_model.php