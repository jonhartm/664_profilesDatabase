<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if ( isset($_POST['cancel'])) {
  header("Location: index.php");
  return;
}

// If we have a delete post, try to delete the entry
if ( isset($_POST['delete'])
&& isset($_POST['profile_id'])
&& isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("DELETE FROM profile WHERE profile_id = :profileid AND user_id = :userid");
  $stmt->execute(array(":profileid" => $_POST['profile_id'],
                       ":userid" => $_SESSION['user_id']));
  $_SESSION['success'] = 'Record deleted';
  header( 'Location: index.php' ) ;
  return;
}

// Guardian: Make sure that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :profileid AND user_id = :userid");
$stmt->execute(array(":profileid" => $_GET['profile_id'],
                     ":userid" => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = "Unable to find profile_id: {$_GET['profile_id']} with user_id: {$_SESSION['user_id']}";
    header( 'Location: index.php' ) ;
    return;
}
 ?>

<!DOCTYPE html>
<html>
<head>
<title>Jonathan's Profile Delete</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
</head>
<body>
  <div class="container">
  <h1>Deleting Profile</h1>
    <form method="post" action="delete.php">
      <p>First Name: <?= $row['first_name'] ?></p>
      <p>Last Name: <?= $row['last_name'] ?></p>
      <input type="hidden" name="profile_id" value="<?= $_GET['profile_id'] ?>">
      <input type="submit" name="delete" value="Delete">
      <input type="submit" name="cancel" value="Cancel">
      </p>
    </form>
  </div>
</body>
</html>
