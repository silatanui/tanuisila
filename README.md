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
2. Copy `.env.example` to `.env` and fill in your local credentials
3. Import `config/db.sql` into phpMyAdmin to create the tables
4. Run `C:\xampp\php\php.exe database/seed.php` once to add the portfolio data
5. Browse to `index.php` to view the public portfolio
6. Access the admin login at `admin/login.php`

## 📖 Access Points

- **Public Portfolio**: `index.php` → displays portfolio to visitors
- **Admin Panel**: `admin/index.php?key=YOUR_ADMIN_KEY` → manage content

## ⚙️ Configuration

Runtime settings are loaded from the untracked `.env` file:
- `DB_HOST` - MySQL server address
- `DB_USER` - MySQL username
- `DB_PASS` - MySQL password
- `DB_NAME` - Database name
- `ADMIN_KEY` - Optional secret key for legacy access (set a unique value if used)

## 🛡️ Security Notes

- The admin endpoint uses basic key authentication via URL parameter
- For production, implement proper authentication (login system)
- Database queries use PDO prepared statements (SQL injection safe)
- Keep `ADMIN_KEY` confidential and change from default
- Never commit `.env` or real credentials to version control
- Use `.env.example` as the public configuration template

## 🎨 Customization

- Edit `public/index.php` HTML structure
- Update `assets/css/styles.css` for styling
- Add JavaScript to `assets/js/` folder
- Store images in `assets/images/` folder
