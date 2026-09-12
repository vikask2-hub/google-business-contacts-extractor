# Google Business Contacts Extractor

[![Live demo](https://img.shields.io/badge/Live_Demo-tech4projects.online-2563eb?style=for-the-badge)](https://tech4projects.online/gmb-extractor)
[![Google Places](https://img.shields.io/badge/API-Google_Places-4285f4?logo=googlemaps)](https://developers.google.com/maps/documentation/places/web-service)

A clean sales-prospecting tool that turns a city and business category into an exportable local-business contact list powered by Google Places.

## Product highlights

- Curated city and category selectors for fast, consistent searches
- Live Google Places discovery with names, websites, phone numbers, and addresses
- Conservative public-email discovery from business websites
- Search-result caching and bounded outbound requests for a faster experience
- CSV export and responsive tabular results
- Graceful configuration and API error states with tested HTTP integrations

## Stack

PHP 8.3+ · Laravel 13 · Google Places API · Blade · Tailwind CSS 4 · PHPUnit

## Run locally

```bash
git clone https://github.com/vikask2-hub/google-business-contacts-extractor.git
cd google-business-contacts-extractor
composer install
cp .env.example .env
php artisan key:generate
# Add GOOGLE_PLACES_API_KEY to .env
npm install && npm run build
php artisan serve
```

API keys are read from the environment and are never committed.

---

Built by [Vikask2](https://github.com/vikask2-hub) · [View the complete product portfolio](https://tech4projects.online/)
