<?php

/**
 * @file
 * Contains \Drupal\comment\Plugin\Field\FieldFormatter\CommentDefaultFormatter.
 */

namespace Drupal\comment\Plugin\Field\FieldFormatter;

use Drupal\comment\CommentManagerInterface;
use Drupal\comment\CommentStorageInterface;
use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default comment formatter.
 *
 * @FieldFormatter(
 *   id = "comment_default",
 *   module = "comment",
 *   label = @Translation("Comment list"),
 *   field_types = {
 *     "comment"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class CommentDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'pager_id' => 0,
    ) + parent::defaultSettings();
  }

  /**
   * The comment storage.
   *
   * @var \Drupal\comment\CommentStorageInterface
   */
  protected $storage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The comment render controller.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity.manager')->getStorage('comment'),
      $container->get('entity.manager')->getViewBuilder('comment'),
      $container->get('entity.form_builder')
    );
  }

  /**
   * Constructs a new CommentDefaultFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\comment\CommentStorageInterface $comment_storage
   *   The comment storage.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $comment_view_builder
   *   The comment view builder.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, CommentStorageInterface $comment_storage, EntityViewBuilderInterface $comment_view_builder, EntityFormBuilderInterface $entity_form_builder) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->viewBuilder = $comment_view_builder;
    $this->storage = $comment_storage;
    $this->currentUser = $current_user;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();
    $output = array();

    $field_name = $this->fieldDefinition->getName();
    $entity = $items->getEntity();

    $status = $items->status;

    if ($status != CommentItemInterface::HIDDEN && empty($entity->in_preview) &&
      // Comments are added to the search results and search index by
      // comment_node_update_index() instead of by this formatter, so don't
      // return anything if the view mode is search_index or search_result.
      !in_array($this->viewMode, array('search_result', 'search_index'))) {
      $comment_settings = $this->getFieldSettings();

      // Only attempt to render comments if the entity has visible comments.
      // Unpublished comments are not included in
      // $entity->get($field_name)->comment_count, but unpublished comments
      // should display if the user is an administrator.
      if ((($entity->get($field_name)->comment_count && $this->currentUser->hasPermission('access comments')) ||
        $this->currentUser->hasPermission('administer comments'))) {
        $mode = $comment_settings['default_mode'];
        $comments_per_page = $comment_settings['per_page'];
        $comments = $this->storage->loadThread($entity, $field_name, $mode, $comments_per_page, $this->getSetting('pager_id'));
        if ($comments) {
          comment_prepare_thread($comments);
          $build = $this->viewBuilder->viewMultiple($comments);
          $build['pager']['#theme'] = 'pager';
          if ($this->getSetting('pager_id')) {
            $build['pager']['#element'] = $this->getSetting('pager_id');
          }
          $output['comments'] = $build;
        }
      }

      // Append comment form if the comments are open and the form is set to
      // display below the entity. Do not show the form for the print view mode.
      if ($status == CommentItemInterface::OPEN && $comment_settings['form_location'] == CommentItemInterface::FORM_BELOW && $this->viewMode != 'print') {
        // Only show the add comment form if the user has permission.
        if ($this->currentUser->hasPermission('post comments')) {
          // All users in the "anonymous" role can use the same form: it is fine
          // for this form to be stored in the render cache.
          if ($this->currentUser->isAnonymous()) {
            $comment = $this->storage->create(array(
              'entity_type' => $entity->getEntityTypeId(),
              'entity_id' => $entity->id(),
              'field_name' => $field_name,
              'comment_type' => $this->getFieldSetting('comment_type'),
              'pid' => NULL,
            ));
            $output['comment_form'] = $this->entityFormBuilder->getForm($comment);
          }
          // All other users need a user-specific form, which would break the
          // render cache: hence use a #post_render_cache callback.
          else {
            $callback = 'comment.post_render_cache:renderForm';
            $context = array(
              'entity_type' => $entity->getEntityTypeId(),
              'entity_id' => $entity->id(),
              'field_name' => $field_name,
            );
            $placeholder = drupal_render_cache_generate_placeholder($callback, $context);
            $output['comment_form'] = array(
              '#post_render_cache' => array(
                $callback => array(
                  $context,
                ),
              ),
              '#markup' => $placeholder,
            );
          }
        }
      }

      $elements[] = $output + array(
        '#comment_type' => $this->getFieldSetting('comment_type'),
        '#comment_display_mode' => $this->getFieldSetting('default_mode'),
        'comments' => array(),
        'comment_form' => array(),
      );
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = array();
    $element['pager_id'] = array(
      '#type' => 'select',
      '#title' => $this->t('Pager ID'),
      '#options' => range(0, 10),
      '#default_value' => $this->getSetting('pager_id'),
      '#description' => $this->t("Unless you're experiencing problems with pagers related to this field, you should leave this at 0. If using multiple pagers on one page you may need to set this number to a higher value so as not to conflict within the ?page= array. Large values will add a lot of commas to your URLs, so avoid if possible."),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // Only show a summary if we're using a non-standard pager id.
    if ($this->getSetting('pager_id')) {
      return array($this->t('Pager ID: @id', array(
        '@id' => $this->getSetting('pager_id'),
      )));
    }
    return array();
  }

}
