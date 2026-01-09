#!/bin/bash

# Script to safely migrate admin role to employee role on EC2
# This script includes backup, verification, and rollback capabilities

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Safe Employee Role Migration Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Configuration
BACKUP_DIR="/var/www/html/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/users_backup_${TIMESTAMP}.sql"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo -e "${YELLOW}Step 1: Checking current database state...${NC}"
echo ""

# Get database credentials from .env
DB_DATABASE=$(grep DB_DATABASE /var/www/html/.env | cut -d '=' -f2 | tr -d ' ')
DB_USERNAME=$(grep DB_USERNAME /var/www/html/.env | cut -d '=' -f2 | tr -d ' ')
DB_PASSWORD=$(grep DB_PASSWORD /var/www/html/.env | cut -d '=' -f2 | tr -d ' ')

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo -e "${RED}Error: Could not read database credentials from .env${NC}"
    exit 1
fi

# Count users with admin role
ADMIN_COUNT=$(mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -se "SELECT COUNT(*) FROM users WHERE role='admin';" 2>/dev/null || echo "0")
EMPLOYEE_COUNT=$(mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -se "SELECT COUNT(*) FROM users WHERE role='employee';" 2>/dev/null || echo "0")

echo -e "Current users with role='admin': ${YELLOW}$ADMIN_COUNT${NC}"
echo -e "Current users with role='employee': ${YELLOW}$EMPLOYEE_COUNT${NC}"
echo ""

if [ "$ADMIN_COUNT" -eq 0 ]; then
    echo -e "${GREEN}No users with 'admin' role found. Migration may not be necessary.${NC}"
    echo -e "${YELLOW}Do you want to continue anyway? (y/n)${NC}"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo "Migration cancelled."
        exit 0
    fi
fi

echo -e "${YELLOW}Step 2: Creating database backup...${NC}"
echo ""

# Create backup of users table
mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" users > "$BACKUP_FILE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup created successfully: $BACKUP_FILE${NC}"
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo -e "  Backup size: $BACKUP_SIZE"
else
    echo -e "${RED}✗ Failed to create backup!${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 3: Preview of users that will be updated...${NC}"
echo ""

# Show users that will be affected
mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "SELECT id, name, email, role FROM users WHERE role='admin';" 2>/dev/null

echo ""
echo -e "${YELLOW}Step 4: Ready to run migration${NC}"
echo -e "This will update ${RED}$ADMIN_COUNT${NC} user(s) from role='admin' to role='employee'"
echo ""
echo -e "${YELLOW}Do you want to proceed? (y/n)${NC}"
read -r response

if [[ ! "$response" =~ ^[Yy]$ ]]; then
    echo "Migration cancelled. Backup is saved at: $BACKUP_FILE"
    exit 0
fi

echo ""
echo -e "${YELLOW}Step 5: Running migration...${NC}"

# Navigate to project directory
cd /var/www/html || exit 1

# Run the migration
php artisan migrate --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ Migration completed successfully!${NC}"
    echo ""
    
    # Verify the migration
    NEW_ADMIN_COUNT=$(mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -se "SELECT COUNT(*) FROM users WHERE role='admin';" 2>/dev/null || echo "0")
    NEW_EMPLOYEE_COUNT=$(mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -se "SELECT COUNT(*) FROM users WHERE role='employee';" 2>/dev/null || echo "0")
    
    echo -e "${GREEN}Verification:${NC}"
    echo -e "  Users with role='admin': ${YELLOW}$NEW_ADMIN_COUNT${NC}"
    echo -e "  Users with role='employee': ${YELLOW}$NEW_EMPLOYEE_COUNT${NC}"
    echo ""
    
    if [ "$NEW_ADMIN_COUNT" -eq 0 ] && [ "$NEW_EMPLOYEE_COUNT" -gt 0 ]; then
        echo -e "${GREEN}✓ Migration verified successfully!${NC}"
        echo ""
        echo -e "${GREEN}Backup saved at: $BACKUP_FILE${NC}"
        echo -e "${YELLOW}To rollback, use: mysql -u$DB_USERNAME -p$DB_DATABASE < $BACKUP_FILE${NC}"
    else
        echo -e "${YELLOW}⚠ Warning: Migration results may need manual verification${NC}"
    fi
else
    echo ""
    echo -e "${RED}✗ Migration failed!${NC}"
    echo -e "${YELLOW}To restore from backup, run:${NC}"
    echo -e "  mysql -u$DB_USERNAME -p$DB_DATABASE < $BACKUP_FILE"
    exit 1
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Migration completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
