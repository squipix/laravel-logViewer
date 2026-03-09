# Security Audit — LogViewer

Audit date: 2026-03-09 · Scope: all files under `/home/mastermind/Documents/php-libraries/LogViewer/src`, `views/`, `config/`, and `helpers.php`.

---

## Summary

| Severity | Count |
|----------|-------|
| 🔴 High | 3 |
| 🟡 Medium | 4 |
| 🟢 Low / Informational | 3 |

---

## 🔴 High Severity

### 1. Path Traversal via `$date` parameter

**Files:** [Filesystem.php](file:///home/mastermind/Documents/php-libraries/LogViewer/src/Utilities/Filesystem.php#L308-L326), [LogViewerController.php](file:///home/mastermind/Documents/php-libraries/LogViewer/src/Http/Controllers/LogViewerController.php#L101-L110)

The `$date` parameter from the URL route (`{date}`) is used directly in file-path construction with **no validation or sanitization**:

```php
// Filesystem.php:319
$path = $this->storagePath.DIRECTORY_SEPARATOR.$this->prefixPattern.$date.$this->extension;
```

An attacker could supply a value like `../../etc/passwd%00` or `../../../.env` to read or delete arbitrary files. Although `realpath()` is called afterwards, the `exists()` check happens **before** `realpath()`, and the extension (`.log`) is appended — but path-traversal sequences (`../`) are still evaluated by the filesystem.

> [!CAUTION]
> **Recommendation:** Validate `$date` against a strict regex (e.g. `^\d{4}-\d{2}-\d{2}$`) in the controller or add a route constraint. Also verify the resolved path is within `storagePath`.

```php
// Example route constraint in LogViewerRoute.php
$this->get('{date}', [LogViewerController::class, 'show'])
     ->where('date', '\d{4}-\d{2}-\d{2}')
     ->name('show');
```

---

### 2. XSS via unescaped Blade output (`{!! !!}`)

**Files:** [show.blade.php](file:///home/mastermind/Documents/php-libraries/LogViewer/views/bootstrap-5/show.blade.php)

Several uses of `{!! !!}` render content without HTML escaping:

| Line | Expression | Risk |
|------|-----------|------|
| 130 | `{!! $entry->level() !!}` | Level name with icon — icon HTML from config, lower risk |
| 162 | `{!! $entry->stack() !!}` | Stack trace from the log file |
| 186 | `{!! $entries->appends(compact('query'))->render() !!}` | Pagination — Laravel-generated, low risk |
| 25,30 | `{!! $item['icon'] !!}` | Icons from config |

The `stack()` method in `LogEntry.php` does call `htmlentities()`, which is good. However, the highlight feature in the `@section('scripts')` block then **replaces content via `innerHTML`** using a regex built from config:

```javascript
// show.blade.php:265-267
elt.innerHTML = elt.innerHTML.trim()
    .replace(/({!! $htmlHighlight !!})/gm, '<strong>$1</strong>')
```

If `log-viewer.highlight` config values are modified (e.g. via env injection or `.env` manipulation), arbitrary JavaScript could be injected through the regex pattern.

> [!WARNING]
> **Recommendation:** Escape the highlight patterns for use in JS regex context, or use `textContent` manipulation instead of `innerHTML`. While the stack is escaped via `htmlentities()`, the highlight step re-interprets it as HTML.

---

### 3. Delete endpoint lacks CSRF token validation for AJAX

**File:** [LogViewerController.php](file:///home/mastermind/Documents/php-libraries/LogViewer/src/Http/Controllers/LogViewerController.php#L190-L199)

```php
public function delete(Request $request)
{
    abort_unless($request->ajax(), 405, 'Method Not Allowed');
    $date = $request->input('date');
    return response()->json([
        'result' => $this->logViewer->delete($date) ? 'success' : 'error'
    ]);
}
```

The only protection is `$request->ajax()` which checks the `X-Requested-With` header. This header **can be set by any JavaScript from any origin** via `fetch()` if CORS is misconfigured. The JavaScript in the views sends `Content-type: application/json` but **does not include the CSRF token** in the request body or headers.

> [!CAUTION]
> **Recommendation:** Include the CSRF token in the AJAX request headers (e.g. `X-CSRF-TOKEN`) and ensure the `VerifyCsrfToken` middleware applies to this route. Also add `$date` validation.

---

## 🟡 Medium Severity

### 4. No authentication/authorization middleware by default

**File:** [log-viewer.php config](file:///home/mastermind/Documents/php-libraries/LogViewer/config/log-viewer.php#L56-L60)

```php
'middleware' => env('ARCANEDEV_LOGVIEWER_MIDDLEWARE')
    ? explode(',', env('ARCANEDEV_LOGVIEWER_MIDDLEWARE'))
    : null,
```

When `ARCANEDEV_LOGVIEWER_MIDDLEWARE` is not set, **no middleware is applied** — meaning the log viewer is publicly accessible. This exposes sensitive application internals (stack traces, database credentials, API keys logged in errors, etc.) to anyone who can reach the endpoint.

> [!IMPORTANT]
> **Recommendation:** Default to at least `['web', 'auth']` instead of `null`. Add a comment warning users to configure this.

---

### 5. Information disclosure via file path display

**File:** [show.blade.php](file:///home/mastermind/Documents/php-libraries/LogViewer/views/bootstrap-5/show.blade.php#L56-L58)

```blade
<td>{{ $log->getPath() }}</td>
```

The absolute server file path is displayed to the user. In a production setting, this leaks internal server directory structure.

> [!NOTE]
> **Recommendation:** Consider showing only the filename or a relative path, or hide this behind a debug/admin flag.

---

### 6. Search query reflected without output encoding in input `value`

**File:** [show.blade.php](file:///home/mastermind/Documents/php-libraries/LogViewer/views/bootstrap-5/show.blade.php#L85)

```blade
<input ... value="{{ $query }}" ...>
```

While Blade's `{{ }}` does escape output, the `$query` comes directly from `$request->get('query')` with no server-side sanitization. The escaping here is adequate for the HTML attribute context, but the query is also passed to `$entries->appends(compact('query'))` which builds URL parameters — ensure this doesn't lead to open-redirect scenarios.

> [!NOTE]
> This is **low risk** because Blade escaping handles it, but adding explicit input validation would be defense-in-depth.

---

### 7. DOM XSS in logs list delete modal

**File:** [logs.blade.php](file:///home/mastermind/Documents/php-libraries/LogViewer/views/bootstrap-5/logs.blade.php#L108-L112)

```javascript
let date = event.currentTarget.getAttribute('data-log-date')
let message = "{{ __('Are you sure you want to delete this log file: :date ?') }}"
deleteLogModalElt.querySelector('.modal-body p').innerHTML = message.replace(':date', date)
```

The `date` value from the `data-log-date` HTML attribute is inserted into the DOM via `.innerHTML`. If a crafted date value containing HTML/JS were somehow placed into the attribute, it would execute. This is mitigated because the date values come from Blade-escaped output (`{{ $date }}`), but it's a fragile pattern.

> [!WARNING]
> **Recommendation:** Use `.textContent` instead of `.innerHTML`, or sanitize the `date` variable before insertion.

---

## 🟢 Low / Informational

### 8. CDN dependencies without fallback

**File:** [_master.blade.php](file:///home/mastermind/Documents/php-libraries/LogViewer/views/bootstrap-5/_master.blade.php#L11-L13)

External CDN resources (Bootstrap CSS/JS, Font Awesome, Chart.js, Google Fonts) are loaded without local fallbacks. If a CDN is compromised, scripts could be injected. The use of `integrity` attributes on Bootstrap and Chart.js mitigates this, but **Font Awesome lacks an integrity attribute**:

```html
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
```

> **Recommendation:** Add `integrity` and `crossorigin` attributes to all CDN resources, or bundle them locally.

---

### 9. Missing `Content-Security-Policy` headers

The master layout has inline `<style>` and `<script>` blocks, which would require `unsafe-inline` in a CSP. While this is the host application's responsibility, the library makes it impossible to use a strict CSP.

> **Recommendation:** Consider externalizing CSS and JS assets to allow strict CSP policies.

---

### 10. Regex Denial of Service (ReDoS) potential

**File:** [LogParser.php](file:///home/mastermind/Documents/php-libraries/LogViewer/src/Helpers/LogParser.php#L89-L108)

The parsing regex patterns are applied to potentially huge log files. While the patterns themselves are not obviously vulnerable to catastrophic backtracking, processing very large log files could still cause resource exhaustion.

> **Recommendation:** Consider adding a file-size limit check before parsing, and/or paginate the file reading at the filesystem level.

---

## Actionable Fix Priority

| Priority | Issue | Effort |
|----------|-------|--------|
| 1 | Add `$date` regex validation (path traversal) | Low |
| 2 | Add CSRF token to AJAX delete requests | Low |
| 3 | Default to `auth` middleware | Low |
| 4 | Fix innerHTML → textContent in JS | Low |
| 5 | Sanitize highlight regex patterns | Medium |
| 6 | Add SRI to all CDN links | Low |
| 7 | Hide absolute file paths | Low |
| 8 | Add log file size limits | Medium |
