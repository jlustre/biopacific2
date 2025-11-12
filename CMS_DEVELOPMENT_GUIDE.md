# CMS (Content Management System) Development Guide for Blogs/Articles

This guide outlines the steps to design and integrate a comprehensive CMS for managing blogs and articles in your Laravel application. The CMS is fully integrated with the admin dashboard and sidebar, supporting rich text editing, media uploads, and granular permissions.

---

## 1. Planning & Requirements

- Define CMS features: CRUD for articles, categories/tags, rich text/HTML editor, media uploads, SEO fields, status (draft/published), versioning, author management, scheduling, search/filter, permissions, sidebar integration, and dashboard widgets.
- Decide on database structure: articles, categories, tags, media, revisions, facility_id for multi-tenant support.

---

## 2. Database Design

- Create migrations for:
  - `cms_articles` (id, facility_id, title, slug, content, excerpt, status, author_id, published_at, updated_at)
  - `cms_categories` (id, name, slug, description)
  - `cms_tags` (id, name, slug)
  - `cms_article_category` (pivot)
  - `cms_article_tag` (pivot)
  - `cms_media` (id, file_path, type, article_id, uploaded_by, created_at)
  - `cms_article_revisions` (id, article_id, content, updated_by, updated_at)

---

## 3. Models & Relationships

- Create Eloquent models:
  - `CmsArticle`, `CmsCategory`, `CmsTag`, `CmsMedia`, `CmsArticleRevision`
- Define relationships (belongsTo, hasMany, belongsToMany, supports multi-tenancy via facility_id).

---

## 4. Admin Controllers

- Create controllers for:
  - Article management (CRUD, search, filter, status, revisions, multi-tenant filtering)
  - Category/tag management
  - Media management (upload, attach, delete)
  - Revision/history management

---

## 5. Admin Views (Blade)

- Article list (table with filters/search/status, facility filter)
- Article create/edit (form with rich text editor, SEO fields, category/tag selection, media upload, scheduling)
- Category/tag management pages
- Media library modal
- Revision/history modal

---

## 6. Rich Text Editor Integration

- Integrate CKEditor or TinyMCE for article content (with image/file upload support).
- Support for HTML and RTF.

---

## 7. Sidebar Integration

- Update sidebar Blade:
  - Add “CMS” link under “Webcontent” below “Blog”
  - Link to `/admin/cms/articles`
  - Show CMS link only for users with appropriate permissions

---

## 8. Routes

- Add admin routes:
  - `/admin/cms/articles` (index, create, edit, show, delete)
  - `/admin/cms/categories`
  - `/admin/cms/tags`
  - `/admin/cms/media`
  - Use route groups and middleware for admin access and multi-tenant filtering.

---

## 9. Permissions

- Restrict CMS access to admin roles.
- Add granular permissions for authors/editors (view, create, edit, delete, publish).

---

## 10. Testing

- Write feature/unit tests for all CMS functionality, including multi-tenant filtering and permissions.

---

## 11. Documentation

- Document CMS usage for admins, including sidebar navigation, article creation, media upload, and revision history.

---

## 12. Deployment

- Migrate database, seed initial categories/tags if needed.
- Link media storage (public disk).
- Update sidebar and navigation.
- Test CMS access and permissions for all roles.

---

**Next Steps:**

- Start with migrations and models, or design the sidebar and admin routes first as needed.
