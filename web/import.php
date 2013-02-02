<?php
// $Id$

require "defaultincludes.inc";
require_once "functions_ical.inc";
require_once "mrbs_sql.inc";


function create_field_default_rooms($name, $value)
{
  global $tbl_room, $tbl_area;
  global $use_default_rooms;
  
  echo "<div>\n";
  // Get all rooms that aren't disabled and don't use periods
  $sql = "SELECT R.id, R.room_name, A.area_name
            FROM $tbl_room R, $tbl_area A
           WHERE R.area_id = A.id
             AND R.disabled=0
             AND A.disabled=0
             AND A.enable_periods=0
        ORDER BY A.area_name, R.sort_key";
  $res = sql_query($sql);
  if ($res === FALSE)
  {
    trigger_error(sql_error(), E_USER_WARNING);
    fatal_error(FALSE, get_vocab("fatal_db_error"));
  }
  $options = array();
  for ($i = 0; ($row = sql_row_keyed($res, $i)); $i++)
  {
    if (!isset($options[$row['area_name']]))
    {
      $options[$row['area_name']] = array();
    }
    $options[$row['area_name']][$row['id']] = $row['room_name'];
  }
  
  $params = array('label'       => get_vocab("default_rooms") . ':',
                  'label_title' => get_vocab("default_rooms_note"),
                  'name'        => $name . '[]',
                  'id'          => $name,
                  'options'     => $options,
                  'force_assoc' => TRUE,
                  'multiple'    => TRUE,
                  'value'       => $value);
  generate_select($params);
  $params = array('label'       => get_vocab("use_default_rooms"),
                  'label_after' => TRUE,
                  'name'        => 'use_default_rooms',
                  'value'       => $use_default_rooms);
  generate_checkbox($params);
  echo "</div>\n";
}


// Gets the id of the area/room with $location, creating an area and room if allowed.
// Returns FALSE if it can't find an id or create an id, with an error message in $error
function get_room_id($location, &$error)
{
  global $area_room_order, $area_room_delimiter, $area_room_create;
  global $tbl_room, $tbl_area;
  
  // If there's no delimiter we assume we've just been given a room name (that will
  // have to be unique).   Otherwise we split the location into its area and room parts
  if (strpos($location, $area_room_delimiter) === FALSE)
  {
    $location_area = '';
    $location_room = $location;
  }
  elseif ($area_room_order == 'area_room')
  {
    list($location_area, $location_room) = explode($area_room_delimiter, $location);
  }
  else
  {
    list($location_room, $location_area) = explode($area_room_delimiter, $location);
  }
  $location_area = trim($location_area);
  $location_room = trim($location_room);
  
  // Now search the database for the room
  
  // Case 1:  we've just been given a room name, in which case we hope it happens
  // to be unique, because if we find more than one we won't know which one is intended
  // and if we don't find one at all we won't be able to create it because we won't 
  // know which area to put it in.
  if ($location_area == '')
  {
    $sql = "SELECT COUNT(*) FROM $tbl_room WHERE room_name='" . sql_escape($location_room) . "'";
    $count = sql_query1($sql);
    if ($count < 0)
    {
      trigger_error(sql_error(), E_USER_WARNING);
      fatal_error(FALSE, get_vocab("fatal_db_error"));
    }
    elseif ($count == 0)
    {
      $error = "'$location_room': " . get_vocab("room_does_not_exist_no_area");
      return FALSE;
    }
    elseif ($count > 1)
    {
      $error = "'$location_room': " . get_vocab("room_not_unique_no_area");
      return FALSE;
    }
    else // we've got a unique room name
    {
      $sql = "SELECT id FROM $tbl_room WHERE room_name='" . sql_escape($location_room) . "' LIMIT 1";
      $id = sql_query1($sql);
      if ($id < 0)
      {
        trigger_error(sql_error(), E_USER_WARNING);
        fatal_error(FALSE, get_vocab("fatal_db_error"));
      }
      return $id;
    }
  }
  
  // Case 2:  we've got an area and room name
  else
  {
    // First of all get the area id
    $sql = "SELECT id
              FROM $tbl_area
             WHERE area_name='" . sql_escape($location_area) . "'
             LIMIT 1";
    $area_id = sql_query1($sql);
    if ($area_id < 0)
    {
      $sql_error = sql_error();
      if (!empty($sql_error))
      {
        trigger_error(sql_error(), E_USER_WARNING);
        fatal_error(FALSE, get_vocab("fatal_db_error"));
      }
      else
      {
        // The area does not exist - create it if we are allowed to
        if (!$area_room_create)
        {
          $error = get_vocab("area_does_not_exist") . " '$location_area'";
          return FALSE;
        }
        else
        {
          echo get_vocab("creating_new_area") . " '$location_area'<br>\n";
          $error_add_area = '';
          $area_id = mrbsAddArea($location_area, $error_add_area);
          if ($area_id === FALSE)
          {
            $error = get_vocab("could_not_create_area") . " '$location_area'";
            return FALSE;
          }
        }
      }
    }
  }
  // Now we've got the area_id get the room_id
  $sql = "SELECT id
            FROM $tbl_room
           WHERE room_name='" . sql_escape($location_room) . "'
             AND area_id=$area_id
           LIMIT 1";
  $room_id = sql_query1($sql);
  if ($room_id < 0)
  {
    $sql_error = sql_error();
    if (!empty($sql_error))
    {
      trigger_error(sql_error(), E_USER_WARNING);
      fatal_error(FALSE, get_vocab("fatal_db_error"));
    }
    else
    {
      // The room does not exist - create it if we are allowed to
      if (!$area_room_create)
      {
        $error = get_vocab("room_does_not_exist") . " '$location_room'";
        return FALSE;
      }
      else
      {
        echo get_vocab("creating_new_room") . " '$location_room'<br>\n";
        $error_add_room = '';
        $room_id = mrbsAddRoom($location_room, $area_id, $error_add_room);
        if ($room_id === FALSE)
        {
          $error = get_vocab("could_not_create_room") . " '$location_room'";
          return FALSE;
        }
      }
    }
  }
  return $room_id;
}


function get_room_ids($locations, &$errors)
{
  global $location_delimiter;
  
  $room_ids = array();
  $locations = explode($location_delimiter, $locations);

  foreach ($locations as $location)
  {
    $error = '';
    $room_id = get_room_id($location, $error);
    if ($room_id === FALSE)
    {
      $errors[] = $error;
    }
    else
    {
      $room_ids[] = $room_id;
    }
  }
  
  return $room_ids;
}


// Add a VEVENT to MRBS.   Returns TRUE on success, FALSE on failure
function process_event($vevent)
{
  global $import_default_type, $skip, $default_rooms, $use_default_rooms;
  global $morningstarts, $morningstarts_minutes, $resolution, $enable_periods;
  
  // We are going to cache the settings ($resolution etc.) for the rooms
  // in order to avoid lots of database lookups
  static $room_settings = array();
  
  // Set up the booking with some defaults
  $booking = array();
  $booking['status'] = 0;
  $booking['rep_type'] = REP_NONE;
  $booking['type'] = $import_default_type;
  // Parse all the lines first because we'll need to get the start date
  // for calculating some of the other settings
  $properties = array();
  $problems = array();

  $line = current($vevent);
  while ($line !== FALSE)
  {
    $property = parse_ical_property($line);
    // Ignore any sub-components (eg a VALARM inside a VEVENT) as MRBS does not
    // yet handle things like reminders.  Skip through to the end of the sub-
    // component.   Just in case you can have sub-components at a greater depth
    // than 1 (not sure if you can), make sure we've got to the matching END.
    if ($property['name'] != 'BEGIN')
    {
      $properties[$property['name']] = array('params' => $property['params'],
                                             'value' => $property['value']);
    }
    else
    {
      $component = $property['value'];
      while (!(($property['name'] == 'END') && ($property['value'] == $component)) &&
             ($line = next($vevent)))
      {
        $property = parse_ical_property($line);;
      }
    }
    $line = next($vevent);
  }
  // Get the start time because we'll need it later
  if (!isset($properties['DTSTART']))
  {
    trigger_error("No DTSTART", E_USER_WARNING);
  }
  else
  {
    $booking['start_time'] = get_time($properties['DTSTART']['value'],
                                      $properties['DTSTART']['params']);
  }
  // Now go through the rest of the properties
  foreach($properties as $name => $details)
  {
    switch ($name)
    {
      case 'ORGANIZER':
        $booking['create_by'] = get_create_by($details['value']);
        break;
      case 'SUMMARY':
        $booking['name'] = $details['value'];
        break;
      case 'DESCRIPTION':
        $booking['description'] = $details['value'];
        break;
      case 'LOCATION':
        $errors = array();
        $booking['rooms'] = get_room_ids($details['value'], $errors);
        if (!empty($errors))
        {
          $problems = array_merge($problems, $errors);
        }
        break;
      case 'DTEND':
        $booking['end_time'] = get_time($details['value'], $details['params']);
        break;
      case 'DURATION':
        trigger_error("DURATION not yet supported by MRBS", E_USER_WARNING);
        break;
      case 'RRULE':
        $rrule_errors = array();
        $repeat_details = get_repeat_details($details['value'], $booking['start_time'], $rrule_errors);
        if ($repeat_details === FALSE)
        {
          $problems = array_merge($problems, $rrule_errors);
        }
        else
        {
          foreach ($repeat_details as $key => $value)
          {
            $booking[$key] = $value;
          }
        }
        break;
      case 'CLASS':
        if (in_array($details['value'], array('PRIVATE', 'CONFIDENTIAL')))
        {
          $booking['status'] |= STATUS_PRIVATE;
        }
        break;
      case 'STATUS':
        if ($details['value'] == 'TENTATIVE')
        {
          $booking['status'] |= STATUS_TENTATIVE;
        }
        break;
      case 'UID':
        $booking['ical_uid'] = $details['value'];
        break;
      case 'SEQUENCE':
        $booking['ical_sequence'] = $details['value'];
        break;
      case 'LAST-MODIFIED':
        // We probably ought to do something with LAST-MODIFIED and use it
        // for the timestamp field
        break;
      default:
        break;
    }
  }
  
  // If we didn't manage to work out a username then just put the booking
  // under the name of the current user
  if (!isset($booking['create_by']))
  {
    $booking['create_by'] = getUserName();
  }
  
  // A SUMMARY is optional in RFC 5545, however a brief description is mandatory
  // in MRBS.   So if the VEVENT didn't include a name, we'll give it one
  if (!isset($booking['name']))
  {
    $booking['name'] = "Imported event - no SUMMARY name";
  }
  
  // On the other hand a UID is mandatory in RFC 5545.   We'll be lenient and
  // provide one if it is missing
  if (!isset($booking['ical_uid']))
  {
    $booking['ical_uid'] = generate_global_uid($booking['name']);
    $booking['sequence'] = 0;  // and we'll start the sequence from 0
  }
  
  // If we haven't got any rooms, then use the default rooms if we've been
  // told to, otherwise report an error
  if (!isset($booking['rooms']) || empty($booking['rooms']))
  {
    if ($use_default_rooms)
    {
      $booking['rooms'] = $default_rooms;
    }
    else
    {
      $problems[] = get_vocab("no_LOCATION");
    }
  }
  
  // Get the area settings for these rooms, if we haven't got them already
  if (isset($booking['rooms']))
  {
    foreach ($booking['rooms'] as $room_id)
    {
      if (!isset($room_settings[$room_id]))
      {
        get_area_settings(get_area($room_id));
        $room_settings[$room_id]['enable_periods'] = $enable_periods;
        $room_settings[$room_id]['morningstarts'] = $morningstarts;
        $room_settings[$room_id]['morningstarts_minutes'] = $morningstarts_minutes;
        $room_settings[$room_id]['resolution'] = $resolution;
      }
    }
    // Check that the areas are suitable
    foreach ($booking['rooms'] as $room_id)
    {
      // We don't know how to import into areas that use periods
      if ($room_settings[$room_id]['enable_periods'])
      {
        $problems[] = get_vocab("cannot_import_into_periods") . " (" . get_full_room_names($room_id) . ")";
      }
      // All the areas for this linked booking must have the same key settings
      if ($room_settings[$room_id] !== $room_settings[$booking['rooms'][0]])
      {
        $problems[] = get_vocab("area_settings_not_similar");
      }
    }
  }
  
  if (empty($problems))
  {
    $room_id = $booking['rooms'][0];
    
    // Round the start and end times to slot boundaries
    $date = getdate($booking['start_time']);
    $m = $date['mon'];
    $d = $date['mday'];
    $y = $date['year'];
    $am7 = mktime($room_settings[$room_id]['morningstarts'],
                  $room_settings[$room_id]['morningstarts_minutes'],
                  0, $m, $d, $y);
    $booking['start_time'] = round_t_down($booking['start_time'],
                                          $room_settings[$room_id]['resolution'],
                                          $am7);
    $booking['end_time'] = round_t_up($booking['end_time'],
                                      $room_settings[$room_id]['resolution'],
                                      $am7);
    // Make the booking
    $result = mrbsMakeBooking($booking, NULL, FALSE, $skip);
    if ($result['valid_booking'])
    {
      return TRUE;
    }
  }
  // There were problems - list them
  echo "<div class=\"problem_report\">\n";
  echo get_vocab("could_not_import") . " UID:" . htmlspecialchars($booking['ical_uid']);
  echo "<ul>\n";
  foreach ($problems as $problem)
  {
    echo "<li>" . htmlspecialchars($problem) . "</li>\n";
  }
  if (!empty($result['rules_broken']))
  {
    echo "<li>" . get_vocab("rules_broken") . "\n";
    echo "<ul>\n";
    foreach ($result['rules_broken'] as $rule)
    {
      echo "<li>$rule</li>\n";
    }
    echo "</ul></li>\n";
  }
  if (!empty($result['conflicts']))
  {
    echo "<li>" . get_vocab("conflict"). "\n";
    echo "<ul>\n";
    foreach ($result['conflicts'] as $conflict)
    {
      echo "<li>$conflict</li>\n";
    }
    echo "</ul></li>\n";
  }
  echo "</ul>\n";
  echo "</div>\n";
  
  return FALSE;
}


// Check the user is authorised for this page
checkAuthorised();

print_header($day, $month, $year, $area, $room);

$import = get_form_var('import', 'string');
$area_room_order = get_form_var('area_room_order', 'string', 'area_room');
$area_room_delimiter = get_form_var('area_room_delimiter', 'string', $area_room_separator);
$default_rooms = get_form_var('default_rooms', 'array');
$use_default_rooms = get_form_var('use_default_rooms', 'string', 0);
$location_delimiter = get_form_var('location_delimiter', 'string', $location_separator);
$area_room_create = get_form_var('area_room_create', 'string', '0');
$import_default_type = get_form_var('import_default_type', 'string', $default_type);
$skip = get_form_var('skip', 'string', ((empty($skip_default)) ? '0' : '1'));


// PHASE 2 - Process the files
// ---------------------------

if (!empty($import))
{
  if ($_FILES['ics_file']['error'] !== UPLOAD_ERR_OK)
  {
    echo "<p>\n";
    echo get_vocab("upload_failed");
    switch($_FILES['ics_file']['error'])
    {
      case UPLOAD_ERR_INI_SIZE:
        echo "<br>\n";
        echo get_vocab("max_allowed_file_size") . " " . ini_get('upload_max_filesize');
        break;
      case UPLOAD_ERR_NO_FILE:
        echo "<br>\n";
        echo get_vocab("no_file");
        break;
      default:
        // None of the other possible errors would make much sense to the user, but should be reported
        trigger_error($_FILES['ics_file']['error'], E_USER_NOTICE);
        break;
    }
    echo "</p>\n";
  }
  elseif (!is_uploaded_file($_FILES['ics_file']['tmp_name']))
  {
    // This should not happen and if it does may mean that somebody is messing about
    echo "<p>\n";
    echo get_vocab("upload_failed");
    echo "</p>\n";
    trigger_error("Attempt to import a file that has not been uploaded", E_USER_WARNING);
  }
  // We've got a file
  else
  {
    $vcalendar = file_get_contents($_FILES['ics_file']['tmp_name']);
    if ($vcalendar !== FALSE)
    {
      $vevents = array();
      $lines = explode("\r\n", ical_unfold($vcalendar));
      $first_line = array_shift($lines);
      if (isset($first_line))
      {
        // Get rid of empty lines at the end of the file
        // (Strictly speaking there must be a CRLF at the end of the file, but
        // we will be tolerant and accept files without one)
        do
        {
          $last_line = array_pop($lines);
        }
        while (isset($last_line) && ($last_line == ''));
      }
      // Check that this bears some resemblance to a VCALENDAR
      if (!isset($last_line) ||
          ($first_line != "BEGIN:VCALENDAR") ||
          ($last_line != "END:VCALENDAR"))
      {
        echo "<p>\n" . get_vocab("badly_formed_ics") . "</p>\n";
      }
      // Looks OK - find all the VEVENTS which we are going to put in a two dimensional array -
      // each event will consist of an array of lines making up the event.  (Note - we
      // are going to ignore any VTIMEZONE definitions.   We will honour TZID data in
      // a VEVENT but we will use the PHP definition of the timezone)
      else
      {
        while ($line = array_shift($lines))
        {
          if ($line == "BEGIN:VEVENT")
          {
            $vevent = array();
            while (($vevent_line = array_shift($lines)) && ($vevent_line != "END:VEVENT"))
            {
              $vevent[] = $vevent_line;
            }
            $vevents[] = $vevent;
          }
        }
      }
      // Process each event, putting it in the database
      $n_success = 0;
      $n_failure = 0;
      foreach ($vevents as $vevent)
      {
        (process_event($vevent)) ? $n_success++ : $n_failure++;
      }
      echo "<p>\n";
      echo "$n_success " . get_vocab("events_imported");
      if ($n_failure > 0)
      {
        echo "<br>\n$n_failure " . get_vocab("events_not_imported");
      }
      echo "</p>\n";
    }
  }
}

// PHASE 1 - Get the user input
// ----------------------------
echo "<form class=\"form_general\" method=\"POST\" enctype=\"multipart/form-data\" action=\"" . htmlspecialchars(basename($PHP_SELF)) . "\">\n";

echo "<fieldset class=\"admin\">\n";
echo "<legend>" . get_vocab("import_icalendar") . "</legend>\n";

echo "<p>\n" . get_vocab("import_intro") . "</p>\n";
  
echo "<div>\n";
$params = array('label'      => get_vocab("file_name") . ':',
                'name'       => 'ics_file',
                'type'       => 'file',
                'mandatory'  => TRUE,
                'attributes' => 'accept="text/calendar"');
generate_input($params);
echo "</div>\n";

echo "<fieldset>\n";
echo "<legend>" . get_vocab("area_room_settings") . "</legend>\n";

echo "<div>\n";
$options = array('area_room' => get_vocab("area_room"),
                 'room_area' => get_vocab("room_area"));
$params = array('label'       => get_vocab("area_room_order") . ':',
                'label_title' => get_vocab("area_room_order_note"),
                'name'        => 'area_room_order',
                'options'     => $options,
                'value'       => $area_room_order);
generate_radio_group($params);
echo "</div>\n";

echo "<div>\n";
$params = array('label'       => get_vocab("area_room_delimiter") . ':',
                'label_title' => get_vocab("area_room_delimiter_note"),
                'name'        => 'area_room_delimiter',
                'value'       => $area_room_delimiter);
generate_input($params);
echo "</div>\n";

echo "<div>\n";
$params = array('label'       => get_vocab("location_delimiter") . ':',
                'label_title' => get_vocab("location_delimiter_note"),
                'name'        => 'location_delimiter',
                'value'       => $location_delimiter);
generate_input($params);
echo "</div>\n";

create_field_default_rooms('default_rooms', $default_rooms);

echo "<div>\n";
$params = array('label' => get_vocab("area_room_create") . ':',
                'name'  => 'area_room_create',
                'value' => $area_room_create);
generate_checkbox($params);
echo "</div>\n";

echo "</fieldset>\n";

echo "<fieldset>\n";
echo "<legend>" . get_vocab("other_settings") . "</legend>\n";

echo "<div>\n";
$options = array();
foreach ($booking_types as $type)
{
  $options[$type] = get_vocab("type.$type");
}
$params = array('label'       => get_vocab("default_type") . ':',
                'name'        => 'import_default_type',
                'options'     => $options,
                'force_assoc' => TRUE,
                'value'       => $import_default_type);
generate_select($params);
echo "</div>\n";

echo "<div>\n";
$params = array('label' => get_vocab("skip_conflicts") . ':',
                'name'  => 'skip',
                'value' => $skip);
generate_checkbox($params);
echo "</div>\n";

echo "</fieldset>\n";

// The Submit button
echo "<div id=\"import_submit\">\n";
echo "<input class=\"submit\" type=\"submit\" name=\"import\" value=\"" . get_vocab("import") . "\">\n";
echo "</div>\n";

echo "</fieldset>\n";

echo "</form>\n";
  
output_trailer();
?>