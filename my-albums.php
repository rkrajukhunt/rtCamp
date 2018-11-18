<?php
  session_start();
  require_once('fb-config.php');

  if(!isset($_SESSION['facebook_access_token']))
        header('location:'.DOMAIN);

  $fb = new Facebook\Facebook([
    'app_id' => APP_ID, // Replace {app-id} with your app id
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.2',
    'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : APP_SECRET
    ]);

  if(isset($_GET['download'])){
    echo '<iframe src="download.php?link='.$_GET['download'].'" id="ifame" style="display : none"></iframe>';
  }
  
  try
  { 
    $accessToken= $_SESSION['facebook_access_token'];
    $response= $fb->get('/me?fields=albums',$accessToken);
    $user = $response->getGraphUser();

}
  catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo $e->getMessage();
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
  }

?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <title>My Facebook Album</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="lib/css/bootstrap.min.css">
    <link rel="stylesheet" href="lib/css/Mycss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="lib/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  </head>
  <style type="text/css">
  body {
      background-color:dark;
  }
  .items {
    margin: 2%;
    box-shadow:5px 5px 8px darkgrey;
    overflow: hidden;
  }
  .items img {
    max-width: 100%;
    -moz-transition: all 0.3s;
    -webkit-transition: all 0.3s;
    transition: all 0.3s;
  }
  .items:hover img {
    -moz-transform: scale(1.1);
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }
  #btngroup {
    position: fixed;
    bottom: 10px;
    right: 10px;
    z-index: 99;
    font-size: 18px;
    border: none;
    outline: none;
  }
  #myCarousel{
    height: 100%;
    width:100%;
  }
 .carousel-inner img {
    margin: auto;
} 
 .card-img-top{
    height: 350px;
 }
 .modal-full {
    min-width: 100%;
    margin: 0;
}

.modal-full .modal-content {
    min-height: 100vh;
}
  </style>
  <body>
  
  <nav class="navbar navbar-expand-sm bg-primary navbar-dark">
  <!-- Brand/logo -->
  <a class="navbar-brand" href="#">
    <img src="images/rtcamp.png" alt="logo" style="width:40px;">
  </a>
  
  <!-- Links -->
  <ul class="navbar-nav justify-content-center">
    <li class="nav-item">
      <a class="nav-link active">My Albums</a>
    </li>
  </ul>
</nav>
<div class="album py-5 bg-light">
        <div class="container">
          <div class="row">
            <?php

              for($i=0;$i<count($user['albums']);$i++){ 
                $cp = $fb->get('/'.$user['albums'][$i]['id'].'?fields=cover_photo',$accessToken);
                $gn=$cp->getGraphNode();

                if(isset($gn['cover_photo']['id'])){
                  $ree = $fb->get(
                    '/'. $gn['cover_photo']['id'].'?fields=images',
                    $accessToken
                  );
                  $graphNode = $ree->getGraphNode();
                  $coverPhoto = $graphNode['images'][0];
                  
                  ?>
                  <div class="col-md-4">
                    <div class="card mb-4 box-shadow items" >
                      <img class="card-img-top" src="<?php echo $coverPhoto['source'] ?>" alt="Card image cap" onclick="displaySlider('<?php echo $user['albums'][$i]['id']  ?>')">
                      <div class="card-body">
                        <p class="card-text" style="font-size: 18px;">
                        <input type="checkbox" name="chk" onClick="onoff()" value="<?php echo $user['albums'][$i]['id'].'_'.$user['albums'][$i]['name']  ?>">&nbsp;
                        <?php echo $user['albums'][$i]['name'] ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                          <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadAlbum('<?php echo $user['albums'][$i]['id'].'_'.$user['albums'][$i]['name']  ?>')"></span>Download</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="moveAlbum('<?php echo $user['albums'][$i]['id'].'_'.$user['albums'][$i]['name']  ?>')">Move to Drive</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php  
                }
              }
            ?>
          </div>
        </div>
      </div>
      <div id="btngroup">
        <button type="button" class="btn btn-primary" id="download_seleted" onclick="downloadSelectedAlbums()">Download Selected</button>
        <button  class="btn btn-primary" onclick="downloadAllAlbums()">Download All</button>
        <button  class="btn btn-primary" onclick="moveSelectedAlbums()" id="move_selected">Move Selected</button>
        <button  class="btn btn-primary" onclick="moveAllAlbums()">Move All</button>
        <button  class="btn btn-danger" onclick="logout()">Logout</button>
      </div>

      <center class="h-100 row align-items-center">
        <div class="modal fade " id="myModal"> 
          <div class="modal-dialog" style=" padding-top: 180px; width: 100%;">
            <div class="modal-content">
            <i class="fa fa-spinner fa-spin align-items-center" style="font-size:100px;"></i>
            <h2 id="msg"></h2>
            <div class="container">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%" id="pbar"></div>
                </div>
                <div></div>
            </div>
          </div>
          </div>
        </div>
      </center>

  <div class="modal" id="mySlider" style="padding-bottom: 15px; padding-right: 15px;padding-left: 15px;">
    <div class="modal-dialog modal-full" role="document">
        <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
            <h4 class="modal-title">My Album</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          
          <!-- Modal body -->
          <div class="modal-body">
            <div id="demo" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner" id="img-container">
    
                </div>
                <a class="carousel-control-prev" href="#demo" data-slide="prev">
                  <span class="carousel-control-prev-icon"></span>
                </a>
                <a class="carousel-control-next" href="#demo" data-slide="next">
                  <span class="carousel-control-next-icon"></span>
                </a>
            </div>
        </div>
        
          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          </div>
          
    </div>
</div>
</body>
</html>
<script type="text/javascript">
  disableAllButton();
  $(document).ready(function() {
    var f=document.getElementById("ifame");
    if(f!=null)
    {
      window.location="<?php echo DOMAIN ?>";
    }
});
  function logout(){
    window.location="logout.php";
  }
  function onoff(){
    var selected_chk=document.querySelectorAll('input[name=chk]:checked');
    if(selected_chk.length>0)
      enableAllButton();
    else
      disableAllButton();
  }
  function enableAllButton(){
    document.getElementById("download_seleted").disabled = false; 
    document.getElementById("move_selected").disabled = false; 
  }
  function disableAllButton(){
    document.getElementById("download_seleted").disabled = true; 
    document.getElementById("move_selected").disabled = true; 
  }
  var imageCount=0;
  var a=0;
  var downloadcount=0;

  function progressBarInit(){
    $("#pbar").css("width","0%");
  }
  function run(){
      var increase=100/imageCount;
      a=a+increase;
      $("#pbar").css("width",a+"%");
      ++downloadcount;
  }
  function downloadAlbum(id){
     progressBarInit();
     $('#myModal').modal('toggle');
     albumid=id;
     getCount(id);
     var xx = new XMLHttpRequest();
        xx.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) 
          {
             var x = new XMLHttpRequest();
             x.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) 
            {     
              var arr=this.responseText.split(',');
              for(var i=0;i<arr.length-1;i++)
              {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) 
                {      
                  run();
                  if(downloadcount==imageCount)
                  {
                    $('#myModal').modal('toggle');
                    downloadZip();
                  }
                }
                };
              xhttp.open("GET", "save-image.php?imageid="+arr[i], true);
              xhttp.send();
            }
         }
        };
        x.open("GET", "get-imageids.php?albumid="+albumid, true);
        x.send(); 
          }     
       }
      xx.open("GET", "delete-dir.php", true);
      xx.send();
     
  }
  function downloadZip(){
    var x = new XMLHttpRequest();
    x.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) 
     {     
         window.location="<?php echo DOMAIN ?>my-albums.php?download="+this.responseText;
      }
      };
      x.open("GET", "create-zip.php", true);
      x.send();
  }
  function getCount(id){
     var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) 
      {      
        imageCount=parseInt(this.responseText);
      }
      };
    xhttp.open("GET", "get-count.php?albumid="+id, true);
    xhttp.send();
  }
  function downloadSelectedAlbums(){
      progressBarInit();
      $('#myModal').modal('toggle');
      var selected_chk=document.querySelectorAll('input[name=chk]:checked');
      var selctedAlbums="";
      var idField=document.getElementById("newids");
      for(var i=0;i<selected_chk.length;i++)
      {
           selctedAlbums=selctedAlbums+selected_chk[i].value+"_";
      }
       getCount(selctedAlbums);
       var xx = new XMLHttpRequest();
        xx.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) 
          {
             var x = new XMLHttpRequest();
             x.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) 
            {     
              var arr=this.responseText.split(',');
              for(var i=0;i<arr.length-1;i++)
              {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) 
                {      
                  run();
                  if(downloadcount==imageCount)
                  {
                    $('#myModal').modal('toggle');
                    downloadZip();
                  }
                }
                };
              xhttp.open("GET", "save-image.php?imageid="+arr[i], true);
              xhttp.send();
            }
         }
        };
        x.open("GET", "get-imageids.php?albumid="+selctedAlbums, true);
        x.send(); 
          }     
       }
      xx.open("GET", "delete-dir.php", true);
      xx.send();  
       
  }
  function downloadAllAlbums(){
       downloadSelectedAlbums();
  }

  function displaySlider(id){
    
     document.getElementById("img-container").innerHTML = '';
    $("#img-container").append("<div class='carousel-item active'><img src='images/wc.jpg' style='height : 100vh; width:100% '></div>");
    loadImages(id);
   // doFS();
    $('#mySlider').modal('toggle');
  }

  function loadImages(id)
  {
    albumid=id;
    var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200)
        {
          var arr=this.responseText.split(',');
          for(var i=0;i<arr.length-1;i++)
          {
             var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) 
              {     
                 $("#img-container").append(" <div class='carousel-item'> <img src='"+this.responseText+"' style='height : 100vh; position: absolute; z-index:-1; width:100%; filter: blur(10px);'><img src='"+this.responseText+"' class='mx-auto d-block' style='height:100vh;'> </div>");
               }
            };
            xhttp.open("GET", "load-album.php?imageid="+arr[i], true);
            xhttp.send();
          }
        }
      };
      xhttp.open("GET", "get-Images.php?albumid="+id, true);
      xhttp.send(); 
  }

  function moveAlbum(id){
    var c=getCookie('credentials');
    if(c!="")
    {
      $("#msg").html("Uploading....");
      $('#myModal').modal('toggle');
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText="sucess"){
              $('#myModal').modal('toggle');
              swal("Good job!", "Album successfully uploaded!", "success");
            }
        }
        else if(this.readyState == 4 && this.status != 200)
        {
          alert(this.responseText);
        }
      };
      xhttp.open("GET", "save-to-drive.php?album="+id, true);
      xhttp.send();
    }
    else
      
    {
      window.location="save-credentials.php";
    }
  }
  function getCookie(cname) {
    var name = cname + "=";
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
    return "";
  }
  function moveSelectedAlbums(){
    var selected_chk=document.querySelectorAll('input[name=chk]:checked');
    for(var i=0;i<selected_chk.length;i++){
      moveAlbum(selected_chk[i].value);
    }
  }
  function moveAllAlbums(){
    var selected_chk=document.querySelectorAll('input[name=chk]');
    for(var i=0;i<selected_chk.length;i++){
      moveAlbum(selected_chk[i].value);
    }
  }

  function doFS(){
    var elem = document.getElementById("mySlider");
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.mozRequestFullScreen) { /* Firefox */
    elem.mozRequestFullScreen();
  } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) { /* IE/Edge */
    elem.msRequestFullscreen();
  }
 } 

if (document.addEventListener)
  {
    document.addEventListener('webkitfullscreenchange', exitHandler, false);
    document.addEventListener('mozfullscreenchange', exitHandler, false);
    document.addEventListener('fullscreenchange', exitHandler, false);
    document.addEventListener('MSFullscreenChange', exitHandler, false);
  }
  var a=0;
  function exitHandler()
  {
    if(document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement !== null){
       if(a==0)
          a=1;
      else{
        $('#mySlider').modal('toggle');
        a=0;
      }
    }
  }
  
</script>
