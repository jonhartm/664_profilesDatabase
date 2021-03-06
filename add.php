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
    header("Location: add.php");
    return;
  }

  $message = validatePosition();
  if (is_string($message)) {
    $_SESSION['error'] = $message;
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

  $profile_id = $pdo->lastInsertId();

  $_SESSION['info']="here";
  // Insert the position entries
  insertPositions($pdo, $profile_id);

  // Insert the education entries
  insertEducations($pdo, $profile_id);

  $_SESSION['success'] = 'Profile Added';
  header("Location: index.php");
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
        <p>Education: <button type="button" id="addEdu">+</button></p>
        <div id="education_fields"></div>
        <p>Position: <button type="button" id="addPos">+</button></p>
      <div id="position_fields"></div>
      <input type="submit" value="Add">
      <input type="submit" name="cancel" value="Cancel">
      </p>
    </form>
  </div>
</body>

<script>
countPos = 0;
countEdu = 0;

$(document).ready(function() {
  $("#addPos").click(function(event){
    event.preventDefault();
    if (countPos >= 9) {
      alert("Maximum of nine position entries reached");
      return;
    }
    var div = $("<div>", {id:"position"+countPos});
    div.append($("<label>", {for:"year"+countPos, html:"Year:"}));
    div.append($("<input>", {type:"text", name:"year"+countPos, value:""}));
    div.append($("<input>", {type:"button", value:"-", onclick:"$('#position"+countPos+"').remove(); return false;"}));
    div.append('<br>');
    div.append($("<textarea>", {name:"desc"+countPos, rows:"8", cols:"80"}));
    $("#position_fields").append( div );
    countPos++;
  });

  $("#addEdu").click(function(event){
    event.preventDefault();
    if (countEdu >= 9) {
      alert("Maximum of nine position entries reached");
      return;
    }
    var div = $("<div>", {id:"education"+countPos});
    div.append($("<label>", {for:"year"+countPos, html:"Year:"}));
    div.append($("<input>", {type:"text", name:"edu_year"+countPos, value:""}));
    div.append($("<input>", {type:"button", value:"-", onclick:"$('#education"+countPos+"').remove(); return false;"}));
    div.append('<br>');
    div.append($("<label>", {for:"edu_school"+countPos, html:"School:"}));
    div.append($("<input>", {type:"text", name:"edu_school"+countPos, value:""}));
    $("#education_fields").append( div );
    countEdu++;

  })
})
</script>
</html>
