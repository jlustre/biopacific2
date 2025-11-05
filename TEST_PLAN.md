# Bio-Pacific Healthcare Application Test Plan

## Overview

This test plan covers the comprehensive testing strategy for the Bio-Pacific Healthcare multi-tenant application. It addresses all major modules, user flows, business logic, and system integrations based on the developed features and architecture.

---

## 1. Core Modules & Features

- Facility management (CRUD, listing, details)
- User authentication and authorization
- Admin dashboard and management tools
- Audit logging system
- News and events management
- Gallery image management
- FAQ and testimonial modules
- Color scheme and layout builder
- HIPAA checklist interactive tool
- Webmaster contact management
- Service management
- Web content management
- **Video modal integration (NEW)** - YouTube video embedding in hero sections
- **Enhanced hero layouts (UPDATED)** - Multiple hero templates with video support
- **Book a tour functionality (NEW)** - Tour booking system with email notifications

## 2. Test Types

- **Unit Tests**: Validate individual models, helpers, and services
- **Feature Tests**: Test user flows, controller actions, and Livewire components
- **Integration Tests**: Ensure modules interact correctly (e.g., services with facilities)
- **UI/UX Tests**: Validate Blade views, Livewire interactivity, and frontend components
- **Security Tests**: Authentication, authorization, and data isolation
- **Compliance Tests**: Audit logging, HIPAA checklist, and data privacy

## 3. Test Coverage Matrix

| Module                | Unit | Feature | Integration | UI/UX | Security | Compliance |
| --------------------- | ---- | ------- | ----------- | ----- | -------- | ---------- |
| Facility Management   | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| User Auth             | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Admin Dashboard       | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Audit Logging         | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| News/Events           | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Gallery Management    | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| FAQ/Testimonial       | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Layout Builder        | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| HIPAA Checklist       | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Webmaster Contact     | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Service Management    | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| Web Content           | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| **Video Integration** | ✓    | ✓       | ✓           | ✓     | ✓        | N/A        |
| **Book a Tour**       | ✓    | ✓       | ✓           | ✓     | ✓        | ✓          |
| **Hero Layouts**      | ✓    | ✓       | ✓           | ✓     | ✓        | N/A        |

## 4. Test Scenarios

### Facility Management

- Create, update, delete, and view facilities
- Multi-tenant isolation (no data leakage)
- Facility-specific layouts and settings

### User Authentication & Authorization

- Registration, login, password reset, 2FA
- Role-based access (Super Admin, Facility Admin, Content Manager, Viewer)
- Permission checks for sensitive actions

### Admin Dashboard

- Facility overview, statistics, and quick actions
- Navigation and access control

### Audit Logging

- Log all critical actions (login, content changes, settings updates)
- Retrieve and export audit logs
- Compliance with security requirements

### News & Events

- CRUD operations for news/events
- Facility-specific news display
- Image upload and deletion

### Gallery Management

- Add, edit, delete gallery images
- Facility-specific galleries
- Image ordering and categorization

### FAQ & Testimonials

- Manage FAQs and testimonials per facility
- Display on public and admin views

### Layout Builder & Dynamic Layouts

- Select and configure layout templates
- Add, edit, and remove layout sections
- Preview and apply changes

### HIPAA Checklist

- Interactive checklist completion
- Save and retrieve compliance status
- Admin and facility user flows

### Webmaster Contact

- Submit and manage contact requests
- Email notifications and logging

### Service Management

- CRUD for services and amenities
- Facility-specific service display
- Feature highlighting and ordering

### Web Content Management

- Edit and publish web content per facility
- Versioning and rollback

### Video Integration & Modal System (NEW)

- YouTube video embedding in hero sections
- Video modal functionality with overlay
- Video playback controls and accessibility
- Responsive video display across devices

### Book a Tour System (NEW)

- Tour booking form submission and validation
- Email notification system for tour requests
- Tour request management and tracking
- Integration with facility-specific contact forms

### Enhanced Hero Layouts (UPDATED)

- Multiple hero template variations (hero1-hero6)
- Video background support with image fallbacks
- Dynamic content and color scheme integration
- Responsive design across all hero variants

## 5. Data & Database Testing

- Migration and seeder validation
- Factory-based test data generation
- Data integrity and relationship checks

## 6. Frontend & UI Testing

- Blade view rendering for all modules
- Livewire component interactivity
- Responsive design and accessibility

## 7. Security & Compliance

- Authentication and session management
- Authorization and role checks
- Audit log completeness
- HIPAA compliance validation

## 8. Automation & Tools

- Use Pest and PHPUnit for all automated tests
- Run `php artisan test` for full suite
- Integrate with CI/CD for automated test runs

## 9. Manual Testing Checklist

- User flows for all roles
- Edge cases and error handling
- Multi-tenant isolation
- Data privacy and compliance

## 10. Documentation & Reporting

- Document test cases and results
- Update FEATURES.md and developer guides as needed
- Track issues and improvements

## 11. Comprehensive Test Cases by Module

### Facility Management

- Test creating a facility with valid and invalid data
- Test updating facility details (name, address, images, colors)
- Test deleting a facility and verifying data removal
- Test listing all facilities (admin and public views)
- Test multi-tenant isolation (no cross-facility data access)
- Test facility-specific layout selection and rendering
- Test searching/filtering facilities

### User Authentication & Authorization

- Test user registration with valid/invalid data
- Test login with correct/incorrect credentials
- Test password reset flow (request, email, reset)
- Test two-factor authentication setup and login
- Test role assignment and permission enforcement
- Test access restrictions for each role (Super Admin, Facility Admin, Content Manager, Viewer)
- Test session expiration and logout

### Admin Dashboard

- Test dashboard loading for different roles
- Test facility statistics accuracy
- Test quick actions (edit, delete, preview)
- Test navigation between dashboard sections
- Test access control for dashboard features

### Audit Logging

- Test logging of user login/logout events
- Test logging of content changes (create, update, delete)
- Test logging of settings changes
- Test retrieval and filtering of audit logs
- Test export of audit logs in supported formats
- Test compliance with security and privacy requirements

### News & Events

- Test creating, editing, deleting news/events
- Test listing news/events per facility
- Test image upload, display, and deletion for news/events
- Test public and admin news/event views
- Test validation for news/event fields

### Gallery Management

- Test adding, editing, deleting gallery images
- Test listing images per facility
- Test image ordering and categorization
- Test image upload validation (type, size)
- Test gallery display on public and admin views

### FAQ & Testimonials

- Test creating, editing, deleting FAQs/testimonials
- Test listing FAQs/testimonials per facility
- Test display on public and admin views
- Test featured/testimonial highlighting

### Layout Builder & Dynamic Layouts

- Test selecting layout templates for a facility
- Test configuring layout sections and variants
- Test adding/removing sections
- Test previewing layout changes
- Test saving and applying layout configurations

### HIPAA Checklist

- Test interactive checklist completion for a facility
- Test saving and retrieving checklist status
- Test admin and facility user flows for HIPAA compliance
- Test validation of required checklist items

### Webmaster Contact

- Test submitting contact requests (public and admin)
- Test email notification delivery
- Test logging of contact requests
- Test viewing and managing contact requests in admin

### Service Management

- Test creating, editing, deleting services/amenities
- Test listing services per facility
- Test feature highlighting and ordering
- Test service display on public and admin views
- Test validation for service fields

### Web Content Management

- Test editing and publishing web content per facility
- Test versioning and rollback of content
- Test display of web content on public site
- Test validation for web content fields

### Video Integration & Modal System (NEW)

- Test YouTube video embedding with valid/invalid video IDs
- Test video modal opening and closing functionality
- Test video autoplay and user controls
- Test modal accessibility (keyboard navigation, screen readers)
- Test responsive video display on mobile/tablet/desktop
- Test video modal integration with different hero layouts
- Test video playback with reduced motion preferences
- Test multiple video modals on the same page
- Test video modal closing with Escape key and click-outside

### Book a Tour System (NEW)

- Test tour booking form validation (required fields, email format)
- Test successful tour booking submission
- Test email notification delivery to facility contacts
- Test tour request storage and persistence
- Test facility-specific tour booking configurations
- Test integration with existing contact forms
- Test spam protection and rate limiting
- Test mobile responsiveness of booking forms
- Test admin interface for managing tour requests

### Enhanced Hero Layouts (UPDATED)

- Test all hero layout variants (hero1-hero6) rendering correctly
- Test video background functionality with YouTube integration
- Test image fallback when video is unavailable
- Test responsive behavior across all screen sizes
- Test color scheme integration with hero elements
- Test hero content management (headlines, subheadlines, CTAs)
- Test accessibility of hero sections (alt text, focus management)
- Test performance optimization of video loading
- Test reduced motion accessibility preferences
- Test hero layout switching and preview functionality

### Data & Database

- Test running migrations and seeders
- Test factory-based test data generation
- Test data integrity and relationship constraints
- Test backup and restore operations
- **Test hero_video_id field storage and validation (NEW)**
- **Test book a tour data persistence and relationships (NEW)**
- **Test facility-specific video and tour configurations (NEW)**

### Frontend & UI

- Test Blade view rendering for all modules
- Test Livewire component interactivity
- Test responsive design on multiple devices
- Test accessibility (ARIA, keyboard navigation)
- Test error and edge case handling in UI
- **Test video modal component rendering and functionality (NEW)**
- **Test hero layout variations and responsive behavior (UPDATED)**
- **Test book a tour form interactivity and validation (NEW)**
- **Test JavaScript functionality across all hero templates (UPDATED)**
- **Test video component accessibility features (NEW)**

### Security & Compliance

- Test authentication and session management
- Test authorization and role checks
- Test audit log completeness and integrity
- Test HIPAA compliance validation
- Test data privacy and isolation

## 12. Recent Feature Enhancements (Updated Nov 2025)

### Video Integration System

#### Video Modal Component

- **Reusable Component**: `resources/views/components/video-modal.blade.php`
- **Features**: YouTube embed, autoplay, responsive design, accessibility
- **Testing Focus**: Modal functionality, video playback, cross-browser compatibility

#### Hero Layout Updates

- **Enhanced Templates**: All hero layouts now support video integration
- **Video Background**: Full-width video backgrounds with image fallbacks
- **Accessibility**: Reduced motion support, keyboard navigation
- **Testing Focus**: Layout rendering, video loading, responsive behavior

#### Database Changes

- **New Field**: `hero_video_id` in facilities table
- **Purpose**: Store YouTube video IDs for hero sections
- **Testing Focus**: Data validation, storage, retrieval

### Book a Tour System

#### Tour Booking Features

- **Form Handling**: Livewire component for tour requests
- **Email Notifications**: Automated email sending to facility contacts
- **Data Persistence**: Tour request storage and management
- **Testing Focus**: Form validation, email delivery, data integrity

#### Integration Points

- **Facility Integration**: Tour forms tied to specific facilities
- **Contact System**: Integration with existing webmaster contact system
- **Admin Interface**: Management tools for tour requests
- **Testing Focus**: Multi-tenant isolation, admin functionality

### Testing Priorities

1. **Critical Path Testing**

   - Video modal functionality across all browsers
   - Tour booking form submission and email delivery
   - Hero layout rendering with video integration

2. **Cross-Browser Testing**

   - Video playback on Chrome, Firefox, Safari, Edge
   - Modal functionality on mobile devices
   - JavaScript compatibility across platforms

3. **Performance Testing**

   - Video loading and streaming performance
   - Modal rendering speed
   - Hero section loading times

4. **Accessibility Testing**
   - Screen reader compatibility for video controls
   - Keyboard navigation for modals
   - Reduced motion preferences handling

---

_This test plan should be updated as new features are developed or requirements change._

**Last Updated**: November 2025 - Added video integration, book a tour system, and enhanced hero layouts
