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
  if ( isset($_SESSION['information']) ) {
      echo '<p style="color:yellow; background-color:gray">'.htmlentities($_SESSION['information'])."</p>\n";
      unset($_SESSION['information']);
  }
}

function validateProfile() {
  // Make sure all fields have something in them
  if (strlen($_POST['first_name']) < 1
  || strlen($_POST['last_name']) < 1
  || strlen($_POST['email']) < 1
  || strlen($_POST['headline']) < 1
  || strlen($_POST['summary']) < 1) {
    return "All fields are required";
  }

  // Validate the Email
  if (strpos($_POST['email'], '@') == 0) {
    return 'E-mail must contain an "@"';
  }

  // Validate the Webpage
  if (strlen($_POST['webpage']) > 0 && (strpos($_POST['webpage'], "http://") == 0 || strpos($_POST['webpage'], "https://"))){
    return 'Webpage must begin with either "http:\\\\" or "https:\\\\"';
  }

  return true;
}

function validatePosition() {
  for ($x=1; $x <= 9; $x++) {
    if (!isset($_POST['year'.$x])) continue;
    if (!isset($_POST['desc'.$x])) continue;
    $year = $_POST['year'.$x];
    $desc = $_POST['desc'.$x];
    if (strlen($year) == 0 || strlen($desc) == 0) {
      return "All fields are required";
    }
    if (!is_numeric($year)) {
      return "Position year must be numeric";
    }
  }
  return true;
}

function getRandomLineFromArray($a, $first_word_only=true) {
  $selected = rand(0, count($a)-1);
  if ($first_word_only) {
    return trim(explode(" ", $a[$selected])[0]);
  } else {
    return $a[$selected];
  }
}
