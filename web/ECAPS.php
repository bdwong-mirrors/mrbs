<?php

/**
 * ACL management class
 *
 * Contributed by Diego Zuccato <ndk@csshl.net>
 */
/*
 * From mail sent to the ML:
 * > Basically, any group of controls should be
 * > protected by three caps: "create/delete", "access(read)" and "edit(write)".
 * > Caps sets can be represented by strings (so requiring only the addition
 * > of a "caps varchar" column to users table and "acl varchar" to
 * > areas/rooms tables) and can leverage current method to override strings
 * > with lists of options (making it more user-friendly).
 * >
 * > A good way to represent caps could be a list of comma-separed words, and
 * > a good way to represent an ACL could be a set of 3 (extended: a cap
 * > could be prefixed by a '-' to indicate that it must *not* be set) caps
 * > sets separed by pipes. More formally:
 * > LETNUM := 'a'..'z' | 'A'..'Z' | '0'..'9' | '_'
 * > CAP := LETNUM | LETNUM CAP
 * > CAPS := '' | CAP | CAP ',' CAPS
 * > ECAP := CAP | '-' CAP
 * > ECAPS := '' | ECAP | ECAP ',' ECAPS
 * > ACL := ECAPS '|' ECAPS' '|' ECAPS
 * Actually I modified this last, to represent full CRUD set, so
 * ACL := ECAPS '|' ECAPS '|' ECAPS '|' ECAPS
 * (caps respectively for Create, Read, Update and Delete)
 *
 * Extension: caps and ACLs can include levels! cap[=lev][,cap=lev]*
 * A cap w/o level is equal to a cap with level=0.
 * An acl is verified iif:
 * - the checked caps set is a superset of acl's caps
 * - every cap in acl's set have a level <= the corresponding compared cap level
 *
 * For example, given an ACL of "level=2,-disabled":
 * - "level=5,disabled" is rejected ('disabled' is set)
 * - "level=1,admin" is rejected (1 < 2)
 * - "level=2,foo=3,bar=0" is accepted ('foo' and 'bar' aren't in ACL, but it's OK)
 * - "foo=100,bar=abc" is rejected (missing required 'level')
*/

define('ALPHA1', "abcdefghijklmnopqrstuvwxyz");
define('ALPHA2', "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('NUMBERS', "0123456789");
define('LETNUM', ALPHA1.ALPHA2.NUMBERS."_");

class ECAPS {
    // Accept an array (one extended capability per *key* -- value is the level) or a string
    // (packed extended capabilities. Duplicated capabilities are overwritten and only the
    // last one remains)
    public function __construct($ecaps) {
	if(is_array($ecaps)) {
	    // Sanitize input array:
	    // - remove illegal keys (not valid caps)
	    // - make sure values are positive integers
	    foreach($ecaps as $k => $v) {
		$t=$this->parse_ecap("$k=$v", TRUE);
		if(FALSE===$t || $t['key']!=$k) { // invalid cap or different key: discard
		    unset($ecaps[$k]);
		} else {
		    $ecaps[$k]=$t['val'];	// sanitize value
		}
	    }
	} else if(is_string($ecaps)) {
	    $ecaps=$this->parse_ecaps_string($ecaps, true);
	} else {	// Ops! Don't know what to do, so I leave caps empty
	    trigger_error("Bad caps constructor parameter -- leaving caps empty!", E_USER_WARNING);
	    return;
	}
	foreach($ecaps as $cap => $v) {
	    if(substr($cap, 0, 1) == '-') {
		$this->neg_caps[substr($cap, 1)]=$v;
	    } else {
		$this->pos_caps[$cap]=$v;
	    }
	}

	$this->pcaps=count($this->pos_caps); // Useless to count every time it's needed to verify a CAPS set

	$tst=array_intersect(array_keys($this->pos_caps), array_keys($this->neg_caps));
	if(count($tst)) {
	    trigger_error("Never-satisfiable CAPS: pos and neg caps overlap for ". implode(",",$tst), E_USER_WARNING);
	}
    }

    // Gets a capability set and returns if the CAPs allows access (TRUE) or not (FALSE)
    public function test($cap) {
	if(is_array($cap)) $caparr=$cap;
	else if(is_string($cap)) $caparr=$this->parse_ecaps_string($cap);
	else return FALSE;

	$pos=count(array_intersect(array_keys($this->pos_caps), array_keys($caparr)));
	$neg=count(array_intersect(array_keys($this->neg_caps), array_keys($caparr)));

	// There must be *NO* negatives and *ALL* positives
	$rv=($neg==0)&&($pos==$this->pcaps);

	if($rv) { // Time to check levels!
	    foreach($this->pos_caps as $cap => $lev) {
		if($caparr[$cap]<$lev) {
		    $rv=FALSE;
		    break; // bail out: at least one level too low!
		}
	    }
	}

	return $rv;
    }

    public function __toString() {
	$tmp=$this->pos_caps;
	foreach($this->neg_caps as $ncap => $nval) {
	    $tmp["-".$ncap]=$nval;
	}
	$out="";
	foreach($tmp as $k => $v) {
	    $out .= ($out==""?"":",")."$k=$v";
	}
	return $out;
    }

    //************************
    private function  parse_ecaps_string($caps, $allow_neg=FALSE) {
	$tcaps = array();
	$tmp=explode(",", $caps);
	foreach($tmp as $tok) { // Probably this can be optimized
	    if(""==$tok) continue;

	    $t=$this->parse_ecap($tok, $allow_neg);
	    if(is_array($t)) {
		$tcaps[$t['key']] = $t['val'];
	    }
	}
	return $tcaps;
    }

    // Gets a single ecap string and returns an array:
    // 'key' => ecap name
    // 'val' => ecap value
    // Returns FALSE if the capability is invalid
    private function parse_ecap($tok, $allow_neg) {
	$rv=array();

	$start=0;
	if($tok[0]=='-' && $allow_neg) { // *Possibly* a neg cap
	    $start=1; // Skip the initial '-'
	}
	$kl=strspn($tok, LETNUM, $start);
	if($kl) { // at least one valid char as key
	    $kl+=$start;
	    $rv['key'] = substr($tok, 0, $kl);
	    $rv['val'] = 0;
	    if(strlen($tok)>$kl && "="==$tok[$kl]) {
		// value present
		$rv['val'] = (int)substr($tok, $kl+1, strspn($tok, NUMBERS, $kl+1));
	    }
	} else {	// Invalid key ==> invalid cap (no name and val)
	    $rv=FALSE;
	}

	return $rv;
    }

    private $pos_caps=array(), $neg_caps=array(), $pcaps=0;
}
