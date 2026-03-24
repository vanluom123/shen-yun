<?php
$phone = "+84971875793";
if (preg_match('/^(\+(?:84|1|82|81|65))(\d+)$/', $phone, $m)) {
    echo "Country: " . $m[1] . "\n";
    echo "Number: " . $m[2] . "\n";
}
