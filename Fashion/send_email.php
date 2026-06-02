<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $to = "pjilagan1014@gmail.com"; // your receiving email
    $subject = "New Contact Form Message";

    $body = "
    You have received a new message:

    Name: $name
    Email: $email

    Message:
    $message
    ";

    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Message sent successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Failed to send message. Try again.'); window.history.back();</script>";
    }

} else {
    header("Location: index.php");
}
?>