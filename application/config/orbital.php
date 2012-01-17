<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Institution Name
|--------------------------------------------------------------------------
|
| The name of the institution responsible for this Orbital Core installation.
|
*/
$config['orbital_institution_name'] = '##ORBITAL_INSTITUTION_NAME##';

/*
|--------------------------------------------------------------------------
| Operation Mode
|--------------------------------------------------------------------------
|
| Orbital Core's behaviour mode.
|
| Accepted values:
|   maintenance	- Respond to all requests with the maintenance status message.
|   readonly	- Only accept read requests, respond to all write requests with the readonly status message.
|   normal		- Normal behaviour.
|
*/
$config['orbital_operation_mode'] = 'normal';

/*
|--------------------------------------------------------------------------
| Status Messages
|--------------------------------------------------------------------------
|
| Messages to be returned during various operation modes.
|
| Message types:
|   maintenance	- During maintenance all requests will be responded to with this message.
|   readonly	- During read-only operation all requests will be responded to with this message.
|
*/

$config['orbital_status_message_maintenance'] = 'Orbital is currently undergoing maintenance.';
$config['orbital_status_message_readonly'] = 'Orbital is currently in read-only mode.';

/*
|--------------------------------------------------------------------------
| Orbital Core Version
|--------------------------------------------------------------------------
|
| The version of the Orbital Core.
|
*/
$config['orbital_core_version'] = '0.0.1';

/* End of file orbital.php */
/* Location: ./system/application/config/orbital.php */