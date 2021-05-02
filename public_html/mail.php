<?php
//mail("alex@kplus79.ru", "Test from my php", "This is body of mail");
$to      = "<alex@kplus79.ru>, ";
$to      .= "<info@kplus79.ru>";
$subject = 'the subject';
$message = "hello\r\n";
$message .= "test\r\n";
$headers = 'From: noreply@kplus79.ru' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
