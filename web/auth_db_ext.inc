<?php
/*****************************************************************************\
*                                                                             *
*   File name       auth_db_ext.inc                                           *
*                                                                             *
*   Description     Authenticate users from a table in another database.      *
*                                                                             *
*   Notes           To use this authentication scheme, set in config.inc.php: *
*                       $auth["type"]  = "db_ext";                            *
*                                                                             *
*   History                                                                   *
*    2005/05/11 JPB Created this file                                         *
*    Remaining history in ChangeLog and CVS logs                              *
*                                                                             *
\*****************************************************************************/

// $Id$

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
    global $auth, $oci8_home;

    $retval = 0;

    $user=strtolower($user);

    require_once("MDB.php");

    $my_mdb=&MDB::connect(array(
                              "phptype"  => $auth['db_ext']['db_sys'],
                              "hostspec" => $auth['db_ext']['db_host'],
                              "port"     => $auth['db_ext']['db_port'],
                              "protocol" => $auth['db_ext']['db_protocol'],
                              "username" => $auth['db_ext']['db_username'],
                              "password" => $auth['db_ext']['db_password'],
                              "database" => $auth['db_ext']['db_name']
                              ),
                          array(
                              'optimize'   => 'portability',
                              'HOME'       => $oci8_home 
                              )
        );

  if (!MDB::isError($my_mdb)) {

      $r = $my_mdb->queryOne("SELECT " . $auth['db_ext']['column_name_password'] .
                             " FROM " . $auth['db_ext']['db_table'] .
                             " WHERE " . $auth['db_ext']['column_name_username'] .
                             "=" . $my_mdb->getTextValue($user),
                             'integer');

      if (MDB::isError($r)) {
          echo $r->getMessage() . "<br>" . $r->getUserInfo();
          exit;
      } else {

          switch ($auth['db_ext']['password_format'])
          {
              case 'md5':
                  if (md5($pass) == $r)
                  {
                      $retval = 1;
                  }
                  break;

              case 'sha1':
                  if (sha1($pass) == $r)
                  {
                      $retval = 1;
                  }
                  break;

              case 'crypt':
                  $recrypt = crypt($pass,$r);
                  if ($r == $recrypt)
                  {
                      $retval = 1;
                  }
                  break;

              default:
                  // Otherwise assume plaintext
                  if ($pass == $r)
                  {
                      $retval = 1;
                  }
                  break;
          }
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
