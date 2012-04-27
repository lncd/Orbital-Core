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
	 
	 }
	 
	 /**
	 * Validate OTK
	 *
	 * Validates an OTK against a file, and marks it as used.
	 *
	 * @param string $key		 One-time key.
	 * @param string $identifier Identifier of file.
	 *
	 * @return bool TRUE if key is valid, FALSE if not.
	 */
	 
	 function validate_otk($key, $identifier)
	 {
	 
	 }
	 
	 
	
	function list_public_for_project($identifier)
	{
		if ($archive_files = $this->db->where('file_project', $identifier)->get('archive_files'))
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
	
}

// End of file projects.php