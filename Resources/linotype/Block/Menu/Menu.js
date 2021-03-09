(function (linotype) {

  var blocks = document.getElementsByClassName("linotype-block-menu");

  for ( var i = 0; i < blocks.length; i++ ) {

    blocks[i].onclick = function() {
      console.log('linotype-block-menu: click');
    }
    
  }

})(linotype)