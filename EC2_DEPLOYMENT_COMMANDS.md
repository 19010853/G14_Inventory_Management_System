# EC2 Deployment Commands for Permission System Update

## ⚠️ IMPORTANT: Backup Before Deployment

Before running any commands, create a backup of your database and application files.

### 1. Backup Database
```bash
# SSH into your EC2 instance
ssh -i your-key.pem ubuntu@your-ec2-ip

# Navigate to project directory
cd /var/www/html/G14_Inventory_Management_System

# Create database backup
php artisan backup:run
# OR manually:
mysqldump -u your_db_user -p your_database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Backup Application Files
```bash
# Create a backup directory
sudo mkdir -p /var/backups/g14-inventory
sudo cp -r /var/www/html/G14_Inventory_Management_System /var/backups/g14-inventory/backup_$(date +%Y%m%d_%H%M%S)
```

## Deployment Steps

### Step 1: Pull Latest Changes
```bash
cd /var/www/html/G14_Inventory_Management_System

# If using Git
git pull origin main
# OR if using a different branch
git pull origin your-branch-name

# If not using Git, upload files via SCP or SFTP
```

### Step 2: Install/Update Dependencies
```bash
# Install Composer dependencies (if any new packages)
composer install --no-dev --optimize-autoloader

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Run Migration
```bash
# Run the migration to add new permissions and update existing roles
php artisan migrate

# Verify migration ran successfully
php artisan migrate:status
```

### Step 4: Run Permission Seeder (Optional - Only if needed)
```bash
# Only run if you need to ensure all permissions exist
# This is safe to run multiple times (uses updateOrCreate)
php artisan db:seed --class=PermissionSeeder
```

### Step 5: Clear All Caches
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Set Proper Permissions
```bash
# Set ownership (adjust user/group as needed)
sudo chown -R www-data:www-data /var/www/html/G14_Inventory_Management_System

# Set directory permissions
sudo find /var/www/html/G14_Inventory_Management_System -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/html/G14_Inventory_Management_System -type f -exec chmod 644 {} \;

# Set storage and cache permissions
sudo chmod -R 775 /var/www/html/G14_Inventory_Management_System/storage
sudo chmod -R 775 /var/www/html/G14_Inventory_Management_System/bootstrap/cache
```

### Step 7: Restart Services (if needed)
```bash
# Restart PHP-FPM (adjust service name based on your setup)
sudo systemctl restart php8.2-fpm
# OR
sudo service php8.2-fpm restart

# Restart Nginx
sudo systemctl restart nginx
# OR
sudo service nginx restart
```

## Verification Steps

### 1. Check Migration Status
```bash
php artisan migrate:status
```
Look for: `2026_01_09_171216_add_new_permissions_to_permissions_table` - should show as "Ran"

### 2. Verify Permissions in Database
```bash
php artisan tinker
```
Then run:
```php
use Spatie\Permission\Models\Permission;
Permission::where('name', 'like', '%.menu')->get();
Permission::where('name', 'like', 'all.%')->get();
```

### 3. Verify Role Permissions
```bash
php artisan tinker
```
Then run:
```php
use Spatie\Permission\Models\Role;
$role = Role::find(1); // Replace with your role ID
$role->permissions->pluck('name');
```

### 4. Test Application
- Log in as different users with different roles
- Verify sidebar shows correct menu items based on permissions
- Test accessing list pages with `.menu` permissions only
- Test accessing list pages with `all.*` permissions
- Verify that assigning `all.*` automatically includes `.menu`

## Rollback Instructions (If Needed)

### Rollback Migration
```bash
# Rollback last migration
php artisan migrate:rollback

# OR rollback specific migration
php artisan migrate:rollback --step=1
```

### Restore Database Backup
```bash
# Restore from backup
mysql -u your_db_user -p your_database_name < backup_YYYYMMDD_HHMMSS.sql
```

### Restore Application Files
```bash
# Restore from backup
sudo cp -r /var/backups/g14-inventory/backup_YYYYMMDD_HHMMSS/* /var/www/html/G14_Inventory_Management_System/
```

## Troubleshooting

### Issue: Migration fails
**Solution:**
```bash
# Check migration status
php artisan migrate:status

# Check for errors in logs
tail -f storage/logs/laravel.log

# Try running migration with verbose output
php artisan migrate -vvv
```

### Issue: Permissions not showing in UI
**Solution:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Re-run permission seeder
php artisan db:seed --class=PermissionSeeder
```

### Issue: Users can't access pages they should have access to
**Solution:**
```bash
# Check user permissions
php artisan tinker
$user = User::find(USER_ID);
$user->getAllPermissions();

# Check role permissions
$role = Role::find(ROLE_ID);
$role->permissions;
```

## Post-Deployment Checklist

- [ ] Database backup created
- [ ] Application files backed up
- [ ] Latest code pulled/uploaded
- [ ] Dependencies installed
- [ ] Migration ran successfully
- [ ] All caches cleared
- [ ] File permissions set correctly
- [ ] Services restarted
- [ ] Permissions verified in database
- [ ] Role permissions verified
- [ ] Application tested with different user roles
- [ ] Sidebar visibility tested
- [ ] List page access tested
- [ ] Permission assignment tested

## Notes

- The migration is **safe** to run on production as it:
  - Uses `updateOrCreate` to avoid duplicates
  - Preserves existing role permissions
  - Only adds new permissions, doesn't remove existing ones
  - Automatically updates roles to include `.menu` when they have `all.*`

- If you encounter any issues, check the Laravel logs:
  ```bash
  tail -f storage/logs/laravel.log
  ```

- For Docker/Sail environments:
  ```bash
  ./vendor/bin/sail artisan migrate
  ./vendor/bin/sail artisan cache:clear
  ```
