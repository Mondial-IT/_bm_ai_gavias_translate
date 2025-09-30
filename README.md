# BM AI Gavias Translate

Safely translates **Gavias Page Builder** JSON via **AI Translate** by extracting whitelisted text values and reinjecting translations **without changing the JSON structure**.

- Default field: **`gva_pagebuilder_content`** (node base field used by Gavias).
- Also works with configurable fields (string_long/text_long) if you change the field name in settings.
- Admin UI: `/admin/config/content/bm-ai-gavias-translate`

## Prompts (per target language)
Use a strict prompt, e.g.:

> Translate values only into {TARGET_LANGUAGE}. Do not change keys, punctuation, HTML, placeholders (`{{ }}`, `[shortcode]`), or URLs. Return JSON with the same keys and translated values.

## Base-field translation
The module includes a `hook_entity_base_field_info_alter()` so `gva_pagebuilder_content` is marked **translatable**. You must also enable the field per bundle at **Configuration → Regional and language → Content language and translation**.
