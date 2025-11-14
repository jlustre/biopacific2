# Developer Guide: Managing Facilities in Bio-Pacific

This guide provides detailed instructions for developers on how to manage and extend facility features, including contents, colors, sections, templates, events, news, and troubleshooting.

This guide provides detailed instructions for developers on how to manage and extend facility features, including contents, colors, sections, templates, and troubleshooting.

## 1. Facility Model & Database

**Models:**

- `app/Models/Facility.php` (core facility data)
- `app/Models/Event.php` (facility/company events)
- `app/Models/News.php` (facility/company news)

**Migrations:**

- `database/migrations/*_create_facilities_table.php`
- `database/migrations/2025_09_28_192406_create_events_table.php` (events)

**Fields:**

- Facility: `name`, `domain`, `tagline`, `phone`, `email`, `address`, `years`, `hours`, `maps`, `layout_template`, `layout_config`, color fields, etc.
- Event: `title`, `description`, `event_date`, `location`, `status`, `facility_id`, `scope`
- News: `title`, `content`, `published_at`, `status`, `facility_id`, `is_global`

**Relationships:**

- Facilities relate to users, templates, sections, events, news, etc.

## 2. Updating Facility Data

Use Eloquent ORM for CRUD operations:

```php
$facility = Facility::find($id);
$facility->update([...]);
$event = Event::create([...]);
$news = News::create([...]);
```

Use form requests for validation in controllers.
Update via Tinker for quick changes:

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

**Sections:**

- Blade files in `resources/views/partials/`
- Configurable via `LayoutSection` model and admin UI.
- Use variants and config arrays for flexibility.

**Templates:**

- Defined in `LayoutTemplate` model and `layout_templates` table.
- `sections` field (array/JSON) lists included section slugs.
- Blade layouts in `resources/views/layouts/`
- Assign templates to facilities via `layout_template` field.

**Events & News:**

- Events and news are managed via their respective models and admin UI tabs.
- See `resources/views/admin/facilities/edit-tabs/news.blade.php` for news management.
- See `database/seeders/NewsSeeder.php` and `database/seeders/FacilitySeeder.php` for sample data.

## 5. Passing Data to Views

- Always pass required data to Blade views to avoid undefined errors.
- Use null coalescing (`??`) for safe access in Blade:

```blade
{{ $facility['tagline'] ?? 'Default tagline' }}
```

- For arrays, use defaults for missing keys.

## 6. Error Handling & Debugging

## 7. Extending Functionality

**To add events/news modules:**

- Create migrations and models for new entities (see `Event`, `News`).
- Add admin UI tabs for managing new content types.
- Update seeders for initial data population.

## 8. Testing

```shell
php artisan test
```

**Testing tips:**

- Test multi-tenant isolation for facilities, events, and news.
- Use Pest and PHPUnit for feature/unit tests.

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
