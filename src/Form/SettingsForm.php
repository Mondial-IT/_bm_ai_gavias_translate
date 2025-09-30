<?php

namespace Drupal\bm_ai_gavias_translate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form.
 */
final class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'bm_ai_gavias_translate_settings';
  }

  protected function getEditableConfigNames(): array {
    return ['bm_ai_gavias_translate.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('bm_ai_gavias_translate.settings');

    $form['field_machine_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Gavias field name (base or configurable)'),
      '#default_value' => $config->get('field_machine_name') ?? 'gva_pagebuilder_content',
      '#required' => TRUE,
      '#description' => $this->t('For Gavias base field use "gva_pagebuilder_content". For a configurable field, enter its machine name (e.g., field_gavias_pagebuilder).'),
    ];

    $form['key_whitelist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exact keys to translate (one per line)'),
      '#default_value' => implode("\n", (array) $config->get('key_whitelist')),
      '#description' => $this->t('These keys will always be translated if present.'),
    ];

    $form['key_patterns'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Regex patterns to translate (one per line)'),
      '#default_value' => implode("\n", (array) $config->get('key_patterns')),
      '#description' => $this->t('PCRE patterns like /^title_\\d+$/'),
    ];

    $form['preserve_placeholders'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Preserve placeholders like {{ token }} and [shortcode]'),
      '#default_value' => (bool) $config->get('preserve_placeholders'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->configFactory->getEditable('bm_ai_gavias_translate.settings');
    $config->set('field_machine_name', $form_state->getValue('field_machine_name'));

    $whitelist = preg_split('/\r?\n/', (string) $form_state->getValue('key_whitelist'));
    $whitelist = array_values(array_filter(array_map('trim', $whitelist)));
    $config->set('key_whitelist', $whitelist);

    $patterns = preg_split('/\r?\n/', (string) $form_state->getValue('key_patterns'));
    $patterns = array_values(array_filter(array_map('trim', $patterns)));
    $config->set('key_patterns', $patterns);

    $config->set('preserve_placeholders', (bool) $form_state->getValue('preserve_placeholders'));
    $config->save();
    parent::submitForm($form, $form_state);
  }
}
