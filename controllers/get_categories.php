php
<?php
include 'utils.php';

$mysqli = connectToDatabase();

$categories = getAllCategories($mysqli);

header('Content-Type: application/json');
echo json_encode($categories);

$mysqli->close();
?>