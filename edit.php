<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if (!isset($_SESSION['user_id']))
  die("ACCESS DENIED");

if (isset($_POST['cancel'])) {
  header("Location: index.php");
  return;
}

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
    header("Location: edit.php?profile_id={$_POST['profile_id']}");
    return;
  }

  // Validate the Email
  if (strpos($_POST['email'], '@') == 0) {
    $_SESSION['error'] = 'E-mail must contain an "@"';
    header("Location: edit.php?profile_id={$_POST['profile_id']}");
    return;
  }

  // Validate the Webpage
  if (strpos($_POST['webpage'], "http://") == 0 || strpos($_POST['webpage'], "https://")){
    $_SESSION['error'] = 'Webpage must begin with either "http:\\\\" or "https:\\\\"';
    header("Location: add.php");
    return;
  }

  // If we got here, insert into the database
  $sql = "UPDATE profile SET first_name=:fn, last_name=:ln, url=:url, email=:em, headline =:he, summary=:su
            WHERE user_id = :u_id AND profile_id = :p_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(
    ':u_id' => $_SESSION['user_id'],
    ':p_id' => $_POST['profile_id'],
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

// Guardian: Make sure that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :p_id AND user_id = :u_id");
$stmt->execute(array(":p_id" => $_GET['profile_id'], ":u_id"=>$_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
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
  <h1>Editing Profile for <?= $_SESSION['name'] ?></h1>
<?php flashMessages(); ?>
    <form method="post">
      <p>First Name:
      <input type="text" name="first_name" size="60" value="<?=htmlentities($row['first_name']) ?>"/></p>
      <p>Last Name:
      <input type="text" name="last_name" size="60" value="<?=htmlentities($row['last_name']) ?>"/></p>
      <p>Webpage:
      <input type="text" name="webpage" size="30" value="<?=htmlentities($row['url']) ?>"/></p>
      <p>Email:
      <input type="text" name="email" size="30" value="<?=htmlentities($row['email']) ?>"/></p>
      <p>Headline:<br/>
      <input type="text" name="headline" size="80" value="<?=htmlentities($row['headline']) ?>"/></p>
      <p>Summary:<br/>
      <textarea name="summary" rows="8" cols="80"><?=htmlentities($row['summary']) ?></textarea>
      <p>
      <input type="hidden" name="profile_id" value="<?=htmlentities($_GET['profile_id']) ?>">
      <input type="submit" name="save" value="Save">
      <input type="submit" name="cancel" value="Cancel">
      </p>
    </form>
  </div>
</body>
</html>
