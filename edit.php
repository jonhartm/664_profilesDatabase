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

  $message = validatePosition();
  if (is_string($message)) {
    $_SESSION['error'] = $message;
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

    // Insert the position entries
    $rank = 1;
    for ($x=0; $x<=9; $x++) {
      if (!isset($_POST['year'.$x])) continue;
      if (!isset($_POST['desc'.$x])) continue;
      $year = $_POST['year'.$x];
      $desc = $_POST['desc'.$x];

      $stmt = $pdo->prepare('UPDATE Position SET year=:yr, description=:desc
        WHERE profile_id=:pid AND rank=:rnk');
      $stmt->execute(array(
        ':pid' => $_POST['profile_id'],
        ':rnk' => $rank,
        ':yr' => $year,
        ':desc' => $desc)
        );
      $rank++;
    }



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

$stmt = $pdo->prepare("SELECT * FROM position WHERE profile_id = :p_id");
$stmt->execute(array(":p_id" => $_GET['profile_id']));
$position_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
      <div id="position_fields">
<?php
  for ($pos=0; $pos<count($position_rows); $pos++) {
?>
    <div id="position<?=$pos?>">
    <label for="year<?=$pos?>">Year:</label>
    <input type="text" name="year<?=$pos?>" value="<?=htmlentities($position_rows[$pos]['year'])?>">
    <input type="button" value="-" onclick="$('#position<?=$pos?>').remove(); return false;">
    <textarea name="desc<?=$pos?>" rows="8" cols="80"><?=htmlentities($position_rows[$pos]['description'])?></textarea>
    </div>
<?php
  }
 ?>
      </div>
      <p>Position: <button type="button" id="addPos">+</button></p>
      <input type="submit" name="save" value="Save">
      <input type="submit" name="cancel" value="Cancel">
      </p>
    </form>
  </div>
</body>
<script>
countPos = <?=count($position_rows)?>;

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
    div.append($("<textarea>", {name:"desc"+countPos, rows:"8", cols:"80"}));
    $("#position_fields").append( div );
    countPos++;
  });
})
</script>
</html>
