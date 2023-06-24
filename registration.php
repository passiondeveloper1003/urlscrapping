<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Registration</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
<?php
    require('db.php');
    include('send_email.php');

    const APP_URL = 'http://localhost/scrap';
    const SENDER_EMAIL_ADDRESS = 'mikecreative0908@gmail.com';

    function send_activation_email(string $email, string $activation_code): void
    {
        // create the activation link
        $activation_link = APP_URL . "/activate.php?email=$email&activation_code=$activation_code";

        // set email subject & body
        $subject = 'Please activate your account';
        $message = '<<<MESSAGE
                Hi,
                Please click the following link to activate your account:
                '.$activation_link.'
                MESSAGE';

        // email header
        $header = "From:" . SENDER_EMAIL_ADDRESS;

        // send the email
        sendEmail($email, $subject, nl2br($message), $header);
    }

    // When form submitted, insert values into the database.

    if (isset($_POST['username'])) {
        // removes backslashes
        $username = stripslashes($_REQUEST['username']);
        //escapes special characters in a string
        $username = mysqli_real_escape_string($con, $username);
        $email    = stripslashes($_REQUEST['email']);
        $email    = mysqli_real_escape_string($con, $email);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con, $password);
        $create_datetime = date("Y-m-d H:i:s");

        // Random activation code
        $code = rand();

        $query = "SELECT code, active FROM users WHERE email='" .$email."'";

        $result = mysqli_query($con, $query);

        // If the user is exist in the DB
        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                if($row['active'] == '0') {
                    echo '<script type="text/javascript">'; 
                    echo 'alert("Exist email. Please verify your email address.");'; 
                    echo 'window.location.href = "registration.php";';
                    echo '</script>';
                } else {
                    echo "<div class='form'>
                        <h3>You've already register Go to the <a href='login.php'>Login</a></h3><br/>
                        </div>";
                }
            }
        } 
        // If the user is not exist in the DB
        else {
            $query    = "INSERT into `users` (username, password, email, create_datetime, code)
                     VALUES ('$username', '" . md5($password) . "', '$email', '$create_datetime', '$code')";
            $result   = mysqli_query($con, $query);
            send_activation_email($email, $code);
            echo "<div class='form'>
                    <h3>Please verify your email address.</h3><br/>
                  </div>";
        }
    } else {
?>
    <form class="form" action="" method="post">
        <h1 class="login-title">Registration</h1>
        <input type="text" class="login-input" name="username" placeholder="Username" required />
        <input type="text" class="login-input" name="email" placeholder="Email Adress">
        <input type="password" class="login-input" name="password" placeholder="Password">
        <input type="submit" name="submit" value="Register" class="login-button">
        <p class="link"><a href="login.php">Click to Login</a></p>
    </form>
<?php
    }
?>
</body>
</html>

