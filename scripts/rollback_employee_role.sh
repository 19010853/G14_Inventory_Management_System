#!/bin/bash

# Script to rollback employee role migration
# This will restore the users table from a backup file

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${RED}========================================${NC}"
echo -e "${RED}Employee Role Migration Rollback${NC}"
echo -e "${RED}========================================${NC}"
echo ""

# Get database credentials from .env
DB_DATABASE=$(grep DB_DATABASE /var/www/html/.env | cut -d '=' -f2 | tr -d ' ')
DB_USERNAME=$(grep DB_USERNAME /var/www/html/.env | cut -d '=' -f2 | tr -d ' ')
DB_PASSWORD=$(grep DB_PASSWORD /var/www/html/.env | cut -d '=' -f2 | tr -d ' ')

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo -e "${RED}Error: Could not read database credentials from .env${NC}"
    exit 1
fi

# Check if backup file is provided
if [ -z "$1" ]; then
    echo -e "${YELLOW}Usage: $0 <backup_file_path>${NC}"
    echo ""
    echo "Available backups:"
    ls -lh /var/www/html/backups/users_backup_*.sql 2>/dev/null || echo "No backups found"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}Error: Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

echo -e "${YELLOW}Warning: This will restore the users table from backup${NC}"
echo -e "${YELLOW}Current state will be lost!${NC}"
echo ""
echo -e "Backup file: ${YELLOW}$BACKUP_FILE${NC}"
echo -e "Database: ${YELLOW}$DB_DATABASE${NC}"
echo ""
echo -e "${RED}Are you sure you want to proceed? (yes/no)${NC}"
read -r response

if [[ ! "$response" == "yes" ]]; then
    echo "Rollback cancelled."
    exit 0
fi

echo ""
echo -e "${YELLOW}Creating current state backup before rollback...${NC}"

# Create a backup of current state before rollback
ROLLBACK_BACKUP="/var/www/html/backups/users_before_rollback_$(date +%Y%m%d_%H%M%S).sql"
mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" users > "$ROLLBACK_BACKUP" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Current state backed up: $ROLLBACK_BACKUP${NC}"
else
    echo -e "${RED}✗ Failed to create backup!${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Restoring from backup...${NC}"

# Restore from backup
mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Rollback completed successfully!${NC}"
    echo ""
    
    # Verify the rollback
    ADMIN_COUNT=$(mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -se "SELECT COUNT(*) FROM users WHERE role='admin';" 2>/dev/null || echo "0")
    EMPLOYEE_COUNT=$(mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -se "SELECT COUNT(*) FROM users WHERE role='employee';" 2>/dev/null || echo "0")
    
    echo -e "${GREEN}Verification:${NC}"
    echo -e "  Users with role='admin': ${YELLOW}$ADMIN_COUNT${NC}"
    echo -e "  Users with role='employee': ${YELLOW}$EMPLOYEE_COUNT${NC}"
    
    # Rollback the migration record
    echo ""
    echo -e "${YELLOW}Rolling back migration record...${NC}"
    cd /var/www/html || exit 1
    php artisan migrate:rollback --step=1 --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php
    
    echo ""
    echo -e "${GREEN}✓ Rollback completed!${NC}"
else
    echo -e "${RED}✗ Rollback failed!${NC}"
    echo -e "${YELLOW}To restore current state, run:${NC}"
    echo -e "  mysql -u$DB_USERNAME -p$DB_DATABASE < $ROLLBACK_BACKUP"
    exit 1
fi
