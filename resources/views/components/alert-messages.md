# Alert Message Components

Reusable alert message components for displaying success, error, warning, and info messages throughout the application.

## Components Available

### AlertMessage (Base Component)

- `x-alert-message` - Main component with full customization options

### Convenience Components

- `x-success-message` - Pre-configured for success messages (dismissible by default)
- `x-error-message` - Pre-configured for error messages (dismissible by default)

## Usage

### Basic Usage

```blade
<x-success-message>
    Your application has been submitted successfully!
</x-success-message>

<x-error-message>
    Please fix the validation errors below.
</x-error-message>
```

### With Dismissible Option

```blade
<x-success-message dismissible="true">
    Settings saved successfully!
</x-success-message>

<x-error-message dismissible="true">
    An error occurred while processing your request.
</x-error-message>
```

### With Title

```blade
<x-success-message title="Success!" dismissible="true">
    Your changes have been saved.
</x-success-message>

<x-error-message title="Validation Error">
    Please check the following fields and try again.
</x-error-message>
```

## AlertMessage Props

| Prop            | Type    | Default     | Description                                         |
| --------------- | ------- | ----------- | --------------------------------------------------- |
| `type`          | string  | `'info'`    | Alert type (success, error, warning, info, primary) |
| `dismissible`   | boolean | `false`     | Whether the alert can be dismissed                  |
| `icon`          | string  | `null`      | Custom FontAwesome icon class                       |
| `title`         | string  | `null`      | Optional title for the alert                        |
| `primary`       | string  | `'#3B82F6'` | Primary color (for primary type)                    |
| `secondary`     | string  | `'#1E40AF'` | Secondary color                                     |
| `accent`        | string  | `'#6366F1'` | Accent color                                        |
| `neutral_dark`  | string  | `'#374151'` | Dark neutral color                                  |
| `neutral_light` | string  | `'#F3F4F6'` | Light neutral color                                 |

## Alert Types

### Success

- Green background and border
- Check circle icon
- Used for successful operations

### Error

- Red background and border
- Exclamation circle icon
- Used for errors and failures

### Warning

- Yellow background and border
- Exclamation triangle icon
- Used for warnings and cautions

### Info

- Blue background and border
- Info circle icon
- Used for informational messages

### Primary

- Uses facility's primary color
- Adapts to branding
- Good for branded notifications

## Advanced Examples

### Custom Alert with All Options

```blade
<x-alert-message
    type="warning"
    title="Important Notice"
    icon="fas fa-bell"
    dismissible="true"
    class="mb-4">
    This is a custom warning message with all options.
</x-alert-message>
```

### Primary Alert with Facility Colors

```blade
<x-alert-message
    type="primary"
    :primary="$primary"
    :secondary="$secondary"
    title="Facility Notification"
    dismissible="true">
    This alert uses your facility's branding colors.
</x-alert-message>
```

### Conditional Alerts in Livewire

```blade
@if($successMessage)
    <x-success-message
        id="success-message"
        wire:key="success-{{ now() }}"
        dismissible="true">
        {{ $successMessage }}
    </x-success-message>
@endif

@if($errorMessage)
    <x-error-message dismissible="true">
        {{ $errorMessage }}
    </x-error-message>
@endif
```

### Integration Examples

#### Laravel Flash Messages

```blade
@if(session('success'))
    <x-success-message dismissible="true">
        {{ session('success') }}
    </x-success-message>
@endif

@if(session('error'))
    <x-error-message dismissible="true">
        {{ session('error') }}
    </x-error-message>
@endif
```

#### Laravel Validation Errors

```blade
@if($errors->any())
    <x-error-message dismissible="true">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-error-message>
@endif
```

## Features

- **Responsive Design**: Works on all screen sizes
- **Smooth Animations**: Fade in/out transitions
- **Alpine.js Integration**: Dismissible functionality
- **Icon Support**: FontAwesome icons included
- **Accessibility**: Proper ARIA labels and keyboard support
- **Customizable**: Override colors, icons, and styling
- **Wire Key Support**: Works with Livewire key attributes

## Notes

- The `dismissible` option requires Alpine.js to be loaded
- Custom icons should be FontAwesome classes
- The `primary` type adapts to facility branding colors
- All standard HTML attributes can be passed through
- Components automatically handle spacing and layout
