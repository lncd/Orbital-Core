<?php

/**
 * rackspace cloud storage
 *
 * Stores files in rackspace cloud.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @link       https://github.com/lncd/Orbital-Core
 */

include_once('cloud_files/cloudfiles.php');

class Storage_rackspacecloud {

	private $_ci;

	function __construct()
	{
		$this->_ci =& get_instance();
	}

	function save($file_location, $identifier, $metadata, $container, $public = FALSE)
	{
		try{

			//If public, container name = project_public:identifier else project:identifier

			if ($public === TRUE)
			{
				$container = "project_public:" . $container;
			}
			else
			{
				$container = "project:" . $container;
			}

			//Create an perform authentication request
			$auth = new CF_Authentication("lncd", "aabbd85ab833dc2ad3cf3f1e51a5e643", NULL, UK_AUTHURL);
			$auth->authenticate();
			$conn = new CF_Connection($auth, $servicenet=False);

			//Check if container exists, else make it
			try
			{
				$cf_container = $conn->get_container($container); //get container
			}
			catch(Exception $e)
			{
				$cf_container = $conn->create_container($container);
				if ($public === TRUE)
				{
					$cf_container->make_public();
				}
			}

			//Check if file exists, else upload it
			try
			{
				$doc = $cf_container->get_object($identifier);
			}
			catch (Exception $e)
			{
				//Push file to container with metadata

				$doc = $cf_container->create_object($identifier);

				# Upload local File's content
				$doc->load_from_filename($file_location);
			}
			if ($doc->public_uri() !== NULL)
			{
				return $doc->public_uri();
			}
			else
			{
				return TRUE;
			}
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	function retrieve($identifier, $container, $public = FALSE)
	{
	}
}