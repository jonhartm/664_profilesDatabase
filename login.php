<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if (isset($_POST['cancel'])) {
  header("Location: index.php");
  return;
}

$salt = 'XyZzy12*_';

// Do we have post data? If so, process it
if (isset($_POST['email']) && isset($_POST['pass'])) {
  if (strlen($_POST['email']) <1 || strlen($_POST['pass']) <1) {
    $_SESSION['error'] = "User name and password are required";
  } else if (strpos($_POST['email'], '@') == 0) {
    $_SESSION['error'] = "Email must have an at-sign (@)";
  } else {
    $check = hash('md5', $salt.$_POST['pass']);
    $stmt = $pdo->prepare('SELECT user_id, name FROM users
      WHERE email=:email and password=:pw');
    $stmt->execute(array(':email'=>$_POST['email'], ':pw'=>$check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row !== false) {
      $_SESSION['name'] = $row['name'];
      $_SESSION['user_id'] = $row['user_id'];
      header("Location: index.php");
      return;
    } else {
      $_SESSION['error'] = "Incorrect password";
    }
  }
  header("Location: login.php");
  return;
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Jonathan Hartman Login Page</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
</head>
<body>
 <div class="login container">
   <h1>Please Log In</h1>
<?php
  flashMessages();
?>
  <form method="post">
    <label for="email">User Name</label>
    <input type="text" name="email" id="email"><br/>
    <label for="pass">Password</label>
    <input type="text" name="pass" id="pass"><br/>
    <input type="submit" value="Log In" onclick="return doValidate();">
    <input type="submit" name="cancel" value="Cancel">
  </post>
</div>
<script>
function doValidate() {
  try {
    email = document.getElementById("email").value;
    pw = document.getElementById("pass").value;
    if (email == null || email == "" || pw == null || pw == "" ) {
      alert('Both fields must be filled out');
      return false;
    } else if (email.indexOf("@") == -1) {
      alert('Email must contain an "@" sign.');
    }
    return true;
  } catch (e) {
    console.log(e);
    return false;
  }
  return false;
 }
 </script>
</body>
