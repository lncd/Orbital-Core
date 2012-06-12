<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "splash";
$route['404_override'] = '';

$route['project/(:any)/datasets'] = "projects/datasets/$1";
$route['project/(:any)/public'] = "projects/view_public/$1";
$route['project/(:any)/member/(:any)'] = "members/specific/$1/$2";
$route['project/(:any)'] = "projects/view/$1";
$route['members/(:any)'] = "members/view/$1";

$route['file/(:any)/download'] = "files/download/$1";
$route['file/(:any)/get_otk'] = "files/get_otk/$1";
$route['file/(:any)/public'] = "files/file_view_public/$1";
$route['file/(:any)'] = "files/file_view/$1";

$route['file_set/create'] = "files/file_set_create";
$route['file_set/(:any)'] = "files/file_set_view/$1";
$route['file_set_files/(:any)'] = "files/file_set_files/$1";
$route['file_file_sets/(:any)'] = "files/file_file_sets/$1";

$route['dataset/create'] = "datasets/dataset_create";
$route['dataset/(:any)/data'] = "datasets/data/$1";
$route['dataset/(:any)'] = "datasets/specific/$1";

$route['licence/(:any)'] = "licences/specific/$1";

$route['licences/enabled'] = "licences/list_enabled";
$route['licence/(:num)'] = "licences/specific/$1";

// End of file routes.php
// Location: ./config/routes.php