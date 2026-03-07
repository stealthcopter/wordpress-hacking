# Stealth Tools — API Reference

All requests are made to the **current page URL** with `?stealth_api=<endpoint>` appended.
The page URL depends on the installation mode:

- **Permalink mode**: `http://<site>/stealth/stealth.php?stealth_api=<endpoint>`
- **Direct mode**: `http://<site>/wp-content/plugins/stealth/stealth.php?page=stealth&stealth_api=<endpoint>`

All responses are `Content-Type: application/json`. Errors follow `{"error": "<message>"}` with an appropriate HTTP status code.

Filters (plugin slug visibility) are persisted in a `stealth_filters` cookie and are automatically applied to `actions`, `shortcodes`, and `routes` list responses.

---

## Endpoints

### `GET ?stealth_api=info`

Returns a full environment snapshot: WordPress version, theme, active plugins, PHP info, and syntax-highlighting keyword lists.

**Response**
```json
{
  "summary": {
    "wp_version": "6.5.0",
    "theme_name": "Twenty Twenty-Four",
    "theme_version": "1.0",
    "theme_uri": "https://wordpress.org/themes/twentytwentyfour",
    "mysql_version": "8.0.32",
    "multisite": false,
    "site_url": "http://localhost",
    "home_url": "http://localhost",
    "abspath": "/var/www/html/",
    "php_version": "8.2.0",
    "php_os": "Linux",
    "server_software": "Apache/2.4.57"
  },
  "active_plugins": [
    {
      "file": "woocommerce/woocommerce.php",
      "name": "WooCommerce",
      "version": "8.0.0",
      "plugin_uri": "https://woocommerce.com"
    }
  ],
  "phpinfo_html": "<h1>PHP Version 8.2.0</h1>...",
  "highlighting": {
    "sinks": ["echo", "print", "wp_die", "..."],
    "sources": ["$_GET", "$_POST", "..."]
  }
}
```

---

### `GET ?stealth_api=counts`

Lightweight endpoint used for navbar badge counts. Returns totals and per-slug breakdowns for actions, shortcodes, routes, and options. Also returns current filter state.

**Response**
```json
{
  "actions": 42,
  "actions_breakdown": [
    { "slug": "woocommerce", "item_type": "plugin", "count": 18 }
  ],
  "shortcodes": 7,
  "shortcodes_breakdown": [...],
  "routes": 31,
  "routes_breakdown": [...],
  "options": 214,
  "filters": { "woocommerce": true, "stealth": false }
}
```

---

### `GET ?stealth_api=actions`

Lists all registered WordPress action/AJAX hooks (filtered by current filter state).

**Response**
```json
{
  "count": 42,
  "items": {
    "<hash>": {
      "hook": "wp_ajax_nopriv_my_action",
      "action": "my_callback_function",
      "slug": "my-plugin",
      "item_type": "plugin"
    }
  }
}
```

---

### `GET ?stealth_api=actions&id=<hash>`

Returns full detail for a single action including its source code, analysis, and a ready-to-use callable URL.

**Parameters**
| Name | In    | Required | Description                        |
|------|-------|----------|------------------------------------|
| `id` | query | yes      | Hash key from the list response    |

**Response**
```json
{
  "id": "<hash>",
  "hook": "wp_ajax_nopriv_my_action",
  "action": "my_callback_function",
  "slug": "my-plugin",
  "item_type": "plugin",
  "code": "function my_callback_function() { ... }",
  "file": "/var/www/html/wp-content/plugins/my-plugin/my-plugin.php",
  "function_name": "my_callback_function",
  "lines": "42-67",
  "parameters": ["$param1", "$param2"],
  "analysis": { "sinks": ["echo"], "sources": ["$_POST"] },
  "link": "/wp-admin/admin-ajax.php?action=my_action",
  "unauthenticated": true
}
```

Notes:
- `link` is only present for `wp_ajax_*` and `admin_post_*` hooks.
- `unauthenticated: true` when the hook is `wp_ajax_nopriv_*`, `admin_post_nopriv_*`, or `init`.

---

### `GET ?stealth_api=shortcodes`

Lists all registered WordPress shortcodes (filtered by current filter state).

**Response**
```json
{
  "count": 7,
  "items": {
    "my_shortcode": {
      "tag": "my_shortcode",
      "function_name": "my_shortcode_handler",
      "slug": "my-plugin",
      "item_type": "plugin",
      "attr_count": 3
    }
  }
}
```

---

### `GET ?stealth_api=shortcodes&tag=<tag>`

Returns full detail for a single shortcode including source code and extracted attributes.

**Parameters**
| Name  | In    | Required | Description             |
|-------|-------|----------|-------------------------|
| `tag` | query | yes      | Shortcode tag name      |

**Response**
```json
{
  "tag": "my_shortcode",
  "function_name": "my_shortcode_handler",
  "attributes": ["color", "size", "link"],
  "uses_shortcode_atts": true,
  "code": {
    "slug": "my-plugin",
    "item_type": "plugin",
    "code": "function my_shortcode_handler($atts) { ... }",
    "file": "/var/www/html/wp-content/plugins/my-plugin/my-plugin.php",
    "function_name": "my_shortcode_handler",
    "lines": "10-35",
    "parameters": ["$atts", "$content"],
    "analysis": { "sinks": [], "sources": [] }
  }
}
```

---

### `GET ?stealth_api=shortcodes&tag=<tag>&attrs=1`

Returns only the attribute list for a shortcode (faster, no code blob).

**Response**
```json
{
  "tag": "my_shortcode",
  "attributes": ["color", "size", "link"],
  "uses_shortcode_atts": true
}
```

---

### `POST ?stealth_api=shortcodes_execute`

Executes a shortcode server-side and returns the rendered HTML output.

**Body** (`multipart/form-data` or `application/x-www-form-urlencoded`)
| Field       | Required | Description                                          |
|-------------|----------|------------------------------------------------------|
| `shortcode` | yes      | Base64-encoded shortcode string, e.g. `[my_shortcode color="red"]` |

**Response**
```json
{
  "shortcode": "[my_shortcode color=\"red\"]",
  "output": "<div class=\"my-widget red\">...</div>",
  "empty": false
}
```

Note: `empty: true` when the shortcode produces no output (may indicate it requires post context or is broken).

---

### `GET ?stealth_api=routes`

Lists all registered WordPress REST API routes (filtered by current filter state).

**Response**
```json
{
  "namespaces": ["wc/v3", "wp/v2"],
  "count": 31,
  "items": [
    {
      "id": 0,
      "namespace": "wc/v3",
      "route": "/wc/v3/orders",
      "method": "GET",
      "callback": "WC_REST_Orders_Controller::get_items",
      "permission_callback": "WC_REST_Orders_Controller::get_items_permissions_check",
      "no_auth": false,
      "parameters": ["context", "page", "per_page"],
      "slug": "woocommerce",
      "item_type": "plugin"
    }
  ]
}
```

Notes:
- `no_auth: true` when `permission_callback` is `none` or `__return_true` — these routes require no authentication and are prime targets.

---

### `GET ?stealth_api=routes&id=<id>`

Returns full detail for a single REST route including callback source code and permission callback source code.

**Parameters**
| Name | In    | Required | Description                          |
|------|-------|----------|--------------------------------------|
| `id` | query | yes      | Numeric index from the list response |

**Response**
```json
{
  "id": 0,
  "namespace": "wc/v3",
  "route": "/wc/v3/orders",
  "method": "GET",
  "callback": "WC_REST_Orders_Controller::get_items",
  "permission_callback": "WC_REST_Orders_Controller::get_items_permissions_check",
  "no_auth": false,
  "parameters": ["context", "page", "per_page"],
  "slug": "woocommerce",
  "item_type": "plugin",
  "callback_code": {
    "slug": "woocommerce",
    "item_type": "plugin",
    "code": "public function get_items($request) { ... }",
    "file": "/var/www/html/wp-content/plugins/woocommerce/includes/...",
    "function_name": "get_items",
    "lines": "100-200",
    "parameters": ["$request"],
    "analysis": { "sinks": [], "sources": ["$_GET"] }
  },
  "permission_code": { "...same shape as callback_code..." }
}
```

---

### `GET ?stealth_api=nonce&action=<action>`

Generates a WordPress nonce for the given action string, using the current session user.

**Parameters**
| Name     | In    | Required | Description              |
|----------|-------|----------|--------------------------|
| `action` | query | yes      | WordPress nonce action string |

**Response**
```json
{
  "action": "my_nonce_action",
  "nonce": "a1b2c3d4e5",
  "user": {
    "id": 1,
    "login": "admin",
    "roles": ["administrator"]
  }
}
```

---

### `GET ?stealth_api=users`

Returns all WordPress users, the currently authenticated user, and the current auth cookie string.

**Response**
```json
{
  "current": {
    "id": 1,
    "login": "admin",
    "roles": ["administrator"],
    "logged_in": true
  },
  "cookies": "wordpress_logged_in_abc=admin|...",
  "users": [
    { "id": 1, "login": "admin", "email": "admin@example.com", "roles": ["administrator"] },
    { "id": 2, "login": "editor", "email": "editor@example.com", "roles": ["editor"] }
  ]
}
```

---

### `POST ?stealth_api=login`

Switches the server-side session to any WordPress user without a password. Use `uid=-1` to log out.

**Body** (`multipart/form-data` or `application/x-www-form-urlencoded`)
| Field | Required | Description                                     |
|-------|----------|-------------------------------------------------|
| `uid` | yes      | WordPress user ID, or `-1` to log out           |

**Response**
```json
{
  "success": true,
  "message": "Logged in as admin",
  "user": {
    "id": 1,
    "login": "admin",
    "roles": ["administrator"]
  }
}
```

Note: This sets a real WordPress auth cookie in the response. Subsequent requests from the same browser session will be authenticated as that user.

---

### `GET ?stealth_api=options`

Returns all WordPress options from `wp_options` (the full `wp_load_alloptions()` result). Keys matching sensitive substrings (`secret`, `password`, `private_key`, `default_role`, `users_can_register`) are flagged.

**Response**
```json
{
  "count": 214,
  "items": {
    "siteurl": { "value": "http://localhost", "warning": false },
    "users_can_register": { "value": "1", "warning": true },
    "default_role": { "value": "subscriber", "warning": true }
  }
}
```

---

### `GET ?stealth_api=gadgets_info`

Returns the status of installed exploit gadgets (LFI payload, PHP object injection class).

**Response**
```json
{
  "obj_class_exists": true,
  "obj_class_code": "<?php\nclass ObjInjec { ... }",
  "lfi_payload_exists": true
}
```

---

### `POST ?stealth_api=gadgets_lfi`

Copies the bundled LFI gadget PHP file to an arbitrary path on the server (e.g. `/tmp/lfi.php`). Once installed, the file can be triggered via an LFI vulnerability in the target plugin.

**Body** (`multipart/form-data` or `application/x-www-form-urlencoded`)
| Field  | Required | Default        | Description                          |
|--------|----------|----------------|--------------------------------------|
| `path` | no       | `/tmp/lfi.php` | Destination path on the server       |

**Response**
```json
{
  "success": true,
  "path": "/tmp/lfi.php",
  "message": "LFI gadget installed to /tmp/lfi.php"
}
```

---

### `POST ?stealth_api=install`

Downloads and optionally activates a plugin or theme from wordpress.org by slug, or from a direct zip URL.

**Body** (`multipart/form-data` or `application/x-www-form-urlencoded`)
| Field      | Required | Description                                          |
|------------|----------|------------------------------------------------------|
| `type`     | yes      | `"plugin"` or `"theme"`                              |
| `slug`     | yes      | wordpress.org slug (e.g. `woocommerce`) or a zip URL |
| `activate` | no       | Any truthy value to activate after install           |

**Response**
```json
{
  "success": true,
  "type": "plugin",
  "slug": "woocommerce",
  "steps": [
    { "label": "Install", "success": true, "message": "Plugin installed successfully." },
    { "label": "Activate", "success": true, "message": "Plugin activated." }
  ],
  "info": {
    "name": "WooCommerce",
    "slug": "woocommerce",
    "version": "8.0.0",
    "installs": 5000000,
    "updated": "2024-01-15",
    "url": "https://wordpress.org/plugins/woocommerce",
    "icon": "https://ps.w.org/woocommerce/assets/icon-128x128.png"
  }
}
```

---

### `GET ?stealth_api=filters`

Returns the list of available plugin/theme slugs and their current enabled state (read from the `stealth_filters` cookie).

**Response**
```json
{
  "available": ["woocommerce", "my-plugin", "stealth"],
  "state": { "woocommerce": true, "my-plugin": true, "stealth": false }
}
```

---

### `POST ?stealth_api=filters`

Saves a new filter state. Writes the `stealth_filters` cookie so that subsequent `actions`, `shortcodes`, and `routes` responses reflect the updated visibility.

**Body** (`application/json`)
```json
{ "woocommerce": true, "my-plugin": false }
```

Unknown slugs are ignored. Missing slugs default to `true` (enabled).

**Response** — same shape as `GET ?stealth_api=filters`.

---

### `GET ?stealth_api=highlighting`

Returns the sink and source keyword lists used for Prism syntax highlighting. Loaded once at startup by the frontend.

**Response**
```json
{
  "sinks":   ["echo", "print", "wp_die", "system", "eval"],
  "sources": ["$_GET", "$_POST", "$_REQUEST", "$_COOKIE"]
}
```

---

### `POST ?stealth_api=upload_echo`

Accepts a multipart file upload and echoes back the parsed `$_FILES` metadata without saving anything to disk. Used to inspect how the server handles uploads (MIME type, tmp path, size).

**Body** (`multipart/form-data`)

Any file field(s).

**Response**
```json
{
  "count": 1,
  "files": [
    {
      "field": "file",
      "name": "shell.php",
      "type": "application/x-php",
      "tmp_name": "/tmp/phpXXXXXX",
      "error": 0,
      "size": 512
    }
  ],
  "post": {}
}
```

---

## Common Response Shapes

### `callback_code` / `permission_code` object

Returned in action detail and route detail responses.

```json
{
  "slug": "my-plugin",
  "item_type": "plugin",
  "code": "function my_func($param) { ... }",
  "file": "/var/www/html/wp-content/plugins/my-plugin/my-plugin.php",
  "function_name": "my_func",
  "lines": "42-67",
  "parameters": ["$param"],
  "analysis": {
    "sinks": ["echo"],
    "sources": ["$_POST"]
  }
}
```

### Error response

```json
{ "error": "Description of what went wrong" }
```

HTTP status codes used: `400` (bad params), `404` (not found), `405` (wrong method), `500` (server error).
