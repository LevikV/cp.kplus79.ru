<?php
//mail("alex@kplus79.ru", "Test from my php", "This is body of mail");
$to      = 'alex@kplus79.ru';
$subject = 'the subject';
$message = 'hello';
$headers = array(
    'From' => 'noreply@kplus79.ru',
    'Reply-To' => 'info@kplus79.ru',
    'X-Mailer' => 'PHP/' . phpversion()
);

mail($to, $subject, $message, $headers);
?>
