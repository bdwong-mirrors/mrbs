<?php
// +---------------------------------------------------------------------------+
// | Meeting Room Booking System.
// +---------------------------------------------------------------------------+
// | MRBS table destruction script.
// |---------------------------------------------------------------------------+
// | This exists because I can never remember the sequence name magic.
// | Usage: put this file in you mrbs directory and run it in you browser.
// | Note: this script will use the default mrbs login defined in
// | config.inc.php. This user must be allowed to DROP tables.
// +---------------------------------------------------------------------------+
// | @author    thierry_bo.
// | @version   $Revision$.
// +---------------------------------------------------------------------------+
//
// $Id$

require "config.inc.php";
require_once("database.inc.php");

$mdb->dropTable($tbl_area);
$mdb->dropSequence("${tbl_area}_id");
$mdb->dropTable($tbl_room);
$mdb->dropSequence("${tbl_room}_id");
$mdb->dropTable($tbl_entry);
$mdb->dropSequence("${tbl_entry}_id");
$mdb->dropTable($tbl_repeat);
$mdb->dropSequence("${tbl_repeat}_id");
?>
