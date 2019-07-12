<?php

$to = "cameronroweau@gmail.com";
$subject = "My subject";
$txt = "Hello world!";
$headers = 'From:cameronroweau@gmail.com' . "\r\n";

if (!mail($to, $subject, $txt, $headers)) {
    echo "mail failed";
} else {
    echo "success";
}
