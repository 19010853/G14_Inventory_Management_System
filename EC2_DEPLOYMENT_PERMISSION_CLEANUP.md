# EC2 Deployment Instructions - Permission Cleanup

## Prerequisites

- SSH access to EC2 instance
- Database backup completed
- Code changes committed and pushed to repository
- Access to Laravel application directory

## Pre-Deployment Checklist

- [ ] **CRITICAL**: Backup production database
- [ ] Review all changes in `PERMISSION_CLEANUP_SUMMARY.md`
- [ ] Verify Git repository is up to date
- [ ] Ensure you have SSH access to EC2 instance
- [ ] Confirm application is running in maintenance mode is acceptable (if needed)

## Step-by-Step Deployment Instructions

### Step 1: SSH into EC2 Instance

```bash
ssh -i /path/to/your-key.pem ubuntu@your-ec2-ip
# Or if using a different user:
ssh -i /path/to/your-key.pem your-user@your-ec2-ip
```

### Step 2: Navigate to Application Directory

```bash
cd /var/www/html
# Or wherever your Laravel application is located
cd ~/G14_Inventory_Management_System
```

### Step 3: Enable Maintenance Mode (Optional but Recommended)

```bash
php artisan down --message="Updating permission system" --retry=60
```

This puts the application in maintenance mode to prevent users from accessing it during migration.

### Step 4: Pull Latest Code Changes

```bash
# If using Git
git pull origin main
# Or
git pull origin master

# Verify the new migration file exists
ls -la database/migrations/2026_01_10_000000_remove_old_permissions_and_update_roles.php
```

### Step 5: Install Dependencies (if needed)

```bash
composer install --no-dev --optimize-autoloader
```

### Step 6: **CRITICAL - Backup Database**

```bash
# Create backup directory if it doesn't exist
mkdir -p ~/backups

# Backup database (adjust credentials as needed)
# For MySQL/MariaDB:
mysqldump -u your_db_user -p your_database_name > ~/backups/backup_before_permission_cleanup_$(date +%Y%m%d_%H%M%S).sql

# Or if using Laravel Sail:
./vendor/bin/sail exec mysql mysqldump -u sail -p sail > ~/backups/backup_before_permission_cleanup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh ~/backups/backup_before_permission_cleanup_*.sql
```

**IMPORTANT**: Verify the backup file size is reasonable (not 0 bytes) before proceeding.

### Step 7: Clear Application Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 8: Run Database Migrations

```bash
# First, check migration status
php artisan migrate:status

# Run the new migration
php artisan migrate

# Verify migration completed successfully
php artisan migrate:status
```

**Expected Output**: You should see the new migration `2026_01_10_000000_remove_old_permissions_and_update_roles` in the list of ran migrations.

### Step 9: Verify Permission Changes

```bash
# Enter Laravel Tinker to verify permissions
php artisan tinker
```

In Tinker, run:

```php
// Check if old permissions are removed
use Spatie\Permission\Models\Permission;

Permission::whereIn('name', ['edit.brand', 'delete.brand', 'all.transfers', 'transfers.menu', 'reports.all'])->get();
// Should return empty collection

// Check if new permissions exist
Permission::whereIn('name', ['all.brand', 'all.transfer', 'transfer.menu', 'all.report', 'report.menu'])->get();
// Should return collection with these permissions

// Check a specific role (replace 'Role Name' with an actual role)
$role = \Spatie\Permission\Models\Role::where('name', 'Role Name')->first();
$role->permissions->pluck('name');
// Verify permissions look correct

exit
```

### Step 10: Run Permission Seeder (if needed)

```bash
# Only if you want to ensure all permissions are up to date
php artisan db:seed --class=PermissionSeeder
```

**Note**: This will update/create permissions but won't affect existing role assignments (except Super Admin which gets all permissions).

### Step 11: Clear Cache Again

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Step 12: Disable Maintenance Mode

```bash
php artisan up
```

### Step 13: Verify Application is Working

1. **Test Login**: Log in as a user with different permission levels
2. **Test Brand Module**:
   - User with `all.brand`: Should be able to add, edit, delete brands
   - User with only `brand.menu`: Should be able to view brands list but not edit/delete
3. **Test Transfer Module**:
   - User with `all.transfer`: Should be able to access all transfer features
   - User with only `transfer.menu`: Should be able to view transfers list
4. **Test Report Module**:
   - User with `all.report`: Should be able to access all report features
   - User with only `report.menu`: Should be able to view reports list
5. **Test Role & Permission UI**:
   - Check that checkbox synchronization works (checking `all.*` checks `*.menu`)
   - Check that unchecking `*.menu` unchecks `all.*`

### Step 14: Monitor Logs

```bash
# Watch Laravel logs for any errors
tail -f storage/logs/laravel.log

# Or if using Sail
./vendor/bin/sail exec laravel.test tail -f storage/logs/laravel.log
```

Monitor for:
- 403 Unauthorized errors
- Permission-related exceptions
- Database errors

## Post-Deployment Tasks

### 1. Review Role Permissions

Log in as Super Admin and review all roles to ensure they have the correct permissions:

1. Navigate to Role & Permission management
2. Check each role:
   - Roles that had `edit.brand` or `delete.brand` should now have `all.brand`
   - Roles that had `all.transfers` should now have `all.transfer` and `transfer.menu`
   - Roles that had `reports.all` should now have `all.report` and `report.menu`

### 2. Update Roles Manually (if needed)

If any roles are missing expected permissions:

1. Go to `/add/roles/permission` or `/admin/edit/roles/{id}`
2. Select the role
3. Check the appropriate permissions:
   - `all.brand` for roles that need full brand access
   - `transfer.menu` for roles that only need to view transfers
   - `all.transfer` for roles that need full transfer access
   - `all.report` for roles that need full report access
   - `report.menu` for roles that only need to view reports

### 3. Test Critical User Flows

Test with different user roles:

- [ ] Super Admin can access everything
- [ ] Users with `brand.menu` only can view brands
- [ ] Users with `all.brand` can add/edit/delete brands
- [ ] Users with `transfer.menu` only can view transfers
- [ ] Users with `all.transfer` can manage transfers
- [ ] Users with `report.menu` only can view reports
- [ ] Users with `all.report` can access all reports

## Rollback Procedure (if needed)

If something goes wrong:

### Option 1: Restore from Backup

```bash
# Stop application
php artisan down

# Restore database
mysql -u your_db_user -p your_database_name < ~/backups/backup_before_permission_cleanup_YYYYMMDD_HHMMSS.sql

# Revert code changes
git checkout HEAD~1  # Or specific commit before changes

# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart application
php artisan up
```

### Option 2: Manual Rollback (if backup not available)

```bash
# Recreate old permissions manually
php artisan tinker
```

```php
use Spatie\Permission\Models\Permission;

// Recreate old permissions (if needed)
Permission::create(['name' => 'edit.brand', 'guard_name' => 'web', 'group_name' => 'Brand']);
Permission::create(['name' => 'delete.brand', 'guard_name' => 'web', 'group_name' => 'Brand']);
// ... etc

exit
```

Then revert code changes and reassign permissions to roles.

## Troubleshooting

### Issue: Migration fails with "Permission not found"

**Solution**: The permission might have already been deleted. Check migration logs and skip if permission doesn't exist.

### Issue: Users getting 403 errors after deployment

**Solution**: 
1. Check if roles have correct permissions
2. Verify permission names match exactly (case-sensitive)
3. Clear all caches
4. Check Laravel logs for specific error messages

### Issue: Checkbox synchronization not working

**Solution**:
1. Clear browser cache
2. Verify JavaScript files are loaded
3. Check browser console for JavaScript errors
4. Verify permission names in JavaScript match database

### Issue: Old permissions still showing in UI

**Solution**:
1. Clear application cache: `php artisan cache:clear`
2. Clear view cache: `php artisan view:clear`
3. Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
4. Verify migration ran successfully

## Verification Commands

Run these commands to verify deployment:

```bash
# Check migration status
php artisan migrate:status | grep remove_old_permissions

# Check permissions in database
php artisan tinker
```

```php
use Spatie\Permission\Models\Permission;

// Should return empty
Permission::whereIn('name', ['edit.brand', 'delete.brand', 'all.transfers', 'transfers.menu', 'reports.all'])->count();

// Should return 1
Permission::where('name', 'all.report')->count();
Permission::where('name', 'all.transfer')->count();
Permission::where('name', 'transfer.menu')->count();

exit
```

## Important Notes

1. **Data Safety**: The migration is designed to preserve all role-permission relationships. No user data, roles, or transactions are deleted.

2. **Permission Migration**: 
   - `reports.all` → `all.report` (automatic)
   - `all.transfers` → `all.transfer` (automatic)
   - `edit.brand`/`delete.brand` → Manual review required (removed, not migrated)

3. **Super Admin**: Super Admin role automatically gets all permissions, so no action needed.

4. **Testing**: Always test in staging/dev environment first before production deployment.

5. **Backup**: Never skip the database backup step. It's your safety net.

## Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check migration status: `php artisan migrate:status`
3. Review this document and `PERMISSION_CLEANUP_SUMMARY.md`
4. Verify database backup is available for rollback

## Deployment Summary

**Files Changed**: 12 files
**Migrations**: 1 new migration
**Permissions Removed**: 5 old permissions
**Permissions Renamed**: 1 permission (`reports.all` → `all.report`)
**Data Impact**: None (only permission structure changed)
**Downtime**: Minimal (maintenance mode recommended during migration)

---

**Last Updated**: 2026-01-10
**Version**: 1.0
