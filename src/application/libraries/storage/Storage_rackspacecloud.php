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
	 * CodeIgniter instance.
	 *
	 * @var _ci instance of CodeIngiter
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
	 *
	 * @return NULL
	 */

	function save($file_location, $identifier, $metadata, $container)
	{
		try
		{
			$container = 'project:' . $container;
			
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
				return TRUE;
			
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}
	
	/**
	 * Delete file from rackspace server
	 *
	 * @param string $identifier The project identifier.
	 * @param string $container Folder containing the project.
	 *
	 * @return BOOL
	 */
	
	function delete($identifier, $container)
	{
		try
		{
			$container = 'project:' . $container;
			
			//Create an perform authentication request
			$auth = new CF_Authentication('lncd', 'aabbd85ab833dc2ad3cf3f1e51a5e643', NULL, UK_AUTHURL);
			$auth->authenticate();
			$conn = new CF_Connection($auth, $servicenet = FALSE);

			try
			{
				//Check if container exists
				$cf_container = $conn->get_container($container);
			}
			catch(Exception $e)
			{
				return FALSE;
			}
			try
			{			
				//Check if file exists
				$doc = $cf_container->get_object($identifier);
				//Delete file
				$doc = $cf_container->delete_object($identifier);
			}
			catch (Exception $e)
			{
				return FALSE;
			}
			
			return TRUE;
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}
}
//End of file Storage_rackspacecloud.php