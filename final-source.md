````markdown
## Project Goal

Build a **single-page PHP** application that allows traders to **define and save one trading strategy** in MySQL. No dashboards or stats yet—just capture:

- **Name** (string)
- **Instrument** (string, e.g., EURUSD)
- **Timeframes** (multi-select array stored as JSON)
- **Sessions** (multi-select array stored as JSON)
- **Entry Rules** (list of strings stored as JSON)
- **Exit Rules** (list of strings stored as JSON)

Use **Tailwind CSS** (via CDN) for styling, **PDO** for secure MySQL access, and store arrays in JSON columns. Provide inline validation, CSRF protection, and success/error alerts.

---

## Tech Stack & Dependencies

- **PHP 8+**  
- **MySQL 5.7+** with JSON support  
- **Tailwind CSS** via `<script src="https://cdn.tailwindcss.com"></script>`  
- **PDO** extension enabled in `php.ini`  
- Web server (Apache or Nginx) configured for PHP  

---

## Database Schema

```sql
CREATE TABLE strategy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  instrument VARCHAR(50) NOT NULL,
  timeframes JSON NOT NULL,
  sessions JSON NOT NULL,
  entry_rules TEXT NOT NULL,
  exit_rules TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
````

---

## File Structure

```
/project-root
│ index.php         ← single entry point
│ /assets
│   tailwind.css    ← optional if using CDN
│ /templates
│   form.php        ← HTML form markup
│ /includes
│   db.php          ← PDO connection setup
│   csrf.php        ← CSRF token functions
│   functions.php   ← validation & helpers
```

---

## index.php (High-Level Flow)

1. `require 'includes/db.php'`
2. `require 'includes/csrf.php'`
3. If POST:

   * Verify CSRF token
   * Sanitize & validate inputs (strings, JSON arrays)
   * Encode arrays: `json_encode($_POST['timeframes'])` etc.
   * `try { $stmt = $pdo->prepare(...); $stmt->execute([...]); } catch { log error; show generic message; }`
   * Redirect on success with `?success=1`; render form otherwise
4. Render `templates/form.php`, injecting old values, error messages, and success alert

---

## Key Code Snippets

### PDO Connection (`includes/db.php`)

```php
<?php
$pdo = new PDO(
  'mysql:host=localhost;dbname=trading;charset=utf8mb4',
  'dbuser',
  'dbpass',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
?>
```

### CSRF Utilities (`includes/csrf.php`)

```php
<?php
session_start();
function csrf_token() {
  if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function verify_csrf($token) {
  return hash_equals($_SESSION['csrf'] ?? '', $token);
}
?>
```

### Form Markup (`templates/form.php`)

```html
<form method="POST" class="max-w-lg mx-auto p-4">
  <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">
  <label class="block mb-2">Strategy Name *</label>
  <input type="text" name="name" value="<?=htmlspecialchars($old['name']??'')?>" class="w-full border p-2 mb-4" required>
  
  <!-- Instrument, Timeframes (multi-select), Sessions, Entry/Exit Rules (textareas or dynamic inputs) -->
  
  <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Save Strategy</button>
</form>
```

---

## Acceptance Criteria

* Form saves valid data to MySQL using PDO prepared statements.
* Inline validation errors display properly; success alert appears on `?success=1`.
* Tailwind-based styling loads via CDN; responsive on mobile/desktop.
* CSRF token verified on each submission; any mismatch blocks save.

---


Here’s a **comprehensive PRD** for your single-page PHP strategy definition tool, followed by a ready-to-use `php-final-source.md` prompt that glues everything together.

---

## Summary

This PRD defines a lightweight, **single-page PHP** application that lets a trader **define and save one strategy** in MySQL, capturing name, instrument, selectable timeframes, sessions, and entry/exit rules in JSON columns for flexible storage ([200OK Solutions][1], [Medium][2]). It uses **PDO** with prepared statements for secure database access ([200OK Solutions][1]) and **Tailwind CSS** (via CDN) for rapid, utility-first styling, deferring statistical analytics to future phases ([GeeksforGeeks][3]).

---

## Purpose & Scope

* **Objective:** Enable traders to **quickly define** a single strategy through one HTML form and persist it in MySQL, without building full analytics yet ([Stack Overflow][4]).
* **Scope:**

  * Fields: Strategy Name, Instrument, Timeframes (multi-select), Sessions (multi-select), Entry Rules, Exit Rules
  * Single-page form with POST action
  * Data stored in a `strategy` table with JSON columns for arrays ([Medium][2]).

---

## Functional Requirements

1. **Form UI**

   * Render a single HTML form with inputs for all strategy fields, styled exclusively with **Tailwind CSS** utilities ([GeeksforGeeks][3]).
2. **Data Submission**

   * On submit, PHP script encodes array inputs as JSON and uses **PDO** to INSERT or UPDATE the `strategy` record ([200OK Solutions][1]).
3. **User Feedback**

   * On success, redirect back with a **Tailwind**-styled success alert; on failure, show **inline validation errors** next to each field ([Nielsen Norman Group][5]).
4. **Single Strategy Only**

   * Only one row exists; subsequent submissions overwrite existing data.

---

## Data Model

```sql
CREATE TABLE strategy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  instrument VARCHAR(50) NOT NULL,
  timeframes JSON NOT NULL,
  sessions JSON NOT NULL,
  entry_rules TEXT NOT NULL,
  exit_rules TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

* **JSON columns** auto-validate data and store arrays efficiently ([Medium][2]).
* Avoid overusing JSON for highly relational data, but here it simplifies variable-length rule lists ([Stack Overflow][6]).

---

## UX/UI Guidelines

* **Labels Outside Fields:** Always show labels above or beside inputs; avoid placeholder-only labels ([Nielsen Norman Group][7]).
* **Inline Validation:** Validate on blur; show concise, color-contrasted error messages next to fields using red text and icons ([Nielsen Norman Group][5], [Nielsen Norman Group][8]).
* **Responsive Design:** Form lays out vertically on mobile, two-column on wider screens, using Tailwind grid utilities ([YouTube][9]).
* **Accessibility:** Ensure focus states, ARIA labels on error messages, and keyboard-only navigation ([Nielsen Norman Group][10]).

---

## Technical Architecture

* **Front-end:** Plain PHP scripts rendered by Apache/Nginx; Tailwind CSS pulled via CDN for MVP speed ([GeeksforGeeks][3]).
* **Back-end:** PHP 8+ with **PDO** configured for `PDO::ERRMODE_EXCEPTION` and **prepared statements** for all SQL ([200OK Solutions][1]).
* **Error Handling:** Wrap DB calls in `try`/`catch`, log exceptions server-side, and display generic “Something went wrong” messages client-side ([200OK Solutions][1]).

---

## Security

* **SQL Injection:** 100% use of PDO prepared statements with named or positional parameters ([Stack Overflow][11]).
* **Input Sanitization:** Validate JSON arrays with `json_decode()` in PHP and `JSON_VALID()` in MySQL if needed ([Medium][2]).
* **CSRF Protection:** Include a hidden CSRF token in the form, stored in session and verified on POST ([Nielsen Norman Group][7]).
* **HTTPS & Headers:** Enforce HTTPS, HSTS, `X-Content-Type-Options: nosniff`, and other OWASP-recommended headers ([Nielsen Norman Group][8]).

---

## DevOps & Deployment

* **Environment:** LAMP stack on managed VPS or shared hosting, PHP 8+, MySQL 5.7+ ([PHP: The Right Way][12]).
* **Tailwind Build:** Use the **CDN** for MVP; later migrate to local CLI build with `npx tailwindcss` and a `tailwind.config.js` pointing to `*.php` templates ([Stack Overflow][4]).
* **Backups:** Automated daily MySQL dumps stored offsite; retain 4 weeks of backups ([Medium][2]).

---

## Timeline (3 Weeks)

| Week | Deliverable                                                               |
| ---- | ------------------------------------------------------------------------- |
| 1    | Project setup: PHP skeleton, Tailwind CDN, PDO connection, DB migration   |
| 2    | Build HTML form + PHP POST handler + inline validation + success/error UX |
| 3    | Polish responsive layout, accessibility tweaks, deploy to staging & test  |

---

## Acceptance Criteria

* ✅ **Data Persistence:** Submitting valid form saves JSON-encoded arrays to MySQL without errors ([200OK Solutions][1]).
* ✅ **Validation:** Invalid or missing inputs trigger inline error messages and prevent DB writes ([Nielsen Norman Group][5]).
* ✅ **Styling:** Form and alerts match Tailwind utility-first styling, responsive on mobile and desktop ([GeeksforGeeks][3]).
* ✅ **Security:** All inputs processed via PDO prepared statements; CSRF token verified on each POST ([Stack Overflow][11]).

---



