<?php

setlocale( LC_MONETARY, 'en_US.UTF-8' );
unset( $spending_limit, $menu_input_file );

// Set basic usage message.

$usage_string = <<<EOF

  Script requires two arguments specifying the target spending limit and the file containing menu items with prices.
  The file containing the menu items and prices must be comma delimited, preferrably with just two columns for each item (row).
  The first row should be the "item description" and the second the price for the item.
  Example usage: php -f $argv[0] 15.05 /path/to/menu

  If php is not in your path you can try to locate it using `which php' or `locate bin/php'

EOF;

// Check that we were passed proper number of arguments.

if ( $argc != 3 ) {
    print "\n" . "ERROR: Script requires exactly two arguments." . "\n"
      . $usage_string . "\n";
    exit( 1 );
}

// Check for valid currency as either of the argumets.

if ( preg_match( '/^[$]?((\d+)?|(\d{1,3}(\,\d{3})+))?(\.\d{2})?$/', $argv[1] ) ) {
    $spending_limit = money_format( '%.2n', $argv[1] );
} elseif ( preg_match( '/^[$]?((\d+)?|(\d{1,3}(\,\d{3})+))?(\.\d{2})?$/', $argv[2] ) ) {
    $spending_limit = money_format( '%.2n', $argv[2] );
}

// Check for a readable input file with the menu items.

if ( is_readable( $argv[1] )) {
    $menu_input_file = $argv[1];
} elseif ( is_readable($argv[2] )) {
    $menu_input_file = $argv[2];
}

// Validate that the currency and menu file tests succeeded.

if ( !isset( $spending_limit )) {
    print "\n" . "ERROR: Please provide a target dollar value." . "\n"
      . $usage_string . "\n";
    exit( 1 );
} elseif ( !isset( $menu_input_file )) {
    print "\n" . "ERROR: Please provide an input file with the menu items and prices." . "\n"
      . $usage_string . "\n";
    exit( 1 );
}

print "\n" . "Using spending target of $spending_limit and prices from file $menu_input_file" . "\n\n";

// Parse input file, populatng an array with the prices and warning if the file line count
// and array element count do not match. Currently only supports price data formated as
// $n.nn or n.nn

$handle = fopen( "$menu_input_file", "r" );
$row = 0;
while (( $data = fgetcsv( $handle, 1000, "," )) !== FALSE) {
    $row++;
    $price = str_replace( array( ' ','$', ',', '.' ), '', $data[1] );
    if ( ctype_digit( $price )) {
        $price_list[] = $price;
    }
}
fclose($handle);

// Transform spending limit into integer without any formating

$target = (int)str_replace( array( '$', ',', '.' ), '', $spending_limit );

// Check menu list line count versus lines with usable values

$menu_item_count = sizeof( $price_list );

if ( $menu_item_count == 0 ) {
    print "\n" . "ERROR: Input file did not contain any valid prices." . "\n\n";
    exit( 1 );
} elseif ( $menu_item_count < $row ) {
    print "\n"
        . "WARNING: Input file contained $row lines but lines with valid prices is $menu_item_count."
        . "\n\n"
        . 'Type "yes" to continue: ';
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    if(trim($line) != 'yes'){
        print "ABORTING!\n";
        exit;
    }
    fclose($handle);
}

// Make sure the target value is at least equal to the least expensive menu item.

sort( $price_list, SORT_NUMERIC );

if ( $target < $price_list[0] ) {
    print "\n" . "Sorry but your spending target of $spending_limit is less than the least expensive item in menu."
      . "\n" . "You'll need to scrounge up more money if you wish to eat." . "\n\n";
    exit;
}

// Do a quick check to make sure spending target isn't equal to or greater than total of entire menu.

$menu_total = array_sum( $price_list );

if ( $menu_total < $target ) {
    print "\n" . "Spending target is more than total of all items in menu." . "\n\n";
} elseif ( $menu_total == $target ) {
    print "\n" . "Spending target matches total of all menu items." . "\n\n";
}

// This is where the magic happens ;>]
// The subset_sums function returns an array with a string of matching values
// for each combination of values that match the spending target.
//
// This uses substraction of values from spending $target with
// recurssion and backtracking to iterate through all possible combinations

function subset_sums( $price_list, $target, $i = 0 ) {
    $results = array();
    while( $i < count( $price_list )) {
        $value = $price_list[$i];
        if( $value == $target ) {
            $results[] = $value;
        } elseif( $value < $target ) {
            foreach( subset_sums( $price_list, $target - $value, $i + 1 ) as $s )
                $results[] = "$value $s";
        }
        $i++;
    }
    return $results;
}

$solutions = subset_sums( $price_list, $target );

// Check to see if there are any matches or not and return result.

if ( sizeof( $solutions ) > 0) {
    foreach( $solutions as $subset ) {
        print "The following combination of item prices match your target:" . "\t";
        foreach( explode( ' ', $subset ) as $item ) {
             print number_format( $item/100, 2, '.', ' ' ) . ' ';
        }
    print "\n";
    }
} else {
    print "\n" . "No matches for spending limit found." . "\n\n";
}

?>
