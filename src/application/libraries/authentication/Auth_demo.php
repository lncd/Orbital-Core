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
		
		echo '<p><a href="http://core.orbital.lncd.org/auth/callback/demo?u=orbital@lncd.org">Sign in as orbital@lncd.org</a></p>';
	
	}
	
	function callback()
	{
		echo 'SIGN IN ' . $this->_ci->input->get('u');
	}

}

// End of file Auth_demo.php