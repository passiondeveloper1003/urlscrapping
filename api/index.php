<?php
    require "./UrlController.php";

    $uri = parse_url($_SERVER['REQUEST_URI']);
    $queryParam = [];
    parse_str($uri['query'], $queryParam);
    
    $objFeedController = new UrlController();
    $strMethodName = $queryParam['action'];
    $objFeedController->{$strMethodName}();
?>