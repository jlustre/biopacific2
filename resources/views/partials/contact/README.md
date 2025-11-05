# Reusable Contact Form Component

This document explains how to use the reusable contact form component across different contact page variants.

## File Location

`resources/views/partials/contact/contact-form.blade.php`

## Usage

### Basic Usage

```blade
@include('partials.contact.contact-form', [
    'facility' => $facility,
    'primary' => $primary,
    'secondary' => $secondary,
    'accent' => $accent,
    'neutral_dark' => $neutral_dark ?? '#1e293b'
])
```

### Parameters

| Parameter      | Type         | Required | Description                                        | Default   |
| -------------- | ------------ | -------- | -------------------------------------------------- | --------- |
| `facility`     | Array/Object | Yes      | Facility data including id, name, slug, and colors | -         |
| `primary`      | String       | No       | Primary color for styling                          | `#0EA5E9` |
| `secondary`    | String       | No       | Secondary color for styling                        | `#155E75` |
| `accent`       | String       | No       | Accent color for styling                           | `#FFFFFF` |
| `neutral_dark` | String       | No       | Dark neutral color for text                        | `#1e293b` |

### Required Facility Data Structure

```php
$facility = [
    'id' => 1,                    // Required for form submission
    'name' => 'Facility Name',    // Used in email notifications
    'slug' => 'facility-slug',    // Used for privacy policy link
    'primary_color' => '#0EA5E9', // Optional, falls back to default
    // ... other facility data
];
```

## Features Included

### 1. Form Validation

- Client-side and server-side validation
- Real-time error display
- Form field persistence on validation errors

### 2. Security Features

- CSRF protection via `@csrf`
- Honeypot field for bot detection
- PHI (Protected Health Information) confirmation

### 3. User Experience

- Loading states during submission
- Success/error message display
- Form reset functionality
- Responsive design

### 4. Accessibility

- Proper label associations
- Required field indicators
- Error message announcements
- Keyboard navigation support

## Form Fields

1. **Full Name** (required)
2. **Email Address** (required)
3. **Phone Number** (optional)
4. **Message** (required)
5. **Consent Checkbox** (required)
6. **PHI Confirmation** (required)

## Backend Integration

### Controller

The form submits to `ContactController@submit` which:

1. Validates the input
2. Saves to `inquiries` table
3. Sends email notifications using `FacilityDataHelper`
4. Returns success/error responses

### Database

Data is stored in the `inquiries` table with the following fields:

- `facility_id`
- `full_name`
- `email`
- `phone`
- `message`
- `consent`
- `no_phi`
- `recipient` (set to 'inquiry')

### Email Notifications

Uses the existing email routing system:

- Queries `email_recipients` table for facility-specific recipients
- Falls back to `employee_email_mappings` if no recipients found
- Sends via `ContactMail` mailable class

## Examples

### Contact Page Variant 1 (Side-by-side layout)

```blade
{{-- contact1.blade.php --}}
<div class="grid gap-8 md:grid-cols-3 items-start">
    <!-- Contact Info -->
    <aside>...</aside>

    <!-- Contact Form -->
    @include('partials.contact.contact-form', [
        'facility' => $facility,
        'primary' => $primary,
        'secondary' => $secondary,
        'accent' => $accent
    ])

    <!-- Map -->
    <div>...</div>
</div>
```

### Contact Page Variant 2 (Stacked layout)

```blade
{{-- contact2.blade.php --}}
<div class="max-w-2xl mx-auto">
    @include('partials.contact.contact-form', [
        'facility' => $facility,
        'primary' => $primary,
        'secondary' => $secondary,
        'accent' => $accent
    ])
</div>
```

### Contact Page Variant 3 (Modal/overlay)

```blade
{{-- contact3.blade.php --}}
<div class="modal-content">
    @include('partials.contact.contact-form', [
        'facility' => $facility,
        'primary' => $primary,
        'secondary' => $secondary,
        'accent' => $accent
    ])
</div>
```

## Customization

### Styling

The form uses inline styles with facility colors and Tailwind CSS classes. Colors are dynamically applied based on the facility's color scheme.

### Validation Messages

Custom validation messages can be added to the controller's validation rules.

### Email Templates

The form uses the existing `emails.contact_form_email` template. Customize this template to change email formatting.

## Dependencies

- Laravel validation
- `FacilityDataHelper` class
- `ContactMail` mailable
- `Inquiry` model
- Tailwind CSS for styling

## Notes

- The form automatically includes CSRF protection
- All submissions are logged for debugging
- The form is fully responsive and accessible
- PHI warning is mandatory for healthcare compliance
- Success/error states persist across page reloads
