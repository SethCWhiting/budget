<?php
// Include config file
require_once 'config.php';

$yesterday = date('Y-m-d', time() - 60 * 60 * 24);
$selected_date = isset($_GET['date']) ? $_GET['date'] : $yesterday;

// Put all category names and IDs into array
$categories = array();
$category_q = "SELECT id, name FROM categories ORDER BY transaction_type_id, id";
if($category_data = $mysqli->query($category_q)) {
  if($category_data->num_rows > 0) {
    while($category = $category_data->fetch_array()) {
      $categories[]=$category;
    }
  } else {
    echo "No categories in database yet.";
    return;
  }
} else {
  echo "ERROR: Something went wrong while grabbing categories from the database. " . $mysqli->error;
  return;
}
$category_data->free();

// Put all entry data into array
$entries = array();
$entry_q = "SELECT
              categories.name AS 'name',
              entries.amount
              FROM
                entries
              JOIN
                categories
              ON
                entries.category_id = categories.id
              WHERE
                entries.transaction_date = '" . $selected_date . "'";
if($entry_data = $mysqli->query($entry_q)) {
  if($entry_data->num_rows > 0) {
    while($entry = $entry_data->fetch_array()) {
      $entries[]=$entry;
    }
  }
} else {
  echo "ERROR: Something went wrong while grabbing entries from the database. " . $mysqli->error;
  return;
}
$entry_data->free();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Input Transactions</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        .wrapper{
            width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h2>Input Transactions</h2>
                    </div>
                    <form action="post.php" method="post">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td>
                                        <label for="date">Date</label>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" name="date" value="<?=$selected_date?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <select class="form-control" name="category" required>
                                            <option value="">Choose Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?=$category['id']?>"><?=$category['name']?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="amount" required />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="submit" class="btn btn-primary" value="Submit Transaction">
                        <?php if ($selected_date == $yesterday): ?>
                            <a href="mail.php" class="btn btn-default">Mail Insights</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php if (count($entries)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <hr>
                        <h3>Transactions from <?=$selected_date?></h3>
                        <table class="table table-bordered table-striped">
                            <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td><?=$entry['name']?></td>
                                    <td>$<?=$entry['amount']?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
