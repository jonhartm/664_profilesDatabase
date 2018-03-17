<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if (!isset($_SESSION['user_id']))
  die("ACCESS DENIED");

// Check to make sure we have all of our post data
if (isset($_POST['first_name'])
&& isset($_POST['last_name'])
&& isset($_POST['email'])
&& isset($_POST['headline'])
&& isset($_POST['summary'])) {

  // Make sure all fields have something in them
  if (strlen($_POST['first_name']) < 1
  || strlen($_POST['last_name']) < 1
  || strlen($_POST['email']) < 1
  || strlen($_POST['headline']) < 1
  || strlen($_POST['summary']) < 1) {
    $_SESSION['error'] = "All fields are required";
    header("Location: add.php");
    return;
  }

  // Validate the Email
  if (strpos($_POST['email'], '@') == 0) {
    $_SESSION['error'] = 'E-mail must contain an "@"';
    header("Location: add.php");
    return;
  }

  // Validate the Webpage
  if (strpos($_POST['webpage'], "http://") == 0 || strpos($_POST['webpage'], "https://")){
    $_SESSION['error'] = 'Webpage must begin with either "http:\\\\" or "https:\\\\"';
    header("Location: add.php");
    return;
  }

  // If we got here, insert into the database
  $stmt = $pdo->prepare('INSERT INTO Profile
    (user_id, first_name, last_name, url, email, headline, summary)
    VALUES ( :uid, :fn, :ln, :url, :em, :he, :su)');
  $stmt->execute(array(
    ':uid' => $_SESSION['user_id'],
    ':fn' => $_POST['first_name'],
    ':ln' => $_POST['last_name'],
    ':url' => $_POST['webpage'],
    ':em' => $_POST['email'],
    ':he' => $_POST['headline'],
    ':su' => $_POST['summary'])
    );
  $_SESSION['success'] = 'Profile Added';
  header("Location: index.php");
  return;
}




?>

<!DOCTYPE html>
<html>
<head>
<title>Jonathan Hartman's Profile Add</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
</head>
<body>
  <div class="container">
  <h1>Adding Profile for <?= $_SESSION['name'] ?></h1>
<?php flashMessages(); ?>
    <form method="post">
      <p>First Name:
      <input type="text" name="first_name" size="60"/></p>
      <p>Last Name:
      <input type="text" name="last_name" size="60"/></p>
      <p>Webpage:
      <input type="text" name="webpage" size="30"/></p>
      <p>Email:
      <input type="text" name="email" size="30"/></p>
      <p>Headline:<br/>
      <input type="text" name="headline" size="80"/></p>
      <p>Summary:<br/>
      <textarea name="summary" rows="8" cols="80"></textarea>
      <p>
      <input type="submit" value="Add">
      <input type="submit" name="cancel" value="Cancel">
      </p>
    </form>
  </div>
</body>
</html>
