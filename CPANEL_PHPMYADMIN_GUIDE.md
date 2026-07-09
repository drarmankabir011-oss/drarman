# cPanel & PhpMyAdmin Integration Guide

## Overview

This guide covers deploying Dr. Arman Kabir's Care application on cPanel hosting with PhpMyAdmin database access.

## Prerequisites

- cPanel hosting account with:
  - ✓ Node.js support (v20.x or higher)
  - ✓ Apache with mod_rewrite enabled
  - ✓ SSH access
  - ✓ pnpm or npm for package management
- SSL/TLS certificate (AutoSSL recommended)

## Deployment Steps

### 1. SSH into cPanel Server

```bash
ssh your-username@yourdomain.com
# Or with IP: ssh your-username@your.server.ip
```

### 2. Clone Repository

```bash
cd ~
git clone https://github.com/armankabirzosid011-boop/drarman.git
cd drarman
```

### 3. Run Deployment Script

```bash
bash deploy.sh
```

The script will automatically:
- ✓ Verify Node.js v20+ and pnpm are installed
- ✓ Install dependencies
- ✓ Build the React frontend
- ✓ Deploy files to `public_html/`
- ✓ Copy `.htaccess` configuration for SPA routing
- ✓ Set correct file permissions (644 files, 755 directories)
- ✓ Create automatic backup of previous deployment

### 4. Post-Deployment in cPanel

1. **Log into cPanel Dashboard**
   - URL: `https://yourdomain.com:2083` (default port)
   - Or: `cPanel/WHM Icon → Account Information`

2. **Verify SSL Certificate**
   - Go to: **SSL/TLS Status**
   - Click: **Manage SSL sites**
   - Select your domain and install **AutoSSL** (free, automated renewal)
   - Status should show: ✓ Active

3. **Enable Required Apache Modules** (if not enabled)
   - Go to: **Apache Handlers** (under Advanced)
   - Verify `mod_rewrite` is listed
   - Verify `mod_deflate` is listed (compression)
   - Verify `mod_expires` is listed (caching)

## PhpMyAdmin Access

### Method 1: Via cPanel GUI (Recommended)

1. Log into cPanel Dashboard
2. Scroll down → Find **Databases** section
3. Click **phpMyAdmin**
4. Opens in new tab with automatic authentication
5. Full database access (no login required when accessed this way)

### Method 2: Direct URL Access

```
https://yourdomain.com/phpmyadmin
```

**Login credentials:**
- Username: `cpanel_username`
- Password: `cpanel_password` (your cPanel login)

### Method 3: Via SSH (Command Line)

```bash
# Connect to MySQL locally
mysql -u cpanel_username -p

# List all databases
SHOW DATABASES;

# Select database
USE database_name;

# Show all tables
SHOW TABLES;
```

## Database Setup for Dr. Arman Care

If you need to set up databases for the application:

### Via PhpMyAdmin GUI

1. Open **phpMyAdmin** (see above)
2. Click **New** (left sidebar)
3. Enter database name: `drarmankabir_care`
4. Click **Create**
5. Select the new database
6. Go to **Privileges** tab
7. Click **Add user**
   - Username: `drarmankabir_user`
   - Host: `localhost`
   - Password: (auto-generate secure password)
   - Grant ALL privileges
   - Click **Go**

### Via SSH/Command Line

```bash
# Connect to MySQL
mysql -u cpanel_username -p

# Create database
CREATE DATABASE drarmankabir_care;

# Create user
CREATE USER 'drarmankabir_user'@'localhost' IDENTIFIED BY 'secure_password_here';

# Grant privileges
GRANT ALL PRIVILEGES ON drarmankabir_care.* TO 'drarmankabir_user'@'localhost';
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

## Configuration Files

### .htaccess (React SPA Routing)

Located at: `public_html/.htaccess`

```apacheconf
# Enables:
✓ GZIP compression (mod_deflate)
✓ Browser caching (mod_expires)
✓ SPA routing (mod_rewrite) - all URLs point to index.html
✓ Security headers (X-Frame-Options, CSP, etc.)
```

**If SPA routing breaks (404 on refresh):**

1. Verify `.htaccess` is in `public_html/`
2. Check syntax: `apachectl configtest` (requires shell access)
3. In cPanel → **Apache Handlers** → verify `mod_rewrite` enabled
4. Restart Apache: cPanel → **Restart Services** → **Apache**

### env.json (Environment Configuration)

Located at: `public_html/env.json`

```json
{
  "apiBaseUrl": "https://yourdomain.com/api",
  "environment": "production",
  "version": "1.0.0"
}
```

Update this if your API endpoints change.

## Testing Deployment

### 1. Frontend SPA Routing

```bash
# Test direct routes work (no hash routing needed)
curl -I https://yourdomain.com/dashboard
curl -I https://yourdomain.com/patients/123
curl -I https://yourdomain.com/settings

# All should return 200 OK and serve index.html
```

### 2. Browser Testing

- Open: `https://yourdomain.com/`
- Should load the application homepage
- Click navigation links → should NOT show 404
- Refresh page (Ctrl+F5) → should still show app
- Open developer console (F12) → check for errors

### 3. Performance Check

- Open DevTools → Network tab
- Check for:
  - ✓ CSS/JS files compressed (Content-Encoding: gzip)
  - ✓ Cache-Control headers present
  - ✓ No mixed HTTP/HTTPS warnings
  - ✓ Load time < 3 seconds

### 4. Database Connectivity (if backend needed)

```bash
# Test connection to database
mysql -u drarmankabir_user -p drarmankabir_care -e "SHOW TABLES;"

# Should connect without errors
```

## Troubleshooting

### Issue: 404 on Page Refresh

**Symptom:** Homepage works, but `/dashboard` → 404

**Fix:**
1. Verify `.htaccess` exists: `ls -la public_html/.htaccess`
2. Check syntax error (Line 30 should be `ExpiresDefault`, not `Default`)
3. Enable mod_rewrite:
   ```bash
   # cPanel: Home → Advanced → Apache Handlers
   # Verify: mod_rewrite is listed
   ```
4. Restart Apache: cPanel → **Restart Services** → **Apache**

### Issue: Slow Performance / Large Bundle

**Symptom:** Page loads slowly, large JS/CSS files

**Causes:**
- Unnecessary ICP dependencies (~500KB unused)
- Unoptimized images
- GZIP not enabled

**Fix:**
```bash
# Remove ICP packages (if not using blockchain)
cd src/frontend
pnpm remove @dfinity/agent @dfinity/auth-client @dfinity/candid \
  @dfinity/identity @dfinity/principal @icp-sdk/core
pnpm build
bash ../../deploy.sh
```

### Issue: Permission Denied Errors

**Symptom:** "Permission denied" when accessing files

**Fix:**
```bash
# SSH into server and fix permissions
cd public_html
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 .htaccess
```

### Issue: PHP Not Working (if backend uses PHP)

**Note:** Current app is React frontend only. If you add PHP backend:

1. Save PHP files in appropriate directory (not in public_html root)
2. Configure PHP version in cPanel: **Software → Select PHP Version**
3. Test: `php -v` via SSH
4. Enable extensions if needed via cPanel

### Issue: Database Connection Fails

**Symptom:** Backend cannot connect to database

**Debug:**
```bash
# Check MySQL is running
ssh your-user@yourdomain.com
mysql -u cpanel_username -p -e "SELECT 1;"

# If fails, contact hosting provider
# MySQL might be down or credentials wrong
```

## Updating Application

### Standard Update (Minor Changes)

```bash
cd ~/drarman
git pull origin main
bash deploy.sh
```

### Full Rebuild (Major Changes, Dependencies Updated)

```bash
cd ~/drarman
git pull origin main
pnpm install --no-frozen-lockfile
bash deploy.sh
```

### Rollback to Previous Version

The deployment script creates automatic backups:

```bash
# List backups
ls -la | grep "public_html.backup"

# Restore specific backup
rm -rf public_html
mv public_html.backup.1234567890 public_html
```

## Security Checklist

Before going live:

- [ ] SSL certificate installed and active (green lock 🔒)
- [ ] `.htaccess` security headers configured
- [ ] File permissions set: 644 files, 755 directories
- [ ] No `node_modules` exposed in public_html
- [ ] `.env` and sensitive config NOT in git
- [ ] PhpMyAdmin access restricted (cPanel login only)
- [ ] Regular backups enabled (cPanel → Backup)
- [ ] Monitor error logs: cPanel → Error Log

## Performance Optimization

### 1. Enable HTTP/2

- cPanel → **EasyApache 4** (or **Apache Modules**)
- Enable: `mod_http2`
- Supported by most modern browsers

### 2. Database Optimization

Via PhpMyAdmin:

1. Select database → **Check all** → Choose **Optimize table**
2. Or via SSH:
   ```sql
   OPTIMIZE TABLE table_name;
   ```

### 3. Image Optimization

Reduce bundle size:

```bash
# Compress images before deployment
cd src/frontend/public/assets
find . -name "*.png" -exec pngquant 256 {} \;
find . -name "*.jpg" -exec jpegoptim -m 85 {} \;
```

### 4. Enable CDN for Static Assets

Set up Cloudflare (free tier):

1. Create account at cloudflare.com
2. Add your domain
3. Update nameservers to Cloudflare's
4. Benefits: ✓ Global CDN, ✓ DDoS protection, ✓ 50+ countries

## Support & Resources

### cPanel Help

- **cPanel Docs:** https://documentation.cpanel.net/
- **Error Logs:** cPanel → Home → **Error Log**
- **Support Ticket:** cPanel → **Support** or contact hosting provider

### React/Frontend Issues

- **React Router Docs:** https://reactrouter.com/
- **Vite Build Docs:** https://vitejs.dev/

### Database Issues

- **MySQL Docs:** https://dev.mysql.com/doc/
- **PhpMyAdmin Help:** https://www.phpmyadmin.net/

### Healthcare Data Compliance

- **HIPAA** (US): Patient data encryption, access logs
- **GDPR** (EU): Data privacy policies
- **Consult:** Your hosting provider's compliance documentation

## Next Steps

1. ✓ Run `bash deploy.sh` from your cPanel SSH connection
2. ✓ Open `https://yourdomain.com` in browser
3. ✓ Test SPA routing (click links, refresh page)
4. ✓ Access PhpMyAdmin if using database backend
5. ✓ Set up SSL certificate (AutoSSL)
6. ✓ Configure backups in cPanel
7. ✓ Monitor performance via cPanel metrics

---

**Last Updated:** 2026-07-09
**Status:** Ready for production deployment ✓
