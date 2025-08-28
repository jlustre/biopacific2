# How to Update a Facility in Bio-Pacific

This guide provides step-by-step instructions for updating a facility, including its contents, color settings, sections, and templates.

## 1. Update Facility Basic Information

1. Go to the admin dashboard.
2. Navigate to **Facilities**.
3. Click **Edit** next to the facility you want to update.
4. Update fields such as:
   - Name
   - Domain
   - Tagline
   - Phone, Email, Address
   - Years of Excellence, Visiting Hours, Maps, etc.
5. Click **Save** to apply changes.

## 2. Update Facility Colors

Facility colors control the look and feel of the site. You can set:

- **Primary Color** (e.g., #047857)
- **Secondary Color** (e.g., #1f2937)
- **Accent Color** (e.g., #06b6d4)

### How to Update Colors

1. In the facility edit page, locate the color fields.
2. Enter the desired hex color codes.
3. Save your changes.

## 3. Update Facility Sections

Sections are reusable content blocks (e.g., Hero, About, Contact, Footer).

### How to Update Sections

1. Go to **Layout Builder** or **Sections** in the admin dashboard.
2. Edit, add, or remove sections as needed.
3. Assign section variants or configure section content (e.g., headings, images).
4. Save your changes.

## 4. Update Facility Template

Templates define the overall layout and which sections are included.

### How to Change Template

1. Go to the facility edit page.
2. Select a template from the **Layout Template** dropdown.
3. Save your changes.

### How to Edit a Template

1. Go to **Layout Templates** in the admin dashboard.
2. Edit the template to:
   - Change included sections
   - Reorder sections
   - Set default configuration for each section
3. Save your changes.

## 5. Preview Facility

After making changes, preview the facility:

1. Go to the facility list.
2. Click **Preview** next to the facility.
3. Review the site and verify your updates.

## 6. Troubleshooting

- If you see errors like "Undefined array key", make sure all required fields are filled in.
- If colors do not appear, ensure hex codes are valid and saved.
- If a section or template is missing, check that it is active and assigned to the facility.

## 7. Advanced: Update via Database

You can also update facility data directly in the database using phpMyAdmin or Laravel Tinker:

### Example (Tinker):

```php
php artisan tinker
$facility = App\Models\Facility::find(5);
$facility->name = 'New Name';
$facility->layout_template = 'new-template';
$facility->save();
```

## 8. Reference

- **Facilities:** Manage basic info and colors
- **Sections:** Manage content blocks
- **Templates:** Manage layout and section order
- **Preview:** Review changes

---

For further help, contact your site administrator or developer.
