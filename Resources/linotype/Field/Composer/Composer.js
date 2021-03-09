(function (linotype) {

  var blocks = document.getElementsByClassName("linotype-field-text");

  for ( var i = 0; i < blocks.length; i++ ) {

    blocks[i].onclick = function() {
      console.log('linotype-field-text: click');
    }
    
  }

})(linotype)