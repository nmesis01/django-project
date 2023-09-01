$(document).ready(function(){
    var btn = $('#button');
$(window).scroll(function(){
    if ($(window).scrollTop() > 300) {
      btn.addClass('show');
    } else {
      btn.removeClass('show');
    }
});
btn.on('click', function(e){
    e.preventDefault();
    $('html, body').animate({scrollTop:0}, '300');
});
})
$(document).ready(function(){
  var SearchString = window.location.search.substring(1);
  var VariableArray = SearchString.split('&');
  for(var i = 0; i < VariableArray.length; i++){
      var KeyValuePair = VariableArray[i].split('=');
      if(KeyValuePair[0] == "search"){
        $("#button-addon2").click()
      }
  }

})
