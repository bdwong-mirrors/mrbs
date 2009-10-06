<?php

/*
 *
 * File level doc block. To be completed.
 *
 */


/*
 * mrbs_acl_api Custom Extended API Class
 *
 * This class is specifically for simplifying
 * calls to phpGACL from within MRBS.
 *
 * Once phpGACL is altered to directly access
 * the data stored in MRBS tables this class
 * will become redundant.
 * 
 * @author Paul van der Westhuizen <proj_admin@users.sourceforge.net>
 *
 */

class MRBS_acl_api extends gacl_api {

  function addObject($section_value, $object_value, $object_name, $object_type)
  {
    $order = 0;
    $hidden = 0;
    if ($object_type == 'ARO') { // Each user is an ARO and an AXO
        $group_id = $this->get_group_id("general-$section_value", '', $object_type); // All users by default are general users
        $object_id = $this->add_object($section_value, $object_name, $object_value, $order, $hidden, $object_type);
        if ($object_id)
            $this->add_group_object($group_id, $section_value, $object_value, $object_type);
        $object_type = 'AXO';
    }
    $group_id = $this->get_group_id("all-$section_value", '', $object_type);
    $object_id = $this->add_object($section_value, $object_name, $object_value, $order, $hidden, $object_type);
    if ($object_id)
      $this->add_group_object($group_id, $section_value, $object_value, $object_type);
    return TRUE;
  }

  function updateObject($section_value, $object_value, $new_name, $object_type)
  {
    $order = 0;
    $hidden = 0;
    $object_id = $this->get_object_id($section_value, $object_value, $object_type);
    $this->edit_object($object_id, $section_value, $new_name, $object_value, $order, $hidden, $object_type);
    return TRUE;
  }

  function delObject($section_value, $object_value, $object_type)
  {
    // Delete object. It will be autoremoved from all groups/ACLs, because 3rd param is TRUE.
    if ($object_type == 'ARO') { // Each user is an ARO and an AXO
        $object_id = $this->get_object_id($section_value, $object_value, $object_type);
        if (!$this->del_object($object_id, $object_type, TRUE)) return FALSE;
        $object_type = 'AXO';
    }
    $object_id = $this->get_object_id($section_value, $object_value, $object_type);

    if ($this->del_object($object_id, $object_type, TRUE))
      return TRUE;
    else
      return FALSE;
  }

}

?>
