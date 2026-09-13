# Projekt-Anweisungen für Codex

## Arbeitsweise
- Antworte auf Deutsch.
- Arbeite in kleinen, nachvollziehbaren Schritten.
- Ändere nur Code, der für die Aufgabe nötig ist.
- Erkläre kurz, welche Dateien geändert wurden und warum.
- Keine unnötigen Refactorings nebenbei.

## Tests
- Führe passende lokale Tests aus, bevor du die Aufgabe abschließt.
- Wenn möglich, verwende dieselben Befehle wie in GitHub Actions.
- Nenne am Ende exakt:
  - welche Tests/Lints ausgeführt wurden
  - ob sie erfolgreich waren
  - falls nicht ausführbar: warum nicht

## GitHub Actions
- Prüfe `.github/workflows/*`, um die relevanten lokalen Befehle abzuleiten.
- Wenn lokale Testbefehle fehlen oder unklar sind, schlage eine dokumentierte lokale Variante vor.

## WordPress-Plugin-Regeln

### Standards
- Halte die WordPress Coding Standards für PHP, JS, CSS und HTML ein.
- Verwende WordPress-APIs statt eigener Lösungen, z. B. Settings API, Options API, HTTP API, Transients API, WP-Cron, REST API.
- Kein direkter Zugriff auf `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_COOKIE` ohne `wp_unslash()` und passende Sanitization.

### Security
- Alle Eingaben validieren und sanitizen.
- Alle Ausgaben kontextabhängig escapen:
  - HTML: `esc_html()`
  - Attribute: `esc_attr()`
  - URLs: `esc_url()`
  - Textarea: `esc_textarea()`
  - JavaScript-Daten: `wp_json_encode()`
- Nonces bei Admin-Aktionen, Formularen und AJAX verwenden.
- Capabilities prüfen, z. B. `current_user_can()`.
- SQL nur mit `$wpdb->prepare()`.
- Keine ungeprüften Redirects; `wp_safe_redirect()` verwenden.

### Internationalisierung
- Keine hardcodierten sichtbaren UI-Texte.
- WordPress-i18n-Funktionen verwenden:
  - `__()`
  - `_e()`
  - `esc_html__()`
  - `esc_html_e()`
  - `esc_attr__()`
  - `esc_attr_e()`
- Immer die Plugin-Textdomain verwenden.
- Übersetzbare Strings nicht dynamisch zusammensetzen; Platzhalter mit `sprintf()` nutzen.

### PHPDoc
- Öffentliche Klassen, Methoden, Hooks, Filter und komplexe Arrays dokumentieren.
- Für Hooks immer Zweck, Parameter und Rückgabewert dokumentieren.
- Bei Arrays möglichst Shape/Struktur beschreiben.

### WordPress-Architektur
- Keine Logik direkt in Template-/View-Dateien, wenn sie in Services/Klassen gehört.
- Hooks zentral und nachvollziehbar registrieren.
- Aktivierung, Deaktivierung und Uninstall sauber trennen.
- Keine Daten beim Deaktivieren löschen; nur bei explizitem Uninstall.
- Datenbankänderungen versionieren.
- Prefixe oder Namespaces verwenden, um Kollisionen mit anderen Plugins zu vermeiden.

### Assets
- CSS/JS nur über `wp_enqueue_style()` und `wp_enqueue_script()` laden.
- Assets nur dort laden, wo sie gebraucht werden.
- Dependencies und Versionen korrekt setzen.
- Inline-Daten über `wp_add_inline_script()` oder passende WordPress-Mechanismen übergeben.

### Abschluss jeder Aufgabe
- Prüfe geänderten Code auf:
  - WordPress Coding Standards
  - Escaping/Sanitizing
  - Nonces und Capabilities
  - Übersetzbarkeit
  - PHPDoc
  - passende Tests oder lokale Checks
- Nenne am Ende konkret, welche Checks ausgeführt wurden.
