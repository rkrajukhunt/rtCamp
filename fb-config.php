<?php 

    define('APP_ID', '1904384126283654');
    define('APP_SECRET', '9a68132d510c01810d97285086ec5702');
    
    /* for localhost */ 
//        define('REDIRECT_URL', 'http://localhost:8080/rtCamp/');
//        define('DOMAIN',"http://localhost:8080/rtCamp/");
    /* end*/
    
    /* for live application*/ 
    define('REDIRECT_URL', 'https://rajukhuntrtcamp.herokuapp.com/index.php');
    define('DOMAIN',"https://rajukhuntrtcamp.herokuapp.com/index.php");
    /* end */

    require_once './lib/Facebook/autoload.php';
?>