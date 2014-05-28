<?php

/**
 * @file
 * Contains \Drupal\training\Controller\TrainingController.
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
  public function upcomingMovies() {
     
    // Render the output list.
    $build = array(
      '#theme' => 'item_list',
      '#items' => training_upcoming_movies(),
      '#title' => t('Top US Upcoming Movies from Rotten Tomatoes'),
    );

    return $build;
  }
}