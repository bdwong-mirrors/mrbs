<?php

# $Id$

require_once("grab_globals.inc.php");
require "config.inc.php";
require "functions.inc";
require_once("database.inc.php");
require "$dbsys.inc";
require "mrbs_auth.inc";

global $tbl_area;
global $tbl_room;

if(!getAuthorised(getUserName(), getUserPassword(), 2))
{
	showAccessDenied($day, $month, $year, $area);
	exit();
}

# This file is for adding new areas/rooms

# we need to do different things depending on if its a room
# or an area

if ($type == "area")
{
    $area_name_q = unslashes($name);
    $id = $mdb->nextId("${tbl_area}_id");
    if (MDB::isError($id))
    {
        fatal_error(1, "<p>" . $id->getMessage() . "<br>" . $id->getUserInfo());
    }
    $sql = "INSERT INTO $tbl_area (id, area_name) 
            VALUES      ($id, " . $mdb->getTextValue($area_name_q) . ")";
    $res = $mdb->query($sql);
    if (MDB::isError($res))
    {
        fatal_error(1, "<p>" . $res->getMessage() . "<br>" . $res->getUserInfo());
    }
    $area = $mdb->currId("${tbl_area}_id");
}

if ($type == "room")
{
    $room_name_q = unslashes($name);
    $description_q = unslashes($description);
	if (empty($capacity)) $capacity = 0;
    $id = $mdb->nextId("${tbl_room}_id");
    $sql = "INSERT INTO $tbl_room (id, room_name, area_id, description, capacity)
            VALUES      ($id, " . $mdb->getTextValue($room_name_q) . ", $area, "
                        . $mdb->getTextValue($description_q) . ", $capacity)";
    $res = $mdb->query($sql);
    if (MDB::isError($res))
    {
        fatal_error(1, "<p>" . $res->getMessage() . "<br>" . $res->getUserInfo());
    }
}

header("Location: admin.php?area=$area");
?>
