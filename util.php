<?php
function flashMessages() {
  if ( isset($_SESSION['error']) ) {
      echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
      unset($_SESSION['error']);
  }
  if ( isset($_SESSION['success']) ) {
      echo '<p style="color:green">'.htmlentities($_SESSION['success'])."</p>\n";
      unset($_SESSION['success']);
  }
}

function getRandomLineFromArray($a, $first_word_only=true) {
  $selected = rand(0, count($a)-1);
  if ($first_word_only) {
    return trim(explode(" ", $a[$selected])[0]);
  } else {
    return $a[$selected];
  }
}
