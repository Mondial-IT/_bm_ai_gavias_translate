---
title: BM AI Gavias Translate — Admin & Editor Guide
---

## Prerequisites
- Content Translation enabled; target bundles marked translatable.
- AI + AI Translate configured with a provider.
- Gavias builder content present (base field `gva_pagebuilder_content` or a configurable field).

## Configure
- Go to `/admin/config/content/bm-ai-gavias-translate` and confirm the field name.
- Add/remove whitelist keys or regex patterns.
- Keep “preserve placeholders” on.

## Prompts
Set strict per‑language prompts at `/admin/config/ai/ai-translate` (see README).

## Translate workflow
1. Create/complete the source node.
2. Add a translation (Translate tab).
3. Click **AI Translations**; review and save.

## Troubleshooting
- Field not translated? Confirm field name and bundle translation setting.
- Model token limits? Reduce keys/patterns.
- Broken layout? Tighten prompt; the extractor already keeps structure intact.
