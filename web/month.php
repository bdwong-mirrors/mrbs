<?php
// $Id$

// mrbs/month.php - Month-at-a-time view

require "defaultincludes.inc";
require_once "mincals.inc";
require_once "functions_table.inc";

$debug_flag = get_form_var('debug_flag', 'int');

// 3-value compare: Returns result of compare as "< " "= " or "> ".
function cmp3($a, $b)
{
  if ($a < $b)
  {
    return "< ";
  }
  if ($a == $b)
  {
    return "= ";
  }
  return "> ";
}

// Check the user is authorised for this page
checkAuthorised();

$user = getUserName();

// print the page header
print_header($day, $month, $year, $area, isset($room) ? $room : "");


// Note $room will be 0 if there are no rooms; this is checked for below.

// Month view start time. This ignores morningstarts/eveningends because it
// doesn't make sense to not show all entries for the day, and it messes
// things up when entries cross midnight.
$month_start = mktime(0, 0, 0, $month, 1, $year);

// What column the month starts in: 0 means $weekstarts weekday.
$weekday_start = (date("w", $month_start) - $weekstarts + 7) % 7;

$days_in_month = date("t", $month_start);

$month_end = mktime(23, 59, 59, $month, $days_in_month, $year);

if ($enable_periods)
{
  $resolution = 60;
  $morningstarts = 12;
  $morningstarts_minutes = 0;
  $eveningends = 12;
  $eveningends_minutes = count($periods)-1;
}


// Define the start and end of each day of the month in a way which is not
// affected by daylight saving...
for ($j = 1; $j<=$days_in_month; $j++)
{
  // are we entering or leaving daylight saving
  // dst_change:
  // -1 => no change
  //  0 => entering DST
  //  1 => leaving DST
  $dst_change[$j] = is_dst($month, $j, $year);
  $am7[$j] = get_start_first_slot($month, $j, $year);
  $pm7[$j] = get_start_last_slot($month, $j, $year);
}

// Section with areas, rooms, minicals.
echo "<div id=\"dwm_header\" class=\"screenonly\">\n";

// Get the area and room names (we will need them later for the heading)
$this_area_name = "";
$this_room_name = "";
$this_area_name = sql_query1("SELECT area_name FROM $tbl_area WHERE id=$area AND disabled=0 LIMIT 1");
$this_room_name = sql_query1("SELECT room_name FROM $tbl_room WHERE id=$room AND disabled=0 LIMIT 1");
// The room is invalid if it doesn't exist, or else it has been disabled, either explicitly
// or implicitly because the area has been disabled
$room_invalid = ($this_area_name === -1) || ($this_room_name === -1);
                          
// Show all available areas
echo make_area_select_html('month.php', $area, $year, $month, $day);  
// Show all available rooms in the current area:
echo make_room_select_html('month.php', $area, $room_selected ? $room : "", $year, $month, $day, true);
    
// Draw the three month calendars
if (!$display_calendar_bottom)
{
  minicals($year, $month, $day, $area, $room_selected ? $room : "", 'month');
}

echo "</div>\n";


// Don't continue if this room is invalid, which could be because the area
// has no rooms, or else the room or area has been disabled
if ($room_invalid)
{
  echo "<h1>".get_vocab("no_rooms_for_area")."</h1>";
  output_trailer();
  exit;
}

// Show Month, Year, Area, Room header:
$this_page_title = $room_selected ? $this_area_name . $area_room_separator . $this_room_name : $this_area_name;
echo "<div id=\"dwm\">\n";
echo "<h2>" . utf8_strftime($strftime_format['monthyear'], $month_start)
  . " - " . htmlspecialchars($this_page_title) . "</h2>\n";
echo "</div>\n";

// Show Go to month before and after links
//y? are year and month and day of the previous month.
//t? are year and month and day of the next month.
//c? are year and month of this month.   But $cd is the day that was passed to us.

$i= mktime(12,0,0,$month-1,1,$year);
$yy = date("Y",$i);
$ym = date("n",$i);
$yd = $day;
while (!checkdate($ym, $yd, $yy) && ($yd > 1))
{
  $yd--;
}

$i= mktime(12,0,0,$month+1,1,$year);
$ty = date("Y",$i);
$tm = date("n",$i);
$td = $day;
while (!checkdate($tm, $td, $ty) && ($td > 1))
{
  $td--;
}

$cy = date("Y");
$cm = date("m");
$cd = $day;    // preserve the day information
while (!checkdate($cm, $cd, $cy) && ($cd > 1))
{
  $cd--;
}


$room_link = $room_selected ? "&amp;room=$room" : "";
$before_after_links_html = "<div class=\"screenonly\">
  <div class=\"date_nav\">
    <div class=\"date_before\">
      <a href=\"month.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area$room_link\">
          &lt;&lt;&nbsp;".get_vocab("monthbefore")."
        </a>
    </div>
    <div class=\"date_now\">
      <a href=\"month.php?year=$cy&amp;month=$cm&amp;day=$cd&amp;area=$area$room_link\">
          ".get_vocab("gotothismonth")."
        </a>
    </div>
    <div class=\"date_after\">
       <a href=\"month.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area$room_link\">
          ".get_vocab("monthafter")."&nbsp;&gt;&gt;
        </a>
    </div>
  </div>
</div>
";

print $before_after_links_html;

if ($debug_flag)
{
  echo "<p>DEBUG: month=$month year=$year start=$weekday_start range=$month_start:$month_end</p>\n";
}

// Used below: localized "all day" text but with non-breaking spaces:
$all_day = preg_replace("/ /", "&nbsp;", get_vocab("all_day"));

//Get all meetings for this month in the room that we care about
// row[0] = Start time
// row[1] = End time
// row[2] = Entry ID
// This data will be retrieved day-by-day fo the whole month

$linked_entries = get_linked_entries($am7[1], $pm7[$days_in_month]);

for ($day_num = 1; $day_num<=$days_in_month; $day_num++)
{
  if( $room_selected ) {
    $sql = "SELECT start_time, end_time, E.id, name, type,
                   repeat_id, status, create_by, \"dummy\" AS room_name, room_id
              FROM $tbl_entry E, $tbl_room_entry RE
             WHERE E.id = RE.entry_id
               AND RE.room_id=$room
               AND start_time <= $pm7[$day_num] AND end_time > $am7[$day_num]
               ORDER BY start_time";
  } else {
    $sql = "SELECT start_time, end_time, E.id, name, type,
                   repeat_id, status, create_by, room_name, room_id
              FROM $tbl_entry E, $tbl_room_entry RE, $tbl_room R
             WHERE E.id = RE.entry_id
               AND R.area_id=$area AND RE.room_id=R.id
               AND start_time <= $pm7[$day_num] AND end_time > $am7[$day_num]
          ORDER BY start_time, end_time, type, status, name, E.id, R.sort_key";
  }

  // Build an array of information about each day in the month.
  // The information is stored as:
  //  d[monthday]["id"][] = ID of each entry, for linking.
  //  d[monthday]["data"][] = "start-stop" times or "name" of each entry.

  $res = sql_query($sql);
  if (! $res)
  {
    trigger_error(sql_error(), E_USER_WARNING);
    fatal_error(TRUE, get_vocab("fatal_db_error"));
  }
  else
  {
    for ($i = 0; ($row = sql_row_keyed($res, $i)); $i++)
    {
      if ($debug_flag)
      {
        echo "<br>DEBUG: result $i, id ".$row['id'].", starts ".$row['start_time'].", ends ".$row['end_time']."\n";
        echo "<br>DEBUG: Entry ".$row['id']." day $day_num\n";
      }
      $d[$day_num]['id'][] = $row['id'];
      $d[$day_num]['color'][] = $row['type'];
      $d[$day_num]['is_repeat'][] = !empty($row['repeat_id']);
      $d[$day_num]['linked'][] = $linked_entries[$row['id']];
      
      // Handle private events
      if (is_private_event($row['status'] & STATUS_PRIVATE)) 
      {
        if (getWritable($row['create_by'], $user, $room)) 
        {
          $private = FALSE;
        }
        else 
        {
          $private = TRUE;
        }
      }
      else 
      {
        $private = FALSE;
      }

      if ($private & $is_private_field['entry.name']) 
      {
        $d[$day_num]['status'][] = $row['status'] | STATUS_PRIVATE;  // Set the private bit
        $d[$day_num]['shortdescrip'][] = '['.get_vocab('unavailable').']';
      }
      else
      {
        $d[$day_num]['status'][] = $row['status'] & ~STATUS_PRIVATE;  // Clear the private bit
        $d[$day_num]['shortdescrip'][] = htmlspecialchars($row['name']);
      }

      $d[$day_num]["room"][] = htmlspecialchars($row['room_name']);
      $d[$day_num]["room_id"][] = $row['room_id'];
      

      // Describe the start and end time, accounting for "all day"
      // and for entries starting before/ending after today.
      // There are 9 cases, for start time < = or > midnight this morning,
      // and end time < = or > midnight tonight.
      // Use ~ (not -) to separate the start and stop times, because MSIE
      // will incorrectly line break after a -.
      
      if (empty( $enable_periods ) )
      {
        switch (cmp3($row['start_time'], $am7[$day_num]) . cmp3($row['end_time'], $pm7[$day_num] + 1))
        {
          case "> < ":         // Starts after midnight, ends before midnight
          case "= < ":         // Starts at midnight, ends before midnight
            $d[$day_num]['data'][] = htmlspecialchars(utf8_strftime(hour_min_format(), $row['start_time'])) . "~" . htmlspecialchars(utf8_strftime(hour_min_format(), $row['end_time']));
            break;
          case "> = ":         // Starts after midnight, ends at midnight
            $d[$day_num]['data'][] = htmlspecialchars(utf8_strftime(hour_min_format(), $row['start_time'])) . "~24:00";
            break;
          case "> > ":         // Starts after midnight, continues tomorrow
            $d[$day_num]['data'][] = htmlspecialchars(utf8_strftime(hour_min_format(), $row['start_time'])) . "~====&gt;";
            break;
          case "= = ":         // Starts at midnight, ends at midnight
            $d[$day_num]['data'][] = $all_day;
            break;
          case "= > ":         // Starts at midnight, continues tomorrow
            $d[$day_num]['data'][] = $all_day . "====&gt;";
            break;
          case "< < ":         // Starts before today, ends before midnight
            $d[$day_num]['data'][] = "&lt;====~" . htmlspecialchars(utf8_strftime(hour_min_format(), $row['end_time']));
            break;
          case "< = ":         // Starts before today, ends at midnight
            $d[$day_num]['data'][] = "&lt;====" . $all_day;
            break;
          case "< > ":         // Starts before today, continues tomorrow
            $d[$day_num]['data'][] = "&lt;====" . $all_day . "====&gt;";
            break;
        }
      }
      else
      {
        $start_str = period_time_string($row['start_time']);
        $end_str   = period_time_string($row['end_time'], -1);
        switch (cmp3($row['start_time'], $am7[$day_num]) . cmp3($row['end_time'], $pm7[$day_num] + 1))
        {
          case "> < ":         // Starts after midnight, ends before midnight
          case "= < ":         // Starts at midnight, ends before midnight
            $d[$day_num]['data'][] = $start_str . "~" . $end_str;
            break;
          case "> = ":         // Starts after midnight, ends at midnight
            $d[$day_num]['data'][] = $start_str . "~24:00";
            break;
          case "> > ":         // Starts after midnight, continues tomorrow
            $d[$day_num]['data'][] = $start_str . "~====&gt;";
            break;
          case "= = ":         // Starts at midnight, ends at midnight
            $d[$day_num]['data'][] = $all_day;
            break;
          case "= > ":         // Starts at midnight, continues tomorrow
            $d[$day_num]['data'][] = $all_day . "====&gt;";
            break;
          case "< < ":         // Starts before today, ends before midnight
            $d[$day_num]['data'][] = "&lt;====~" . $end_str;
            break;
          case "< = ":         // Starts before today, ends at midnight
            $d[$day_num]['data'][] = "&lt;====" . $all_day;
            break;
          case "< > ":         // Starts before today, continues tomorrow
            $d[$day_num]['data'][] = "&lt;====" . $all_day . "====&gt;";
            break;
        }
      }
    }
  }
}
if ($debug_flag)
{
  echo "<p>DEBUG: Array of month day data:</p><pre>\n";
  for ($i = 1; $i <= $days_in_month; $i++)
  {
    if (isset($d[$i]['id']))
    {
      $n = count($d[$i]['id']);
      echo "Day $i has $n entries:\n";
      for ($j = 0; $j < $n; $j++)
      {
        echo "  ID: " . $d[$i]['id'][$j] .
          " Data: " . $d[$i]['data'][$j] . "\n";
      }
    }
  }
  echo "</pre>\n";
}

echo "<table class=\"dwm_main\" id=\"month_main\">\n";

// Weekday name header row:
echo "<thead>\n";
echo "<tr>\n";
for ($weekcol = 0; $weekcol < 7; $weekcol++)
{
  if (is_hidden_day(($weekcol + $weekstarts) % 7))
  {
    // These days are to be hidden in the display (as they are hidden, just give the
    // day of the week in the header row 
    echo "<th class=\"hidden_day\">" . day_name(($weekcol + $weekstarts)%7) . "</th>";
  }
  else
  {
    echo "<th>" . day_name(($weekcol + $weekstarts)%7) . "</th>";
  }
}
echo "\n</tr>\n";
echo "</thead>\n";

// Main body
echo "<tbody>\n";
echo "<tr>\n";

// Skip days in week before start of month:
for ($weekcol = 0; $weekcol < $weekday_start; $weekcol++)
{
  if (is_hidden_day(($weekcol + $weekstarts) % 7))
  {
    echo "<td class=\"hidden_day\"><div class=\"cell_container\">&nbsp;</div></td>\n";
  }
  else
  {
    echo "<td class=\"invalid\"><div class=\"cell_container\">&nbsp;</div></td>\n";
  }
}

// Draw the days of the month:
for ($cday = 1; $cday <= $days_in_month; $cday++)
{
  // if we're at the start of the week (and it's not the first week), start a new row
  if (($weekcol == 0) && ($cday > 1))
  {
    echo "</tr><tr>\n";
  }
  
  // output the day cell
  if (is_hidden_day(($weekcol + $weekstarts) % 7))
  {
    // These days are to be hidden in the display (as they are hidden, just give the
    // day of the week in the header row 
    echo "<td class=\"hidden_day\">\n";
    echo "<div class=\"cell_container\">\n";
    echo "<div class=\"cell_header\">\n";
    // first put in the day of the month
    echo "<span>$cday</span>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</td>\n";
  }
  else
  {   
    echo "<td class=\"valid\">\n";
    echo "<div class=\"cell_container\">\n";
    
    echo "<div class=\"cell_header\">\n";
    // If it's a Monday (the start of the ISO week), show the week number
    if ($view_week_number && (($weekcol + $weekstarts)%7 == 1))
    {
      echo "<a class=\"week_number\" href=\"week.php?year=$year&amp;month=$month&amp;day=$cday&amp;area=$area$room_link\">";
      echo date("W", gmmktime(12, 0, 0, $month, $cday, $year));
      echo "</a>\n";
    }
    // then put in the day of the month
    echo "<a class=\"monthday\" href=\"day.php?year=$year&amp;month=$month&amp;day=$cday&amp;area=$area\">$cday</a>\n";

    echo "</div>\n";
    
    // then the link to make a new booking
    $query_string = "area=$area$room_link&amp;year=$year&amp;month=$month&amp;day=$cday";
    if ($enable_periods)
    {
      $query_string .= "&amp;period=0";
    }
    else
    {
      $query_string .= "&amp;hour=$morningstarts&amp;minute=0";
    }
    
    echo "<a class=\"new_booking\" href=\"edit_entry.php?$query_string\">\n";
    if ($show_plus_link)
    {
      echo "<img src=\"images/new.gif\" alt=\"New\" width=\"10\" height=\"10\">\n";
    }
    echo "</a>\n";
    
    // then any bookings for the day
    if (isset($d[$cday]["id"][0]))
    {
      echo "<div class=\"booking_list\">\n";
      $accumulate=false;
      $n = count($d[$cday]["id"]);
      // Show the start/stop times, 1 or 2 per line, linked to view_entry.
      for ($i = 0; $i < $n; $i++)
      {
        if( !$accumulate ) {
          // new booking
          $rooms="";
          $rooms_for_entry=array();
        }
        $rooms_for_entry[] = $d[$cday]["room_id"][$i];

        if( !$room_selected ) {
          // if no room was selected by the user, show an area overview.
          if( $rooms != "" )
            $rooms .= ", ";
          $rooms .= $d[$cday]["room"][$i];

          if(    $i < $n-1
              && $d[$cday]["id"][$i] === $d[$cday]["id"][$i+1] ) {
            // next booking has same id as the current booking, so it is 
            // actually the same booking, just for another room. Simply 
            // accumulate the parameters.
            $accumulate=true;
            continue;
          } else {
            $accumulate=false;
          }
        }

        // give the enclosing div the appropriate width: full width if both,
        // otherwise half-width (but use 49.9% to avoid rounding problems in some browsers)
        $class = $d[$cday]["color"][$i]; 
        if ($d[$cday]["status"][$i] & STATUS_PRIVATE)
        {
          $class .= " private";
        }
        if ($approval_enabled && ($d[$cday]["status"][$i] & STATUS_AWAITING_APPROVAL))
        {
          $class .= " awaiting_approval";
        }
        if ($confirmation_enabled && ($d[$cday]["status"][$i] & STATUS_TENTATIVE))
        {
          $class .= " tentative";
        }  
        echo "<div class=\"" . $class . "\"" .
          " style=\"width: " . (($monthly_view_entries_details == "both") ? '100%' : '49.9%') . "\">\n";
        $booking_link = "view_entry.php?id=" . $d[$cday]["id"][$i] . "&amp;day=$cday&amp;month=$month&amp;year=$year";
        $slot_text = $d[$cday]["data"][$i];
        $description_text = utf8_substr($d[$cday]["shortdescrip"][$i], 0, 255);
        $full_text = $slot_text . " " . $description_text;
        switch ($monthly_view_entries_details)
        {
          case "description":
          {
            $display_text = $description_text;
            break;
          }
          case "slot":
          {
            $display_text = $slot_text;
            break;
          }
          case "both":
          {
            $display_text = $full_text;
            break;
          }
          default:
          {
            echo "error: unknown parameter";
          }
        }
        if( !$room_selected ) {
          $display_text .= " (" . $rooms . ")";
          $full_text .= " (" . $rooms . ")";
        }
        echo "<a href=\"$booking_link\" title=\"$full_text\">";
        echo ($d[$cday]['is_repeat'][$i]) ? "<img class=\"repeat_symbol\" src=\"images/repeat.png\" alt=\"" . get_vocab("series") . "\" title=\"" . get_vocab("series") . "\" width=\"10\" height=\"10\">" : '';
        if (count($d[$cday]['linked'][$i]) > 1)
        {
          $rooms_not_shown = array_diff($d[$cday]['linked'][$i], $rooms_for_entry);
          if( !empty($rooms_not_shown) )
          {
            // if the entry is linked to rooms not currently shown, display the link icon
            echo "<img class=\"link_symbol\" src=\"images/link.png\" alt=\"" . get_vocab("linked_entry") . "\" title=\"" .
               get_vocab("linked_with") . "\n" . implode("\n", get_full_room_names($rooms_not_shown)) . "\" width=\"16\" height=\"16\">";
          }
        }
        echo "$display_text</a>\n";
        echo "</div>\n";
      }
      echo "</div>\n";
    }
    
    echo "</div>\n";
    echo "</td>\n";
  }
  
  // increment the day of the week counter
  if (++$weekcol == 7)
  {
    $weekcol = 0;
  }

} // end of for loop going through valid days of the month

// Skip from end of month to end of week:
if ($weekcol > 0)
{
  for (; $weekcol < 7; $weekcol++)
  {
    if (is_hidden_day(($weekcol + $weekstarts) % 7))
    {
      echo "<td class=\"hidden_day\"><div class=\"cell_container\">&nbsp;</div></td>\n";
    }
    else
    {
      echo "<td class=\"invalid\"><div class=\"cell_container\">&nbsp;</div></td>\n";
    }
  }
}
echo "</tr></tbody></table>\n";

print $before_after_links_html;
show_colour_key();

// Draw the three month calendars
if ($display_calendar_bottom)
{
  minicals($year, $month, $day, $area, $room_selected ? $room : "", 'month');
}

output_trailer();
?>
