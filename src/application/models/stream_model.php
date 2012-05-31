<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Stream Model
 *
 * Allows interaction with the activity stream.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
*/

class Stream_model extends CI_Model {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}

	function add_item($actor, $verb, $type, $identifier)
	{
		$insert = array(
			'timestamp' => time(),
			'payload' => array(
				'actor' => $actor,
				'verb' => $verb,
				'type' => $type,
				'target' => $identifier
			)
		);
		
		if ($this->mongo_db->insert('stream', $insert))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
			throw new Exception('Stream broken!');
		}
	}
}

// End of file stream_model.php