<?php

/**
 * Orbital Configuration File
 *
 * Configuration for this Orbital Core instance.
 *
 * @category   Configuration
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @link       https://github.com/lncd/Orbital-Core
*/

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Institution Name
 *
 * The name of the institution responsible for this Orbital Core
 * installation.
*/

$config['orbital_institution_name'] = $_SERVER['ORBITAL_INSTITUTION_NAME'];

/**
 * Contact Name
 *
 * The name of an administrative contact for this instance.
*/

$config['orbital_contact_name'] = $_SERVER['ORBITAL_CONTACT_NAME'];

/**
 * Contact Email Address
 *
 * The email address of an administrative contact for this instance.
*/

$config['orbital_contact_email'] = $_SERVER['ORBITAL_CONTACT_EMAIL'];

/*
 * Operation Mode
 *
 * Orbital Core's behaviour mode.
 *
 * Accepted values:
 *   maintenance	- Respond to all requests with the maintenance status
 *                 message.
 *   readonly - Only accept read requests, respond to all write requests
 *              with the readonly status message.
 *   normal - Normal behaviour.
*/
$config['orbital_operation_mode'] = $_SERVER['ORBITAL_OPERATION_MODE'];

/**
 * Cluster Series Number
 *
 * A unique ID for this Orbital Core instance if used in a cluster.
*/

$config['orbital_cluster_sn'] = $_SERVER['ORBITAL_CLUSTER_SN'];

/*
 * Status Messages
 *
 * Messages to be returned during various operation modes.
 *
 * Message types:
 *   maintenance	- During maintenance all requests will be responded to with
 *                 this message.
 *   readonly - During read-only operation all requests will be responded to
 *              with this message.
*/

$config['orbital_status_message_maintenance'] = 'Orbital is currently undergoing maintenance.';
$config['orbital_status_message_readonly'] = 'Orbital is currently in read-only mode.';

/*
 * Orbital Core Version
 *
 * The version of the Orbital Core.
*/
$config['orbital_core_version'] = '0.0.2';

// End of file orbital.php
// Location: ./config/orbital.php