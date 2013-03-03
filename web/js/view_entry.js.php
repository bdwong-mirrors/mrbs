<?php

// $Id$

require "../defaultincludes.inc";

header("Content-type: application/x-javascript");
expires_header(60*30); // 30 minute expiry

if ($use_strict)
{
  echo "'use strict';\n";
}
?>

var setViewMap = function setViewMap() {
    var value = parseInt($('input[name=time_subset]:checked', '#view_nav').val(), 10);
    var events = $('.event', '#view_nav');

    <?php
    // The current event is always selected with the options we have
    // available (may change in the future).
     ?>
    var pastSwitch = (value === <?php echo WHOLE_SERIES ?>);
    var nowSwitch = true;
    var futureSwitch = (value === <?php echo WHOLE_SERIES ?> || 
                        value === <?php echo THIS_AND_FUTURE ?>);

    events.filter('.past').toggleClass('selected', pastSwitch);
    events.filter('.now').toggleClass('selected', nowSwitch);
    events.filter('.future').toggleClass('selected', futureSwitch);
  };

<?php
// =================================================================================

// Extend the init() function 
?>

var oldInitViewEntry = init;
init = function(args) {
  oldInitViewEntry.apply(this, [args]);
  
  $('input[name=time_subset]', '#view_nav').change(function() {
      setViewMap();
    });
    
  setViewMap();
  
  <?php
  // Display the table which is not displayed by default
  ?>
  $('table', '#view_nav').css('display', 'table');
  
  
  $('input[name="delete_button"]', '#view_nav').click(function(e) {
      if (!window.confirm('<?php echo get_vocab("confirmdel")?>'))
      {
        e.preventDefault();
      }
    });

};
