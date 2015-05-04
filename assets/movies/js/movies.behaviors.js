(function($) {

  /*
   * Capitalize the Site Title
   */
  Drupal.behaviors.javascriptTest = {
    attach: function (context, settings) {
      // alert('Javascript is working!');
      $('#site-header a').css('text-transform', 'uppercase');
    }
  };

  /*
   * Hidden Easter Egg
   */
  Drupal.behaviors.konamiCode = {
    attach: function (context, settings) {
      var audio = new Audio("/themes/movies/audio/magicword.wav");
      audio.addEventListener('ended', function() {
        this.currentTime = 0;
        this.play();
      }, false);
      var secret = "80798067798278"; // popcorn
      var input = "";
      var timer;

      // The following function sets a timer that checks for user input.
      $(document).keyup(function(e) {
         input += e.which;
         clearTimeout(timer);
         timer = setTimeout(function() { input = ""; }, 5000);
         check_input();
      });

      // Once the time is up, this function is run to see if the user’s input is the same as the secret code
      function check_input() {
        if(input == secret) {
          audio.play();
          $('.overlay-bg').fadeIn("fast");
        }
      };
      $('.close-btn').click(function() {
        audio.pause();
        $('.overlay-bg').fadeOut("fast");
      })
    }
  };

})(jQuery);
