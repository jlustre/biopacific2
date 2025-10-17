# CMS (Content Management System) Development Guide for Blogs/Articles

This guide outlines the steps to design and integrate a comprehensive CMS (similar to WordPress) for managing blogs and articles in your Laravel application. The focus is on the admin part and sidebar integration.

---

## 1. Planning & Requirements

- Define CMS features: CRUD for articles, categories/tags, rich text/HTML editor, media uploads, SEO fields, status (draft/published), versioning, author management, scheduling, search/filter, permissions.
- Decide on database structure: articles, categories, tags, media, revisions, etc.

---

## 2. Database Design

- Create migrations for:
  - `cms_articles` (title, slug, content, excerpt, status, author_id, published_at, etc.)
  - `cms_categories` (name, slug, description)
  - `cms_tags` (name, slug)
  - `cms_article_category` (pivot)
  - `cms_article_tag` (pivot)
  - `cms_media` (file_path, type, article_id, etc.)
  - `cms_article_revisions` (article_id, content, updated_by, updated_at)

---

## 3. Models & Relationships

- Create Eloquent models:
  - `CmsArticle`, `CmsCategory`, `CmsTag`, `CmsMedia`, `CmsArticleRevision`
- Define relationships (belongsTo, hasMany, belongsToMany).

---

## 4. Admin Controllers

- Create controllers for:
  - Article management (CRUD, search, filter, status, revisions)
  - Category/tag management
  - Media management (upload, attach, delete)
  - Revision/history management

---

## 5. Admin Views (Blade)

- Article list (table with filters/search/status)
- Article create/edit (form with rich text editor, SEO fields, category/tag selection, media upload, scheduling)
- Category/tag management pages
- Media library modal
- Revision/history modal

---

## 6. Rich Text Editor Integration

- Integrate CKEditor or TinyMCE for article content.
- Support for HTML and RTF.
- Image/file upload support.

---

## 7. Sidebar Integration

- Update sidebar Blade:
  - Add “CMS” link under “Webcontent” below “Blog”
  - Link to `/admin/cms/articles`

---

## 8. Routes

- Add admin routes:
  - `/admin/cms/articles` (index, create, edit, show, delete)
  - `/admin/cms/categories`
  - `/admin/cms/tags`
  - `/admin/cms/media`
  - Use route groups and middleware for admin access.

---

## 9. Permissions

- Restrict CMS access to admin roles.
- Optionally, add granular permissions for authors/editors.

---

## 10. Testing

- Write feature/unit tests for all CMS functionality.

---

## 11. Documentation

- Document CMS usage for admins.

---

## 12. Deployment

- Migrate database, seed initial categories/tags if needed.
- Link media storage.
- Update sidebar and navigation.

---

**Next Steps:**

- Start with migrations and models, or design the sidebar and admin routes first as needed.
