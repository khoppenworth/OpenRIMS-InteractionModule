# OpenRIMS Interaction Module

A lightweight LAMP-ready drug interaction knowledge base inspired by [interaktionsdatabasen.dk](https://www.interaktionsdatabasen.dk/). The application exposes the data in multilingual AdminLTE 3.2 UI as well as HL7® FHIR® JSON for interoperability with electronic medicines record systems, supply chain partners, and regulators.

## Features

- 🇬🇧/🇫🇷 bilingual interface (English and French) with simple locale switching.
- AdminLTE 3.2 white theme with rounded cards and dark-yellow accents optimized for clinical readability.
- Drug interaction registry stored in MySQL with ATC classification metadata.
- CSV import/export workflows for bulk maintenance of interaction pairs.
- FHIR `Bundle` endpoint composed of `MedicationKnowledge` resources for system-to-system exchange.
- Sample dataset aligned with ATC coding to bootstrap deployments.

## Requirements

- Linux server with Apache 2.4+, PHP 8.0+ and MySQL 8 (standard LAMP stack).
- PHP extensions: `pdo_mysql`, `mbstring`, `json`, `session`.

## Installation

1. Clone the repository to your Apache document root.
2. Create a MySQL database and user, then import the schema and sample data:
   ```sql
   CREATE DATABASE openrims_interactions CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'openrims'@'localhost' IDENTIFIED BY 'secret';
   GRANT ALL PRIVILEGES ON openrims_interactions.* TO 'openrims'@'localhost';
   FLUSH PRIVILEGES;
   ```
   ```bash
   mysql -u openrims -p openrims_interactions < database/schema.sql
   mysql -u openrims -p openrims_interactions < database/sample_data.sql
   ```
3. Adjust `config/database.php` to match your database credentials (or provide environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`).
4. Point Apache to the `public/` directory and enable HTTPS. Example vhost snippet:
   ```apache
   DocumentRoot /var/www/openrims/public
   <Directory /var/www/openrims/public>
       AllowOverride All
       Require all granted
   </Directory>
   ```
5. Visit the site in a browser. Use the language selector in the header to switch between English and French.

## Data Exchange

- **CSV Export:** `GET /export.php` (respects `query` and `severity` filters).
- **CSV Import:** `POST /import.php` with `multipart/form-data` containing a `file` field. Required headers: `drug_a_name, drug_a_atc, drug_b_name, drug_b_atc, severity, description, clinical_management, evidence_level, source_url`.
- **FHIR Bundle:** `GET /api/fhir/bundle.php` returns an `application/fhir+json` bundle of `MedicationKnowledge` resources.

## Development Notes

- Business logic lives in `app/InteractionService.php`; persistence is handled by `app/InteractionRepository.php`.
- Translations are stored under `resources/lang`.
- UI customization can be adjusted in `public/assets/custom.css`.

## License

MIT License. See [LICENSE](LICENSE).
