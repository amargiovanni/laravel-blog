# Laravel Blog Engine

A complete, feature-rich blog engine built with the TALL stack (TailwindCSS v4, Alpine.js, Livewire 3, Laravel 12) and Filament v4 admin panel.

## Features

### Content Management
- **Posts**: Create, edit, publish, and schedule blog posts with rich text editor
- **Categories**: Hierarchical category system with unlimited nesting
- **Tags**: Flexible tagging system for content organization
- **Media Library**: Centralized media management with image optimization
- **SEO Tools**: Meta tags, Open Graph, Twitter Cards, and sitemap generation
- **Comments**: Built-in comment system with moderation and spam protection

### Admin Panel (Filament v4)
- **Dashboard**: Analytics overview with widgets and statistics
- **CRUD Resources**: Full management for posts, categories, tags, users, and comments
- **Role & Permissions**: Fine-grained access control with Spatie Permission
- **Activity Log**: Track all content changes and user actions
- **Media Manager**: Drag-and-drop file uploads with gallery view

### Frontend
- **Configurable Themes**: Multiple theme support with easy customization
- **Responsive Design**: Mobile-first approach with TailwindCSS v4
- **Dark Mode**: Built-in light/dark mode toggle
- **Search**: Full-text search with filters
- **RSS Feed**: Auto-generated RSS/Atom feeds
- **Social Sharing**: Integrated share buttons for major platforms

### Technology Stack
- **Laravel 12**: Latest PHP framework with modern features
- **Livewire 3 + Volt**: Reactive components with single-file architecture
- **Filament v4**: Server-driven admin panel with tables, forms, and widgets
- **TailwindCSS v4**: Utility-first CSS framework
- **Alpine.js v3**: Lightweight JavaScript for interactivity
- **Flux UI**: Pre-built UI components for rapid development

### Developer Experience
- **Laravel Pulse**: Real-time performance monitoring
- **Laravel Telescope**: Debug assistant (local environment only)
- **Pest v4**: Modern testing with browser testing support
- **Larastan**: Static analysis for type safety
- **Pint**: Automatic code formatting
- **Rector**: Automated refactoring

## Requirements

- PHP 8.4+
- Composer
- Node.js 20+
- MySQL 8.0+ / PostgreSQL 15+ / SQLite

## Installation

```bash
# Clone the repository
git clone https://github.com/amargiovanni/laravel-blog.git
cd laravel-blog

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build

# Start development server
composer run dev
```

## Configuration

### Theme Configuration

Themes are located in `resources/views/themes/`. Create a new directory with your theme name and configure it in `config/blog.php`:

```php
'theme' => env('BLOG_THEME', 'default'),
```

### Admin Panel Access

Access the admin panel at `/admin`. Default credentials after seeding:

- **Email**: admin@example.com
- **Password**: password

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run browser tests
php artisan test --filter=Browser
```

## Contributing

Contributions are welcome! Please read our contributing guidelines before submitting a pull request.

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
