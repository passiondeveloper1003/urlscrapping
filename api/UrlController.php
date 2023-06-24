<?php  
    error_reporting(E_ERROR | E_PARSE);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   
    include('../send_email.php');
    include("../get_urls.php");
    class UrlController
    {
        protected function getQueryStringParams()
        {
            parse_str($_SERVER['QUERY_STRING'], $query);
            return $query;
        }

        protected function sendOutput($data, $httpHeaders=array())
        {
            header_remove('Set-Cookie');
    
            if (is_array($httpHeaders) && count($httpHeaders)) {
                foreach ($httpHeaders as $httpHeader) {
                    header($httpHeader);
                }
            }
    
            echo json_encode(array("data" => $data));
            exit;
        }
        
        public function getGtagSend() {
            $strErrorDesc = '';
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $arrQueryStringParams = $this->getQueryStringParams();
            $url = $arrQueryStringParams["url"];
            $email = $arrQueryStringParams["email"];
            $id = $arrQueryStringParams["id"];
            $user_id = $arrQueryStringParams["user_id"];
            $conn = mysqli_connect("localhost","root","","scrap");

            if (strtoupper($requestMethod) == 'GET') {
                try {
                    $html = file_get_contents($url);
                    $find = "gtag('event','conversion',{'send_to':";
                    $find2 = "https://www.googleadservices.com/pagead/conversion";
                    $html = str_replace(" ", "", $html);
                    $html = str_replace("\"", "'", $html);
                    $index = strpos($html,$find);
                    $index2 = strpos($html,$find2);
                    $date = date("Y/m/d H:i:s");
                    // Use condition to check the existence of URL
                    $headers = @get_headers($url);  
                    $changeStatus = false;
                    $query = "SELECT status, gtag FROM `urls` WHERE id='$id'";
                    $url_rows = mysqli_query($conn, $query);
                    $rowG = mysqli_fetch_array($url_rows);
                    $log = "Google ads conversion event not found for URL #$id. Email sent.";
                    if(strpos( $headers[0], '404') || strpos( $headers[0], '500') || !$headers[0]) {
                        $status = "OFF";
                        $changeStatus = strcmp($rowG[0], "OFF") !== 0;
                        $log = "404 error for URL #$id. Email sent.";
                    }
                    else {
                        $status = "ON";
                        $changeStatus = strcmp($rowG[0], "ON") !== 0;
                    }

                    if($index || $index2){
                        $newStr = str_replace($find,"",substr($html,$index));
                        $sendToValue = explode("'",$newStr)[1];

                        if(!empty($sendToValue) && $index2){
                            $sendToValue = "Exist link conversion";
                        }  

                        if($changeStatus === true){
                            $subject = 'Changing STATUS';
                            $message =  '<<<MESSAGE
                            Hi,
                            Notice you about changing STATUS of site '.$url.'
                            MESSAGE';
                            sendEmail($email, $subject, $message);
                        }

                        if($sendToValue !== $rowG['gtag']){
                            $subject = 'Changing GTAG';
                            $message =  '<<<MESSAGE
                            Hi,
                            Notice you about changing date and changing GTAG when I check your site '.$url.'
                            MESSAGE';
                            sendEmail($email, $subject, $message);    
                        }

                    
                        mysqli_query($conn, "UPDATE urls SET gtag='$sendToValue',status='$status', gtag_updatedtime='$date',status_updatedtime='$date', WHERE id ='$id'");

                        $responseData = (object) array('gtag' => $sendToValue, 'date' => $date, 'changeStatus' => $changeStatus, 'status' => $status);
                    } else {
                        $responseData = (object) array('gtag' => 'Not value', 'date' => $date, 'changeStatus' => $changeStatus, 'status' => $status);
                    }
                    mysqli_query($conn, "INSERT into log (message, time,user_id) VALUES ('$log', '$date','$user_id')");
                }
                catch (Error $e){
                    $responseData = (object) array('gtag' => 'Not value');
                    $this->sendOutput($responseData, 
                    array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error'));
                }
            } else {
                $this->sendOutput('Method not supported', 
                array('Content-Type: application/json', 'HTTP/1.1 422 Unprocessable Entity'));
            }
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            mysqli_close($conn);
        }

        public function getScreenShort() {
            $strErrorDesc = '';
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $arrQueryStringParams = $this->getQueryStringParams();
            $url = $arrQueryStringParams["url"];
            $email = $arrQueryStringParams["email"];
            $id = $arrQueryStringParams["id"];
            $user_id = $arrQueryStringParams["user_id"];
            $isNotSendEmail = $arrQueryStringParams["isNotSendEmail"];
            $conn = mysqli_connect("localhost","root","","scrap");
            $date = date("Y/m/d H:i:s");

            if (strtoupper($requestMethod) == 'GET') {
                try {
                    $screen_shot_json_data = file_get_contents("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&screenshot=true"); 
                    $screen_shot_result = json_decode($screen_shot_json_data, true);
                    $screen_shot = $screen_shot_result['lighthouseResult']['audits']['final-screenshot']['details']['data'];
                    $headers = @get_headers($url);  
                    $log = "Homepage changed for URL #$id. Email sent.";
                    $changeStatus = false;
                    $query = "SELECT status,screenshot  FROM `urls` WHERE id='$id'";
                    $url_rows = mysqli_query($conn, $query);
                    $rowScreen = mysqli_fetch_array($url_rows);
                    // Use condition to check the existence of URL
                    if(strpos( $headers[0], '404') || strpos( $headers[0], '500') || !$headers[0]) {
                        $status = "OFF";    
                        $log = "404 error for URL #$id. Email sent.";
                        $changeStatus = strcmp($rowScreen[0], "OFF") !== 0;
                        $subject = 'Changing STATUS';
                    } else {
                        $status = "ON";
                        $changeStatus = strcmp($rowScreen[0], "ON") !== 0;
                    }

                    if($changeStatus === true){
                        $subject = 'Changing STATUS';
                        $message =  '<<<MESSAGE
                        Hi,
                        Notice you about changing STATUS of site '.$url.'
                        MESSAGE';
                        sendEmail($email, $subject, $message);
                    }
    
                    if($screen_shot !== $rowScreen['screenshot'] && $isNotSendEmail === "undefined" && strcmp($status, "ON") === 0){
                        $subject = 'Changing SCREENSHOT';
                        $message =  '<<<MESSAGE
                        Hi,
                        Notice you about changing date and changing SCREENSHOT when I check your site '.$url.'
                        MESSAGE';
                        sendEmail($email, $subject, $message);
                        mysqli_query($conn, "INSERT into log (message, time,user_id) VALUES ('$log', '$date', '$user_id')");
                    }    

                    $responseData = (object) array('screenshort' => $screen_shot, 'date' => $date, 'changeStatus' => $changeStatus, 'status' => $status);
                    mysqli_query($conn, "UPDATE urls SET screenshot='$screen_shot', status='$status', screenshot_updatedtime='$date',status_updatedtime='$date' WHERE id = $id");
                }
                catch (Error $e){
                    $status = "OFF";    
                    mysqli_query($conn, "UPDATE urls SET status='$status' WHERE id = $id");
                    $this->sendOutput('Something went wrong! Please contact support.', 
                    array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error'));
                }
            } else {
                $this->sendOutput('Method not supported', 
                array('Content-Type: application/json', 'HTTP/1.1 422 Unprocessable Entity'));
            }

                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            mysqli_close($conn);
        }
    }
?>