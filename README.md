<img src="KickScore/public/favicon.ico" width="50px">

# KickScore

## Quick Start Guide

After cloning the application, run `composer install` to install all necessary Symfony bundles and `npm install` to install Node modules (Webpack Encore, Tailwind).

The missing `.env` file will be provided.

Since the base application uses Webpack Encore to compile JavaScript and Tailwind CSS, you first need to run the following command:

```sh
user@host$~/KickScore/ npm run build
[...]
user@host$~/KickScore/ symfony serve
```

Each time you modify the CSS (in `/assets`) or JavaScript, you must recompile to apply the changes.

The Symfony server will automatically retrieve changes from `/public` without needing a restart.

---

## Coding Conventions - PHP Code

All PHP code is processed through a linter and formatted according to [PSR12](https://www.php-fig.org/psr/psr-12/) conventions.

---

## Coding Conventions - SQL Stored Procedures

- **Keywords in uppercase**: `IF`, `THEN`, `ENDIF`, `DECLARE`, `LOOP`  
- **Attributes and logical operators in lowercase**  
  → Creates contrast and improves readability

### Spacing:
- Between conditions
- Between loops
- After the start and end of loops
- Two lines above each procedure
- After the `BEGIN` of procedures  
  → Improves code clarity

### Additional Conventions:
- **Code and comments in English**  
  → Enhances maintainability
- **1 to 3 comments per function/method**  
  → Improves maintainability without overloading the code
- **Camel case**  
  → Avoids special characters and unifies naming conventions
