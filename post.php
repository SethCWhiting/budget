<?php
require_once 'config.php';
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $vals = array();
    $date = date('Y-m-d', strtotime($_POST['date']));
    $val= '(' . $_POST['category'] . ', ' . $_POST['amount'] . ', "' . $date . '")';

    // Prepare an insert statement
    $sql = "INSERT INTO entries (category_id, amount, transaction_date) VALUES " . $val;

    if(mysqli_query($mysqli, $sql)){
        header("location: /?date=" . $date);
        exit();
    } else{
        echo "ERROR: Could not execute query. " . mysqli_error($mysqli);
    }
    // Close connection
    $mysqli->close();
}
?>
