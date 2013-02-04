<?php

// $Id$

// This script links multiple bookings together into one booking, if the 
// bookings differ only in the rooms they use.
// This is especially useful after upgrading a database from MRBS 1.49 or 
// older, wheree linked bookings were not supported.
// This script is idempotent.

// These fields are ignored when comparing for equality. The merged booking contains the value of the booking with the lowest id.
$ignored_fields_for_equality = array('timestamp', 'ical_uid', 'ical_sequence', 'ical_recur_id');

require_once "defaultincludes.inc";

header('Content-Type: text/html; charset="utf-8"');

?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>MRBS link bookings script</title>
  </head>
  <body>

  <h1>MRBS link bookings script</h1>
<pre>
<?php
sql_begin();

$last_start_time = '';
$nr_repeat_deleted=0;
$nr_entry_deleted=0;
$entries = array();
$res = sql_query("SELECT * FROM $tbl_repeat ORDER BY start_time, id");
for ($i = 0; ($row = sql_row_keyed($res, $i)); $i++) {

	if( $last_start_time != $row['start_time'] ) {
		$entries = array();
		$entries[] = $row;
		$last_start_time = $row['start_time'];
		continue;
	}

	// see if any of the other entries is the same as this one
	foreach( $entries as $entry ) {
		$same = true;
		foreach( $entry as $key => $value ) {
			if( $key === 'id' ) {
				$entry_id = $value;
				continue;
			}

			if( in_array($key, $ignored_fields_for_equality, true) )
				continue;

			if( $row[$key] !== $value ) {
				$same = false;
				//echo "Difference in $key: ${row[$key]} vs $value\n";
				break;
			}
		}
		if( $same ) {
			echo "Repeat entry " . $row['id'] . " merged into " . $entry_id . "\n";
			$ok =  sql_command("UPDATE $tbl_room_repeat SET repeat_id=$entry_id WHERE repeat_id=${row['id']}")
			    && sql_command("UPDATE $tbl_entry SET repeat_id=$entry_id WHERE repeat_id=${row['id']}")
			    && sql_command("DELETE FROM $tbl_repeat WHERE id=${row['id']}");
			if( !$ok )
				echo "Error in SQL: ".sql_error()."\n";
			$nr_repeat_deleted++;
			break;
		}
	}
	if( !$same ) {
		$entries[] = $row;
	}
}

$last_start_time = '';
$entries = array();
$res = sql_query("SELECT * FROM $tbl_entry ORDER BY start_time, id");
for ($i = 0; ($row = sql_row_keyed($res, $i)); $i++) {

	if( $last_start_time != $row['start_time'] ) {
		$entries = array();
		$entries[] = $row;
		$last_start_time = $row['start_time'];
		continue;
	}

	// see if any of the other entries is the same as this one
	foreach( $entries as $entry ) {
		$same = true;
		foreach( $entry as $key => $value ) {
			if( $key === 'id' ) {
				$entry_id = $value;
				continue;
			}

			if( in_array($key, $ignored_fields_for_equality, true) )
				continue;

			if( $row[$key] !== $value ) {
				$same = false;
				//echo "Difference in $key: ${row[$key]} vs $value\n";
				break;
			}
		}
		if( $same ) {
			echo "Entry " . $row['id'] . " merged into " . $entry_id . "\n";
			$ok =  sql_command("UPDATE $tbl_room_entry SET entry_id=$entry_id WHERE entry_id=${row['id']}")
			    && sql_command("DELETE FROM $tbl_entry WHERE id=${row['id']}");
			if( !$ok )
				echo "Error in SQL: ".sql_error()."\n";
			$nr_entry_deleted++;
			break;
		}
	}
	if( !$same ) {
		$entries[] = $row;
	}
}

// TODO commit only if everything is OK
sql_commit();

echo "\n$nr_repeat_deleted repeats and $nr_entry_deleted entries merged.";
?>
</pre>
</body>
</html>
