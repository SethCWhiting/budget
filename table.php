<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js"></script>
    <style type="text/css">
        .wrapper{
            width: 650px;
            margin: 0 auto;
        }
        .page-header h2{
            margin-top: 0;
        }
        table tr td:last-child a{
            margin-right: 15px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</head>
<body>
    <?php
      require_once 'config.php';

      // Put all dates from this month into array
      $dates=array();
      $month = date("m");
      $year = date("Y");

      for($d=1; $d<=31; $d++) {
        $time=mktime(12, 0, 0, $month, $d, $year);
        if (date('m', $time)==$month) {
          // $dates[]=date('Y-m-d-D', $time);
          $dates[]=$time;
        }
      }

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

      // Put all transaction data into array
      $transactions = array();
      $transaction_q = "SELECT * FROM transactions";
      if($transaction_data = $mysqli->query($transaction_q)) {
        if($transaction_data->num_rows > 0) {
          while($transaction = $transaction_data->fetch_array()) {
            $transactions[]=$transaction;
          }
        } else {
          echo "No transactions in database yet.";
          return;
        }
      } else {
        echo "ERROR: Something went wrong while grabbing transactions from the database. " . $mysqli->error;
        return;
      }
      $transaction_data->free();

      // Close MySQL connection
      $mysqli->close();
    ?>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th></th>
          <?php foreach ($dates as $date): ?>
            <th><?=date('m/d', $date)?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $category): ?>
          <tr>
            <td><b><?=$category['name']?></b></td>
            <?php
              foreach ($dates as $date) {
                $amount = '0.00';
                foreach ($transactions as $transaction) {
                  if ($transaction['transaction_date'] == date('Y-m-d', $date) && $transaction['category_id'] == $category['id']) {
                    $amount = $transaction['amount'];
                  }
                }
                echo '<td>$' . $amount . '</td>';
              }
            ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</body>
</html>
