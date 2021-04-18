<?php

namespace Drupal\sns_status\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block called "SNS Status Block".
 *
 * @Block(
 *  id = "status_block",
 *  admin_label = @Translation("SNS Status Block")
 * )
 */
class StatusBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
      return ['label_display' => FALSE];
    }

    /**
     * {@inheritdoc}
     */
    public function build() {

      $config = $this->getConfiguration();

      if (!empty($config['status_location'])) {
        $name = $config['status_location'];
      }
//      else {
//        $name = $this->t('');
//      }

      if (!empty($config['phrase'])) {
        $phrase = $config['phrase'];
      }

      $phrase = '';
      $statusoff = '';
      $statuson = '';
      $summary = '';

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $name);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
      $str = curl_exec($curl);
      curl_close($curl);

      preg_match('/<span class="beam_status_summary">(.*?)<\/span>/s', $str, $summary);
      preg_match('/<span class="beam_status_on">(.*?)<\/span>/s', $str, $statuson);
      preg_match('/<span class="beam_status_off">(.*?)<\/span>/s', $str, $statusoff);

      $isstatus = strip_tags($statusoff[0] . $statuson[0]);
      $issummary = strip_tags($summary[0]);
      $basepath = drupal_get_path('module', 'sns_status');

      if ($issummary == $phrase) {
        $ispath = '/' . $basepath . '/images/sns_on.gif';
      } else {
        $ispath = '/' . $basepath . '/images/sns_off.gif';
      }

      return [
        '#theme' => 'status_block',
        '#status' => $isstatus,
        '#summary' => $issummary,
        '#path' => $ispath,
      ];
    }
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['status_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Where'),
      '#description' => $this->t('Enter the status URL?'),
      '#default_value' => $config['status_location'],
    ];

    $form['phrase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('What'),
      '#description' => $this->t('Enter the phrase to look for?'),
      '#default_value' => $config['phrase'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['status_location'] = $values['status_location'];
    $this->configuration['phrase'] = $values['phrase'];
  }
}
