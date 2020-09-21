<?php
$arr = [3, 4, 6, 7, 9, 24, 26];

foreach($arr as $num){
 echo "$num \n";
}

echo "whoa! only even numbers: \n";

foreach($arr as $num){
    if ($num % 2 === 0) {
        echo "$num \n";
    }
}