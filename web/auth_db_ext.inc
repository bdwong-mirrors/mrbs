<?php
/*****************************************************************************\
*									      *
*   File name       auth_db_ext.inc					      *
*									      *
*   Description	    Authenticate users from a table in another database.      *
*									      *
*   Notes	    To use this authentication scheme, set in config.inc.php: *
*			$auth["type"]  = "db_ext";			      *
*                   Assumes passwords are stored in the other table in        *
*                   plaintext, authValidateUser() will need to be changed if  *
*                   the password is stored differently.                       *
*									      *
*   History								      *
*    2005/05/11 JPB Created this file					      *
*									      *
\*****************************************************************************/

// $Id$

// Config

$my_db_sys = 'mysql';
$my_db_host = 'localhost';
$my_db_port = '';
$my_db_protocol = 'tcp';
$my_db_username = 'db_user';
$my_db_password = 'db_password';
$my_db_name = 'db_name';
$my_db_table = 'table_name';
$column_name_username = 'name';
$column_name_password = 'password';


/* authValidateUser($user, $pass)
 * 
 * Checks if the specified username/password pair are valid
 * 
 * $user  - The user name
 * $pass  - The password
 * 
 * Returns:
 *   0        - The pair are invalid or do not exist
 *   non-zero - The pair are valid
 */

function authValidateUser($user, $pass)
{
  global $my_db_sys, $my_db_host, $my_db_port, $my_db_protcol, $my_db_username,
    $my_db_password, $my_db_name, $my_db_table, $column_name_username,
    $column_name_password, $oci8_home;

  $retval = 0;

  $user=strtolower($user);

  require_once("MDB.php");

  $my_mdb=&MDB::connect(array
                        (
                          "phptype"  => $my_db_sys,
                          "username" => $my_db_username,
                          "password" => $my_db_password,
                          "hostspec" => $my_db_host,
                          "protocol" => $my_db_protocol,
                          "port"     => $my_db_port,
                          "database" => $my_db_name
                          )
                        , array
                        (
                          'optimize'   => 'portability',
                          'HOME'       => $oci8_home 
                          )
    );

  if (!MDB::isError($my_mdb)) {

    $r = $my_mdb->queryOne("SELECT count(*)
                            FROM $my_db_table
                            WHERE $column_name_username=" .
                            $my_mdb->getTextValue($user) .
                            " AND $column_name_password=" .
                            $my_mdb->getTextValue($pass),
                            'integer');

    if (MDB::isError($r)) {
      echo $r->getMessage() . "<br>" . $r->getUserInfo();
      exit;
    } else if ($r == 1) {
      $retval = 1;
    }

    $my_mdb->disconnect();

  }
  return $retval;
}

/* authGetUserLevel($user)
 * 
 * Determines the users access level
 * 
 * $user - The user name
 *
 * Returns:
 *   The users access level
 */
function authGetUserLevel($user, $lev1_admin)
{
   // User not logged in, user level '0'
   if(!isset($user))
      return 0;

   // Check if the user is can modify
   for($i = 0; isset($lev1_admin[$i]); $i++)
   {
      if(strcasecmp($user, $lev1_admin[$i]) == 0)
	 return 2;
   }

   // Everybody else is access level '1'
   return 1;
}

?>
