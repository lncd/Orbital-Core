<?php defined('BASEPATH') or exit('No direct script access allowed');

class Queue extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Insert Queue Message
	 *
	 * Adds a new message to the queue.
	 *
	 * @param string $queue Name of the queue.
	 * @param array  $payload The message payload for the queue consumer.
	 *
	 * @return mixed String identifying message if creation has succeeded,
	 * FALSE if not.
	 */
	
	function insert_message($queue, $payload)
	{
		
		// Generate identifier
		$identifier = uniqid($this->config->item('orbital_cluster_sn'));
		
		$insert = array(
			'_id' => $identifier,
			'queue' => $queue,
			'payload' => $payload,
			'timestamp' => time(),
			'state' => 'new'
		);
		
		// Attempt insert
		
		if ($this->mongo_db->insert('queue', $insert))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}

}

// End of file queue.php