<!--
SYNC IMPACT REPORT
==================
Version change: N/A → 1.0.0 (initial ratification)
Modified principles: N/A (initial creation)
Added sections:
  - Core Principles (5 principles)
  - Technology Constraints
  - Development Workflow
  - Governance
Removed sections: N/A
Templates requiring updates:
  - .specify/templates/plan-template.md ✅ (compatible - Constitution Check section exists)
  - .specify/templates/spec-template.md ✅ (compatible - requirements align)
  - .specify/templates/tasks-template.md ✅ (compatible - test-first guidance present)
Follow-up TODOs: None
-->

# Laravel Blog Engine Constitution

## Core Principles

### I. Test-First Development (NON-NEGOTIABLE)

All features MUST have tests written before implementation begins. This project uses Pest v4 for testing.

- Tests MUST be written first and MUST fail before implementation
- Red-Green-Refactor cycle is strictly enforced
- Feature tests live in `tests/Feature/`, unit tests in `tests/Unit/`, browser tests in `tests/Browser/`
- Use `php artisan make:test --pest <name>` to create new tests
- Run `php artisan test --filter=<name>` to verify specific functionality

**Rationale**: Tests document expected behavior, prevent regressions, and ensure code quality. Skipping tests creates technical debt that compounds over time.

### II. Laravel Conventions

All code MUST follow Laravel's official patterns, conventions, and best practices.

- Use Eloquent models and relationships over raw queries
- Use Form Request classes for validation (not inline validation)
- Use queued jobs for time-consuming operations
- Use named routes and the `route()` helper for URL generation
- Use `config()` helper, never `env()` outside config files
- Use `php artisan make:*` commands to generate files
- Follow existing codebase conventions when in doubt

**Rationale**: Consistency with Laravel conventions ensures maintainability, enables use of ecosystem tools, and reduces onboarding friction for new developers.

### III. TALL Stack Compliance

All frontend code MUST use the TALL stack consistently: TailwindCSS v4, Alpine.js v3, Livewire 3, Laravel 12.

- Use Livewire Volt for interactive components (check existing components for class-based vs functional style)
- Use Flux UI components when available before creating custom components
- Use TailwindCSS v4 utilities (not deprecated v3 utilities)
- Alpine.js is included with Livewire; do not manually include it
- Use `wire:model.live` for real-time updates (not the old `wire:model` behavior)

**Rationale**: A unified frontend stack reduces complexity, ensures consistent UX, and leverages the tight integration between these technologies.

### IV. Code Quality Gates

All code MUST pass automated quality checks before being considered complete.

- **Pint**: Run `vendor/bin/pint --dirty` before finalizing changes
- **Larastan**: Static analysis MUST pass without errors
- **Tests**: All tests MUST pass (`php artisan test`)
- **Build**: Frontend assets MUST compile (`npm run build`)

**Rationale**: Automated quality gates catch issues early, maintain code consistency, and prevent degradation of the codebase over time.

### V. Simplicity & YAGNI

Code MUST be as simple as possible while meeting requirements. Avoid over-engineering.

- Start with the simplest solution that works
- Do not add features, abstractions, or configurability beyond what was requested
- Three similar lines of code is better than a premature abstraction
- Do not design for hypothetical future requirements
- Remove unused code completely; do not comment it out or add deprecation shims

**Rationale**: Complexity is the enemy of maintainability. Every abstraction has a cost. Simpler code is easier to understand, test, debug, and modify.

## Technology Constraints

This section defines the non-negotiable technology boundaries for this project.

### Required Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.4+ |
| Frontend Framework | Livewire + Volt | 3.x / 1.x |
| CSS | TailwindCSS | 4.x |
| JavaScript | Alpine.js | 3.x |
| UI Components | Flux UI (Free) | 2.x |
| Admin Panel | Filament | 4.x |
| Testing | Pest | 4.x |
| Static Analysis | Larastan | 3.x |
| Code Style | Pint | 1.x |

### Database

- Use Eloquent ORM; avoid `DB::` facade unless absolutely necessary
- Use migrations for all schema changes
- When modifying columns, include ALL previous attributes (Laravel 11+ behavior)
- Use factories and seeders for test data

### Security

- Validate all user input using Form Request classes
- Use Laravel's built-in authentication and authorization (gates, policies, Sanctum)
- Never commit secrets or credentials to version control
- Log security-relevant events

## Development Workflow

This section defines the required development process for all contributors.

### Feature Development Process

1. **Specification**: Define requirements using `/speckit.specify`
2. **Planning**: Create implementation plan using `/speckit.plan`
3. **Tasks**: Generate task list using `/speckit.tasks`
4. **Implementation**: Execute tasks following Test-First principle
5. **Quality Check**: Run Pint, Larastan, and tests
6. **Review**: Code review before merge

### Commit Standards

- Write clear, concise commit messages
- Reference related issues/tasks when applicable
- Keep commits focused on single logical changes
- Run quality checks before committing

### Branch Strategy

- Feature branches: `feature/description` or `###-feature-name`
- Bug fixes: `fix/description`
- Always branch from and merge to `main`

## Governance

This constitution supersedes all other development practices for this project. All contributors MUST comply with these principles.

### Amendment Process

1. Propose changes via pull request to `.specify/memory/constitution.md`
2. Document rationale for the change
3. Update version number according to semantic versioning:
   - **MAJOR**: Backward-incompatible changes to principles
   - **MINOR**: New principles or sections added
   - **PATCH**: Clarifications, typo fixes, non-semantic changes
4. Update dependent templates if affected
5. Obtain approval before merging

### Compliance

- All pull requests MUST verify compliance with these principles
- Code reviews SHOULD check constitution alignment
- Complexity beyond these guidelines MUST be explicitly justified
- Use the Constitution Check section in plan documents to verify compliance

### Runtime Guidance

For day-to-day development guidance, refer to:
- `CLAUDE.md` for AI assistant instructions
- `AGENTS.md` for agent-specific guidance
- Laravel Boost's `search-docs` tool for framework documentation

**Version**: 1.0.0 | **Ratified**: 2025-11-25 | **Last Amended**: 2025-11-25
