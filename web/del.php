<?php
// $Id$

/* 
 * Delete a room or an area.
*/

require_once "defaultincludes.inc";
require_once "include/mrbs_acl_api.php";

// Get form variables
$day = get_form_var('day', 'int');
$month = get_form_var('month', 'int');
$year = get_form_var('year', 'int');
$area = get_form_var('area', 'int');
$room = get_form_var('room', 'int');
$type = get_form_var('type', 'string');
$confirm = get_form_var('confirm', 'string');

// If we dont know the right date then make it up
if (!isset($day) or !isset($month) or !isset($year))
{
  $day   = date("d");
  $month = date("m");
  $year  = date("Y");
}
if (empty($area))
{
  $area = get_default_area();
}

// This is gonna blast away something. We want them to be really
// really sure that this is what they want to do.

if ($type == "room")
{
  if (!getAuthorised('generic','delete','rooms',$room))
  {
    showAccessDenied($day, $month, $year, $area, "");
    exit();
  }
  // We are supposed to delete a room
  if (isset($confirm))
  {
    // First get a result set of all the bookings in this room to use later.
    $bookings = sql_query("select id from $tbl_entry where room_id=$room");
    if (!$bookings) fatal_error(0, sql_error());

    // They have confirmed it already, so go blast!
    sql_begin();
    // First take out all appointments for this room
    sql_command("delete from $tbl_entry where room_id=$room");
    sql_command("delete from $tbl_repeat where room_id=$room");
   
    // Now take out the room itself
    sql_command("delete from $tbl_room where id=$room");
    sql_commit();

    // Delete all appointments for this room from phpGACL
    for ($i = 0; ($row = sql_row($bookings, $i)); $i++) {
      $mrbs_acl_api->delObject('bookings',$row[$i],'AXO');
    }
    // Remove the room from phpGACL
    $mrbs_acl_api->delObject('rooms',$room,'AXO');
   
    // Go back to the admin page
    Header("Location: admin.php");
  }
  else
  {
    print_header($day, $month, $year, $area, isset($room) ? $room : "");
   
    // We tell them how bad what they're about to do is
    // Find out how many appointments would be deleted
   
    $sql = "select name, start_time, end_time from $tbl_entry where room_id=$room";
    $res = sql_query($sql);
    if (! $res)
    {
      echo sql_error();
    }
    else if (sql_count($res) > 0)
    {
      echo "<p>\n";
      echo get_vocab("deletefollowing") . ":\n";
      echo "</p>\n";
      
      echo "<ul>\n";
      
      for ($i = 0; ($row = sql_row_keyed($res, $i)); $i++)
      {
        echo "<li>".htmlspecialchars($row['name'])." (";
        echo time_date_string($row['start_time']) . " -> ";
        echo time_date_string($row['end_time']) . ")</li>\n";
      }
      
      echo "</ul>\n";
    }
   
    echo "<div id=\"del_room_confirm\">\n";
    echo "<p>" .  get_vocab("sure") . "</p>\n";
    echo "<div id=\"del_room_confirm_links\">\n";
    echo "<a href=\"del.php?type=room&amp;room=$room&amp;confirm=Y\"><span id=\"del_yes\">" . get_vocab("YES") . "!</span></a>\n";
    echo "<a href=\"admin.php\"><span id=\"del_no\">" . get_vocab("NO") . "!</span></a>\n";
    echo "</div>\n";
    echo "</div>\n";
    require_once "trailer.inc";
  }
}

if ($type == "area")
{
  if (!getAuthorised('generic','delete','areas',$area))
  {
    showAccessDenied($day, $month, $year, $area, "");
    exit();
  }
  // We are only going to let them delete an area if there are
  // no rooms. its easier
  $n = sql_query1("select count(*) from $tbl_room where area_id=$area");
  if ($n == 0)
  {
    // OK, nothing there, lets blast it away
    sql_command("delete from $tbl_area where id=$area");

    // Remove the area from phpGACL
    $mrbs_acl_api->delObject('areas',$area,'AXO');
   
    // Redirect back to the admin page
    header("Location: admin.php");
  }
  else
  {
    // There are rooms left in the area
    print_header($day, $month, $year, $area, isset($room) ? $room : "");
    echo "<p>\n";
    echo get_vocab("delarea");
    echo "<a href=\"admin.php\">" . get_vocab("backadmin") . "</a>";
    echo "</p>\n";
    require_once "trailer.inc";
  }
}

?>
