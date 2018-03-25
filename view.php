<?php
require_once "pdo.php";
session_start();

$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :p_id");
$stmt->execute(array(":p_id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$stmt = $pdo->prepare("SELECT * FROM position WHERE profile_id = :p_id");
$stmt->execute(array(":p_id" => $_GET['profile_id']));
$pos_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
