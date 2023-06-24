<?php 
    require('db.php');
    
    extract($_POST);

    function getDomain($url){
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }

    function getScreenShort($url){
        $screen_shot_json_data = file_get_contents("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&screenshot=true"); 
        $screen_shot_result = json_decode($screen_shot_json_data, true);
        $screen_shot = $screen_shot_result['lighthouseResult']['audits']['final-screenshot']['details']['data'];
       
        return $screen_shot;
    }

    function getGtag($url){
        $html = file_get_contents($url);
        $find = "gtag('event','conversion',{'send_to':";
        $find2 = "https://www.googleadservices.com/pagead/conversion";
        $html = str_replace(" ", "", $html);
        $html = str_replace("\"", "'", $html);
        $index = strpos($html,$find);
        $index2 = strpos($html,$find2);
        if($index || $index2){
            $newStr = str_replace($find,"",substr($html,$index));
            $sendToValue = explode("'",$newStr)[1];

            if(true){
                $sendToValue = "Exist link conversion";
            }
            
            return $sendToValue;
        }else {
            return '';
        }
    }

    function getStatus($url){
        $headers = @get_headers($url);  
        // Use condition to check the existence of URL
        if(strpos( $headers[0], '404') || strpos( $headers[0], '500') || !$headers[0]) {
            $status = "OFF";
        }
        else {
            $status = "ON";
        }
        return $status;
    }
    $date = date("Y/m/d H:i:s");
    $status = getStatus($address);
    $screen_shot = "";
    $gtag = "";
    if($status == "ON"){
        $screen_shot = getScreenShort($address);
        $gtag = getGtag($address);
    }

    $domain = getDomain($address);
    $query    = "INSERT into urls (url, domain, user_id, status, screenshot, gtag, gtag_updatedtime, screenshot_updatedtime, status_updatedtime) VALUES ('$address', '$domain', '$id', '$status', '$screen_shot', '$gtag', '$date', '$date', '$date')";
    $result   = mysqli_query($con, $query);

    if($result){
        header("Location: dashboard.php");
        exit();
    }else{
        echo "<pre>";
        echo "An Error occured.<br>";
        echo "Error: ".$con->error."<br>";
        echo "SQL: ".$sql."<br>";
        echo "</pre>";
    }
    
    $con->close();
?>