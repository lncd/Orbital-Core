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
	 * Create dataset
	 *
	 * Creates a new dataset.
	 *
	 * @param string $project_identifier  The project identifier
	 * @param string $dataset_name        The dataset name
	 * @param string $dataset_description The dataset description
	 *
	 * @return ARRAY
	 */

	function create_dataset($project_identifier, $dataset_name, $dataset_description)
	{
		$identifier = uniqid($this->config->item('orbital_cluster_sn'));
		$dset_key = random_string('alnum', 64);
		
		$insert = array(
			'dset_id' => $identifier,
			'dset_name' => $dataset_name,
			'dset_description' => $dataset_description,
			'dset_project' => $project_identifier,
			'dset_key' => $dset_key,
			'dset_licence' => 4,
			'dset_visibility' => 'private'
		);

		// Attempt create

		if ($this->db->insert('datasets', $insert))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
		echo mysqli_error();
	}
	
	/**
	 * List Project Datasets
	 *
	 * List dynamic datasets for the given project.
	 *
	 * @param string $project    The project identifier
	 * @param int    $project    The limit of datasets to show
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
				'description' => $dataset->dset_description,
				'licence' => $dataset->dset_licence,
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
	 *
	 * @param string $dataset   Identifier of the dataset
 	 * @param string $datapoint The datapoint to add
	 *
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
	
	function query_dataset($dataset, $query = array(), $select = array())
	{
	
		foreach ($query as $key => $limits)
		{
			
			foreach ($limits as $type => $value)
			{
				switch ($type){
				
					case 'equals':
						$this->mongo_db->where('data.' . $key, $value);
									break;
						
					case 'gt':
						$this->mongo_db->where_gt('data.' . $key, $value);
									break;
						
					case 'gte':
						$this->mongo_db->where_gte('data.' . $key, $value);
									break;
						
					case 'lt':
						$this->mongo_db->where_lt('data.' . $key, $value);
									break;
						
					case 'lte':
						$this->mongo_db->where_lte('data.' . $key, $value);
									break;
				}
			}
		}
		
		foreach ($select as $field)
		{
			$this->mongo_db->select(array('data.' . $field));
		}
	
		$datapoints = $this->mongo_db
			->select(array('_id'))
			->get('dataset_' . $dataset);
			
		$output = array();
		
		foreach ($datapoints as $datapoint)
		{
			$output[] = array_merge(array(
				'id' => $datapoint['_id']
			), $datapoint['data']);
				
		}
		
		return $output;
		
	}
	
	function get_query($dataset_id, $query_id)
	{		
		$query = $this->mongo_db
			->where(array('set' => $dataset_id, 'query' => $query_id))
			->get('queries');
			
		return $query[0]['value'];
	}

	function get_dataset_details($identifier)
	{
		if ($archive_dataset = $this->db
			->where('dset_id', $identifier)
			->join('projects', 'project_id = dset_project')
			->get('datasets'))
		{
			$archive_dataset = $archive_dataset->row();

			return array
			(
				'id' => $archive_dataset->dset_id,
				'title' => $archive_dataset->dset_name,
				'description' => $archive_dataset->dset_description,
				'licence' => $archive_dataset->dset_licence,
				'visibility' => $archive_dataset->dset_visibility,
				'project' => $archive_dataset->project_id,
				'project_name' => $archive_dataset->project_name,
				'project_public_view' => $archive_dataset->project_public_view,
				'token' => $archive_dataset->dset_key
			);
		}
		else
		{
			return FALSE;
		}
	}
	
	function build_query($dataset_identifier, $query_id, $field, $operator, $value, $output_fields)
	{
		if ($this->mongo_db
			->where(array('set' => $dataset_identifier, 'query' => $query_id))
			->set(array('value.statements.' . $field . '.' . $operator => $value))
			->set(array('value.statements.fields', $output_fields))
			->update('queries', array('upsert' => TRUE)))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}

// End of file dataset_model.php