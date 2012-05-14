<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Splash Page
 *
 * Outputs a splash page for non-API requests of the Core.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Splash extends CI_Controller {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Splash Page
	 *
	 * Displays Orbital Core splash page to the user.
	*/

	function index()
	{
		$this->load->view('splash');
	}
}

// EOF