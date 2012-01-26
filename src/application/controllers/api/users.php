<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';
require APPPATH.'/libraries/Orbital_Controller.php';

class Users extends Orbital_Controller
{
	function list_get()
    {
        $user = $this->mongo_db->get('users');
    	
        if($user)
        {
            $this->response($user, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'Unable to find users.'), 404);
        }
    }
}