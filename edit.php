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

  $message = validateProfile();
  if (is_string($message)) {
    $_SESSION['error'] = $message;
    header("Location: edit.php?profile_id={$_POST['profile_id']}");
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
  $_SESSION['success'] = 'Profile Edited';
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
<?php include "head.php"; ?>
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
