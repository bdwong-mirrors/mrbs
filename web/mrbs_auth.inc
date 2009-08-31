<?php
// $Id$

// include the authentication wrappers
require_once "auth_$auth[type].inc";

if (isset($auth['session']))
{
  require_once "session_$auth[session].inc";
}

// Permissions system
require_once("phpgacl/gacl.class.php");
$gacl_options = array(	'db_type' => 'mysql',
			'db_host' => 'localhost',
			'db_user' => 'mrbs',
			'db_password' => 'mrbs',
			'db_name' => 'mrbs-dev',
			'db_table_prefix' => 'gacl_',
			'debug' => FALSE,
			);
$gacl = new gacl($gacl_options);


/* getAuthorised($level)
 * 
 * Check to see if the current user has a certain level of rights
 * 
 * $level - The access level required
 * 
 * Returns:
 *   0        - The user does not have the required access
 *   non-zero - The user has the required access
 */
/*function getAuthorised($level)
{
  global $auth;

  $user = getUserName();
  if(isset($user) == FALSE)
  {
    authGet();
    return 0;
  }

  return authGetUserLevel($user) >= $level;
}
*/
/* getAuthorised($permission,$object)
 * 
 * Check to see if the current user has a certain permission
 * OR if the permission allowed by default
 * 
 * $user - The user name
 * $asection - The section for the requested action
 * $action - The requested action
 * $osection - The section for the thing being accessed
 * $object - The thing being accessed
 * 
 * Returns:
 *   FALSE	- The user does not have the required access
 *   TRUE	- The user has the required access
 */
function getAuthorised($asection, $action, $osection, $object)
{
  // First check if this permission is allowed by default for all users.
  if (authCheckPermission('default', $asection, $action, $osection, $object))
    return TRUE;

  global $auth;

  $user = getUserName();
  if(isset($user))
  {
    authGet();
    return FALSE;
  }

  return authCheckPermission($user, $asection, $action, $osection, $object);
}

/* getWritable($creator, $user)
 * 
 * Determines if a user is able to modify an entry
 *
 * $creator - The creator of the entry
 * $user    - Who wants to modify it
 *
 * Returns:
 *   0        - The user does not have the required access
 *   non-zero - The user has the required access
 */
function getWritable($creator, $user)
{
  // Always allowed to modify your own stuff
  if(strcasecmp($creator, $user) == 0)
  {
    return 1;
  }

  if(authGetUserLevel($user) >= 2)
  {
    return 1;
  }

  // Unathorised access
  return 0;
}

/* showAccessDenied()
 * 
 * Displays an appropriate message when access has been denied
 * 
 * Returns: Nothing
 */
function showAccessDenied($day, $month, $year, $area, $room)
{
  global $HTTP_REFERER;

  print_header($day, $month, $year, $area, isset($room) ? $room : "");
?>
  <h1><?php echo get_vocab("accessdenied")?></h1>
  <p>
   <?php echo get_vocab("norights")?>
  </p>
  <p>
    <a href="<?php echo htmlspecialchars($HTTP_REFERER); ?>">
      <?php echo get_vocab("returnprev"); ?>
    </a>
  </p>
<?php
  // Print footer and exit
  print_footer(TRUE);
}
?>
