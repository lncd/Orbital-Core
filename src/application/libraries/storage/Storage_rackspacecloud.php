<?php

include_once('cloud_files/cloudfiles.php');

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

class Storage_rackspacecloud {

	/**
	 * CodeIgniter instance
	 */

	private $_ci;

	/**
	 * Constructor
	 */

	function __construct()
	{
		$this->_ci =& get_instance();
	}

	/**
	 * Save file to rackspace server
	 *
	 * @param string $file_location The location of the file to be uploaded.
	 * @param string $identifier    The project identifier.
	 * @param array  $metadata      Additional information about the file.
	 * @param string $container     Folder containing the project.
	 * @param bool   $public    If the project is publicly accessible.
	 */

	function save($file_location, $identifier, $metadata, $container, $public = FALSE)
	{
		try
		{
			//If public, container name = project_public:identifier else project:identifier

			if ($public === TRUE)
			{
				$container = 'project_public:' . $container;
			}
			else
			{
				$container = 'project:' . $container;
			}

			//Create an perform authentication request
			$auth = new CF_Authentication('lncd', 'aabbd85ab833dc2ad3cf3f1e51a5e643', NULL, UK_AUTHURL);
			$auth->authenticate();
			$conn = new CF_Connection($auth, $servicenet = FALSE);

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

				//Upload local File's content
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
			return FALSE;
		}
	}
}