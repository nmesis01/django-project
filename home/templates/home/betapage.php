<?php
    session_start();
    if(!isset($_SESSION["username"])) 
    {
        header("Location: login.php?location=oxford");
    }
?>
<?php include("header.php") ?>
<?php
if(isset($_GET["delete"])&&!empty($_GET["delete"])){
  $deleteword = $_GET["delete"];
  $fordelete = "entoen".$_SESSION["username"];
  mysqli_query($conn,"DELETE FROM $fordelete WHERE word='$deleteword'");
  header("Location: https://learnto.cf/betapage.php");
}?>
<!DOCTYPE html>
<html lang="en">
<head>
<style type="text/css">
label:hover{cursor:pointer;}
#button {
  display: inline-block;
  background-color: #ffc107;
  width: 50px;
  height: 50px;
  text-align: center;
  border-radius: 4px;
  position: fixed;
  bottom: 30px;
  right: 30px;
  transition: background-color .3s, 
    opacity .5s, visibility .5s;
  opacity: 0;
  visibility: hidden;
  z-index: 1000;
}
#button::after {
  content: "\f077";
  font-family: FontAwesome;
  font-weight: normal;
  font-style: normal;
  font-size: 2em;
  line-height: 50px;
  color: #fff;
}
#button:hover {
  cursor: pointer;
  background-color: #333;
}
#button:active {
  background-color: #555;
}
#button.show {
  opacity: 1;
  visibility: visible;
}
/* Styles for the content section */
.content {
  width: 77%;
  margin: 50px auto;
  font-family: 'Merriweather', serif;
  font-size: 17px;
  color: #6c767a;
  line-height: 1.9;
}
@media (min-width: 500px) {
  .content {
    width: 43%;
  }
  #button {
    margin: 30px;
  }
}
.content h1 {
  margin-bottom: -10px;
  color: #03a9f4;
  line-height: 1.5;
}
.content h3 {
  font-style: italic;
  color: #96a2a7;
}
</style>
<?php 
if(isset($_GET["definitions"])&&isset($_GET["update"])){
  $definition_string = "";
  $examples_string = "";
  $word = $_GET["update"];
  $forquery = "entoen".$_SESSION["username"];
  $query = mysqli_query($conn,"SELECT * FROM $forquery WHERE word='$word'");
  $assoc = mysqli_fetch_assoc($query);
  $def_db = $assoc["definitions"];
  $def_db = explode("/!",$def_db);
  $example_db = $assoc["examples"];
  $example_db = explode("||",$example_db);
  $definitions = $_GET["definitions"];
  for($i=0;$i<count($definitions);$i++){
    $examples_string = $examples_string."||";
    $def_db_int = $definitions[$i];
    $definition_string = $definition_string."/!".$def_db[$def_db_int];
    if(isset($_GET["examples"])){
      $examples = $_GET["examples"];
      for($m=0;$m<count($examples);$m++){
        $int_examples = explode(",",$examples[$m]);
        if($int_examples[0]==$def_db_int){
          $for_Explode = $example_db[$def_db_int];
          $for_Explode = explode("/!",$for_Explode);
          $last = $for_Explode[$int_examples[1]];
          $examples_string = $examples_string."/!".$last;
        }
      }
    }
  }
  mysqli_query($conn,"UPDATE $forquery SET definitions='$definition_string' , examples='$examples_string' WHERE word='$word'");
  header("Location: https://learnto.cf/betapage.php");
}?>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford Dictionary</title>
    <script>
        $(document).ready(function(){
        $("#betapage").addClass("active")})
    </script>
        <?php
    $wordlist_string = "";
    $forquery = "entoen".$_SESSION["username"];
    $query = mysqli_query($conn,"SELECT * FROM $forquery");
    while($assoc = mysqli_fetch_assoc($query)){
      $word = $assoc["word"];
      $wordlist_string = $wordlist_string."/!".$word;
    }
    ?>
        <script>
        $(document).ready(function(){
          $(".btn-close").click(function(){
            $(".list-group").empty();
          })
          $("#button-addon2").click(function(){
              var wordlist_string = "<?php echo($wordlist_string); ?>";
              var word_list = wordlist_string.split("/!");
              var words = $("#word_input").val();
              words = words.toLowerCase();
                $.ajax({
                  type:"POST",
                  url:"https://oxfapi.azurewebsites.net/search",
                  data:{"word":words},
                  success:function(x){
                    var new_word = x["word"];
                    if(jQuery.inArray(new_word, word_list) == -1){
                      var l=""
                      var k=""
                      for(let i=0; i<x["definitions"].length;i++){
                        k = k+"/!"+x["definitions"][i];
                      }
                      for(let i=0; i<x["examples"].length;i++){
                        l=l+"||"
                        for(let m=0;m<x["examples"][i].length;m++){
                          l = l+"/!"+x["examples"][i][m];
                        }
                      }
                      $.ajax({type:"POST",url:"add_definition.php",data:{"word":new_word,"definition": k,"examples":l,"user":"<?php echo($_SESSION["username"]);?>"}});
                      var x = "betapage.php?edit="+new_word;
                      setTimeout(() => {  window.location.href = x; }, 50);
                    }
                    else{
                      alert("There is '"+words+"' in the list!");
                      var x = "betapage.php?scroll="+new_word;
                      window.location.href = x;
                    }
                  },
                  error: function(){
                    $.ajax({type:"GET",url:"https://oxfapi.azurewebsites.net/suggest",data:{"word":words},success:function(e){
                      e = e["word"];
                      for(var i=0;i<e.length;i++){
                        $(".list-group").prepend("<a class='list-group-item list-group-item-action list-group-item-light' href='betapage.php?search="+e[i]+"'>"+e[i]+"</a>");
                      }
                      $('#exampleModal').modal('show');
                    }});
                  }
                });
            });
            $(".similar").click(function(){
            var wordlist_string = "<?php echo($wordlist_string); ?>";
            var word_list = wordlist_string.split("/!");
            var words = $(this).val();
              $.ajax({
                type:"GET",
                url:"https://oxfapi.azurewebsites.net/find",
                data:{"word":words},
                success:function(x){
                  var new_word = x["word"];
                  if(jQuery.inArray(new_word, word_list) == -1){
                    var l=""
                    var k=""
                    for(let i=0; i<x["definitions"].length;i++){
                      k = k+"/!"+x["definitions"][i];
                    }
                    for(let i=0; i<x["examples"].length;i++){
                      l=l+"||"
                      for(let m=0;m<x["examples"][i].length;m++){
                         l = l+"/!"+x["examples"][i][m];
                       }
                     }
                     $.ajax({type:"POST",url:"add_definition.php",data:{"word":new_word,"definition": k,"examples":l,"user":"<?php echo($_SESSION["username"]);?>"}});
                    var x = "betapage.php?edit="+new_word;
                    setTimeout(() => {  window.location.href = x; }, 50);
                  }
                  else{
                    alert("There is '"+words+"' in the list!");
                    var x = "betapage.php?scroll="+new_word;
                    window.location.href = x;
                  }
                 },
                error: function(){
                   alert("Unexpected Error! Please Try Again");
                 }
                });
          });
          $(".getsimilar").click(function(){
            word = $(this).val();
            word_exploded = word.split("_");
            word =word_exploded[0];
            word = word.toLowerCase();
            $.ajax({
              type:"GET",
              url:"https://oxfapi.azurewebsites.net/getsimilar",
              data:{"word":word},
              success:function(x){
                words = x["words"];
                labels = x["labels"];
                var m = "betapage.php?similar="+words+"&labels="+labels;
                setTimeout(() => {  window.location.href = m; }, 50);
              }
            });
          });

        });
    </script>
</head>
<body>
<a id="button" style='text-decoration:none'></a>
    <div class="container">
        <div class="row mt-1">
            <div class="col-sm-1 col-md-3"></div>
            <div class="col">
                <div class="input-group mb-3">
                    <input type="text" id="word_input" class="form-control" placeholder="Word" value="<?php if(isset($_GET["search"])){echo($_GET["search"]);} ?>" style="text-align:center" aria-label="Word" aria-describedby="button-addon2">
                    <button type="button" name="add" id="button-addon2" class="btn btn-outline-success">Add</button>

                </div>
            </div>
            <div class="col-sm-1 col-md-3"></div>
        </div>
        <div class="row">
          <form method='get' action="betapage.php">
          <?php
          if(isset($_GET["similar"])){
            echo("<div class='card w-100 text-center' style='width: 18rem;'>
            <div class='card-body'>
              <h3 class='card-title'>Some Similar Words</h3>
            </div>  <ul class='list-group list-group-flush'>");
            $similar = explode(",",$_GET["similar"]);
            $labels = explode(",",$_GET["labels"]);
            for($i=0;$i<count($similar);$i++){
              $exploded = explode("_",$similar[$i]);
              if(count($exploded) ==2 && $exploded[1]!=""){
                echo("<li class='list-group-item'><button class='btn similar' type='button' value='$labels[$i]'><strong class='w-auto'>$exploded[0]</strong><i> ($exploded[1])</i></button></li>");
              }
              else{
                echo("<li class='list-group-item'><button class='btn similar' type='button' value='$labels[$i]'><strong class='w-auto'>$exploded[0]</strong></button></li>");
              }
            }
            echo("</ul></div>");
          }
          else{
          $last = "entoen".$_SESSION["username"];
            $query = mysqli_query($conn,"SELECT * FROM $last order by id desc");
            while($assoc = mysqli_fetch_assoc($query)){
              $word = $assoc["word"];
              $exploded_word = explode("_",$word);
              $control_toggle = "";
              if((isset($_GET["edit"])&&$_GET["edit"]==$word) or (isset($_GET["scroll"])&&$_GET["scroll"]==$word)){
                $control_toggle = "show";
              }
              $explode_word = explode("-",$exploded_word[0]);
              if(count($explode_word)>1){
                $forvision = $explode_word[0];
                for($i=1;$i<count($explode_word);$i++){
                  $forvision = $forvision." ".$explode_word[$i];
                }
                $exploded_word[0] = $forvision;
              }
              $defition = $assoc["definitions"];
              $defition = explode("/!",$defition);
              $examples = $assoc["examples"];
              $examples = explode("||",$examples);
              echo("<div class='accordion-item' id='$word-accordion'>
              <h2 class='accordion-header' id='headingTwo'>
                <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#$word' aria-expanded='false' aria-controls='$word'>
                  <h4>$exploded_word[0]</h4>&nbsp<sub><i>($exploded_word[1])</i><sub>
                </button>
              </h2>
              <div id='$word' class='accordion-collapse collapse $control_toggle' aria-labelledby='$word' data-bs-parent='#accordionExample'>
                <div class='accordion-body'>");
                for($i=1;$i<count($defition);$i++){
                  $check_control="";
                  $check_control_footer="";
                  if(isset($_GET["edit"])&&$_GET["edit"]==$word){
                    $check_control = "<div class='w-auto p-0'><div class='form-check'>
                    <input class='form-check-input' type='checkbox' name='definitions[]' value='$i' id='$i-check'>
                    <label class='form-check-label' for='$i-check'>";
                    $check_control_footer="</label></div></div>";
                  }
                  echo("<div class='row'>$check_control<strong>".$defition[$i]."</strong>$check_control_footer</div>");
                  $lastexploded = explode("/!",$examples[$i]);
                  for($m=1;$m<count($lastexploded);$m++){
                    $check_control="";
                    $check_control_footer = "";
                    if(isset($_GET["edit"])&&$_GET["edit"]==$word){
                      $check_control = "<div class='form-check p-0'>
                      <label class='form-check-label' style='float:left;width:95%' for='ex$i-$m'>";
                      $check_control_footer="</label><input class='form-check-input' style='float:right' type='checkbox' name='examples[]' value='$i,$m' id='ex$i-$m'></div>";
                    }
                    $explode_for_example = explode("/-/",$lastexploded[$m]);
                    if(count($explode_for_example) == 2){
                      echo("<div class='row'><div class='ms-2'>$check_control<span style='color:green'>-".$explode_for_example[0]."</span> $explode_for_example[1] $check_control_footer</div></div>");
                    }
                    else{
                      echo("<div class='row'><div class='ms-2'>$check_control-".$lastexploded[$m]."$check_control_footer</div></div>");
                    } 
                  }
                };
                echo("<div class='row'>");
                if(isset($_GET["edit"])&&$_GET["edit"]==$word){
                  echo("<div style='text-align:left' class='col-3'><button style='color:green' type='submit' class='btn' name='update' value='$word'>Update</button></div>");
                }
                else{
                  echo("<div style='text-align:left;' class='col-3'><button style='color:green' type='submit' class='btn' name='edit' value='$word'>Edit</button></div>");
                }
                echo("<div style='text-align:center;' class='col-6'><button style='color:blue' type='button' class='btn getsimilar' id='similar' value='$word'>Similar Words</button></div>
                <div style='text-align:right;' class='col-3'><button style='color:red' type='submit' class='btn' name='delete' value='$word'>Delete</button></div>
                </div>
              </div>
            </div></div>");
            }}
          ?>
          </form>
        </div>
        <?php
        if(isset($_GET["edit"])){
          $word = $_GET["edit"]."-accordion";
        }
        if(isset($_GET["scroll"])){
          $word = $_GET["scroll"]."-accordion";
        }
        ?>
        <script type="text/javascript">
          document.getElementById("<?php echo($word); ?>").scrollIntoView();
          var btn = $('#button');
          $(window).scroll(function() {
            if ($(window).scrollTop() > 300) {
              btn.addClass('show');
            } else {
              btn.removeClass('show');
            }
          });
          btn.on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({scrollTop:0}, '300');
          });
          $(document).ready(function(){
            $('#word_input').keypress(function (e) {
            var key = e.which;
            if(key == 13)  // the enter key code
            {
                $('#button-addon2').click();
                return false;  
            }});});
        </script>
        <br>
        <br>
        <br>
        <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h1 class="modal-title fs-5" id="exampleModalLabel">Did you mean:</h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div id='modalbody' class="modal-body text-center">
      <div class="list-group">
      </div>
      </div>
</div>
<?php if(isset($_GET["search"])){echo('<script>$(document).ready(function(){$("#button-addon2").click()})</script>');} ?>
</body>
</html>
