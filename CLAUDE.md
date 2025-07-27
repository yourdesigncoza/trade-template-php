# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Trading Journal & Execution Tracker** - a comprehensive PHP web application that helps traders log, review, and maintain discipline when executing trades based on predefined strategies. The system enforces entry/exit criteria, tracks emotional state, and logs both executed and missed trades.

## Development Commands

### Database Setup
```bash
# Initialize/reset database schema
/opt/lampp/bin/mysql -u root trade_template < database/schema.sql

# Access database directly
/opt/lampp/bin/mysql -u root trade_template
```

### Development Server
```bash
# Start XAMPP services (Apache + MySQL)
sudo /opt/lampp/lampp start

# Access application
http://localhost/trade-template-php/
```

### Testing
- No automated test framework configured
- Manual testing via browser interface
- Sample data included in schema.sql for testing

## Core Architecture

### Multi-Page Application Structure
The system consists of four main pages with distinct responsibilities:

1. **index.php** - Trade logging form with dynamic checklist enforcement
2. **strategies.php** - Strategy CRUD operations with criteria management  
3. **trade-history.php** - Historical trade data with filtering and analytics
4. **analytics.php** - Performance metrics and statistical analysis

### Database Design Philosophy
The system uses a **relational approach with JSON flexibility**:

- **Normalized relationships** for referential integrity (strategies → criteria → trades)
- **JSON columns** for arrays (timeframes, sessions) to avoid over-normalization
- **Audit trails** with created_at/updated_at timestamps
- **Cascading deletes** to maintain data consistency

### Key Data Flow
```
Strategy Creation → Criteria Definition → Trade Logging → Performance Analysis
     ↓                    ↓                  ↓              ↓
  strategies →      entry_criteria →    trades →      analytics.php
  table            exit_criteria     checklist_logs
                   invalidations     screenshots
```

## Security Architecture

### CSRF Protection
- All forms include CSRF tokens via `csrf_field()`
- Validation happens in `validate_csrf()` before any data processing
- Tokens regenerated after successful submissions

### Database Security
- **100% PDO prepared statements** - no direct SQL concatenation anywhere
- All user input sanitized through `sanitize()` function
- Database errors logged server-side, generic messages shown to users

### File Upload Security
- Upload validation in `validate_upload()` checks MIME types and file sizes
- Files stored outside web root in `/assets/uploads/`
- Unique filenames generated to prevent conflicts/overwriting

## Configuration Management

### Environment Variables
Database credentials stored in `.env` file:
```
DB_HOST=localhost
DB_NAME=trade_template  
DB_USER=root
DB_PASS=
```

### Configuration Loading
`includes/config.php` handles:
- `.env` file parsing (custom implementation, no external library)
- Session initialization for CSRF
- Application constants and timezone setup
- Error reporting configuration

## Dynamic Form System

### Strategy-Driven UI
The trade form dynamically adapts based on selected strategy:
- AJAX call to `api/get-strategy-details.php` loads criteria
- JavaScript populates entry/exit checkboxes from database
- Form submission disabled until all entry criteria checked
- Price fields hidden/shown based on "trade taken" toggle

### Checklist Enforcement Logic
Critical business rule: **Trades cannot be submitted unless ALL entry criteria are checked**
- Implemented in `assets/js/trade-form.js`
- Server-side validation in `includes/validation.php` 
- Prevents emotional/undisciplined trade entries

## Data Calculation Engine

### R-Multiple Calculation
Real-time calculation of risk/reward ratio:
```php
function calculate_r_multiple($entry, $stop, $exit) {
    $risk = abs($entry - $stop);
    $reward = $exit - $entry;
    // Handle short trades
    if ($stop > $entry) {
        $reward = $entry - $exit;
    }
    return round($reward / $risk, 2);
}
```

### Analytics Aggregation
Performance metrics calculated in `analytics.php`:
- Win rate, expectancy, total R across all trades
- Strategy-specific performance breakdowns  
- Equity curve progression (simplified text representation)

## API Endpoints

### Internal AJAX APIs
- `api/get-strategy-details.php` - Returns strategy with all related criteria
- `api/save-trade.php` - Handles complex trade submission with file uploads

Both APIs use POST-only with CSRF validation and return JSON responses.

## Development Guidelines

### Database Queries
- Always use PDO prepared statements via `db()` helper function
- Wrap in try/catch blocks with appropriate error handling
- Use transactions for multi-table operations (see `api/save-trade.php`)

### Form Handling Pattern
1. Include required files (`config.php`, `db.php`, `csrf.php`, etc.)
2. Initialize variables and get data for dropdowns/lists
3. Handle POST requests with CSRF validation
4. Process data with validation functions
5. Redirect with flash messages on success/error

### JavaScript Integration
- Vanilla JavaScript only (no frameworks)
- Event-driven architecture for form interactions
- AJAX calls use fetch API with proper error handling
- Form state management for complex conditional fields

## File Upload System

Images stored in `assets/uploads/` with database references:
- Original filename preserved in database for user reference
- Actual files renamed with unique identifiers
- File validation before processing
- Automatic cleanup on trade deletion (cascading foreign keys)

## Current State

The system is fully implemented and functional with:
- Complete database schema with sample data
- All four main pages operational
- AJAX-driven dynamic forms
- Basic analytics and reporting
- Security measures and validation
- Responsive Tailwind CSS design