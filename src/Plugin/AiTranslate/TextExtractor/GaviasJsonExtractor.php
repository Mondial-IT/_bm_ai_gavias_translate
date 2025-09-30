<?php

namespace Drupal\bm_ai_gavias_translate\Plugin\AiTranslate\TextExtractor;

use Drupal\ai_translate\Plugin\AiTranslate\TextExtractor\TextExtractorBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Extracts translatable strings from a Gavias Page Builder JSON field.
 *
 * @TextExtractor(
 *   id = "bm_gavias_pagebuilder_json",
 *   label = @Translation("Gavias Page Builder JSON extractor (BM)"),
 *   description = @Translation("Parses Gavias JSON, extracts whitelisted keys, and writes translations back."),
 *   field_types = {
 *     "string_long",
 *     "text_long"
 *   }
 * )
 */
final class GaviasJsonExtractor extends TextExtractorBase {

  public function applies(FieldItemListInterface $items): bool {
    $config = \Drupal::config('bm_ai_gavias_translate.settings');
    $target = $config->get('field_machine_name') ?? 'gva_pagebuilder_content';
    return $items->getName() === $target;
  }

  public function extract(FieldItemListInterface $items): array {
    $raw = (string) ($items->value ?? $items->getString());
    if ($raw === '') {
      return [];
    }
    $data = json_decode($raw, TRUE);
    if (!is_array($data)) {
      return [];
    }

    [$whitelist, $patterns, $preserve] = $this->settings();
    $paths = $this->collectPaths($data, '', $whitelist, $patterns);
    $map = [];
    $__bm_count = 0;
    foreach ($paths as $path) {
      $val = $this->getByPath($data, $path);
      if (is_string($val) && trim($val) !== '') {
        $map[$path] = $preserve ? $this->maskPlaceholders($val) : $val;
        $__bm_count++;
      }
    }
    if ($__bm_count > 0) { \Drupal::messenger()->addStatus(t('AI Translate: sending @count text value(s) from Gavias JSON for translation.' , ['@count' => $__bm_count])); }
    return $map;
  }

  public function inject(FieldItemListInterface $items, array $translations): void {
    $raw = (string) ($items->value ?? $items->getString());
    $data = json_decode($raw, TRUE);
    if (!is_array($data)) {
      return;
    }
    [, , $preserve] = $this->settings();

    $__bm_applied = 0;
    foreach ($translations as $path => $text) {
      if (!is_string($text)) {
        continue;
      }
      $final = $preserve ? $this->unmaskPlaceholders($text) : $text;
      $this->setByPath($data, $path, $final);
      $__bm_applied++;
    }
    $items->value = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($__bm_applied > 0) { \Drupal::messenger()->addStatus(t('AI Translate: received and applied @count translation(s) to Gavias JSON.' , ['@count' => $__bm_applied])); }
  }

  // ---- helpers ------------------------------------------------------------

  private function settings(): array {
    $config = \Drupal::config('bm_ai_gavias_translate.settings');
    $whitelist = (array) $config->get('key_whitelist') ?: ['title','sub_title','desc','content','button_text'];
    $patterns  = (array) $config->get('key_patterns') ?: ['/^title_\d+$/','/^content_\d+$/'];
    $preserve  = (bool) $config->get('preserve_placeholders');
    return [$whitelist, $patterns, $preserve];
  }

  private function collectPaths(array $data, string $prefix, array $whitelist, array $patterns): array {
    $paths = [];
    foreach ($data as $k => $v) {
      $path = $prefix === '' ? (string) $k : $prefix . '.' . $k;
      $match = in_array($k, $whitelist, TRUE) || $this->matchesAny($k, $patterns);
      if ($match && is_string($v)) {
        $paths[] = $path;
      }
      if (is_array($v)) {
        $paths = array_merge($paths, $this->collectPaths($v, $path, $whitelist, $patterns));
      }
    }
    return $paths;
  }

  private function matchesAny(string $key, array $patterns): bool {
    foreach ($patterns as $p) {
      $regex = $p;
      if (!str_starts_with($p, '/')) {
        $regex = '/' . str_replace('/', '\/', $p) + '/';
      }
      if (@preg_match($regex, $key) && preg_match($regex, $key)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function getByPath(array $data, string $path) {
    $parts = explode('.', $path);
    foreach ($parts as $p) {
      if (is_array($data) && array_key_exists($p, $data)) {
        $data = $data[$p];
      }
      else {
        return NULL;
      }
    }
    return $data;
  }

  private function setByPath(array &$data, string $path, $value): void {
    $parts = explode('.', $path);
    $ref =& $data;
    foreach ($parts as $p) {
      if (!is_array($ref)) {
        $ref = [];
      }
      if (!array_key_exists($p, $ref)) {
        $ref[$p] = [];
      }
      $ref =& $ref[$p];
    }
    $ref = $value;
  }

  private function maskPlaceholders(string $s): string {
    $patterns = [
      '/{{\s*[^}]+\s*}}/u',
      '/\[[^\]]+\]/u',
    ];
    $i = 0;
    $repls = [];
    $masked = $s;
    foreach ($patterns as $rx) {
      $masked = preg_replace_callback($rx, function($m) use (&$i, &$repls) {
        $key = "__PH_" . (++$i) . "__";
        $repls[$key] = $m[0];
        return $key;
      }, $masked);
    }
    if (!empty($repls)) {
      $json = json_encode($repls, JSON_UNESCAPED_UNICODE);
      $masked += "\n<!--BM_PH:" . base64_encode($json) . "-->";
    }
    return $masked;
  }

  private function unmaskPlaceholders(string $s): string {
    if (preg_match('/<!--BM_PH:([^>]+)-->\s*$/', $s, $m)) {
      $map = json_decode(base64_decode($m[1]), TRUE) ?: [];
      $s = preg_replace('/<!--BM_PH:[^>]+-->\s*$/', '', $s);
      foreach ($map as $k => $v) {
        $s = str_replace($k, $v, $s);
      }
    }
    return $s;
  }

}
