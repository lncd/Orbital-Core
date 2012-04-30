<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * archive storage
 *
 * Stores files in archive.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Upload extends CI_Controller
{	
	function index()
	{
		$this->load->view('upload/form');
	}

	function handler()
	{
		$this->load->library('Uploadhandler');
		$upload_handler = new UploadHandler(array(
			'upload_dir' => $this->config->item('orbital_storage_directory') . '/',
			'discard_aborted_uploads' => true,
			'image_versions' => array()
		));
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Content-Disposition: inline; filename="files.json"');
		header('X-Content-Type-Options: nosniff');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

		switch ($_SERVER['REQUEST_METHOD']) {
		case 'OPTIONS':
			break;
		case 'HEAD':
		case 'GET':
			$upload_handler->get();
			break;
		case 'POST':
			if (isset($_REQUEST['_method']) AND $_REQUEST['_method'] === 'DELETE') {
				$upload_handler->delete();
			} else {
				$upload_handler->post();
			}
			break;
		case 'DELETE':
			$upload_handler->delete();
			break;
		default:
			header('HTTP/1.1 405 Method Not Allowed');
		}
	}
}

// End of file archive.php