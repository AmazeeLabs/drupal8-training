<?php

/**
 * @file
 * Definition of Drupal\views\Plugin\views\field\Serialized.
 */

namespace Drupal\views\Plugin\views\field;

use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;

/**
 * Field handler to show data of serialized fields.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("serialized")
 */
class Serialized extends FieldPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['format'] = array('default' => 'unserialized');
    $options['key'] = array('default' => '');
    return $options;
  }


  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['format'] = array(
      '#type' => 'select',
      '#title' => t('Display format'),
      '#description' => t('How should the serialized data be displayed. You can choose a custom array/object key or a print_r on the full output.'),
      '#options' => array(
        'unserialized' => t('Full data (unserialized)'),
        'serialized' => t('Full data (serialized)'),
        'key' => t('A certain key'),
      ),
      '#default_value' => $this->options['format'],
    );
    $form['key'] = array(
      '#type' => 'textfield',
      '#title' => t('Which key should be displayed'),
      '#default_value' => $this->options['key'],
      '#states' => array(
        'visible' => array(
          ':input[name="options[format]"]' => array('value' => 'key'),
        ),
      ),
    );
  }

  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    // Require a key if the format is key.
    if ($form_state->getValue(array('options', 'format')) == 'key' && $form_state->getValue(array('options', 'key')) == '') {
      $form_state->setError($form['key'], t('You have to enter a key if you want to display a key of the data.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $values->{$this->field_alias};

    if ($this->options['format'] == 'unserialized') {
      return String::checkPlain(print_r(unserialize($value), TRUE));
    }
    elseif ($this->options['format'] == 'key' && !empty($this->options['key'])) {
      $value = (array) unserialize($value);
      return String::checkPlain($value[$this->options['key']]);
    }

    return $value;
  }

}
