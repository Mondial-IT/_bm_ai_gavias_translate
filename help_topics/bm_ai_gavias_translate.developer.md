---
title: BM AI Gavias Translate — Developer Guide
---

## Internals
- `@TextExtractor` plugin parses JSON and returns **dot‑path → text** pairs.
- Writes back by path; preserves structure.
- Masks placeholders like `{{ token }}` and `[shortcode]` during extraction; unmasks on inject.

## Config
- `bm_ai_gavias_translate.settings`:
  - `field_machine_name` (default: `gva_pagebuilder_content`)
  - `key_whitelist` / `key_patterns`
  - `preserve_placeholders` (bool)

## Base field support
- `hook_entity_base_field_info_alter()` marks the base field translatable if present on `node`.

## Extend
- Add more patterns (e.g., `label`, `description`).
- If Gavias uses a custom field type, add it to the plugin’s `field_types` annotation.
