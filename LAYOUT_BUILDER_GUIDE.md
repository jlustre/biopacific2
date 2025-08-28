# Layout Builder - User Guide

## Overview

The Layout Builder allows you to visually design and customize facility layouts by selecting sections, reordering them, and changing their variants.

## Getting Started

### 1. Access the Layout Builder

- Navigate to **Admin Dashboard** → **Layout Builder**
- Or use the direct URL: `http://127.0.0.1:8000/admin/layout-builder`

### 2. Select a Facility

- Use the dropdown to select which facility you want to customize
- The system will load the current layout template and sections

### 3. Customize Your Layout

#### **Reorder Sections**

- Drag sections by the grip handle (⋮⋮) to reorder them
- The preview will show the new order

#### **Change Section Variants**

- Use the variant dropdown on each section to change its style
- Available variants differ by section type:
  - **Hero**: `default`, `split`
  - **About**: `default`, `default2`
  - **Services**: `default`, `grid`
  - **Contact**: `form`, `info`

#### **Add/Remove Sections**

- **Add**: Click the (+) button on sections from the right sidebar
- **Remove**: Click the trash icon (🗑️) on any section

### 4. Preview Your Changes

- Click **Preview Layout** to see your design in a new window
- The preview shows all sections with your current configuration

### 5. Save Your Work

#### **Save Changes**

- Click **Save Changes** to apply modifications to the facility
- This updates the facility's layout configuration

#### **Create New Template**

- Click **Save as Template** to create a reusable template
- Option to apply the new template to the current facility
- Useful for applying the same design to multiple facilities

#### **Duplicate Layout**

- Click **Duplicate Layout** to create a copy of the current layout
- Creates a new template without modifying the original

### 6. Reset if Needed

- Click **Reset to Original** to undo all unsaved changes
- This restores the layout to its last saved state

## Section Types Available

| Section          | Variants              | Description                   |
| ---------------- | --------------------- | ----------------------------- |
| **Hero**         | `default`, `split`    | Main banner area              |
| **About**        | `default`, `default2` | About/company information     |
| **Services**     | `default`, `grid`     | Services and amenities        |
| **Contact**      | `form`, `info`        | Contact information and forms |
| **Testimonials** | `default`, `carousel` | Customer testimonials         |
| **Footer**       | `default`, `minimal`  | Website footer                |

## Tips & Best Practices

### **Design Flow**

1. Start with a facility that has a good base template
2. Reorder sections to match your content flow
3. Choose appropriate variants for each section
4. Preview frequently to see your changes
5. Save as template for reuse across facilities

### **Section Order Recommendations**

- **Hero** → **About** → **Services** → **Testimonials** → **Contact** → **Footer**
- Always keep Hero first and Footer last
- Place Contact section near the end for better conversion

### **Variant Selection**

- **Hero Split**: Good for facilities with strong imagery
- **Services Grid**: Better for facilities with many services
- **Contact Form**: For lead generation
- **Contact Info**: For simple contact display

## Troubleshooting

### **Layout Not Loading**

- Ensure the facility has a layout template assigned
- Check that layout sections are properly seeded in the database

### **Section Not Displaying**

- Verify the section's partial file exists in `resources/views/partials/`
- Check if the variant is properly configured

### **Preview Issues**

- Ensure all required partial templates exist
- Check browser console for JavaScript errors

## Technical Details

### **File Locations**

- **Controller**: `app/Http/Controllers/Admin/LayoutBuilderController.php`
- **Views**: `resources/views/admin/layout-builder/`
- **Partials**: `resources/views/partials/[section]/[variant].blade.php`
- **Service**: `app/Services/DynamicLayoutService.php`

### **Database Tables**

- **layout_templates**: Template definitions
- **layout_sections**: Available sections and variants
- **facilities**: Layout configuration per facility

## Support

For additional help or custom section development, refer to the technical documentation or contact your system administrator.
