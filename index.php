<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if (isset($_POST['search'])) {
  if (strlen($_POST['filter_by']) > 0) {
    $_SESSION['success'] = "Search results for names containing \"{$_POST['filter_by']}\"";
    header("Location: index.php?filter={$_POST['filter_by']}");
    return;
  } else {
    header("Location: index.php");
    return;
  }
}

if (isset($_POST['next_10'])) {
  $offset = isset($_GET['offset']) ? $_GET['offset']+10 : 10;
  header("Location: index.php?offset=$offset");
  return;
} elseif (isset($_POST['prev_10'])) {
  $offset = isset($_GET['offset']) ? $_GET['offset']-10 : 0;
  header("Location: index.php?offset=$offset");
  return;
}

if (isset($_GET['filter'])) {
  $sql = 'SELECT profile_id, user_id, first_name, last_name, headline FROM profile WHERE first_name LIKE "%'.$_GET['filter'].'%" OR last_name LIKE "%'.$_GET['filter'].'%"';
} else {
  $sql = 'SELECT profile_id, user_id, first_name, last_name, headline FROM profile LIMIT 10';
}

if (isset($_GET['offset'])) {
  $sql .= " OFFSET {$_GET['offset']}";
}

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
if (!isset($_SESSION['user_id'])) {
  echo '<p><a href="login.php">Please log in</a></p>';
} else {
  echo "<h2>Hello {$_SESSION['name']}</h2>";
  echo '<p><a href="logout.php">Log out</a></p>';
}
flashMessages();

echo '<form method="post">';
echo '<p>Search:';
echo '<input type="text" name="filter_by" value="" size="20"/>';
echo '<input type="submit" name="search" value="Search">';
echo '</form>';
if (isset($_GET['filter'])) {
  echo '<form action="index.php">';
  echo '<input type="submit" value="Clear Search" />';
  echo '</form>';
}
echo '</p>';

if (!$rows) {
  echo '<p>No Profiles Found</p>';
} else {
  // Search Form
  echo '<table border="1">';
  echo '<tr><th>Name</th><th>Headline</th><th>Action</th><tr>';
  foreach ($rows as $row) {
    echo '<tr>';
    echo '<td><a href="view.php?profile_id='.$row['profile_id'].'">'.$row['first_name'].' '.$row['last_name'].'</a></td>';
    echo '<td>'.$row['headline'].'</td>';
    if (isset($_SESSION['user_id']) && $row['user_id'] == $_SESSION['user_id']) {
      echo '<td><a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> ';
      echo '<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a></td>';
    } else {
      echo '<td></td>';
    }
    echo '</tr>';
  }
  echo '</table>';

  // Previous and Next buttons
  echo '<form method="post">';
  if (isset($_GET['offset']) && !$_GET['offset'] == 0) {
    echo '<input type="submit" name="prev_10" value="< Previous 10">';
  }
  if (count($rows) >= 10) {
    echo '<input type="submit" name="next_10" value="Next 10 >">';
  }
  echo '</form>';
}

if (isset($_SESSION['user_id'])) {
  echo '<p><a href="add.php">Add New Entry</a></p>';
}
?>
</div>
</body>
