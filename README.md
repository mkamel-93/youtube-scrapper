# YouTube Scraper

> A Laravel-based YouTube scraper for extracting and managing video data.

---

## 📋 Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Make](https://www.gnu.org/software/make/)

---

## 🚀 Quick Start

```bash
make rebuild-container
```

This single command:
- Creates all Docker containers (PHP, Nginx, MySQL, Redis, phpMyAdmin)
- Installs Composer dependencies
- Generates application key
- Runs database migrations

---

## 🎥 Video Demonstrations

- **[System Demo-1](https://www.loom.com/share/c0efe1e1b90b424d9feb1d70248e574f)** — Complete walkthrough of Youtube scrapper
- **[System Demo-2](https://www.loom.com/share/0c6fb493eb1242f3891eb64caeb29844)** — Complete walkthrough of Youtube scrapper

---

## 🛠️ Tech Stack

| Layer | Technologies                      |
|-------|-----------------------------------|
| **Backend** | PHP 8.3 · Laravel 12              |
| **Database** | MySQL · Redis                     |
| **Testing** | PHPStan · Pint             |
| **Dev Tools** | Husky · Debugbar |


## 🧪 Test Types

| Type | Tool         | Location | Focus             |
|------|--------------|----------|-------------------|
| **Static Analysis** | PHPStan      | Config: `phpstan.neon` | Type safety       |
| **Code Style** | Laravel Pint | Config: `pint.json` | PSR-12 compliance |

---

## 📚 Documentation

- **[Docker Setup](docs/docker.md)** — Container management & commands
- **[Husky Git Hooks](docs/husky/husky.md)** — Automated code quality checks
- **[Database Schema](docs/erd/database.md)** — ERD and database relationships
- **[Testing Guide](docs/testing/testing.md)** — Testing strategy and best practices
