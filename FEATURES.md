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
- **Dynamic Layout System** - 6 customizable layout templates with enhanced variants
- **Video Integration** - YouTube video embedding with modal overlays (NEW)
- **Book a Tour System** - Automated tour booking with email notifications (NEW)
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

1. **Almaden Healthcare and Rehabilitation Center** - `almadenhealthandrehabilitationcenter.com`
2. **Autumn Hills Healthcare Center** - `autumnhillshealthcarecenter.com`
3. **Creekside Healthcare Center** - `creeksidehealthcarecenter.com`
4. **Driftwood Healthcare Center- Hayward** - `driftwoodhealthcarecenter-hayward.com`
5. **Driftwood Healthcare Center-Santa Cruz** - `driftwoodhealthcarecenter-santacruz.com`
6. **Fremont Healthcare Center** - `fremonthealthcarecenter.com`
7. **Fruitvale Healthcare Center** - `fruitvalehealthcarecenter.com`
8. **Glendale Transitional Care Center** - `glendaletransitionalcarecenter.com`
9. **Hayward Hills Healthcare Center** - `haywardhillshealthcarecenter.com`
10. **Inglewood Healthcare Center** - `inglewoodhealthcarecenter.com`
11. **La Crescenta Healthcare** - `lacrescentahealthcarecenter.com`
12. **Monterey Palms Healthcare Center** - `montereypalmshealthcarecenter.com`
13. **Palm Springs Healthcare & Rehabilitation Center** - `palmspringshealthandrehabilitationcenter.com`
14. **Pine Ridge Healthcare Center** - `pineridgecarecenter.com`
15. **Santa Monica Healthcare Center** - `santamonicahealthcarecenter.com`
16. **Skyline Healthcare Center-San Jose** - `skylinehealthcarecenter-sanjose.com`
17. **Vale Healthcare Center** - `valehealthcarecenter.com`
18. **Village Square Healthcare Center** - `villagesquarehealthcarecenter.com`

---

## 🎨 Dynamic Layout System

### Layout Templates

The system provides 6 main layout templates, each offering different design philosophies:

#### 1. Layout 1 - Classic Modern Layout

- **Description**: Modern animated layout with floating elements
- **Sections**: Hero (Animated) → About → Services → Contact
- **Features**: Background animations, video modal support
- **Best For**: Facilities wanting contemporary appeal with motion

#### 2. Layout 2 - Modern Layout

- **Description**: Professional modern layout with video hero and card services
- **Sections**: Hero (Video) → About (Stats) → Services (Cards) → Contact (Map)
- **Features**: Video backgrounds, statistical displays
- **Best For**: Contemporary facilities emphasizing modern amenities

#### 3. Layout 3 - Minimal Layout

- **Description**: Clean minimal layout focused on essential content
- **Sections**: Hero (Split) → About (Timeline) → Contact (Info)
- **Features**: Split-screen design, timeline elements
- **Best For**: Boutique facilities preferring clean, focused design

#### 4. Layout 4 - Service-Focused Layout

- **Description**: Layout that emphasizes services and amenities
- **Sections**: Hero → Services (Tabs) → About → Contact
- **Features**: Tabbed service display, enhanced service cards
- **Best For**: Facilities with extensive service offerings

#### 5. Layout 5 - Premium Healthcare Layout

- **Description**: Premium design with advanced interactions
- **Sections**: Hero (Premium) → About (Enhanced) → Services → Contact
- **Features**: Advanced animations, premium styling, video integration
- **Best For**: High-end facilities seeking luxury presentation

#### 6. Layout 6 - Full-Feature Layout

- **Description**: Comprehensive layout with all available features
- **Sections**: Hero (Video) → About (Complete) → Services (Advanced) → Contact (Full)
- **Features**: Video backgrounds, complete feature set, advanced interactions
- **Best For**: Large facilities wanting to showcase all capabilities

### Section Types & Variants

#### Hero Section

- **Default**: Full-width video background with image fallback and overlay controls
- **Hero1**: Animated background with floating elements and video modal
- **Hero2**: Professional layout with gradient backgrounds
- **Hero3**: Split-screen design with image and text separation
- **Hero4**: Minimalist approach with clean typography
- **Hero5**: Premium styling with advanced interactions
- **Hero6**: Comprehensive hero with all features enabled

**Configuration Options:**

- Background type (image, video, gradient, animated)
- Text alignment (left, center, right)
- Call-to-action visibility and styling
- **Video integration** - YouTube video ID support with modal overlay
- **Accessibility features** - Reduced motion support, keyboard navigation
- **Responsive behavior** - Mobile-optimized layouts

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
- hero_video_id (NEW) - YouTube video ID for hero sections
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
- `hasMany(BookATour::class)` - Tour booking requests (NEW)

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
name, slug, template_id
variant, config
order, is_active
created_at, updated_at
```

### User Model (Enhanced)

User management with role-based permissions and granular CMS access.

**Roles:**

- **Super Admin**: Full system access across all tenants
- **Facility Admin**: Full access to specific facility
- **Content Manager**: Content editing permissions
- **Viewer**: Read-only access

### BookATour Model

Tour booking request management for facilities.

**Key Attributes:**

```php
facility_id, name, email, phone
preferred_date, preferred_time
message, status
created_at, updated_at
```

**Features:**

- Email notification system
- Status tracking (pending, confirmed, completed)
- Facility-specific tour management
- Integration with contact forms and admin dashboard

### AuditLog Model

Comprehensive activity logging for compliance and security.

**Tracked Activities:**

- User login/logout events
- Content modifications
- Settings changes
- Data exports
- Administrative actions
- Tour booking submissions
- CMS article changes (NEW)

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
- **BookATour**: Tour booking form with validation and email notifications (NEW)

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
- hero_video_id (NEW) - YouTube video ID storage
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

#### book_a_tours (NEW)

```sql
- id, facility_id, name, email, phone
- preferred_date, preferred_time, message
- status, created_at, updated_at
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

## 🎥 Video Integration System (NEW)

### Video Modal Component

A reusable Blade component for YouTube video integration with modal overlays.

**Component Location:** `resources/views/components/video-modal.blade.php`

**Features:**

- **YouTube Integration**: Seamless YouTube video embedding
- **Modal Overlay**: Full-screen video experience with backdrop
- **Accessibility**: Keyboard navigation, screen reader support
- **Responsive Design**: Mobile-optimized video playback
- **Auto-close**: Click-outside and Escape key functionality
- **Customizable**: Configurable colors and styling

**Usage:**

```blade
<x-video-modal
    :videoId="$facility['hero_video_id']"
    :accentColor="$facility['accent_color']"
    modalId="uniqueModalId"
    playBtnId="uniquePlayBtn"
/>
```

**Props:**

```php
- videoId: YouTube video ID (required)
- accentColor: Theme accent color (default: #F59E0B)
- zIndex: Modal z-index (default: 2001)
- background: Modal background color (default: rgba(0,0,0,0.75))
- modalId: Unique modal identifier (default: videoModal)
- playBtnId: Play button identifier (default: playVideoBtn)
- closeBtnId: Close button identifier (default: closeVideoBtn)
- iframeId: Iframe identifier (default: youtubeIframe)
```

### Hero Video Integration

All hero layout templates now support video integration:

- **Video Background**: Full-width background videos with image fallbacks
- **Modal Triggers**: "Watch Intro" buttons that open video modals
- **Accessibility**: Reduced motion support for users with motion sensitivity
- **Performance**: Lazy loading and optimized video delivery

### Database Support

The `facilities` table now includes `hero_video_id` field for storing YouTube video identifiers, enabling easy video management through the admin interface.

---

## 📋 Book a Tour System (NEW)

### Tour Booking Features

A comprehensive tour booking system integrated into facility websites.

**Component Location:** `app/Livewire/BookATour.php`

**Features:**

- **Form Validation**: Client and server-side validation
- **Email Notifications**: Automatic notifications to facility staff
- **Data Persistence**: Tour requests stored in database
- **Status Tracking**: Booking status management (pending, confirmed, completed)
- **Multi-Tenant**: Facility-specific tour bookings
- **Spam Protection**: Built-in validation and rate limiting

**Form Fields:**

```php
- name (required)
- email (required, validated)
- phone (required)
- preferred_date (required)
- preferred_time (required)
- message (optional)
```

**Email Integration:**

- **Automatic Notifications**: Sent to facility email contacts
- **Template Support**: Customizable email templates
- **Facility Branding**: Emails styled with facility colors and branding
- **Delivery Tracking**: Email delivery status monitoring

### Admin Management

Tour bookings can be managed through the admin interface with features for:

- Viewing all tour requests
- Updating booking status
- Filtering by date/status
- Exporting booking data
- Email communication tracking

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

#### Tour Booking Management (NEW)

```
GET    /api/facilities/{id}/tours        # List tour bookings
POST   /api/facilities/{id}/tours        # Submit tour booking
PUT    /api/tours/{id}/status            # Update booking status
GET    /api/tours/{id}                   # Get booking details
```

#### Video Management (NEW)

```
GET    /api/facilities/{id}/video        # Get facility video settings
PUT    /api/facilities/{id}/video        # Update video configuration
POST   /api/video/validate               # Validate YouTube video ID
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
<<<<<<< HEAD
# php artisan db:seed --class=FacilitiesSeeder
=======
php artisan db:seed --class=FacilitiesSeeder
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
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

## 🆕 Recent Updates (November 2025)

### Video Integration System

- **YouTube Video Modals**: Reusable video modal component for hero sections
- **Enhanced Hero Layouts**: All 6 hero templates now support video integration
- **Accessibility Features**: Reduced motion support, keyboard navigation
- **Database Integration**: `hero_video_id` field added to facilities table

### Book a Tour System

- **Tour Booking Forms**: Integrated booking system with validation
- **Email Notifications**: Automated email delivery to facility contacts
- **Admin Management**: Tour request tracking and status management
- **Multi-Tenant Support**: Facility-specific tour configurations

### Performance & UI Enhancements

- **Responsive Improvements**: Enhanced mobile experience across all layouts
- **Component Reusability**: Standardized video modal for consistent UX
- **Code Organization**: Improved component structure and maintainability

---

_Last Updated: November 4, 2025_
_Version: 1.1.0_
_Bio-Pacific Healthcare Multi-Tenant Application_
