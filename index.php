<?php
require_once "pdo.php";
require_once "util.php";

session_start()

 ?>

<!DOCTYPE html>
<html>
<head>
<title>Jonathan Hartman's Resume Registry</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

</head>
<body>
<div class="container">
<h1>Jonathan Hartman's Resume Registry</h1>
<?php
  flashMessages();
if (!isset($_SESSION['user_id'])) {
  echo '<p><a href="login.php">Please log in</a></p>';
} else {
  $stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM profile");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if (!$rows) {
    echo '<p>No Profiles Found</p>';
  } else {
    echo '<table border="1">';
    echo '<tr><th>Name</th><th>Headline</th><tr>';
    foreach ($row as $rows) {
      echo '<tr>';
      echo '<td><a href="view.php?profile_id='.$row['profile_id'].'">'.$row['first_name'].' '.$row['last_name'].'</a></td><td>';
      echo '<td>'.$row['headline'].'</td>';
    }
    echo '</table>';
  }
  echo '<p><a href="add.php">Add New Entry</a></p>';
}

?>
<p>
<b>Note:</b> Your implementation should retain data across multiple
logout/login sessions.  This sample implementation clears all its
data periodically - which you should not do in your implementation.
</p>
</div>
</body>
