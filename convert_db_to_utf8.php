<?php

# $Id$

# This script converts text in the database from a particular encoding
# to UTF-8

include "grab_globals.inc.php";

?>

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>MRBS database encoding fixing script</title>
  </head>
  <body>

  <h1>MRBS database encoding fixing script</h1>

<?php

if (!isset($encoding))
{
  echo '

    <p>
      This script will convert all the text in the database from a selected encoding to
      UTF-8, to use with MRBS\'s new Unicode feature ($unicode_encoding in config.inc.php
      set to 1).<br>
      <b>NOTE: Only run this script <u>once</u>. Running it more than once will
      make a right mess of any non-ASCII text in the database. I\'d recommend you
      backup your database before running this script if you\'re at all worried.</b>
    </p>

    <form method=get action="'.$PHP_SELF.'">
      Encoding to convert from:<br>
      <select name="encoding">
        <option value="iso-8859-1">Latin 1 (English/French/German/Italian/Norwegian etc.)
        <option value="iso-8859-2">Latin 2 (Czech)
        <option value="Big-5">Big 5 (Chinese Traditional)
        <option value="Shift-JIS">Shift-JIS (Japanese)
      </select>
      <p>
      <input type=submit value="Do it">
    </form>
';
}
else
{
  include "config.inc.php";
  require_once("database.inc.php");
  include "$dbsys.inc";
  include "functions.inc";

  echo '
    Starting update, this could take a while...<p>

    Updating areas:
';

  $sql = "SELECT id,area_name FROM $tbl_area";
  $types = array('integer', 'text');
  $areas_res = $mdb->query($sql, $types);
  if (MDB::isError($areas_res))
  {
    fatal_error(1, $areas_res->getMessage() . "<BR>" . $areas_res->getUserInfo() . "<BR>");
  }
  while ($row = $mdb->fetchInto($areas_res))
  {
    $id = $row[0];
    $name = slashes(iconv($encoding,"utf-8",$row[1]));

    $upd_sql = "UPDATE $tbl_area
                SET    area_name=" . $mdb->getTextValue($name). "
                WHERE  id=$id";
    $upd_res = $mdb->query($sql);
    if (MDB::isError($upd_res))
    {
      fatal_error(1, $upd_res->getMessage() . "<BR>" . $upd_res->getUserInfo() . "<BR>");
    }
    $mdb->freeResult($upd_res);

    echo ".";
  }
  $mdb->freeResult($areas_res);
  echo " done.<br>Updating rooms: ";

  $sql = "SELECT id,room_name,description,capacity FROM $tbl_room";
  $types = array('integer', 'text', 'text', 'integer');
  $rooms_res = $mdb->query($sql, $types);
  if (MDB::isError($rooms_res))
  {
    fatal_error(1, $rooms_res->getMessage() . "<BR>" . $rooms_res->getUserInfo() . "<BR>");
  }
  while ($row = $mdb->fetchInto($rooms_res))
  {
    $id = $row[0];
    $name = slashes(iconv($encoding,"utf-8",$row[1]));
    $desc = slashes(iconv($encoding,"utf-8",$row[2]));
    $capa = slashes(iconv($encoding,"utf-8",$row[3]));

    $upd_sql = "UPDATE $tbl_room
                SET    room_name=" . $mdb->getTextValue($name). ",
                       description=" . $mdb->getTextValue($desc). ",
                       capacity=" . $mdb->getTextValue($capa). "
                WHERE  id=$id";
    $upd_res = $mdb->query($sql);
    if (MDB::isError($upd_res))
    {
      fatal_error(1, $upd_res->getMessage() . "<BR>" . $upd_res->getUserInfo() . "<BR>");
    }
    $mdb->freeResult($upd_res);
    echo ".";
  }
  $mdb->freeResult($rooms_res);
  echo " done.<br>Updating repeating entries: ";

  $sql = "SELECT id,name,description FROM $tbl_repeat";
  $types = array('integer', 'text', 'text');
  $repeats_res = $mdb->query($sql, $types);
  if (MDB::isError($repeats_res))
  {
    fatal_error(1, $repeats_res->getMessage() . "<BR>" . $repeats_res->getUserInfo() . "<BR>");
  }
  while ($row = $mdb->fetchInto($repeats_res))
  {
    $id = $row[0];
    $name = slashes(iconv($encoding,"utf-8",$row[1]));
    $desc = slashes(iconv($encoding,"utf-8",$row[2]));

    $upd_sql = "UPDATE $tbl_repeat
                SET    name=" . $mdb->getTextValue($name). ",
                       description=" . $mdb->getTextValue($desc). "
                WHERE  id=$id";
    $upd_res = $mdb->query($sql);
    if (MDB::isError($upd_res))
    {
      fatal_error(1, $upd_res->getMessage() . "<BR>" . $upd_res->getUserInfo() . "<BR>");
    }
    $mdb->freeResult($upd_res);

    echo ".";
  }
  $mdb->freeResult($repeats_res);
  echo " done.<br>Updating normal entries: ";

  $sql = "SELECT id,name,description FROM $tbl_entry";
  $types = array('integer', 'text', 'text');
  $entries_res = $mdb->query($sql, $types);
  if (MDB::isError($entries_res))
  {
    fatal_error(1, $entries_res->getMessage() . "<BR>" . $entries_res->getUserInfo() . "<BR>");
  }
  while ($row = $mdb->fetchInto($entries_res))
  {
    $id = $row[0];
    $name = slashes(iconv($encoding,"utf-8",$row[1]));
    $desc = slashes(iconv($encoding,"utf-8",$row[2]));

    $upd_sql = "UPDATE $tbl_entry
                SET    name=" . $mdb->getTextValue($name). ",
                       description=" . $mdb->getTextValue($desc). "
                WHERE  id=$id";
    $upd_res = $mdb->query($sql);
    if (MDB::isError($upd_res))
    {
      fatal_error(1, $upd_res->getMessage() . "<BR>" . $upd_res->getUserInfo() . "<BR>");
    }
    $mdb->freeResult($upd_res);

    echo ".";
  }
  $mdb->freeResult($entries_res);
  echo 'done.<p>

    Finished everything, byebye!
';
}
?>
  </body>
</html>
