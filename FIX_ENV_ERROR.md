# Quick Fix for .env Line 60 Error

## Problem
```
./.env: line 60: qvyt: command not found
The environment file is invalid!
Failed to parse dotenv file. Encountered unexpected whitespace at [ewiu qvyt jlgz gpnq].
```

## Root Cause
Line 60 in your `.env` file contains `MAIL_PASSWORD` with spaces but **without quotes**. The Gmail App Password "ewiu qvyt jlgz gpnq" needs to be wrapped in quotes.

## Solution

### Option 1: Quick Fix via Command Line

```bash
cd ~/G14_Inventory_Management_System

# Backup first
cp .env .env.backup

# Fix MAIL_PASSWORD line - add quotes if missing
sed -i 's/^MAIL_PASSWORD=\([^"]*[[:space:]][^"]*\)$/MAIL_PASSWORD="\1"/' .env

# Alternative: If the above doesn't work, manually edit line 60
# Open the file
nano .env

# Find line 60 and change:
# MAIL_PASSWORD=ewiu qvyt jlgz gpnq
# To:
# MAIL_PASSWORD="ewiu qvyt jlgz gpnq"
```

### Option 2: Manual Fix (Recommended)

1. **Open the .env file:**
   ```bash
   nano .env
   ```

2. **Go to line 60** (press `Ctrl + _`, type `60`, press Enter)

3. **Find the MAIL_PASSWORD line**. It probably looks like:
   ```
   MAIL_PASSWORD=ewiu qvyt jlgz gpnq
   ```

4. **Change it to** (add quotes):
   ```
   MAIL_PASSWORD="ewiu qvyt jlgz gpnq"
   ```

5. **Save and exit:**
   - Press `Ctrl + X`
   - Press `Y` to confirm
   - Press `Enter` to save

### Option 3: Remove Spaces from Password

If you prefer, you can also remove the spaces from the app password:

```bash
# In nano, change:
MAIL_PASSWORD=ewiu qvyt jlgz gpnq
# To:
MAIL_PASSWORD=ewiuqvytjlgzgpnq
```

## Verify the Fix

After fixing, test the .env file:

```bash
# Clear config cache
./vendor/bin/sail artisan config:clear

# Try to cache config (this will validate .env)
./vendor/bin/sail artisan config:cache
```

If you see no errors, the fix worked! ✅

## Correct Format Examples

```env
# ✅ CORRECT - With quotes (spaces allowed)
MAIL_PASSWORD="ewiu qvyt jlgz gpnq"

# ✅ CORRECT - Without spaces (no quotes needed)
MAIL_PASSWORD=ewiuqvytjlgzgpnq

# ❌ WRONG - Spaces without quotes (causes error)
MAIL_PASSWORD=ewiu qvyt jlgz gpnq
```

## Complete Mail Configuration (Reference)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD="ewiu qvyt jlgz gpnq"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Group 14 Inventory System"
```

**Remember:** Any value with spaces MUST be wrapped in quotes!

