<?php
    require('db.php');
    $email = $_GET['email'];
    $code = $_GET['activation_code'];
    $query = "UPDATE users SET active = '1' WHERE email = '" . $email . "' and code = $code" ;

    $result = mysqli_query($con, $query) or die(mysql_error());
        
    if(! $result ) {
        die('Could not update data: ' . mysql_error());
    } 
    // if user exists and activate the user successfully
    else {
        echo "<div class='form'>
                <h3>You account has been activated successfully. Please login <a href='login.php'>here</a></h3><br/>
            </div>";

    }
?>