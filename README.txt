1) Place the `result-system` directory into your web server root.
2) Create the database: import db.sql into MySQL (phpMyAdmin or CLI).
   - e.g. mysql -u root -p < db.sql
3) Update config.php with DB credentials.
4) Run create_admin.php (via browser or CLI) to create an admin account:
   - Browser: open http://yoursite/result-system/create_admin.php and submit username/password
   - OR CLI: php create_admin.php admin mypassword
5) (Optional) Remove or secure create_admin.php after use.
6) Open http://yoursite/result-system/admin.php to login and upload CSV files.
   CSV format: header must start with: roll,name, then subjects. Example:
     roll,name,Bangla,English,Math,Science
   Rows: 101,John Doe,78,80,90,85
7) Students can search on http://yoursite/result-system/index.php
8) Printable marksheet is available from search results.

Notes:
- This is a simple system meant as a starting point. For production:
  * Add CSRF protection, stronger authentication, input validation.
  * Add file size limits and validation for CSV.
  * Consider role permissions and HTTPS.
  * Back up DB regularly.
