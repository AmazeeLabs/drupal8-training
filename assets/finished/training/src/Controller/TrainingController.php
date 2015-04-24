<?php
/**
  * @file
  * Contains \Drupal\training\Controller
  */
namespace Drupal\training\Controller;

/**
 * Controller for training movie review page route.
 */
class TrainingController {
  /**
   * Constructs a page with current top movie reviews from Rotten Tomatoes.
   * Calls the custom training_upcoming_movies function in .module
   *
   * Our router maps this method to the path 'upcoming-movies'.
   */
  public function content() {
    // Render the output list.
    return array(
      '#theme' => 'item_list',
      '#items' => training_upcoming_movies(),
      '#title' => t('Top US Upcoming Movies from Rotten Tomatoes'),
    );
  }
}
