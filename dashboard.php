<?php
    require('db.php');
    include("auth_session.php");
    include("get_urls.php");
    include('send_email.php');
    header("Cache-Control: no-cache, must-revalidate");
    $date = date("Y/m/d H:i:s");
    $con= mysqli_connect("localhost","root","","scrap");
    $query = "SELECT gtag_updatedtime, screenshot_updatedtime, status_updatedtime FROM `urls`";
    $update_time_db = mysqli_query($con, $query);
    $update_time_db = mysqli_fetch_array($update_time_db);
    $update_time_db = $update_time_db[0];

    const APP_URL = 'http://localhost/scrap';
    const SENDER_EMAIL_ADDRESS = 'mikecreative0908@gmail.com';

    function send_email($email,$subject, $message)
    {
        if(isset($email) && isset($subject) && isset($message)){
            // send the email
            sendEmail($email, $subject, nl2br($message));
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard - Client area</title>
    <link rel="stylesheet" href="style.css" />
    <script src="js/jquery.min.js"></script>
    <style>
        body {
            margin:0;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3498db;
            width: 40px;
            height: 40px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }
        .show{
            display:flex;
        }
        .hidden{
            display:none;
        }

        /* Safari */
        @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="form main">
        <div class="header">
            <h3><?php echo $_SESSION['username']; ?></h3>
            <h2>Dashboard</h2>
            <p><a href="logout.php">Logout</a></p>
        </div>
        <div class="content">
            <form id="addurl-form" class="add_url" action="add_url.php" method="post">
                <div>
                    <h2 class="login-title">Add new URL</h2>
                    <?php
                        if($url_count < 10) 
                            echo "<button type='submit' id='add-button'>Add URL</button>";
                        else
                            echo "<button class='disable' type='submit' id='add-button'>Add URL</button>";
                    ?>
                </div>
                <div style="margin-right: -1rem; margin-left: -1rem;">
                    <input type="text" class="login-input" name="address" placeholder="URL" required />
                </div>
            </form>
            <div style="margin-bottom: 8px;">
            <?php
                $index=1;
                $arr_rows = [];
                while($row = mysqli_fetch_array($url_rows))
                {
                    $arr_rows[] = $row;
                }

                while($log = mysqli_fetch_array($logs))
                {
                    if($index <= 5){
                        echo $index.". ".$log['time']." - ".$log['message']. "<br>";
                        $index++;    
                    }
                }
            ?>
            </div>
            <div>
                <table id="url_table">
                    <tr>
                        <th style="width:2%">No</th>
                        <th style="width:48%">URL</th>
                        <th style="width:10%">Domain</th>
                        <th style="width:10%">Gtag</th>
                        <th style="width:29%">Screenshort_URL</th>
                        <th style="width:1%">status</th>
                        <th>Action</th>
                    </tr>
                    <?php
                        error_reporting(E_ERROR | E_PARSE);
                        $arr_url = [];
                        $arr_id = [];
                        $i=1;
                        foreach($arr_rows as $url) {
                            $link = $url['url'];
                            $gtag = $url['gtag'];
                            $arr_url[] = $link;
                            $arr_id[] =  $url['id'];
                            $screen_shot = $url['screenshot'];
                            $status = $url['status'];
                            if(!isset($gtag) || empty($gtag)){
                                $gtag = "No value";
                            }

                            echo "<tr class='row_col'>
                                    <td>". $i ."</td>
                                    <td class='site_link'>
                                        <a href='".$link."' target='_blank'>". $link ."</a>
                                    </td>
                                    <td>" .$url['domain']. "</td>
                                    <td class='gtag'>".$gtag."</td>
                                    <td>
                                        <img onclick=openInNewTab('$screen_shot') style='width:150px' class='img-responsive screen-short img-thumbnail' src='$screen_shot'/>
                                    </td>
                                    <td class='url_status'>$status</td><td>
                                    <button onClick='removeRow(" . $url['id'] . ")' number=" . $url['id'] . ">Delete</button></td>
                                </tr>";
                            $i++;
                        }
                    ?>
                </table>
            </div>
        </div>
    </div>
    <div class="hidden show" id="loader-wrapper" style="width:100%;height:100%;position:fixed;top:0;justify-content: center;align-items: center;background: rgba(0,0,0,0.4);">
        <div class="loader"></div>
    </div>

    <script type="text/javascript">
        const arrayUrl = <?php echo json_encode($arr_url); ?>;
        const ids = <?php echo json_encode($arr_id); ?>;
        $("document").ready(function(e) {
            // Setting
            const requestSetting = {
                method: "GET", // *GET, POST, PUT, DELETE, etc.
                mode: "no-cors", // no-cors, *cors, same-origin
                cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
                credentials: "same-origin", // include, *same-origin, omit
                headers: {
                    "Content-Type": "application/json",
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, PUT, GET, OPTIONS",
                    "Access-Control-Allow-Headers":
                        "Origin, X-Requested-With, Content-Type, Accept, Authorization",
                },
                referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            };

            every24hourScreenshot(true);

            setInterval(() => {
                every5minuteGetGTag();
            }, 5 * 60 * 1000); 

            setInterval(() => {
                every24hourScreenshot();
            }, 24 * 60 * 60 * 1000);        

            // Check URL
            isValidURL(requestSetting);
            getScreenshot();

            checkURLInterval = setInterval(() => {
                isValidURL()
            }, 300000);
        });
        // Add URL function
        $("#add-button").click(function(e) {
            e.preventDefault();
            var form = $("#addurl-form");
            var url = form.attr('action');
            var inputUrl = $("input[name=address]").val();
            if (arrayUrl.includes(inputUrl))
            {
                alert("Exist Url");
                return;
            }
            var id = <?php echo json_encode($_SESSION['id']);?>;
            if(inputUrl != '' && id) {
                $("#loader-wrapper").removeClass("hidden");
                $.ajax({
                    type: "POST",
                    url: url,
                    data: 'id=' + id + '&address='+ inputUrl,
                    success: function(data) {
                        // Ajax call completed successfully\
                        window.location='dashboard.php';
                        $("#loader-wrapper").addClass("hidden");
                    },
                    error: function(data) {
                        // Some error in ajax call
                        alert("Some Error");
                        $("#loader-wrapper").addClass("hidden");
                    }
                });
            }
            else {
                alert("Plz fill the form");
            }
        });
        // Delete URL function
        function removeRow(id) {
            $.ajax({
                type: "POST",
                url: 'remove.php',
                data: 'id=' + id,
                success: function(data) {
                    // Ajax call completed successfully\
                    window.location='dashboard.php';
                },
                error: function(data) {
                    // Some error in ajax call
                    alert("some Error");
                }
            });
        }

        // Get the screenshot of site
        function getScreenshot() {
            $(".url_status").each(function() {
                var link = $(this).siblings('.site_link').children().attr('href');
                // $.ajax({
                //     xhrFields: { cors: false, withCredentials: true },
                //     crossDomain: true,
                //     headers: {
                //             "Access-Control-Allow-Origin": "*",
                //             "Access-Control-Allow-Methods": "GET",
                //             "Access-Control-Allow-Headers":
                //             "Origin, X-Requested-With, Content-Type, Accept",
                //     },
                //     url: 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?url=' + link + '&screenshot=true',
                //     context: 'this',
                //     type: 'GET',
                //     dataType: 'json',
                //     timeout: 60000,
                //     success: function(result) {
                //         var imgData = result.screenshot.data.replace(/_/g, '/').replace(/-/g, '+');
                //         $("img").attr('src', 'data:image/jpeg;base64,' + imgData);
                //         $("#msg").html('');
                //     },
                //     error:function(e) {
                //         $("#msg").html("Error to fetch image preview. Please enter full url (eg: http://www.iamrohit.in)");
                //     }
                // });
                // $('#img').attr('src','img.php?url='+encodeURIComponent(link));
            }); 
        }
        async function isValidURL(setting) {
            $(".url_status").each(function() {
                link = $(this).siblings('.site_link').children().attr('href');
                // try {
                //     fetch(link, setting)
                //     .then((response) => {
                //         console.log(response);
                //         $(this).text("ON");
                //     })
                //     .catch((error) => {
                //         $(this).text("OFF");
                //     });
                // } catch (error) {
                //     console.log(error);
                // }
                try {
                    $.ajax({
                        url: link,
                        type: "GET",
                        cache: false,
                        async: true,
                        crossDomain: true,
                        xhrFields: { cors: false, withCredentials: true },
                        dataType: "jsonp",
                        headers: {
                            "Access-Control-Allow-Origin": "*",
                            "Access-Control-Allow-Methods": "GET",
                            "Access-Control-Allow-Headers":
                            "Origin, X-Requested-With, Content-Type, Accept",
                        },
                        success: function (response) {
                            console.log(link + " exist");
                        },
                        error: function (error) {
                            console.log(error.status);
                            if(error.status == 404) {
                                console.log('aaa');
                            }
                        },
                    });
                } catch (error) {
                    console.log(error);
                }
            });
        }
        
        function openInNewTab(url) {
            var newTabWindow = window.open('about:blank');

            setTimeout(function(){
                newTabWindow.document.body.appendChild(newTabWindow.document.createElement('iframe'))
                    .src = url;
            }, 0);
        }

        function isJsonString(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }

        function every24hourScreenshot(isNotSendEmail) {
            try {
                const arrElement = document.getElementsByClassName("screen-short");
                const arrStatus = document.getElementsByClassName("url_status");
                const email = "<?php echo $_SESSION['email'] ?>"
                const user_id = "<?php echo $_SESSION['id'] ?>"
                const arrPromise = 
                arrayUrl.map((itemUrl,index) => {
                    return fetch("api/?action=getScreenShort&url=" + itemUrl + "&id=" + ids[index] + "&email=" + email + "&isNotSendEmail=" + isNotSendEmail + "&user_id=" + user_id )
                });
                Promise.all(arrPromise)
                .then(results => {
                    return Promise.all(results.map(res => res.text()))} )
                .then(arrResponse => {
                    arrResponse.map((response, index) => {
                        if(isJsonString(response)){
                            response = JSON.parse(response);
                            if(response.data.screenshort){
                                arrElement[index].src = response.data.screenshort;
                                arrElement[index].onclick = function(){openInNewTab(response.data.screenshort)};
                            }
                            if(response.data.status){
                                arrStatus[index].innerHTML = response.data.status;
                            }
                        }
                })})
            } catch (error) {
                console.log(error);
            }
        }

        function every5minuteGetGTag() {
            try {
                const arrElement = document.getElementsByClassName("gtag");
                const arrStatus = document.getElementsByClassName("url_status");
                const email = "<?php echo $_SESSION['email'] ?>"
                const user_id = "<?php echo $_SESSION['id'] ?>"
                const arrPromise = 
                arrayUrl.map((itemUrl,index) => {
                    return fetch("api/?action=getGtagSend&url=" + itemUrl + "&id=" + ids[index] + "&email=" + email + "&user_id=" + user_id )
                });
                Promise.all(arrPromise).then(results => Promise.all(results.map(res => res.text())) )
                .then(arrResponse => {
                    arrResponse.map((response, index) => {
                        if(isJsonString(response)){
                            response = JSON.parse(response)
                            arrElement[index].innerHTML = response.data.gtag;
                            if(response.data.status){
                                arrStatus[index].innerHTML = response.data.status;
                            }
                        }
                })})
            } catch (error) {
                console.log(error);
            }
        }
    </script>
</body>
</html>
