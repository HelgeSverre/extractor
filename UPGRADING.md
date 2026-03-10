# Upgrading from v0.4.x to v0.5.0

This guide covers all breaking changes in v0.5.0 and how to migrate your code.

---

## 1. Publish the Updated Config

v0.5.0 adds new config keys. Publish the updated config file:

```bash
php artisan vendor:publish --tag="extractor-config" --force
```

New keys in `config/extractor.php`:

| Key             | Env Variable              | Default                                                                        |
| --------------- | ------------------------- | ------------------------------------------------------------------------------ |
| `model`         | `EXTRACTOR_MODEL`         | `gpt-4o-mini`                                                                  |
| `system_prompt` | `EXTRACTOR_SYSTEM_PROMPT` | `null` (disabled by default) |

---

## 2. Default Model Changed

The default model changed from `gpt-3.5-turbo-1106` to `gpt-4o-mini`. The `$model` parameter on `ExtractorManager::extract()`, `::fields()`, and `::view()` changed from a string default to `null`, with the actual default now resolved from config.

**Before (v0.4.x):**

```php
// Implicitly used gpt-3.5-turbo-1106
Extractor::extract(MyExtractor::class, $text);
```

**After (v0.5.0):**

```php
// Now uses gpt-4o-mini by default (better and cheaper)
Extractor::extract(MyExtractor::class, $text);

// To keep the old model explicitly:
Extractor::extract(MyExtractor::class, $text, model: 'gpt-3.5-turbo-1106');
```

Or set it globally in your `.env`:

```dotenv
EXTRACTOR_MODEL=gpt-3.5-turbo-1106
```

> **Note:** `gpt-3.5-turbo-1106` is scheduled for shutdown by OpenAI on 2026-09-28. We recommend migrating to `gpt-4o-mini` or newer.

---

## 3. Legacy Completion Models Removed

The OpenAI Completions API (`/v1/completions`) is no longer supported. Only the Chat Completions API (`/v1/chat/completions`) is used.

**Models that no longer work:**

- `text-davinci-002`
- `text-davinci-003`
- `gpt-3.5-turbo-instruct`

**Migration:** Switch to any current chat model:

```php
// Before (v0.4.x)
Extractor::extract(MyExtractor::class, $text, model: 'text-davinci-003');

// After (v0.5.0)
Extractor::extract(MyExtractor::class, $text, model: 'gpt-4o-mini');
```

---

## 4. Engine Model Constants Changed

Many constants on the `Engine` class have been renamed or removed, and new ones have been added.

### Renamed (deprecated aliases kept until v0.6.0)

| v0.4.x                    | v0.5.0                |
| ------------------------- | --------------------- |
| `Engine::GPT_4_OMNI`      | `Engine::GPT_4O`      |
| `Engine::GPT_4_OMNI_MINI` | `Engine::GPT_4O_MINI` |
| `Engine::GPT_4o`          | `Engine::GPT_4O`      |

The old names still work in v0.5.0 but will be removed in v0.6.0. Update your code now:

```php
// Before
Engine::GPT_4_OMNI
Engine::GPT_4_OMNI_MINI

// After
Engine::GPT_4O
Engine::GPT_4O_MINI
```

### Removed Constants

These constants have been removed entirely. Replace them with model name strings or the new constants:

| Removed Constant               | Replacement                                   |
| ------------------------------ | --------------------------------------------- |
| `Engine::GPT_4`                | `'gpt-4'` or `Engine::GPT_4O`                 |
| `Engine::GPT4_32K`             | `Engine::GPT_4O`                              |
| `Engine::GPT_3_TURBO`          | `Engine::GPT_4O_MINI`                         |
| `Engine::GPT_3_TURBO_16K`      | `Engine::GPT_4O_MINI`                         |
| `Engine::GPT_3_TURBO_INSTRUCT` | _(no replacement — completion model removed)_ |
| `Engine::GPT_3_TURBO_1106`     | `Engine::GPT_4O_MINI`                         |
| `Engine::GPT_4_1106_PREVIEW`   | `Engine::GPT_4O`                              |
| `Engine::GPT_4_VISION`         | `Engine::GPT_4O`                              |
| `Engine::GPT_O1_MINI`          | `Engine::O3_MINI`                             |
| `Engine::GPT_O1_PREVIEW`       | `Engine::O3`                                  |
| `Engine::TEXT_DAVINCI_002`     | _(no replacement — completion model removed)_ |
| `Engine::TEXT_DAVINCI_003`     | _(no replacement — completion model removed)_ |

### New Constants

```php
Engine::GPT_5_2       // 'gpt-5.2'
Engine::GPT_5_1       // 'gpt-5.1'
Engine::GPT_5         // 'gpt-5'
Engine::GPT_5_MINI    // 'gpt-5-mini'
Engine::GPT_4_1       // 'gpt-4.1'
Engine::GPT_4_1_MINI  // 'gpt-4.1-mini'
Engine::GPT_4_1_NANO  // 'gpt-4.1-nano'
Engine::GPT_4O        // 'gpt-4o'
Engine::GPT_4O_MINI   // 'gpt-4o-mini'
Engine::O3            // 'o3'
Engine::O3_MINI       // 'o3-mini'
Engine::O3_PRO        // 'o3-pro'
Engine::O4_MINI       // 'o4-mini'
```

---

## 5. `Engine::extractResponseText()` Signature Changed

This is an internal method, but if you called it directly:

**Before (v0.4.x):**

```php
public function extractResponseText(ChatResponse|CompletionResponse $response): mixed
```

**After (v0.5.0):**

```php
public function extractResponseText(ChatResponse $response): string
```

`CompletionResponse` is no longer accepted (completion models were removed). The return type is now `string` instead of `mixed`.

---

## 6. System Prompt Support (Opt-In)

v0.5.0 adds support for system prompts in extraction requests. System prompts are **disabled by default** — set the `EXTRACTOR_SYSTEM_PROMPT` env var to enable.

**Recommended system prompt:**

```dotenv
EXTRACTOR_SYSTEM_PROMPT="You are a precise data extraction assistant. Always respond with valid JSON."
```

Or in `config/extractor.php`:

```php
'system_prompt' => env('EXTRACTOR_SYSTEM_PROMPT', 'Your custom prompt'),
```

Individual extractors can override this by implementing the `systemPrompt()` method:

```php
class MyExtractor extends Extractor
{
    public function systemPrompt(): string
    {
        return 'You are a specialist in extracting invoice data.';
    }
}
```

---

## 7. JSON `response_format` Always Sent

All requests now include `response_format: { "type": "json_object" }`. Previously, this was only sent for an allowlisted set of models.

**Impact:** If you use a model that doesn't support `response_format`, the API will return an error.

**Migration:** Upgrade to a model that supports structured JSON output (all current OpenAI models do). If you're using an alternative provider via `OPENAI_BASE_URI`, ensure your model supports the `response_format` parameter.

---

## Quick Migration Checklist

- [ ] Run `php artisan vendor:publish --tag="extractor-config" --force`
- [ ] Check if you were relying on the `gpt-3.5-turbo-1106` default — switch to `gpt-4o-mini` or set `EXTRACTOR_MODEL` explicitly
- [ ] Replace any usage of `text-davinci-*` or `gpt-3.5-turbo-instruct` with a chat model
- [ ] Replace removed `Engine::*` constants with new equivalents
- [ ] Rename `Engine::GPT_4_OMNI` → `Engine::GPT_4O` and `Engine::GPT_4_OMNI_MINI` → `Engine::GPT_4O_MINI`
- [ ] Verify your model supports `response_format` (all current OpenAI models do)
- [ ] Review the new system prompt behavior — disable with `EXTRACTOR_SYSTEM_PROMPT=""` if needed
