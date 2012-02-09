<?php

class Auth_demo {

	private $_ci;
	
	function __construct()
	{
		$this->_ci = get_instance();
	}

	function signin()
	{
	
		echo '<h1>Sign In Demo</h1>';
		
		echo '<p><a href="http://core.orbital.lncd.org/auth/callback/demo?u=orbital@lncd.org&client_id=' . $this->_ci->input->get('client_id') . '">Sign in as orbital@lncd.org</a></p>';
	
	}
	
	function callback()
	{
		if ($this->_ci->input->get('u'))
		{
			$response->email = $this->_ci->input->get('u');
			$response->app_id = $this->_ci->input->get('client_id');
			
			return $response;
		}
		else
		{
			return FALSE;
		}
	}

}

// End of file Auth_demo.php