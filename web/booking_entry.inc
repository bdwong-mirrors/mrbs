<?php
/**
* This class describes one booking entry.
*/

class BookingEntry {
  var $ical_uid;
  var $ical_sequence;
  var $start_time;
  var $end_time;
  var $room_id;
  var $create_by;
  var $name;
  var $type;
  var $description;
  var $status;
  var $rep_type;

  var $end_date;
  var $rep_opt;
  var $rep_num_weeks;

  var $entry_type;
  var $ical_recur_id;
  var $repeat_id;

  var $duration;
  var $dur_units;

  var $id;

  var $room_name;
  var $area_name;
  var $area_admin_email;
  var $room_admin_email;

  // TODO integrate custom_fields differently

  function is_private() {
    return $this->status & STATUS_PRIVATE;
  }
  function is_tentative() {
    return $this->status & STATUS_TENTATIVE;
  }
  function is_awaiting_approval() {
    return $this->status & STATUS_AWAITING_APPROVAL;
  }

  function set_private() {
    $this->status |= STATUS_PRIVATE;
  }
  function set_tentative() {
    $this->status |= STATUS_TENTATIVE;
  }
  function set_awaiting_approval() {
    $this->status |= STATUS_AWAITING_APPROVAL;
  }
}

