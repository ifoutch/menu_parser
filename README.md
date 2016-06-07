# menu_parser

## Original instructions

Write a program which will processes the data listed below. For purposes of this exercise hardcoding the values is ok. The first variable is the target price and the following data values are menu items you could buy. The program should then find an order of those dishes that has a total of exactly the target price. If there is no solution, then the program should print that there is no combination of dishes that is equal to the target price. It can be run with different data files, so provide instructions on how to run the program with the correct file. Use PHP to solve this puzzle.
Here are some sample data values:

Target price $15.05

mixed fruit,$2.15
french fries,$2.75
side salad,$3.35
hot wings,$3.55
mozzarella sticks,$4.20
sampler plate,$5.80

Deliverable
- Your code must run without errors or warnings
- Your variables and functions must be descriptive
- Is your code well structured and organized

## Usage

  Script requires two arguments specifying the target spending limit and the file containing menu items with prices.
  The file containing the menu items and prices must be comma delimited, preferrably with just two columns for each item (row).
  The first row should be the "item description" and the second the price for the item.
  
  Example usage:
  
  php -f ./menu_parser.php 15.05 ./menu

  If php is not in your path you can try to locate it using `which php' or `locate bin/php'
  
## Testing

To see script in action using different values, try the following:

for x in `seq 2 20`; do for y in `seq 5 5 95`; do php -f ./menu_parser.php $x.$y menu; done; done | egrep 'Using|match your target'
