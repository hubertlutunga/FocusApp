# FocusApp

## Deployment notes

This project loads `config/database.local.php` first when it exists.
Keep that file only on the server and out of Git.

To prepare production:

1. Copy `config/database.local.example.php` to `config/database.local.php`
2. Fill in the production database password on the server
3. Use cPanel `Pull or Update from Remote`, then `Deploy HEAD Commit`
