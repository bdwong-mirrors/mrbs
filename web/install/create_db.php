<?php
/* 
 * Called during install using AJAX call.
 */

require_once "../grab_globals.inc.php";
require_once "../systemdefaults.inc.php";
require_once "../config.inc.php";

$user = get_form_var('un', 'string');
$password = get_form_var('pw', 'string');
$sql_file = '../../tables.my.sql';

function mysql_exec_batch ($p_query, $p_transaction_safe = true) {
  if ($p_transaction_safe) {
      $p_query = 'START TRANSACTION;' . $p_query . '; COMMIT;';
    };
  $query_split = preg_split ("/[;]+/", $p_query);
  foreach ($query_split as $command_line) {
    $command_line = trim($command_line);
    if ($command_line != '') {
      $query_result = mysql_query($command_line);
      if ($query_result == 0) {
        break;
      };
    };
  };
  return $query_result;
}

function parse_mysql_dump($path) {
    $file_content = file($path);
    foreach($file_content as $sql_line) {
        if(trim($sql_line) != "" && strpos($sql_line, "--") === false) $stmt .= $sql_line;
    }
    if (!$result = mysql_exec_batch($stmt)) die ('var fail_msg = "Create tables failed: ' . mysql_error() . '";'."\n");
}

    if (!empty($user) && !empty($password)) {
    // Connect to database
        for ($i = 0; $i < 5; $i++) {
            $link = mysql_connect($db_host, $user, $password);
            $errno = mysql_errno();
            if ($errno == 1040 || $errno == 1226 || $errno == 1203 || $errno == 2002) {
                sleep(1);
            }  else {
                break;
            }
        }
        // Create DB
        $result = mysql_query("SELECT Db FROM mysql.db WHERE Db='$db_database'") or die('var fail_msg = "' . mysql_error() . '";'."\n");
        if (mysql_num_rows($result)!==0)
            mysql_query("DROP DATABASE $db_database") or die('var fail_msg = "Drop database failed: ' . mysql_error() . '";'."\n");
        mysql_query("CREATE DATABASE $db_database") or die('var fail_msg = "Create database failed: ' . mysql_error() . '";'."\n");
        // Create MRBS user
        $result = mysql_query("SELECT user FROM mysql.user WHERE user='$db_login'") or die('var fail_msg = "' . mysql_error() . '";'."\n");
        if (mysql_num_rows($result)!==0)
            mysql_query("DROP USER '$db_login'@'$db_host'") or die('var fail_msg = "Drop user failed: ' . mysql_error() . '";'."\n");;
        mysql_query("CREATE USER '$db_login'@'$db_host' IDENTIFIED BY '$db_password'") or die('var fail_msg = "Create user failed: ' . mysql_error() . '";'."\n");;
        mysql_query("GRANT ALL ON $db_database.* TO '$db_login'@'$db_host'") or die('var fail_msg = "Grant privileges to user failed: ' . mysql_error() . '";'."\n");;
        // Create and populate tables
        mysql_select_db($db_database) or die ('var fail_msg = "Database not found: ' . $db_database . '";'."\n");
        parse_mysql_dump($sql_file, $db_host, $db_database, $db_login, $db_password);

        echo "var success = true;\n"; //must have succeeded to get here.
    } else {
        echo "var success = false;\n";
        echo "var fail_msg = 'No username or password';";
    }

?>