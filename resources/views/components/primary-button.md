# PrimaryButton Component

A reusable button component that adapts to facility branding colors throughout the application.

## Color System

The component uses the facility's color scheme which is determined by the `color_scheme_id` in the facilities table. The controller automatically provides these variables in views:

- `$primary` - Primary brand color
- `$secondary` - Secondary brand color
- `$accent` - Accent color
- `$neutral_dark` - Dark neutral color
- `$neutral_light` - Light neutral color

## Usage

```blade
<x-primary-button>
    Click Me
</x-primary-button>
```

## Props

| Prop            | Type    | Default        | Description                         |
| --------------- | ------- | -------------- | ----------------------------------- |
| `type`          | string  | `'button'`     | Button type (button, submit, reset) |
| `size`          | string  | `'md'`         | Button size (xs, sm, md, lg, xl)    |
| `disabled`      | boolean | `false`        | Whether the button is disabled      |
| `loading`       | boolean | `false`        | Whether to show loading state       |
| `loadingText`   | string  | `'Loading...'` | Text to show when loading           |
| `icon`          | string  | `null`         | FontAwesome icon class              |
| `iconPosition`  | string  | `'left'`       | Icon position (left, right)         |
| `primary`       | string  | `'#3B82F6'`    | Primary color                       |
| `secondary`     | string  | `'#1E40AF'`    | Secondary color                     |
| `accent`        | string  | `'#6366F1'`    | Accent color                        |
| `neutral_dark`  | string  | `'#374151'`    | Dark neutral color                  |
| `neutral_light` | string  | `'#F3F4F6'`    | Light neutral color                 |

## Examples

### Basic Button

```blade
<x-primary-button>
    Save Changes
</x-primary-button>
```

### Submit Button with Loading

```blade
<x-primary-button
    type="submit"
    :loading="$isSubmitting"
    loading-text="Saving...">
    Save Changes
</x-primary-button>
```

### Button with Icon

```blade
<x-primary-button
    icon="fas fa-download"
    size="lg">
    Download File
</x-primary-button>
```

### Button with Custom Colors

```blade
<x-primary-button
    :primary="$primary"
    :secondary="$secondary"
    :accent="$accent"
    size="lg">
    Apply Now
</x-primary-button>
```

### Button with All Color Variables

```blade
<x-primary-button
    :primary="$primary"
    :secondary="$secondary"
    :accent="$accent"
    :neutral_dark="$neutral_dark"
    :neutral_light="$neutral_light"
    size="md">
    Complete Action
</x-primary-button>
```

### With Alpine.js

```blade
<x-primary-button
    @click="openModal = true"
    size="sm">
    Open Modal
</x-primary-button>
```

### Disabled Button

```blade
<x-primary-button
    :disabled="!$canSubmit">
    Submit Form
</x-primary-button>
```

## Size Classes

- `xs`: Very small button
- `sm`: Small button (good for cards)
- `md`: Default medium size
- `lg`: Large button (good for forms)
- `xl`: Extra large button (good for CTAs)

## Notes

- The component automatically handles hover effects (gradient reversal)
- Focus states include proper ring styling
- Loading state replaces content with spinner
- Colors are passed from the controller based on facility's `color_scheme_id`
- The `$primary`, `$secondary`, and `$accent` variables are available in views that have facility context
- All standard button attributes can be passed through
- Alpine.js directives like `@click` work seamlessly with the component
