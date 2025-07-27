# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Trade Template PHP** project - a single-page PHP application for defining and saving trading strategies to MySQL. The project is in initial setup phase with only placeholder files currently present.

## Project Architecture

Based on the documentation in `final-source.md`, this will be a simple LAMP stack application with:

- **Single-page PHP application** (`index.php` as main entry point)
- **MySQL database** with JSON column support for flexible data storage  
- **Tailwind CSS via CDN** for styling
- **PDO** for secure database access with prepared statements
- **CSRF protection** for form security

### Planned File Structure
```
/project-root
├── index.php              ← Main application entry point
├── /includes
│   ├── db.php             ← PDO database connection setup
│   ├── csrf.php           ← CSRF token generation/verification
│   └── functions.php      ← Validation helpers and utilities
└── /templates
    └── form.php           ← HTML form markup for strategy input
```

### Database Schema
```sql
CREATE TABLE strategy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  instrument VARCHAR(50) NOT NULL,
  timeframes JSON NOT NULL,
  sessions JSON NOT NULL,
  entry_rules TEXT NOT NULL,
  exit_rules TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Development Environment

- **PHP 8+** required
- **MySQL 5.7+** with JSON support
- **Apache/Nginx** web server with PHP enabled
- **PDO extension** must be enabled in php.ini

## Development Commands

Since this is a vanilla PHP project without build tools:

- **Start development server**: `php -S localhost:8000` (from project root)
- **Database setup**: Execute SQL schema in MySQL manually
- **Testing**: No test framework configured - manual testing via browser
- **No build process**: Direct PHP execution, Tailwind loaded via CDN

## Key Implementation Notes

- **Security**: All database operations must use PDO prepared statements
- **Data Storage**: Arrays (timeframes, sessions, rules) stored as JSON in MySQL
- **Styling**: Exclusively use Tailwind CSS utilities via CDN
- **Form Handling**: Single form with POST action, includes CSRF protection
- **Error Handling**: Try/catch blocks around PDO operations with generic user messages
- **Single Strategy**: Application manages only one strategy record (INSERT or UPDATE)

## Dependencies

- No Composer dependencies planned
- No npm/Node.js dependencies  
- Tailwind CSS loaded via `<script src="https://cdn.tailwindcss.com"></script>`
- All functionality implemented in vanilla PHP

## Current State

Project is in initial phase with only documentation and placeholder files. The `index.php` file exists but is empty and needs full implementation according to the specifications in `final-source.md`.