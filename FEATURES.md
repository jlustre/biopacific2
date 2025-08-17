# Bio-Pacific Healthcare Multi-Tenant Application

## 📋 Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Multi-Tenant Features](#multi-tenant-features)
4. [Dynamic Layout System](#dynamic-layout-system)
5. [Core Models](#core-models)
6. [Services](#services)
7. [Livewire Components](#livewire-components)
8. [Database Structure](#database-structure)
9. [Security Features](#security-features)
10. [API & Integrations](#api--integrations)
11. [Deployment](#deployment)
12. [Development Guidelines](#development-guidelines)

---

## 🏥 Overview

Bio-Pacific Healthcare is a comprehensive multi-tenant Laravel application designed to manage 17+ nursing home and healthcare facility websites from a single codebase. Each facility operates as an independent tenant with its own domain, branding, content, and layout configuration while sharing the same underlying infrastructure.

### Key Features

- **Multi-Tenant Architecture** - Single application serving multiple domains
- **Dynamic Layout System** - 4-6 customizable layout templates
- **Content Management** - Facility-specific content and media
- **Role-Based Access Control** - Secure user management with permissions
- **Audit Logging** - Comprehensive activity tracking
- **Responsive Design** - Mobile-first, modern UI
- **SEO Optimized** - Search engine friendly structure

---

## 🏗️ Architecture

### Technology Stack

- **Backend**: Laravel 11.x
- **Frontend**: Livewire 3.x, Alpine.js, Tailwind CSS
- **Database**: MySQL 8.0+
- **Build Tools**: Vite
- **Permissions**: Spatie Laravel Permission

### Application Structure

```
bio-pacific/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # HTTP Controllers
│   │   └── Middleware/      # Custom Middleware
│   ├── Livewire/           # Livewire Components
│   │   ├── Auth/           # Authentication Components
│   │   ├── Actions/        # Action Components
│   │   └── Settings/       # Settings Management
│   ├── Models/             # Eloquent Models
│   ├── Services/           # Business Logic Services
│   ├── Traits/             # Reusable Traits
│   └── Providers/          # Service Providers
├── database/
│   ├── migrations/         # Database Migrations
│   └── seeders/           # Database Seeders
├── resources/
│   ├── views/             # Blade Templates
│   │   ├── layouts/       # Layout Templates
│   │   ├── partials/      # Reusable Components
│   │   └── components/    # Blade Components
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript Files
└── routes/                 # Application Routes
```

---

## 🏢 Multi-Tenant Features

### Tenant Resolution

The application automatically detects the current tenant (facility) based on the incoming domain name through the `ResolveTenant` middleware.

#### Domain Mapping

- **Production**: Each facility has its own domain (e.g., `valehealthcare.com`, `pacificmanorcare.com`)
- **Development**: Subdomain-based routing (e.g., `vale.localhost`, `pacific.localhost`)

#### Tenant Isolation

- **Data Isolation**: All tenant-aware models automatically filter data by `facility_id`
- **Asset Isolation**: Tenant-specific images, logos, and media files
- **Configuration Isolation**: Separate settings and configurations per tenant

### Supported Facilities

The application currently supports these nursing homes:

1. **Vale Health Care Center** - `valehealthcare.com`
2. **Pacific Manor Care** - `pacificmanorcare.com`
3. **Sunrise Gardens Healthcare** - `sunrisegardens.com`
4. **Mountain View Assisted Living** - `mountainviewal.com`
5. **Oakwood Senior Community** - `oakwoodsenior.com`
6. **[Additional 12+ facilities as configured]**

---

## 🎨 Dynamic Layout System

### Layout Templates

The system provides 4 main layout templates, each offering different design philosophies:

#### 1. Layout 1 - Classic Layout

- **Description**: Traditional layout with all sections
- **Sections**: Hero → About → Services → Contact
- **Best For**: Established facilities wanting a professional, traditional look

#### 2. Layout 2 - Modern Layout

- **Description**: Modern layout with video hero and card services
- **Sections**: Hero (Video) → About (Stats) → Services (Cards) → Contact (Map)
- **Best For**: Contemporary facilities emphasizing modern amenities

#### 3. Layout 3 - Minimal Layout

- **Description**: Minimal layout focused on essential content
- **Sections**: Hero (Split) → About (Timeline) → Contact (Info)
- **Best For**: Boutique facilities preferring clean, focused design

#### 4. Layout 4 - Service-Focused Layout

- **Description**: Layout that emphasizes services and amenities
- **Sections**: Hero → Services (Tabs) → About → Contact
- **Best For**: Facilities with extensive service offerings

### Section Types & Variants

#### Hero Section

- **Default**: Traditional hero with background image and centered text
- **Video**: Hero with video background
- **Split**: Hero with text on one side and image on the other

**Configuration Options:**

- Background type (image, video, gradient)
- Text alignment (left, center, right)
- Call-to-action visibility

#### About Section

- **Default**: Standard about section with text and image
- **Stats**: About section with statistics display
- **Timeline**: About section with timeline/history

**Configuration Options:**

- Image layout (image-left, image-right, centered)
- Statistics display toggle
- Content positioning

#### Services Section

- **Grid**: Services displayed in a responsive grid
- **Cards**: Services in modern card format
- **Tabs**: Services organized in tabbed interface

**Configuration Options:**

- Grid columns (2, 3, 4)
- Icon display toggle
- Service categorization

#### Contact Section

- **Form**: Contact section with inquiry form
- **Info**: Contact information display only
- **Map**: Contact section with embedded map

**Configuration Options:**

- Form visibility toggle
- Map integration toggle
- Contact methods display

---

## 📊 Core Models

### Facility Model

Central model representing each nursing home facility.

**Key Attributes:**

```php
- id, name, slug
- domain, subdomain, is_active
- address, city, state, phone, email
- headline, subheadline, about_text
- logo_url, hero_image_url, about_image_url
- primary_color, secondary_color, accent_color
- layout_template, layout_config, settings
- beds, ranking_position, ownership_role
- social_media (facebook, twitter, instagram)
```

**Relationships:**

- `hasMany(Service::class)` - Facility services
- `hasMany(Testimonial::class)` - Patient testimonials
- `hasMany(GalleryImage::class)` - Photo gallery
- `hasMany(FacilityValue::class)` - Core values
- `hasMany(AuditLog::class)` - Activity logs

### Service Model

Represents services and amenities offered by facilities.

**Key Attributes:**

```php
- facility_id, name, description
- icon, image_url, order
- is_featured, is_active
```

### Testimonial Model

Patient and family testimonials for each facility.

**Key Attributes:**

```php
- facility_id, name, relationship
- content, rating, image_url
- is_featured, is_active
```

### GalleryImage Model

Photo gallery management for facility images.

**Key Attributes:**

```php
- facility_id, title, description
- image_url, category, order
- is_featured, is_active
```

### LayoutTemplate Model

Defines available layout templates and their configurations.

**Key Attributes:**

```php
- name, slug, description
- sections, default_config
- preview_image, is_active
```

### LayoutSection Model

Defines individual sections and their variants.

**Key Attributes:**

```php
- name, slug, description
- variants, config_schema
- component_path, is_active
```

### User Model (Enhanced)

User management with role-based permissions.

**Roles:**

- **Super Admin**: Full system access across all tenants
- **Facility Admin**: Full access to specific facility
- **Content Manager**: Content editing permissions
- **Viewer**: Read-only access

### AuditLog Model

Comprehensive activity logging for compliance and security.

**Tracked Activities:**

- User login/logout events
- Content modifications
- Settings changes
- Data exports
- Administrative actions

---

## 🔧 Services

### TenantConfigService

Manages tenant-specific configuration and settings.

**Key Methods:**

```php
- get($key, $default = null)     // Retrieve setting
- set($key, $value)              // Update setting
- getThemeColors()               // Get theme colors
- getLayoutConfig($section)      // Get layout config
- setLayoutConfig($section, $config) // Update layout
```

### TenantAssetService

Handles tenant-specific asset management.

**Key Methods:**

```php
- getLogoUrl()                   // Facility logo
- getHeroImageUrl()              // Hero background
- getFaviconUrl()                // Site favicon
- getAssetPath($type, $filename) // Asset file paths
- getCustomCSS()                 // Custom styling
```

### DynamicLayoutService

Core service for dynamic layout rendering.

**Key Methods:**

```php
- getLayoutSections()            // Get all sections for current layout
- getSectionConfig($section)     // Get section configuration
- setSectionConfig($section, $config) // Update section config
- changeTemplate($templateSlug)  // Switch layout template
- renderSection($section, $data) // Render individual section
```

### AuditService

Provides comprehensive audit logging functionality.

**Key Methods:**

```php
- log($action, $description, $data) // Log activity
- getAuditTrail($filters)           // Retrieve audit logs
- exportAuditLog($format)           // Export audit data
```

---

## ⚡ Livewire Components

### Authentication Components

- **LoginForm**: Secure user authentication
- **RegistrationForm**: New user registration
- **PasswordReset**: Password recovery functionality
- **TwoFactorAuth**: Enhanced security options

### Facility Management

- **FacilitiesIndex**: Browse and manage facilities
- **FacilityLandingPage**: Individual facility display
- **FacilityEditor**: Edit facility information
- **LayoutSelector**: Choose and configure layouts

### Content Management

- **ServiceManager**: Manage facility services
- **TestimonialManager**: Handle patient testimonials
- **GalleryManager**: Photo gallery administration
- **ContentEditor**: Rich text content editing

### Settings & Configuration

- **ThemeCustomizer**: Visual theme customization
- **LayoutConfigurator**: Layout section configuration
- **UserManagement**: User and role management
- **SystemSettings**: Global application settings

### Actions Components

- **ExportData**: Data export functionality
- **ImportContent**: Bulk content import
- **BackupManager**: System backup operations
- **AuditViewer**: Activity log viewing

---

## 🗄️ Database Structure

### Core Tables

#### facilities

```sql
- id, name, slug, domain, subdomain
- logo_url, hero_image_url, about_image_url
- headline, subheadline, about_text
- address, city, state, phone, email
- primary_color, secondary_color, accent_color
- layout_template, layout_config, settings
- beds, ranking_position, ownership_role
- facebook, twitter, instagram
- is_active, created_at, updated_at
```

#### services

```sql
- id, facility_id, name, description
- icon, image_url, order
- is_featured, is_active
- created_at, updated_at
```

#### testimonials

```sql
- id, facility_id, name, relationship
- content, rating, image_url
- is_featured, is_active
- created_at, updated_at
```

#### gallery_images

```sql
- id, facility_id, title, description
- image_url, category, order
- is_featured, is_active
- created_at, updated_at
```

#### layout_templates

```sql
- id, name, slug, description
- sections (JSON), default_config (JSON)
- preview_image, is_active
- created_at, updated_at
```

#### layout_sections

```sql
- id, name, slug, description
- variants (JSON), config_schema (JSON)
- component_path, is_active
- created_at, updated_at
```

#### audit_logs

```sql
- id, facility_id, user_id
- action, description, old_values (JSON)
- new_values (JSON), ip_address, user_agent
- created_at
```

### Relationships & Constraints

- All tenant-aware tables include `facility_id` foreign key
- Cascade delete policies maintain data integrity
- Proper indexing for performance optimization
- JSON columns for flexible configuration storage

---

## 🔒 Security Features

### Authentication & Authorization

- **Secure Authentication**: Laravel Sanctum for API authentication
- **Role-Based Access Control**: Spatie Laravel Permission integration
- **Session Management**: Secure session handling with CSRF protection
- **Password Security**: Bcrypt hashing with configurable rounds

### Multi-Tenant Security

- **Data Isolation**: Automatic tenant scoping via global scopes
- **Domain Validation**: Secure domain-based tenant resolution
- **Asset Protection**: Tenant-specific asset access controls
- **Cross-Tenant Prevention**: Prevents data leakage between tenants

### Audit & Compliance

- **Activity Logging**: Comprehensive audit trail for all actions
- **Data Integrity**: Foreign key constraints and validation rules
- **Access Monitoring**: Failed login attempt tracking
- **Compliance Ready**: HIPAA-compliant logging capabilities

### Input Validation & Sanitization

- **Form Requests**: Validated input handling
- **XSS Protection**: Output escaping and content sanitization
- **SQL Injection Prevention**: Eloquent ORM parameterized queries
- **File Upload Security**: Validated and sanitized file handling

---

## 🔌 API & Integrations

### RESTful API Endpoints

The application provides RESTful APIs for external integrations:

#### Facility Management

```
GET    /api/facilities              # List all facilities
GET    /api/facilities/{id}         # Get facility details
POST   /api/facilities              # Create facility
PUT    /api/facilities/{id}         # Update facility
DELETE /api/facilities/{id}         # Delete facility
```

#### Services Management

```
GET    /api/facilities/{id}/services     # List facility services
POST   /api/facilities/{id}/services     # Add service
PUT    /api/services/{id}                # Update service
DELETE /api/services/{id}                # Delete service
```

#### Content Management

```
GET    /api/facilities/{id}/testimonials # List testimonials
GET    /api/facilities/{id}/gallery      # List gallery images
POST   /api/content/upload               # Upload media files
```

### External Integrations

- **Google Maps API**: Location mapping and directions
- **Email Services**: SMTP/SendGrid for notifications
- **CDN Integration**: Asset delivery optimization
- **Analytics**: Google Analytics 4 integration
- **Social Media**: Facebook, Twitter API connections

---

## 🚀 Deployment

### Environment Requirements

- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx or Apache with URL rewriting
- **Node.js**: 18+ for asset compilation
- **Memory**: Minimum 2GB RAM recommended

### Production Setup

#### 1. Server Configuration

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed --class=LayoutTemplatesSeeder
php artisan db:seed --class=FacilitiesSeeder
```

#### 2. Asset Compilation

```bash
# Install Node dependencies
npm install

# Build production assets
npm run build
```

#### 3. Permissions & Optimization

```bash
# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 4. Domain Configuration

Configure DNS to point all facility domains to the same server:

```
valehealthcare.com      → [SERVER_IP]
pacificmanorcare.com    → [SERVER_IP]
sunrisegardens.com      → [SERVER_IP]
mountainviewal.com      → [SERVER_IP]
oakwoodsenior.com       → [SERVER_IP]
```

#### 5. SSL Certificates

Install SSL certificates for all domains using Let's Encrypt or commercial certificates.

### Monitoring & Maintenance

- **Log Monitoring**: Centralized logging with ELK stack
- **Performance Monitoring**: Application performance metrics
- **Backup Strategy**: Daily automated database and file backups
- **Update Schedule**: Regular security and feature updates

---

## 👨‍💻 Development Guidelines

### Coding Standards

- **PSR-12**: PHP coding standard compliance
- **Laravel Conventions**: Follow Laravel naming conventions
- **Documentation**: Comprehensive code documentation
- **Testing**: Unit and feature test coverage

### Multi-Tenant Development Rules

#### 1. Model Implementation

Always use the `BelongsToTenant` trait for tenant-aware models:

```php
use App\Traits\BelongsToTenant;

class YourModel extends Model
{
    use BelongsToTenant;

    protected $fillable = ['facility_id', 'other_fields'];
}
```

#### 2. Service Classes

Access current tenant through dependency injection:

```php
class YourService
{
    protected $facility;

    public function __construct()
    {
        $this->facility = app('current_facility');
    }
}
```

#### 3. Livewire Components

Always scope data to current tenant:

```php
class YourComponent extends Component
{
    public function render()
    {
        $data = Model::where('facility_id', app('current_facility')->id)->get();
        return view('your-component', compact('data'));
    }
}
```

### Adding New Layout Templates

#### 1. Create Template Record

```php
LayoutTemplate::create([
    'name' => 'New Template',
    'slug' => 'new-template',
    'sections' => ['hero', 'about', 'services'],
    'default_config' => [
        'hero' => ['variant' => 'default'],
        'about' => ['variant' => 'default'],
        'services' => ['variant' => 'grid']
    ]
]);
```

#### 2. Create Section Variants

Create new Blade components in `resources/views/partials/[section]/[variant].blade.php`

#### 3. Update Service Configuration

Modify `DynamicLayoutService` to handle new template logic if needed.

### Testing Guidelines

- **Feature Tests**: Test multi-tenant isolation
- **Unit Tests**: Test individual service methods
- **Browser Tests**: End-to-end functionality testing
- **Performance Tests**: Database query optimization

### Git Workflow

1. **Feature Branches**: Create feature branches from `develop`
2. **Code Review**: All changes require code review
3. **Testing**: Automated tests must pass
4. **Deployment**: Staging deployment before production

---

## 📈 Performance Optimization

### Database Optimization

- **Indexing**: Proper indexes on foreign keys and search fields
- **Query Optimization**: Eager loading relationships
- **Connection Pooling**: Database connection optimization
- **Caching**: Redis/Memcached for data caching

### Asset Optimization

- **Asset Bundling**: Vite for optimized asset compilation
- **Image Optimization**: WebP format with fallbacks
- **CDN Integration**: Asset delivery via CDN
- **Lazy Loading**: Progressive image loading

### Application Performance

- **OPcache**: PHP bytecode caching
- **Route Caching**: Cached route definitions
- **Config Caching**: Cached configuration files
- **Queue Workers**: Background job processing

---

## 🔧 Maintenance & Support

### Regular Maintenance Tasks

- **Security Updates**: Monthly security patch reviews
- **Performance Monitoring**: Weekly performance analysis
- **Backup Verification**: Daily backup integrity checks
- **Log Analysis**: Weekly log review for issues

### Support Documentation

- **User Guides**: Comprehensive user documentation
- **Admin Manual**: Administrative procedures
- **API Documentation**: Developer integration guides
- **Troubleshooting**: Common issue resolution

### Version Control & Updates

- **Semantic Versioning**: Clear version numbering
- **Change Logs**: Detailed update documentation
- **Migration Guides**: Version upgrade procedures
- **Rollback Plans**: Emergency rollback procedures

---

## 📞 Contact & Support

### Development Team

- **Lead Developer**: [Contact Information]
- **System Administrator**: [Contact Information]
- **Project Manager**: [Contact Information]

### Emergency Contacts

- **24/7 Support**: [Emergency Phone]
- **Critical Issues**: [Emergency Email]
- **Infrastructure**: [Hosting Provider Contact]

### Documentation Updates

This documentation is maintained alongside the application codebase. For updates or corrections, please contact the development team or submit a pull request.

---

_Last Updated: August 15, 2025_
_Version: 1.0.0_
_Bio-Pacific Healthcare Multi-Tenant Application_
