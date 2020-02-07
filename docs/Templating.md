Templating
==========

1. [Predefined Specials](#predefined-specials)

Predefined Specials
-------------------

These must be set to `true` to be active.

```php
// For example:
$template->addSpecial('is-focus-page', true);
```

- **`is-focus-page`**: Header is smaller and has only a logo-to-home link
- **`is-funnel-page`**: Header is the same as in `is-focus-page`, but the logo is just a logo, not a link.
- **`is-minimal-page`**: No header and footer with just translation links (easy to remove by editing `footer.minimal` template)
