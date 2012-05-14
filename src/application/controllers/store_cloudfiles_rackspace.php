<?php defined('BASEPATH') or exit('No direct script access allowed');


class Store_cloudfiles_rackspace extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->library('storage/Storage_rackspacecloud');
		
		echo $this->storage_rackspacecloud->save('/Users/hnewton/Desktop/TEST.txt', 'projectTESTIdentifier', 'This is a test', 'NICKNICKNICK');
		echo $this->storage_rackspacecloud->save('/Users/hnewton/Desktop/TEST.txt', 'projectTESTIdentifier', 'This is a test', 'NICKNICKNICK', TRUE);

	}
}
