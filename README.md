# SEO Rank Tracker Dashboard

A PHP application for tracking and analyzing SEO keyword rankings across multiple search engines.

## Overview

The SEO Rank Tracker Dashboard allows SEO professionals to import and analyze keyword ranking data for multiple client domains. The application supports tracking rankings across various search engines, comparing performance against baseline reports, and visualizing ranking changes over time.

## Features

- **Multi-client support**: Track SEO rankings for multiple domains
- **Multi-engine support**: Import and analyze rankings from Google, Google Mobile, Yahoo, Bing
- **CSV Import**: Easily import data from CSV exports of popular rank tracking tools
- **Baseline comparisons**: Set baseline reports to compare performance over time
- **Position tracking**: View detailed position reports with change indicators
- **Historical data**: Track ranking changes over time with month-to-month navigation

## Installation

### Requirements

- PHP 7.2 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Composer (recommended for dependency management)

### Setup

1. Clone this repository to your web server directory
   ```
   git clone https://github.com/yourusername/rank-tracker-dashboard.git
   ```

2. Set up the database:
   - Create a new MySQL database
   - Import the database schema from `database/schema.sql`
   - Update database connection details in `app/config/database.php`

3. Configure file permissions:
   ```
   mkdir uploads
   chmod 755 uploads
   ```

4. Access the application through your web browser

## Usage

### Importing Data

1. Go to the Import page
2. Enter the client domain
3. Select the report period (YYYY-MM)
4. Optionally, check "Set as baseline report" to use this report as a baseline for future comparisons
5. Upload a CSV file with ranking data
6. Submit the form

### Viewing Reports

- **Dashboard**: View all clients and their latest reports
- **Client Reports**: View all reports for a specific client
- **Report Details**: View detailed ranking data for a specific report
- **Position Reports**: See keyword rankings across all configured search engines
- **Baseline Comparisons**: Compare current rankings against baseline reports

## Project Structure

```
rank-tracker-dashboard/
├── app/
│   ├── config/
│   │   ├── database.php
│   │   └── EngineConfig.php
│   ├── controllers/
│   │   ├── ClientController.php
│   │   ├── ImportController.php
│   │   └── ReportController.php
│   ├── models/
│   │   ├── Client.php
│   │   ├── RankingData.php
│   │   └── Report.php
│   ├── services/
│   │   ├── FileUploadService.php
│   │   ├── ImportService.php
│   │   └── RankingDataParser.php
│   └── views/
│       ├── clients/
│       ├── import/
│       ├── layout/
│       └── reports/
└── uploads/
```
