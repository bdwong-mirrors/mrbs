<?
/*
 * Stage Two of Installation
 *
 * This file installs the default permissions data for MRBS
 * into the phpGACL tables.
 *
*/

require_once "../config.inc.php"; //To get MRBS DB config
$gacl_options = array(	'db_type' => $dbsys,
			'db_host' => $db_host,
			'db_user' => $db_login,
			'db_password' => $db_password,
			'db_name' => $db_database,
			'db_table_prefix' => 'gacl_',
			'debug' => FALSE,
			);
require_once "gacl.class.php";
require_once "gacl_api.class.php";



/*
$gacl_options = array(	'db_type' => 'mysqli',
//			'db_host' => 'localhost',
//			'db_user' => 'root',
//			'db_password' => 'root',
//			'db_name' => 'mrbs-test1',
//			'db_table_prefix' => 'gacl_',
//			'debug' => FALSE,
			);
*/
$gacl = new gacl_api($gacl_options);

echo "<p>Creating ARO groups<br />";

$group_mrbs_users = $gacl->add_group('mrbs-users','MRBS Users',0,'ARO');
$group_admins = $gacl->add_group('administrators','Administrators',$group_mrbs_users,'ARO');
$group_app_admins = $gacl->add_group('application-administrators','Application Administrators',$group_admins,'ARO');
$group_area_admins = $gacl->add_group('area-administrators','Area Administrators',$group_admins,'ARO');
$group_booking_admins = $gacl->add_group('booking-administrators','Booking Administrators',$group_admins,'ARO');
$group_room_admins = $gacl->add_group('room-administrators','Room Administrators',$group_admins,'ARO');
$group_user_admins = $gacl->add_group('user-administrators','User Administrators',$group_admins,'ARO');
$group_users = $gacl->add_group('users','Users',$group_mrbs_users,'ARO');
$group_general_users = $gacl->add_group('general-users','General Users',$group_users,'ARO');

echo "Creating AXO groups<br />";

$group_objects = $gacl->add_group('objects','Objects',0,'AXO');

$parent_id = $gacl->get_group_id('objects');
$group_apps = $gacl->add_group('applications','Applications',$group_objects,'AXO');
$group_areas = $gacl->add_group('areas','Areas',$group_objects,'AXO');
$group_bookings = $gacl->add_group('bookings','Bookings',$group_objects,'AXO');
$group_rooms = $gacl->add_group('rooms','Rooms',$group_objects,'AXO');
$group_users = $gacl->add_group('users','Users',$group_objects,'AXO');

$group_all_apps = $gacl->add_group('all-applications','All Applications',$group_apps,'AXO');
$group_all_areas = $gacl->add_group('all-areas','All Areas',$group_areas,'AXO');
$group_all_bookings = $gacl->add_group('all-bookings','All Bookings',$group_bookings,'AXO');
$group_all_rooms = $gacl->add_group('all-rooms','All Rooms',$group_rooms,'AXO');
$group_all_users = $gacl->add_group('all-users','All Users',$group_users,'AXO');

echo "Creating ARO sections and objects<br />";
$gacl->add_object_section('Users','users',0,0,'ARO');
$gacl->add_object('users','Default user','default',0,0,'ARO');
$gacl->add_object('users','Default administrator','admin',0,0,'ARO');

echo "Creating AXO sections and objects<br />";
$gacl->add_object_section('Bookings','bookings',0,0,'AXO');
$gacl->add_object_section('Rooms','rooms',0,0,'AXO');
$gacl->add_object_section('Areas','areas',0,0,'AXO');
$gacl->add_object_section('Users','users',0,0,'AXO');
$gacl->add_object_section('Applications','applications',0,0,'AXO');
$gacl->add_object('bookings','New','new',0,0,'AXO');
$gacl->add_object('rooms','New','new',0,0,'AXO');
$gacl->add_object('areas','New','new',0,0,'AXO');
$gacl->add_object('users','New','new',0,0,'AXO');
$gacl->add_object('applications','MRBS','mrbs',0,0,'AXO');
$gacl->add_object('applications','MRBS Admin','mrbs-admin',0,0,'AXO');
$gacl->add_object('applications','phpGACL','phpgacl',0,0,'AXO');

echo "Creating sections and objects<br />";
$gacl->add_object_section('Generic Actions','generic',0,0,'ACO');
$gacl->add_object('generic','View','view',0,0,'ACO');
$gacl->add_object('generic','Create','create',0,0,'ACO');
$gacl->add_object('generic','Edit','edit',0,0,'ACO');
$gacl->add_object('generic','Delete','delete',0,0,'ACO');

echo "Creating ACLs<br /></p>";
$gacl->add_acl(array('generic' => array('view')), array('users' => array('default')), '', array('applications' => array('mrbs')) , '');
$gacl->add_acl(array('generic' => array('view')), array('users' => array('default')), '', '', array($group_all_areas,$group_all_bookings,$group_all_rooms));
$gacl->add_acl(array('generic' => array('create')),  array('users' => array('default')), '', array('bookings' => array('new')), '');
$gacl->add_acl(array('generic' => array('view','edit','delete')), '', array($group_area_admins), '', array($group_all_areas));
$gacl->add_acl(array('generic' => array('view','edit','delete')), '', array($group_room_admins), '', array($group_all_rooms));
$gacl->add_acl(array('generic' => array('view','edit','delete')), '', array($group_booking_admins), '', array($group_all_bookings));
$gacl->add_acl(array('generic' => array('view','edit','delete')), '', array($group_user_admins), '', array($group_all_users));
$gacl->add_acl(array('generic' => array('view','edit','delete')), '', array($group_app_admins), '', array($group_all_apps));
$gacl->add_acl(array('generic' => array('create')), '', array($group_area_admins), array('areas' => array('new')), '');
$gacl->add_acl(array('generic' => array('create')), '', array($group_room_admins), array('rooms' => array('new')), '');
$gacl->add_acl(array('generic' => array('create')), '', array($group_booking_admins), array('bookings' => array('new')), '');
$gacl->add_acl(array('generic' => array('create')), '', array($group_user_admins), array('users' => array('new')), '');

echo '<p>Done!</p>';
echo '<p>Now let\'s <a href="../index.php">start using MRBS...<a/></p>';

?>
