<?php 

    define('APP_ID', '730774217279573');
    define('APP_SECRET', '087985c1445c3e2cc1320d39fcc01029');
    
    /* for localhost */ 
    define('REDIRECT_URL', 'http://localhost:8080/rtCamp/');
    define('DOMAIN',"http://localhost:8080/rtCamp/");
    /* end*/
    
    /* for live application*/ 
    define('REDIRECT_URL', 'https://zerothcode.com/rajukhunt/');
    define('DOMAIN',"https://zerothcode.com/rajukhunt/");
    /* end */

    require_once './lib/Facebook/autoload.php';
?>