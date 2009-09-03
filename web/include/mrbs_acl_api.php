<?

/*
 * Call and configure the mrbs_acl_api class
 *
*/

require_once str_replace('//','/',dirname(__FILE__).'/') . '../phpgacl/gacl_api.class.php';
require_once str_replace('//','/',dirname(__FILE__).'/') . 'mrbs_acl_api.class.php';

$gacl_options = array(	'db_type' => $dbsys,
			'db_host' => $db_host,
			'db_user' => $db_login,
			'db_password' => $db_password,
			'db_name' => $db_database,
			'db_table_prefix' => 'gacl_',
			'debug' => FALSE,
			);
$mrbs_acl_api = new MRBS_acl_api($gacl_options);


?>
