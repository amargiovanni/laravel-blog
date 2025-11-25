# Research Findings: Backoffice Enhancements

**Feature**: 002-backoffice-enhancements
**Date**: 2025-11-25

## 1. llms.txt Standard

### Decision
Implement llms.txt following the [official specification](https://llmstxt.org/) - a Markdown file served at `/llms.txt` that provides AI crawlers with structured site information.

### Rationale
- Emerging standard proposed by Jeremy Howard (Answer.AI) in September 2024
- Early adopters include Anthropic, Cloudflare, and Mintlify
- Designed specifically for LLM inference time (not training)
- Markdown format is lightweight and human-readable

### Specification Summary

```markdown
# Site Name (H1 - required)

> Brief description (blockquote - optional)

Optional detailed information...

## Section Name (H2 - file list headers)

- [Page Title](https://url): Optional description

## Optional

- [Less critical content](https://url)
```

### Implementation Approach
1. Create `LlmsTxtService` to generate the file content
2. Serve via Laravel route `/llms.txt` with `text/markdown` content type
3. Cache the output, invalidate on post publish/update/delete
4. Include: site info, recent posts, category index, about page
5. Optionally provide `.md` versions of key pages

### Alternatives Considered
- **Static file generation**: Rejected - harder to keep in sync
- **robots.txt extension**: Rejected - different purpose, not LLM-specific
- **Sitemap enhancement**: Rejected - llms.txt is complementary, not replacement

### Sources
- [llmstxt.org](https://llmstxt.org/) - Official specification
- [Search Engine Land](https://searchengineland.com/llms-txt-proposed-standard-453676) - Overview
- [Semrush Blog](https://www.semrush.com/blog/llms-txt/) - Usage guide

---

## 2. JSON-LD Structured Data for Blog Posts

### Decision
Implement JSON-LD using the `BlogPosting` schema type from Schema.org, embedded in page `<head>`.

### Rationale
- Google explicitly recommends JSON-LD over Microdata
- BlogPosting is the correct schema type for blog articles
- Rich snippets improve search appearance
- Required properties are well-documented

### Required Properties

```json
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "Post title (max 110 chars)",
  "description": "Post excerpt",
  "image": "Featured image URL",
  "author": {
    "@type": "Person",
    "name": "Author name",
    "url": "Author profile URL"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Site name",
    "logo": {
      "@type": "ImageObject",
      "url": "Logo URL"
    }
  },
  "datePublished": "2025-01-01T00:00:00+00:00",
  "dateModified": "2025-01-02T00:00:00+00:00",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "Post URL"
  }
}
```

### Implementation Approach
1. Create `JsonLdService` with methods for different content types
2. Inject via Blade component in `<head>` section
3. Use post data, settings (for publisher), and author model
4. Validate output against [Google Rich Results Test](https://search.google.com/test/rich-results)

### Alternatives Considered
- **Microdata**: Rejected - Google prefers JSON-LD, harder to maintain
- **RDFa**: Rejected - more complex, less tooling support
- **Third-party package**: Rejected - simple enough to implement, avoids dependency

### Sources
- [Google Structured Data](https://developers.google.com/search/docs/appearance/structured-data/article)
- [Schema.org BlogPosting](https://schema.org/BlogPosting)
- [JSON-LD.com Examples](https://jsonld.com/blog-post/)

---

## 3. User & Role Management

### Decision
Use Filament Shield package for role/permission management UI, building on existing Spatie Laravel-Permission.

### Rationale
- Project already uses Spatie Laravel-Permission
- Filament Shield provides ready-made UI for permissions
- Auto-generates policies for Filament resources
- Maintained by Bezhan Salleh (Filament core team member)

### Implementation Approach
1. Install `bezhansalleh/filament-shield ^3.0`
2. Run setup command to generate permissions
3. Use Shield's RoleResource for role management
4. Create custom UserResource for user management
5. Add `is_active` field to users for deactivation

### Key Features from Shield
- Permission generation per resource (`view_post`, `create_post`, etc.)
- Role management with permission assignment
- Super admin role with all permissions
- Integration with existing HasRoles trait

### Alternatives Considered
- **Custom implementation**: Rejected - reinventing the wheel
- **filament-access-control**: Rejected - less active, different approach
- **Manual policies only**: Rejected - no UI for role management

### Sources
- [Filament Shield GitHub](https://github.com/bezhanSalleh/filament-shield)
- [Laravel Daily Tutorial](https://laraveldaily.com/lesson/filament-3/shield-plugin-roles-permissions)

---

## 4. Media Library Approach

### Decision
Enhance existing MediaResource with grid view, search, filtering, and usage tracking.

### Rationale
- Media model already exists with necessary fields
- ImageService already handles image processing
- Filament provides table/grid view components
- Just need to add the Resource and enhance the model

### Implementation Approach
1. Create MediaResource with:
   - Grid/list toggle view
   - Upload with drag-drop
   - Search by filename
   - Filter by type (image/document)
   - Bulk delete action
2. Add `usedByPosts()` relationship for usage tracking
3. Modify Post form to use media picker from library

### Model Enhancements Needed
```php
// Media model additions
public function usedAsFeaturedBy(): HasMany
{
    return $this->hasMany(Post::class, 'featured_image_id');
}

public function isUsed(): bool
{
    return $this->usedAsFeaturedBy()->exists();
}
```

### Alternatives Considered
- **Spatie Media Library**: Rejected - overkill, we have simpler needs
- **File Manager package**: Rejected - adds complexity, existing model works

---

## 5. Theme Settings Storage

### Decision
Use existing `Setting` model with group `theme` to store theme configuration.

### Rationale
- Setting model already supports key-value storage
- Groups allow logical separation (theme, geo, general)
- Cache already implemented for settings
- No new migration needed

### Settings Keys
```php
// Theme settings (group: 'theme')
'theme.primary_color'     => '#3B82F6'     // Hex color
'theme.secondary_color'   => '#10B981'     // Hex color
'theme.logo_path'         => 'media/logo.png'
'theme.favicon_path'      => 'media/favicon.ico'
'theme.footer_text'       => '© 2025 Blog'
'theme.footer_links'      => json_encode([...])
'theme.social_links'      => json_encode([...])

// GEO settings (group: 'geo')
'geo.llms_enabled'        => true
'geo.llms_include_posts'  => true
'geo.llms_include_pages'  => true
'geo.jsonld_enabled'      => true
```

### Implementation Approach
1. Create Filament Page `ThemeSettings` (not Resource)
2. Use form components (ColorPicker, FileUpload)
3. Save to Settings model on submit
4. Inject CSS variables into frontend layout
5. Add reset to defaults action

### Alternatives Considered
- **New ThemeSetting model**: Rejected - redundant, Setting model works
- **Config file**: Rejected - not editable from admin
- **Environment variables**: Rejected - not user-friendly

---

## 6. Filament Resources Pattern

### Decision
Follow existing PostResource pattern for new resources.

### Existing Pattern (from PostResource)
```php
// Form sections
Forms\Components\Group::make()->schema([
    Forms\Components\Section::make('Title')->schema([...])
])->columnSpan(['lg' => 2])

// Table with filters
Tables\Filters\SelectFilter::make('status')
Tables\Filters\TrashedFilter::make()

// Actions
Tables\Actions\ViewAction::make()
Tables\Actions\EditAction::make()
Tables\Actions\DeleteAction::make()

// Bulk actions
Tables\Actions\BulkActionGroup::make([...])
```

### Consistency Requirements
- All resources use same layout pattern
- All resources have proper policies
- All resources support soft deletes where applicable
- All resources have searchable tables

---

## Summary of Dependencies

| Feature | New Dependencies | Existing Dependencies |
|---------|-----------------|----------------------|
| Media Library | None | Intervention Image, Laravel Storage |
| Categories/Tags | None | Eloquent, Filament |
| User Management | filament-shield ^3.0 | Spatie Permission |
| Theme Settings | None | Setting model |
| GEO Features | None | Laravel Cache, Blade |

## Open Questions Resolved

| Question | Resolution |
|----------|------------|
| llms.txt format | Standard Markdown per llmstxt.org spec |
| JSON-LD schema type | BlogPosting for posts, Organization for site |
| Role management approach | Filament Shield package |
| Theme storage | Existing Setting model with 'theme' group |
| Media library approach | Enhance existing Media model + new Resource |
