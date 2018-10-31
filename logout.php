<?php 

	session_start();
	require_once('fb-config.php');
	
	if(isset($_SESSION['facebook_access_token']))
		unset($_SESSION['facebook_access_token']);

	if(isset($_COOKIE["credentials"]))
	{
		unset($_COOKIE['credentials']);
		setcookie("credentials", "", time() - 3600);
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<script> 

	function delete_cookie(name) {
    document.cookie=name+'=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	};

	 function getCookie(cname) {
    var name = cname + '=';
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');

    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
  }
	delete_cookie('credentials');
	window.location='".DOMAIN."'; 
	</script>";


?>