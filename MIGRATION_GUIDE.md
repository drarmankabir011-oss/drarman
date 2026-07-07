# ICP → PHP + MySQL Migration Guide

## Quick Start

### 1. Prerequisites
- cPanel hosting with PHP 8.1+
- MySQL 8.0+
- SSH access
- Git installed locally

### 2. Setup Steps

#### Step 1: Clone Repository
```bash
cd /home/cpanel_user/public_html
git clone -b feat/php-mysql-migration https://github.com/zosidsamia/drarmankabir.git .
```

#### Step 2: Install PHP Dependencies
```bash
cd php
composer install
```

#### Step 3: Setup Environment
```bash
cp ../.env.example .env
# Edit .env with your database credentials
nano .env
```

#### Step 4: Create Database
```bash
mysql -u cpanel_user -p < ../database/schema.sql
```

#### Step 5: Deploy Frontend
```bash
cd ../src/frontend
pnpm install
pnpm build
# Copy dist to public/
```

### 3. Database Schema
The complete schema is in `database/schema.sql` and includes:
- Users & Authentication (9 roles)
- Patients & Demographics
- Admissions & Bed Management
- Vitals & Clinical Monitoring
- Visits & Consultations
- SOAP Notes & Ward Rounds
- Investigations & Lab Results
- Prescriptions & Medications
- Clinical Alerts
- Appointments
- Billing & Payments
- Audit Logging

### 4. API Integration
Update frontend API base URL in `src/frontend/src/api/client.ts`:
```typescript
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api';
```

### 5. Key Files
- **Configuration**: `php/src/config/`
- **Models**: `php/src/models/`
- **Controllers**: `php/src/controllers/`
- **Middleware**: `php/src/middleware/`
- **Services**: `php/src/services/`
- **Database**: `database/schema.sql`

## Architecture Overview

### Request Flow
1. Frontend makes HTTP request
2. `.htaccess` routes to `php/public/index.php`
3. Router matches endpoint to controller
4. Middleware validates auth & permissions
5. Controller calls models/services
6. Response returned as JSON

### RBAC (Role-Based Access Control)
9 roles with specific permissions:
- **Admin**: Full access
- **Consultant**: Clinical leads
- **Registrar**: Senior MOs
- **Medical Officer**: SOAP reviewers
- **Intern**: Note writers
- **Nurse**: Vitals recorders
- **Reception**: Admissions & billing
- **Patient**: Self-service

## Migration Checklist

- [ ] Database created and schema imported
- [ ] Environment configured (.env)
- [ ] Composer dependencies installed
- [ ] Frontend built and deployed
- [ ] SSL certificate installed
- [ ] API endpoints tested
- [ ] Authentication verified
- [ ] RBAC tested for all roles
- [ ] Sample data imported (if exists)
- [ ] Backup created

## Testing

```bash
# Test database connection
php -r "require 'php/src/config/Database.php';"

# Test API endpoint
curl -X GET http://localhost/api/

# Run PHP linter
php -l php/src/models/Patient.php
```

## Troubleshooting

### Database Connection Error
- Check credentials in .env
- Verify MySQL service is running
- Check character set is utf8mb4

### JWT Authentication Error
- Ensure JWT_SECRET is set in .env
- Check token format: `Bearer <token>`
- Verify token hasn't expired

### RBAC Permission Denied
- Check user role in database
- Verify permission mapping in `Authorization.php`
- Check JWT payload includes role

## Performance Tips

1. Enable query caching in MySQL
2. Add indexes for frequently queried columns
3. Implement pagination (limit 50)
4. Use connection pooling
5. Cache commonly used data

## Security

- ✅ JWT authentication
- ✅ RBAC enforcement
- ✅ Prepared statements (SQL injection prevention)
- ✅ Password hashing (bcrypt)
- ✅ CORS headers configured
- ✅ Audit logging enabled

## Next Steps

1. Implement remaining controllers
2. Add more service layers
3. Setup email/WhatsApp notifications
4. Configure offline sync queue
5. Add comprehensive testing

## Support

For issues, contact: support@drarmankabir.clinic
