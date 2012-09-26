<?php

function random($min, $max) {
  // md5() generates a hexadecimal number, so we must convert it into base 10
  $rand = base_convert( md5( microtime() ), 16, 10);
  // the modulus operator doesn't work with great numbers, so we have to cut the number
  $rand = substr($rand, 10, 6);
  $diff = $max - $min + 1;
  return ($rand % $diff) + $min;
}

// test:
/*$arr = array();
for ($i = 0; $i < 1000; $i++) {
  $arr[random(1, 10)]++;
}*/

for ($i = 1; $i <= 10; $i++)
  echo '<br />' . $i . ': ' . random(1, 9) . "\n";

?>