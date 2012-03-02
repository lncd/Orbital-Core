<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -------------------------------------------------------------------
 * EXPLANATION OF VARIABLES
 * -------------------------------------------------------------------
 *
 * ['mongo_hostbase'] The hostname (and port number) of your MongoDB server.
 * ['mongo_database'] The name of the database you want to connect to
 * ['mongo_username'] The username used to connect to the database (if auth mode is enabled)
 * ['mongo_password'] The password used to connect to the database (if auth mode is enabled)
 * ['mongo_persist']  Persist the connection
 * ['mongo_persist_key'] The persistant connection key
 * ['mongo_query_safety'] Safety level of write queries. "safe" = committed in memory, "fsync" = committed to harddisk
 * ['mongo_supress_connect_error'] If the driver can't connect by default it will throw an error which dislays the username and password used to connect. Set to TRUE to hide these details.
 * ['mongo_host_db_flag']   If running in auth mode and the user does not have global read/write then set this to true
 */

$config['default']['mongo_hostbase'] = $_SERVER['ORBITAL_MONGO_SERVERS'];
$config['default']['mongo_database'] = $_SERVER['ORBITAL_MONGO_DATABASE'];
$config['default']['mongo_username'] = $_SERVER['ORBITAL_MONGO_USER'];
$config['default']['mongo_password'] = $_SERVER['ORBITAL_MONGO_PASSWORD'];
$config['default']['mongo_persist']  = TRUE;
$config['default']['mongo_persist_key']	 = 'orbital_core_persist_key';
$config['default']['replica_set']  = 'orbital';
$config['default']['mongo_query_safety'] = 'safe';
$config['default']['mongo_supress_connect_error'] = TRUE;
$config['default']['mongo_host_db_flag']   = TRUE;

$config['admin']['mongo_hostbase'] = $_SERVER['ORBITAL_MONGO_SERVERS'];
$config['admin']['mongo_database'] = 'admin';
$config['admin']['mongo_username'] = $_SERVER['ORBITAL_MONGO_ADMIN_USER'];
$config['admin']['mongo_password'] = $_SERVER['ORBITAL_MONGO_ADMIN_PASSWORD'];
$config['admin']['mongo_persist']  = TRUE;
$config['admin']['mongo_persist_key']	 = 'orbital_core_admin_persist_key';
$config['admin']['replica_set']  = 'orbital';
$config['admin']['mongo_query_safety'] = 'safe';
$config['admin']['mongo_supress_connect_error'] = TRUE;
$config['admin']['mongo_host_db_flag']   = TRUE;