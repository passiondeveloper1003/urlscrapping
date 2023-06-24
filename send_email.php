
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

        function sendEmail($email, $subject, $message)
        {
        // create object of PHPMailer class with boolean parameter which sets/unsets exception.
        $mail = new PHPMailer(true);
        try {

            $mail->isSMTP(); // using SMTP protocol     
            
            $mail->CharSet  = "utf-8";

            $mail->Host = 'smtp.gmail.com'; // SMTP host as gmail 

            $mail->SMTPAuth = true;  // enable smtp authentication                             

            $mail->Username = 'transondinh2000@gmail.com';  // sender gmail host              

            $mail->Password = 'hzzllokjjdnreyjf'; // sender gmail host password     

            // $mail->Username = 'mikecreative0908@gmail.com';  // sender gmail host              

            // $mail->Password = 'iukemqztprpfrmhp'; // sender gmail host password   
                     

            $mail->SMTPSecure = 'ssl';  // for encrypted connection                           

            $mail->Port = 465;   // port for SMTP     

            $mail->setFrom('transondinh2000@gmail.com', "Sender"); // sender's email and name

            $mail->addAddress($email, $email);

            $mail->isHTML(true); 

            $mail->Subject =  $subject;

            $mail->Body    = $message;

            $mail->send();

        } catch (Exception $e) { // handle error.

        }

        }

?>