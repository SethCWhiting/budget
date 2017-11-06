<?php
require_once 'config.php';

$yesterday = date('Y-m-d', time() - 60 * 60 * 24);

// Put all category names and IDs into array
$entries = array();
$first_of_month = date('Y') . '-' . date('m') . '-01';
$category_list = "'2', '6', '8', '9', '12', '14', '16'";
$entry_q = "SELECT
              categories.name,
              entries.amount,
              entries.transaction_date
            FROM
              entries
            JOIN categories ON
              entries.category_id = categories.id
            WHERE
              entries.category_id IN (" . $category_list . ") &&
              entries.transaction_date >= " . $first_of_month;
if($entry_data = $mysqli->query($entry_q)) {
  if($entry_data->num_rows > 0) {
    while($entry = $entry_data->fetch_array()) {
      $entries[]=$entry;
    }
  } else {
    echo "No categories in database yet.";
    return;
  }
} else {
  echo "ERROR: Something went wrong while grabbing categories from the database. " . $mysqli->error;
  return;
}
$entry_data->free();
?>

<pre>
  <?php print_r($entries); ?>
</pre>
