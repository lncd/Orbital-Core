<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Splash extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->config->load('orbital');
		$this->load->view('splash');
	}
}

// EOF