<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Core Functions
 *
 * Support functions relating to the operation of the core.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Core extends Orbital_Controller
{

	/**
	 * Returns a 'pong' response message.
	 *
	 * @access public
	 */

	public function ping_get()
	{
	
		$ping->message = 'pong';
		$ping->error = FALSE;
		
		$this->response($ping);
		
	}
	
	/**
	 * Returns a list of all supported authentication types
	 *
	 * @access public
	 */
	
	public function auth_types_get()
	{
	
		$auth_types = $this->oauth_model->get_handlers();
	
		if (count($auth_types) > 0)
		{
		
			foreach ($auth_types as $auth_type)
			{
				$auth_type['uri'] = site_url('auth/signin/' . $auth_type['tag']);
				$response->auth_types[] = $auth_type;
			}
			
			$response->error = FALSE;
			
			$this->response($response);
			
		}
		else
		{
			$response->error = TRUE;
			$response->message = 'No authentication types are configured.';
			
			$this->response($response, 500);
		
		}
		
	}
	
	/**
	 * Returns the current status of the Mongo system
	 *
	 * @access public
	 */
	
	public function mongo_server_status_get()
	{
	
		if ($user = $this->access->valid_user(array('administration')))
		{
			if ($this->access->user_is_admin($user))
			{
				if ($status = $this->mongo_db->admin_server_status())
				{
				
					$response->server = $status;
				
					if (isset($response->server['repl']['setName']))
					{
						$response->replica_set = $this->mongo_db->admin_replica_set_status();
					}
					
					$response->error = FALSE;
					
					$this->response($response);
					
				}
				else
				{
					$response->error = TRUE;
					$response->message = 'Error retrieving server status.';
					
					$this->response($response, 500);
				}
			}
		}
	}
}