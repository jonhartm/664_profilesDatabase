<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if (count($_POST) > 0) {
  $get_header = array();

  // get any existing get paramaters
  if (isset($_GET['filter'])) {
    $get_header['filter'] = $_GET['filter'];
  }

  if (isset($_GET['offset'])) {
    $get_header['offset'] = $_GET['offset'];
  }

  // If there's a filter by post, set the get query 'filter' to that
  if (isset($_POST['search']) && strlen($_POST['filter_by']) > 0) {
    $_SESSION['success'] = "Search results for names containing \"{$_POST['filter_by']}\"";
    $get_header['filter'] = $_POST['filter_by'];
    $get_header['offset'] = 0;
  }

  // Set the offset query based on if either of the next or prev buttons were pressed
  if (isset($_POST['next_10'])) {
    $get_header['offset'] = isset($_GET['offset']) ? $_GET['offset']+10 : 10;
  } elseif (isset($_POST['prev_10'])) {
    $get_header['offset'] = isset($_GET['offset']) ? $_GET['offset']-10 : 0;
  }

  header("Location: index.php?".http_build_query($get_header));
}

if (isset($_GET['filter'])) {
  $sql = 'SELECT profile_id, user_id, first_name, last_name, headline FROM profile WHERE first_name LIKE "%'.$_GET['filter'].'%" OR last_name LIKE "%'.$_GET['filter'].'%" LIMIT 10';
} else {
  $sql = 'SELECT profile_id, user_id, first_name, last_name, headline FROM profile LIMIT 11';
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
<?php include "head.php"; ?>
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
echo '<p>Search by Name:';
$filter_text = isset($_GET['filter']) ? $_GET['filter'] : '';
echo '<input type="text" name="filter_by" value="'.$filter_text.'" size="20"/>';
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
  for ($row=0;$row<count($rows)-1;$row++) {
    echo '<tr>';
    echo '<td><a href="view.php?profile_id='.$rows[$row]['profile_id'].'">'.$rows[$row]['first_name'].' '.$rows[$row]['last_name'].'</a></td>';
    echo '<td>'.$rows[$row]['headline'].'</td>';
    if (isset($_SESSION['user_id']) && $rows[$row]['user_id'] == $_SESSION['user_id']) {
      echo '<td><a href="edit.php?profile_id='.$rows[$row]['profile_id'].'">Edit</a> ';
      echo '<a href="delete.php?profile_id='.$rows[$row]['profile_id'].'">Delete</a></td>';
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
  if (count($rows) == 11) {
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
