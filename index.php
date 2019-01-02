<?php

/**
* What will be the closing spot price for an ounce of gold against the US dollar on 28 December 2018?
*
*   Market closes 5pm EST
*   Data from 1st Jan 2014
*   Question closed.
*/



/*******************************************************
*
*
* Constants
*
*
*******************************************************/

$external_links = [
  "Home"        => "http://vostro.home/2018-11-17_gold/",
  "Question"    => "https://www.gjopen.com/questions/1045-what-will-be-the-closing-spot-price-for-an-ounce-of-gold-against-the-us-dollar-on-28-december-2018",
  "Resolution"  => "https://www.bloomberg.com/quote/XAUUSD:CUR",
  "Days until"  => "https://www.timeanddate.com/date/workdays.html?d1=&m1=&y1=&d2=28&m2=12&y2=2018&ti=on&",
];

$historical_data_filename = 'XAU_USD Historical Data.csv';
$targets = [1150, 1200, 1250, 1300];

# Market closes about 10.05pm my time.

$price = 1249.3100;                 # FRI EVE @ 22:55
$trading_days_remaining = 17;       # trading also on saturdays

$price = 1244.46;                 # FRI EVE @ 22:55
$trading_days_remaining = 15;       # trading also on saturdays

$price = 1243.25;                 # MON EVE @ 22:05.
$trading_days_remaining = 14;       # trading also on saturdays

$price = 1241.9900;               # SUN PM
$trading_days_remaining = 10;       # trading also on saturdays

$price = 1239.0200;               # MON EVE
$trading_days_remaining = 9;        # trading also on saturdays

$price = 1245.8500;               # TUE EVE
$trading_days_remaining = 8;        # trading also on saturdays

$price = 1243.0800;               # THU
$trading_days_remaining = 7;        # trading also on saturdays

$price = 1259.8600;               # FRI
$trading_days_remaining = 6;        # trading also on saturdays

$price = 1269.2200;               # TUE
$trading_days_remaining = 3;        # trading also on saturdays

$price = 1268.5400;               # WED
$trading_days_remaining = 2;        # trading also on saturdays

$price = 1267.1400;               # THU
$trading_days_remaining = 1;        # trading also on saturdays

# 21:35 Tue 27 Nov 2018. Bloomberg "1,214.9800USD.                  PREV CLOSE 1,214.9800 - AS OF 04:32 PM EST 11/27/2018 EDT"
# 22:34 Tue 27 Nov 2018. Bloomberg "1,215.0500USD. OPEN 1,215.0500  PREV CLOSE 1,215.0500 - AS OF 05:00 PM EST 11/27/2018 EDT"
# 22:17 Wed 28 Nov 2018. Bloomberg "1,221.3300USD. OPEN 1,221.2500  PREV CLOSE 1,221.2300 - AS OF 05:00 PM EST 11/28/2018 EDT"


/*******************************************************
*
*
* Functions
*
*
*******************************************************/

/**
* Convert csv file of historical data to an array. Sample of the return array;
*   [7916] => 74.84
*   [7917] => 74.99
*   [7918] => 74.16
*/
function import_csv($csv_filename) {
  
  $arr = file($csv_filename);                   // Load the csv file into an array, one array element per line of the file.
  $preamble_removed = array_slice($arr, 1, 1296);     # 1296 = 1 Jan 2014. 1034 = 5 Jan 2015. Data from 1st Jan 2014. Remove the one line of preamble in the csv file from the array
  # echo '<xmp>'.print_r($preamble_removed, true).'</xmp>';
  
  $fn_price = function($line) {                 // Function to return just the price from a line in the csv file.
    $str = str_getcsv($line)[1];
    return (float)str_replace(',', '', $str);
  };
  
  $prices = array_map($fn_price, $preamble_removed);
  return array_reverse($prices);                // Reverse the order of the prices so the array contains prices from oldest to newest.
}




/**
* get the probability of the price reaching the target price in the specified number of days, given the historical data
*
*   @return   float   probability
*/
function get_probability($days, $price, $target, $historical_data) {
  
  $target_change = ($target - $price) / $price;    # percentage change
  
  $counter = 0;
  
  foreach (array_slice($historical_data, 0, -$days) as $key => $value) {
    $future_price = $historical_data[$key + $days];
    $future_change = ($future_price - $value) / $value;    # percentage change
    if ($future_change <= $target_change)
      $counter++;
  }
  
  return $counter / (count($historical_data) - $days);
}




/**
* calculate the buckets
*
*   @return   array   buckets
*/
function get_buckets($days, $price, $targets, $historical_data)
{
  $buckets = [];
  
  # first
  $previous = $targets[0];
  $probability = get_probability($days, $price, $previous, $historical_data);
  $buckets['Less than $'.number_format($previous)] = 100 * round($probability, 4).'%';
  
  # neither first nor last
  foreach(array_slice($targets, 1) as $target) {
    $key = 'Between $'.number_format($previous).' and $'.number_format($target);
    $probability = get_probability($days, $price, $target, $historical_data) - get_probability($days, $price, $previous, $historical_data);
    $buckets[$key] = 100 * round($probability, 4).'%';
    $previous = $target;
  }
  
  # last
  $probability = get_probability($days, $price, $target, $historical_data);
  $buckets['More than $'.number_format($previous)] = 100 * round(1 - $probability, 4).'%';
  
  return $buckets;
}




/*******************************************************
*
*
* Begin
*
*
*******************************************************/


# import historical data from csv file
$historical_data = import_csv($historical_data_filename);

#determine the buckets
$buckets = get_buckets($trading_days_remaining, $price, $targets, $historical_data);

    
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      a.btn
        {-webkit-appearance:button; -moz-appearance:button;appearance:button; padding:0.25em 0.5em; text-decoration:none; margin:0.25em 0}
      td.pad
        {padding:1em; vertical-align:top}
      a.external
        {text-decoration:none; display:inline-block; margin:0em 0.1em 1em}
      a.external:hover
        {text-decoration: underline}
    </style>
  </head>
  <body>
    <h1>What will be the closing spot price for an ounce of gold against the US dollar on 28 December 2018?</h1>
<?php foreach($external_links as $title => $url): ?>
    <div><a class="external" href="<?=$url?>">
      <span><?=$url?></span>
    </a></div>
<?php endforeach ?>
    <h3>Market closes: 5pm EST, 10pm my time.</h3>
    <h3>Using historical data from 1st Jan 2014</h3>
    <h3>Trading days remaining: <?=$trading_days_remaining?> (with Saturdays)</h3>
    <h3>Latest closing spot price: $<?=$price?></h3>
    <h2>Results</h2>
    <xmp><?=print_r($buckets, true)?></xmp>
    <xmp><?php echo print_r($historical_data, true)?></xmp>
  </body>
</html>