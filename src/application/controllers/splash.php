<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Splash Page
 *
 * Outputs a splash page for non-API requests of the Core.
 *
 * @package		Orbital
 * @subpackage  Core
 * @author		Nick Jackson
 * @link		https://github.com/lncd/Orbital-Core
 */

class Splash extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->view('splash');
	}
}

// EOF