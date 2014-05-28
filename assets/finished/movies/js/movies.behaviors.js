(function ($, window) {

  "use strict";

  /*
  * Konami code
  * */
  Drupal.behaviors.konamiCode = {
    attach: function (context, settings) {
      var audio = new Audio("themes/movies/audio/magicword.wav");
      audio.addEventListener('ended', function() {
        this.currentTime = 0;
        this.play();
      }, false);
      // var secret = "777986736983"; // movies
      var secret = "80798067798278";
      var input = "";
      var timer;

      // The following function sets a timer that checks for user input. You can change the variation in how long the user has to input by changing the number in ‘setTimeout.’ In this case, it’s set for 500 milliseconds or ½ second.
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
          // $('.overlay-bg').css({ display: "block" });
          // $('.overlay-bg').fadeIn("slow");
          $('.overlay-bg').fadeIn("fast");
        }
      };
      $('.close-btn').click(function() {
        audio.pause();
        $('.overlay-bg').fadeOut("fast");
      })
    }
  };

})(jQuery, window);
