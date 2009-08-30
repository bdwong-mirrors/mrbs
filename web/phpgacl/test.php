<?php


	require_once("gacl.class.php");

	$gacl_options = array(
									'db_type' => 'mysql',
									'db_host' => 'localhost',
									'db_user' => 'root',
									'db_password' => '',
									'db_name' => 'gacl',
								);

	$gacl = new gacl($gacl_options);
	
	if ( $gacl->acl_check( <ACO Section VALUE>, <Access Control Object VALUE> , <ARO Section VALUE>, <Access Request Object VALUE> ) ) {
		echo "Access Granted!";
	} else {
		echo "Access Denied!";
	}

?>
