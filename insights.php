<?php
require_once 'config.php';

$yesterday = date('Y-m-d', time() - 60 * 60 * 24);
$first_of_month = date('Y') . '-' . date('m') . '-01';
$category_list = "'2', '6', '8', '9', '12', '14', '16'";
$days_in_month = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($yesterday)), date('Y', strtotime($yesterday)));

// Put all category names and IDs into array
$transactions = array();
$transaction_q = "SELECT
              transactions.amount,
              transactions.transaction_date,
              transactions.category_id
            FROM
              transactions
            WHERE
              transactions.category_id IN (" . $category_list . ") &&
              transactions.transaction_date >= " . $first_of_month;
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

// Put all category names and IDs into array
$targets = array();
$target_q = "SELECT
              categories.name AS 'name',
              monthly_targets.amount,
              monthly_targets.category_id
            FROM
              monthly_targets
            JOIN categories ON
              monthly_targets.category_id = categories.id
            WHERE
              monthly_targets.category_id IN (" . $category_list . ")";
if($target_data = $mysqli->query($target_q)) {
  if($target_data->num_rows > 0) {
    while($target = $target_data->fetch_array()) {
      $targets[]=$target;
    }
  } else {
    echo "No targets in database yet.";
    return;
  }
} else {
  echo "ERROR: Something went wrong while grabbing targets from the database. " . $mysqli->error;
  return;
}
$target_data->free();

function meetsCriteria($transaction_cat, $category_id) {
  return $category_id ? $transaction_cat == $category_id : true;
}

function sumTargets($targets) {
  $sum = 0;
  foreach ($targets as $target) {
    $sum += $target['amount'];
  }
  return number_format($sum, 2, '.', '');
}

function sumTransactions($transactions, $category_id = null) {
  $sum = 0;
  foreach ($transactions as $transaction) {
    if (meetsCriteria($transaction['category_id'], $category_id)) {
      $sum += $transaction['amount'];
    }
  }
  return number_format($sum, 2, '.', '');
}

function calculateDefecit($total, $days_in_month, $yesterday, $target) {
  $targetToDate = ($target / $days_in_month) * date('d', strtotime($yesterday));
  $defecit = $total - $targetToDate;
  return $defecit > 0 ? number_format((($defecit / $targetToDate) *  100), 2, '.', '') : false;
}

function calculateDailyTarget($target, $total, $yesterday, $days_in_month) {
  return number_format(($target - $total) / ($days_in_month - date('d', strtotime($yesterday))), 2, '.', '');
}
?>

<html>
<head>
  <title>Whiting Budget Insights</title>
</head>
<body style="max-width:640px;margin:auto;font-family:sans-serif;">
  <?php if (count($targets)): ?>
    <h3>Overview:</h3>
    <p>
      Your total monthly budget is <b>$<?=$targetsTotal = sumTargets($targets)?></b>.
      You've spent <b>$<?=$total = sumTransactions($transactions)?></b> so far this month.
      <?php if ($defecit = calculateDefecit($total, $days_in_month, $yesterday, $targetsTotal)): ?>
        This puts you <span style="color:red;">off track</span> by <b><?=$defecit?>%</b>.
      <?php else: ?>
        <span style="color:green;">You guys are on track for the month!</span>
      <?php endif; ?>
      You have <b>$<?=number_format(($targetsTotal - $total), 2, '.', '')?></b> left in your budget.
      This means you could spend <b>$<?=calculateDailyTarget($targetsTotal, $total, $yesterday, $days_in_month)?></b> each day for the rest of the month and still hit your target.
    </p>
    <hr>
    <?php foreach ($targets as $target): ?>
      <h3><?=$target['name']?>:</h3>
      <p>
        Your monthly target for <b><?=$target['name']?></b> is <b>$<?=$target['amount']?></b>.
        You've spent <b>$<?=$total = sumTransactions($transactions, $target['category_id'])?></b> so far this month.
        <?php if ($defecit = calculateDefecit($total, $days_in_month, $yesterday, $target['amount'])): ?>
          This puts you <span style="color:red;">off track</span> by <b><?=$defecit?>%</b>.
        <?php else: ?>
          <span style="color:green;">You guys are on track for the month!</span>
        <?php endif; ?>
        You have <b>$<?=number_format($target['amount'] - $total, 2, '.', '')?></b> left in your budget.
        This means you could spend <b>$<?=calculateDailyTarget($target['amount'], $total, $yesterday, $days_in_month)?></b> each day for the rest of the month and still hit your target.
      </p>
      <hr>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
