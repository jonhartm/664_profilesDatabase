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
  if ( isset($_SESSION['info']) ) {
      echo '<p style="color:yellow; background-color:gray">'.htmlentities($_SESSION['info'])."</p>\n";
      unset($_SESSION['info']);
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
  for ($x=0; $x < 9; $x++) {
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

function validateEducation() {
  for($i=1; $i<=9; $i++) {
    if (!isset(($_POST['edu_year'.$i]))) continue;
    if (!isset(($_POST['edu_school'.$i]))) continue;
    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];
    if (strlen($year) == 0 || strlen($school) == 0) {
      return "All fields are required";
    }

    if (!is_numeric($year)) {
      return "Education year must be numeric";
    }
  }
  return true;
}

function loadPos($pdo, $profile_id) {
  $stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :p_id ORDER BY rank");
  $stmt->execute(array(":p_id" => $profile_id));
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function loadEdu($pdo, $profile_id) {
  $stmt = $pdo->prepare("SELECT year, name FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id WHERE profile_id = :p_id ORDER BY rank");
  $stmt->execute(array(":p_id" => $profile_id));
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertPositions($pdo, $profile_id) {
  $rank = 1;
  for ($x=0; $x<9; $x++) {
    if (!isset($_POST['year'.$x])) continue;
    if (!isset($_POST['desc'.$x])) continue;
    $year = $_POST['year'.$x];
    $desc = $_POST['desc'.$x];

    $stmt = $pdo->prepare('INSERT INTO Position
      (profile_id, rank, year, description)
      VALUES ( :pid, :rnk, :yr, :desc)');
    $stmt->execute(array(
      ':pid' => $profile_id,
      ':rnk' => $rank,
      ':yr' => $year,
      ':desc' => $desc)
      );
    $rank++;
  }
}

function insertEducations($pdo, $profile_id) {
  $rank = 1;
  for($i=0; $i<9;$i++) {
    if (!isset(($_POST['edu_year'.$i]))) continue;
    if (!isset(($_POST['edu_school'.$i]))) continue;
    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];

    // Look up to see if the school is already in the database
    $institution_id = false;
    $stmt = $pdo->prepare("SELECT institution_id FROM Institution WHERE name = :name");
    $stmt->execute(array(":name"=>$school));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row !== false) $institution_id = $row['institution_id'];

    // If the school doesn't exist in the database, add it here
    if ($institution_id === false) {
      $stmt = $pdo->prepare("INSERT INTO Institution (name) VALUES (:name)");
      $stmt->execute(array(":name"=>$school));
      $institution_id = $pdo->lastInsertId();
    }

    // Add the entry to the education table
    $stmt = $pdo->prepare("INSERT INTO Education (profile_id, rank, year, institution_id) VALUES (:p_id, :rank, :year, :i_id)");
    $stmt->execute(array(
      ":p_id"=>$profile_id,
      ":rank"=>$rank,
      ":year"=>$year,
      ":i_id"=>$institution_id
    ));
    $rank++;
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
