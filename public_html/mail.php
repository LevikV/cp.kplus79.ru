<?php
//mail("alex@kplus79.ru", "Test from my php", "This is body of mail");
$to      = 'alex@kplus79.ru';
$subject = 'the subject';
$message = 'hello\r\ntest\r\nwow';
$headers = 'From: noreply@kplus79.ru' . "\r\n" .
    'Reply-To: info@kplus79.ru' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
