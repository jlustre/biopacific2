# Developer Guide: Managing Facilities in Bio-Pacific

This guide provides detailed instructions for developers on how to manage and extend facility features, including contents, colors, sections, templates, and troubleshooting.

## 1. Facility Model & Database

- **Model:** `app/Models/Facility.php`
- **Migration:** `database/migrations/*_create_facilities_table.php`
- **Fields:**
  - `name`, `domain`, `tagline`, `phone`, `email`, `address`, `years`, `hours`, `maps`, `layout_template`, `layout_config`, color fields, etc.
- **Relationships:**
  - Facilities may relate to users, templates, sections, etc.

## 2. Updating Facility Data

- Use Eloquent ORM for CRUD operations:

```php
$facility = Facility::find($id);
$facility->update([...]);
```

- Use form requests for validation in controllers.
- Update via Tinker for quick changes:

```php
php artisan tinker
$facility = App\Models\Facility::find(5);
$facility->tagline = 'New tagline';
$facility->save();
```

## 3. Color Management

- Colors are typically stored as hex codes in the facility record.
- Pass colors to Blade views as `$colors` array:

```php
$colors = [
  'primary' => $facility->primary_color ?? '#047857',
  'secondary' => $facility->secondary_color ?? '#1f2937',
  'accent' => $facility->accent_color ?? '#06b6d4',
];
return view('layouts.default-template', compact('facility', 'colors'));
```

- Use default values in Blade to avoid errors.

## 4. Sections & Templates

- **Sections:**
  - Blade files in `resources/views/partials/`
  - Configurable via `LayoutSection` model and admin UI.
  - Use variants and config arrays for flexibility.
- **Templates:**
  - Defined in `LayoutTemplate` model and `layout_templates` table.
  - `sections` field (array/JSON) lists included section slugs.
  - Blade layouts in `resources/views/layouts/`
  - Assign templates to facilities via `layout_template` field.

## 5. Passing Data to Views

- Always pass required data to Blade views to avoid undefined errors.
- Use null coalescing (`??`) for safe access in Blade:

```blade
{{ $facility['tagline'] ?? 'Default tagline' }}
```

- For arrays, use defaults for missing keys.

## 6. Error Handling & Debugging

- Use try/catch in controllers for robust error handling.
- Log errors with `Log::error()` for backend issues.
- Use Laravel's built-in error pages for debugging.
- Check browser dev tools for frontend AJAX errors.

## 7. Extending Functionality

- Add new fields to the facility model and migration as needed.
- Create new sections by adding Blade files and updating the admin UI.
- Add new templates by creating Blade layouts and updating the `LayoutTemplate` model.
- Use Livewire or Vue for dynamic admin features if needed.

## 8. Testing

- Write unit and feature tests in `tests/Feature` and `tests/Unit`.
- Use factories for test data (`database/factories/UserFactory.php`, etc.).
- Run tests with:

```shell
php artisan test
```

## 9. Deployment & Maintenance

- Run migrations after schema changes:

```shell
php artisan migrate
```

- Clear caches after updates:

```shell
php artisan config:cache
php artisan view:clear
```

- Document changes in `FEATURES.md` or similar files.

## 10. Reference

- **Models:** `Facility`, `LayoutTemplate`, `LayoutSection`
- **Views:** `resources/views/layouts/`, `resources/views/partials/`
- **Controllers:** `app/Http/Controllers/Admin/`
- **Migrations:** `database/migrations/`
- **Tests:** `tests/`

---

For advanced customization, follow Laravel best practices and keep code modular. For questions, consult the Laravel documentation or your team lead.
