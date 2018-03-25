<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if (isset($_POST['clear'])) {
  // Delete everything from the users table beyond the first two
  $stmt = $pdo->prepare("DELETE FROM users WHERE user_id > 2");
  $stmt->execute();

  // Delete anything in the profiles where the profile contains "{GENERATED PROFILE DATA}"
  $stmt = $pdo->prepare('DELETE FROM profile WHERE summary LIKE "%{GENERATED PROFILE DATA}"');
  $stmt->execute();

  $_SESSION['success'] = 'Generated records deleted';
  header('Location: generate_entries.php');
  return;
}

if (isset($_POST['num_entries'])) {
  if (!is_numeric($_POST['num_entries'])) {
    $_SESSION['error'] = "Number of entries must be numeric";
    header('Location: generate_entries.php');
    return;
  }
  if ($_POST['num_entries'] < 0 || $_POST['num_entries'] > 100) {
    $_SESSION['error'] = "Number of entries should be more than 0 but less than 100";
    header("Location: generate_entries.php");
    return;
  }

  // Go head and start generating...
  $firstnames = file("data/first_names.txt") or die("Unable to open first_names.txt");
  $lastnames = file("data/last_names.txt") or die("Unable to open last_names.txt");
  $randomwords = file("data/words.txt") or die("Unable to open words.txt");
  $loremipsum = file("data/lorem_ipsum.txt") or die("Unable to open lorem_ipsum.txt");

  function generateEntry($firstnames, $lastnames, $randomwords, $loremipsum) {
    $entry = array();
    // Name
    $entry['firstname'] = ucfirst(strtolower(getRandomLineFromArray($firstnames)));
    $entry['lastname'] = ucfirst(strtolower(getRandomLineFromArray($lastnames)));

    $entry['password'] = getRandomLineFromArray($randomwords).rand(1,99);
    $entry['passwordhash'] = hash('md5', 'XyZzy12*_'.$entry['password']);

    // webpage
    if (rand(0,10) > 3) {
      $protocol = ['http://www.', 'https://www.'];
      $domains = ['.com', '.net', '.co.uk', '.edu', '.gov'];
      $entry['webpage'] = $protocol[rand(0,1)].getRandomLineFromArray($randomwords).getRandomLineFromArray($randomwords).$domains[rand(0,4)];
    } else {
      $entry['webpage'] = '';
    }

    // email
    $emaildomains = ["aol.com", "att.net", "comcast.net", "facebook.com", "gmail.com", "gmx.com", "googlemail.com",
    "google.com", "hotmail.com", "hotmail.co.uk", "mac.com", "me.com", "mail.com", "msn.com",
    "live.com", "sbcglobal.net", "verizon.net", "yahoo.com", "yahoo.co.uk"];
    $entry['email'] = strtolower($entry['firstname'][0]).strtolower($entry['lastname'])."@".getRandomLineFromArray($emaildomains);

    // profile
    $entry['headline'] = getRandomLineFromArray($randomwords)." ".getRandomLineFromArray($randomwords);
    $entry['profile'] = getRandomLineFromArray($loremipsum, false);

    // did they have a lot to say? chances they wrote more
    $talkative = rand(0,10);
    if ($talkative < 7) {
      $entry['profile'].='\n\n'.getRandomLineFromArray($loremipsum, false);
    }

    if ($talkative < 2) {
      $entry['profile'].='\n\n'.getRandomLineFromArray($loremipsum, false);
    }

    $entry['profile'].=' {GENERATED PROFILE DATA}';
    $entry['profile'].=' {password: '.$entry['password'].'}';

    // positions
    $entry['positions'] = array();
    $year = 2018;
    for ($num_positions=0; $num_positions < rand(0,9); $num_positions++) {
        $new_pos = array();
        $year = $year - rand(0,4);
        $new_pos['year'] = $year;
        $titles = ["Head of", "Director of", "Assistant to the", "Manager of", "Vice President of", "Chief", "President for", "", "", "", "", ""];

        $new_pos['desc'] = $titles[rand(0,11)]." ".getRandomLineFromArray($randomwords)." ".getRandomLineFromArray($randomwords);
        array_push($entry['positions'], $new_pos);
    }

    return $entry;
  }

  $entries = array();
  for ($i=0; $i < $_POST['num_entries']; $i++) {
    array_push($entries, generateEntry($firstnames, $lastnames, $randomwords, $loremipsum));
  }

  // Add these entries to the database
  foreach ($entries as $new_entry) {
    // Create a new user
    $stmt = $pdo->prepare('INSERT INTO users
      (name, email, password)
      VALUES (:name, :email, :password)');
    $stmt->execute(array(
      ':name' => $new_entry['firstname'],
      ':email' => $new_entry['email'],
      ':password' => $new_entry['passwordhash']
    ));
    // Add a profile for this user
    $stmt = $pdo->prepare('INSERT INTO Profile
      (user_id, first_name, last_name, url, email, headline, summary)
      VALUES ( (SELECT user_id FROM users WHERE email=:em AND password=:pw), :fn, :ln, :url, :em, :he, :su)');
    $stmt->execute(array(
      ':pw' => $new_entry['passwordhash'],
      ':fn' => $new_entry['firstname'],
      ':ln' => $new_entry['lastname'],
      ':url' => $new_entry['webpage'],
      ':em' => $new_entry['email'],
      ':he' => $new_entry['headline'],
      ':su' => $new_entry['profile'])
    );

    $profile_id = $pdo->lastInsertId();

    $rank = 1;
    foreach ($new_entry['positions'] as $pos) {
      $stmt = $pdo->prepare('INSERT INTO Position
        (profile_id, rank, year, description)
        VALUES ( :pid, :rnk, :yr, :desc)');
      $stmt->execute(array(
        ':pid' => $profile_id,
        ':rnk' => $rank,
        ':yr' => $pos['year'],
        ':desc' => $pos['desc'])
        );
      $rank++;
    }
  }
}

 ?>

<!DOCTYPE html>
<html>
<head>
<title>Profile Generator</title>
<?php require_once "head.php"; ?>
</head>
<body>
  <div class="container">
    <h1>Generate Random Profile Entries</h1>
    <?php flashMessages(); ?>
    <form method="post">
      <p>Number of entries to add:
      <input type="text" name="num_entries" size="10"/></p>
      <input type="submit" name="generate" value="Generate">
      <input type="submit" name="clear" value="Clear All Generated Entries">
    </form>
  </div>
  <hr>
  <div class="container">
<?php
if (isset($entries)) {
  echo '<pre>';
  print_r($entries);
  echo '</pre>';
}
 ?>
  </div>
</body>
</html>
