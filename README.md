# Portfolio Application

## 📁 Project Structure

```
tanuisila/
├── public/                 # Client-facing portfolio pages
│   └── index.php          # Main portfolio page (public view)
│
├── admin/                  # Admin management panel
│   └── index.php          # Admin dashboard & content management
│
├── config/                 # Configuration & database
│   ├── config.php         # Database connection settings
│   └── db.sql             # Database schema & setup
│
├── includes/              # Shared utilities
│   └── db_check.php       # Database verification utilities
│
├── assets/                # Static files
│   ├── css/               # Stylesheets
│   │   └── styles.css     # Main stylesheet
│   ├── js/                # JavaScript files (add as needed)
│   └── images/            # Image files (add as needed)
│
├── index.php              # Entry point (routes to sections)
├── README.md              # This file
└── Tanui-Sila-Logo.png    # Portfolio logo
```

## 🚀 Quick Setup

1. Copy files to your web server document root (e.g., `htdocs` or `www`)
2. Edit `config/config.php` and set `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
3. Import `config/db.sql` into phpMyAdmin to create database
4. Browse to `index.php` to view the public portfolio
5. Access admin at `admin/index.php?key=change-me` (update `ADMIN_KEY` in `config/config.php`)

## 📖 Access Points

- **Public Portfolio**: `index.php` → displays portfolio to visitors
- **Admin Panel**: `admin/index.php?key=YOUR_ADMIN_KEY` → manage content

## ⚙️ Configuration

All database settings are in `config/config.php`:
- `DB_HOST` - MySQL server address
- `DB_USER` - MySQL username
- `DB_PASS` - MySQL password
- `DB_NAME` - Database name
- `ADMIN_KEY` - Secret key for admin access (⚠️ change immediately)

## 🛡️ Security Notes

- The admin endpoint uses basic key authentication via URL parameter
- For production, implement proper authentication (login system)
- Database queries use PDO prepared statements (SQL injection safe)
- Keep `ADMIN_KEY` confidential and change from default
- Never commit `config/config.php` with real credentials to version control

## 🎨 Customization

- Edit `public/index.php` HTML structure
- Update `assets/css/styles.css` for styling
- Add JavaScript to `assets/js/` folder
- Store images in `assets/images/` folder
