<?php
require_once "pdo.php";
require_once "util.php";
session_start();

$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :p_id");
$stmt->execute(array(":p_id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$pos_rows = loadPos($pdo, $_GET['profile_id']);
$edu_rows = loadEdu($pdo, $_GET['profile_id']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Jonathan Hartman's Profile View</title>
<?php include "head.php"; ?>
</head>
<body>
  <div class="container">
    <h1>Profile information</h1>
    <p>First Name: <?=htmlentities($row['first_name']) ?></p>
    <p>Last Name: <?=htmlentities($row['last_name']) ?></p>
    <p>Webpage: <?=htmlentities($row['url']) ?></p>
    <p>Email: <?=htmlentities($row['email']) ?></p>
    <p>Headline:<br/>
    <p><?=htmlentities($row['headline']) ?></p>
    <p>Summary:<br/>
    <p><?=htmlentities($row['summary']) ?></p>
    <p>Education:</p>
<?php
foreach ($edu_rows as $edu) {
  echo '<li>'.$edu['year'].': '.$edu['name'].'</li>';
}
 ?>
    <p>Position</p>
    <p><ul>
<?php
foreach ($pos_rows as $pos) {
  echo '<li>'.$pos['year'].': '.$pos['description'].'</li>';
}
 ?>
</ul></p>
    <a href="javascript:history.back()">Done</a>
  </div>
</body>
</html>
