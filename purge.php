<?php
// +---------------------------------------------------------------------------+
// | Meeting Room Booking System.
// +---------------------------------------------------------------------------+
// | Purge old MRBS entries.
// |---------------------------------------------------------------------------+
// | This PHP script will delete old entries from your MRBS database.
// | By default, entries which ended 30 days or more in the past will be
// | removed. Also, repeat table records with no corresponding entry records
// | will be removed.
// |
// | If old entries get purged from a series, then somebody edits the series,
// | the old entries will be re-created unless they change the start date on
// | the form. Fixing this would require changing the start_time and end_time
// | in the repeat record to match oldest undeleted entry; this is left as an
// | exercise for the reader.
// +---------------------------------------------------------------------------+
// | @author    thierry_bo.
// | @version   $Revision$.
// +---------------------------------------------------------------------------+
//
// $Id$

require_once("grab_globals.inc.php");
require "config.inc.php";
require_once("database.inc.php");
require "$dbsys.inc";

$mdb->autoCommit(FALSE);

$purge_date = time() - (60 * 60 * 24 * 30);
$mdb->query("DELETE
			 FROM $tbl_entry
			 WHERE end_time < $purge_date");

$mdb->query("DELETE
			 FROM $tbl_repeat
			 WHERE id
             NOT IN (" . $mdb->subSelect("SELECT repeat_id FROM $tbl_entry")
             . ")");

$mdb->commit();
?>
