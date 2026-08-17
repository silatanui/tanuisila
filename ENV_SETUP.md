# Environment Configuration Guide

## Overview
This project now uses environment variables for sensitive configuration to keep credentials out of version control.

## Setup Instructions

### 1. Install PHP dotenv (if not already installed)
```bash
composer require vlucas/phpdotenv
```

### 2. Create your `.env` file
Copy `.env.example` to `.env` and fill in your actual values:

```bash
cp .env.example .env
```

### 3. Configure `.env` with your credentials
Edit `.env` and add your actual values:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=ipusgkqs_portfolio_db
DB_USER=ipusgkqs_tanui
DB_PASS=your_actual_password_here

# Admin Credentials
ADMIN_USERNAME=silatanuikipngetich@gmail.com
ADMIN_PASSWORD=your_actual_password_here

# Admin Key (change this to something unique and secure)
ADMIN_KEY=your-secure-admin-key

# OpenAI API Key
OPENAI_API_KEY=sk-your-actual-openai-key-here
```

### 4. Important Security Notes

⚠️ **Never commit `.env` file to Git!** 
- `.env` is already in `.gitignore`
- It contains sensitive credentials
- Only `.env.example` should be in version control

✅ **Best practices:**
- Keep `.env` file only on your local machine and production server
- Use different credentials for development and production
- Rotate API keys and passwords regularly
- Use strong, unique passwords for database and admin accounts
- On shared hosting, ensure `.env` is outside the web root if possible

### 5. For Production Deployment

On your production server:
1. Create the `.env` file with production credentials
2. Ensure `.env` has restricted file permissions: `chmod 600 .env`
3. Keep the file path outside the web-accessible directory if possible
4. Consider using your hosting provider's environment variable management tools

## File Structure

```
project/
├── config/
│   └── config.php          # Loads env vars (no secrets here)
├── .env                    # Local config (NEVER commit)
├── .env.example            # Template for configuration
├── .gitignore              # Prevents .env from being committed
└── ...other files
```

## Troubleshooting

**"Database connection failed" error:**
1. Check that your `.env` file exists
2. Verify database credentials are correct
3. Ensure MySQL server is running
4. Check that the database user has proper permissions

**Variables not loading:**
1. Confirm `.env` file is in the project root directory
2. Check that `vlucas/phpdotenv` is installed
3. Verify `.env` file syntax (no extra spaces around `=`)

## Additional Security Recommendations

1. **Rotate credentials regularly** - Change database passwords and API keys periodically
2. **Use secrets manager** - Consider using your platform's secrets management (AWS Secrets Manager, Azure Key Vault, etc.)
3. **Limit database user permissions** - Create a database user with only necessary permissions
4. **Use HTTPS** - Always use HTTPS in production to protect credentials in transit
5. **Monitor logs** - Check server logs for unauthorized access attempts
