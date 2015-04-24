(function($) {

  /**
   * Adding Javascript to 'Movies' Theme Test
   */
  Drupal.behaviors.javascriptTest = {
    attach: function (context, settings) {
      // alert('Javascript is working!');
      $('#site-header a').css('text-transform', 'uppercase');
    }
  };

})(jQuery);
