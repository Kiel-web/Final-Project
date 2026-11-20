# 🐾 WhiskerHub - Cat Shop & Adoption Site

IPT Final Project: a **custom PHP MVC framework (built from scratch, no Composer, no Laravel)** and a
**cat shop + adoption web application** built on top of it.

| Requirement | How it is met |
|---|---|
| Custom PHP framework | `core/` - Router, Request, Response, Middleware, Controller, Model, View, Database (PDO), Validator, Session, Auth, Config/Env, Cache, HttpClient, Migrator |
| E-commerce application | Shop, cart, checkout, orders, admin panel (+ adoption listings and inquiries) |
| External API | **TheCatAPI** - breed facts, breed photos, "cat of the day" |
| Database | MySQL / MariaDB (8 tables, foreign keys, transactions) |
| Documentation + presentation | see `docs/` |

---

## 1. Run it on XAMPP (Windows) - step by step

1. **Copy the folder** `whiskerhub` into `C:\xampp\htdocs\` (you get `C:\xampp\htdocs\whiskerhub`).
2. Open the **XAMPP Control Panel** and **Start Apache** and **Start MySQL**.
3. **Create the database** (choose ONE way):
   - *phpMyAdmin:* open <http://localhost/phpmyadmin> → **Import** tab → choose `database/whiskerhub_full.sql` → **Go**.
   - *or command line:* open **Shell** in the XAMPP panel and run  
     `cd C:\xampp\htdocs\whiskerhub` then `php bin/migrate.php --fresh`
4. **Get a free API key** (needed for breed facts and photos): sign up at <https://thecatapi.com/signup>,
   copy the key from the e-mail, open the file `.env` in Notepad and paste it:  
   `CAT_API_KEY=your-key-here`
5. Open **<http://localhost/whiskerhub/>** 🎉

### Demo accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@whiskerhub.test` | `admin123` |
| Customer | `customer@whiskerhub.test` | `customer123` |

You can also register your own customer account on the *Sign up* page.

### Different MySQL password / port?
Edit `.env` (`DB_USERNAME`, `DB_PASSWORD`, `DB_PORT`). XAMPP's default is user `root` with an empty password.

### Currency
Prices are shown in Philippine pesos (₱). To change the symbol, edit `APP_CURRENCY` in `.env` (for example `$`, `€`, `£`).

---

## 2. Try it without internet or without an API key (offline demo mode)

A tiny fake copy of TheCatAPI is included. In a second XAMPP **Shell** window run:

```
cd C:\xampp\htdocs\whiskerhub
php -S 127.0.0.1:8081 tests/mock_catapi/index.php
```

then in `.env` set `CAT_API_URL=http://127.0.0.1:8081/v1`. The site now works fully offline
(photos are simple placeholders). Set it back to `https://api.thecatapi.com/v1` for the real API.

---

## 3. Run the tests

```
cd C:\xampp\htdocs\whiskerhub
php tests/run.php                                  (unit tests - framework, validator, model, API errors)
php tests/smoke.php http://localhost/whiskerhub    (end-to-end test of the whole website)
```

For the API error-handling tests start the mock server first (section 2). The tests add a few test records
to the database; use `php bin/migrate.php --fresh` afterwards to reset the demo data.

---

## 4. Project structure

```
whiskerhub/
├── public/                 ← the ONLY folder the web server exposes
│   ├── index.php           ← front controller: every request enters here
│   ├── .htaccess           ← sends all URLs to index.php
│   └── assets/             ← css/style.css, js/app.js
├── core/                   ← THE FRAMEWORK (reusable, knows nothing about cats)
│   ├── Application.php     ← wires everything together, error pages
│   ├── Router.php          ← URL + HTTP method → controller action (+ middleware pipeline)
│   ├── Request.php / Response.php
│   ├── Controller.php / Model.php / View.php
│   ├── Database.php        ← PDO wrapper, prepared statements, transactions
│   ├── Validator.php, Session.php, Auth.php, Config.php, Env.php
│   ├── Cache.php, HttpClient.php (cURL), Logger.php, Migrator.php, helpers.php
│   └── Middleware/         ← CsrfMiddleware, LogMiddleware
├── app/                    ← THE APPLICATION (uses the framework)
│   ├── Controllers/        ← Home, Shop, Cart, Order, Adoption, Breed, Auth, Api  + Admin/*
│   ├── Models/             ← User, Category, Product, Pet, Inquiry, Order, OrderItem
│   ├── Middleware/         ← AuthMiddleware, AdminMiddleware, GuestMiddleware
│   ├── Services/           ← CatApiService (external API), CartService
│   └── Views/              ← PHP templates (layouts, partials, pages)
├── routes/web.php          ← the map of all URLs
├── config/                 ← app.php, database.php, services.php (read from .env)
├── database/               ← migrations/*.sql, seeds/seed.sql, whiskerhub_full.sql
├── bin/                    ← migrate.php, build-sql.php (command-line tools)
├── tests/                  ← run.php (unit), smoke.php (end-to-end), mock_catapi/
├── storage/logs/           ← request + error logs
└── docs/                   ← documentation, presentation script, screenshots
```

## 5. How a request flows (good for the presentation)

```
Browser → public/index.php → Application → Router → [LogMiddleware → CsrfMiddleware → AuthMiddleware → ...]
        → Controller → Model (MySQL) / Service (Cat API) → View (template) → Response → Browser
```

## 6. Troubleshooting

| Problem | Fix |
|---|---|
| "Database problem… is MySQL running?" | Start **MySQL** in XAMPP, import `database/whiskerhub_full.sql`, check `DB_*` in `.env`. |
| Every page except the home page shows 404 | Apache `mod_rewrite` must be on and `AllowOverride All` for htdocs (both are the XAMPP default). Restart Apache. |
| Breed guide says "API key is missing or invalid" | Put your key in `.env` as `CAT_API_KEY=...` - the change is used immediately. |
| "SSL certificate problem" in `storage/logs` | Set `CAT_API_CA_BUNDLE=C:/xampp/apache/bin/curl-ca-bundle.crt` in `.env`. |
| Photos missing but the site works | Normal when the API is unreachable or rate-limited - the site shows a placeholder and tries again later. |
| Port 80 or 3306 already in use | Stop Skype/IIS/other MySQL, or change the port in XAMPP *Config*. |

## 7. Credits
Cat photos and breed data: [TheCatAPI](https://thecatapi.com). Everything else was written for this course project.
