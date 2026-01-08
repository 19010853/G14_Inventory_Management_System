# Gmail SMTP Deployment Instructions

This document provides step-by-step instructions for deploying Gmail SMTP functionality to your EC2 instance.

## Overview

The following features have been implemented:
1. **Admin Account Creation Email**: Sends email with randomly generated password when creating new admin accounts
2. **Forgot Password**: Sends password reset link via email and redirects to login page
3. **First Login Verification**: Automatically sets `email_verified_at` on first successful login
4. **Removed Registration**: All user registration functionality has been removed for security

---

## 1. Environment Configuration (.env file)

Add or update the following variables in your `.env` file on the EC2 instance:

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD="your-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="Group 14 Inventory System"
```

**⚠️ IMPORTANT: .env File Syntax Rules**

1. **No spaces around the `=` sign**: 
   - ✅ Correct: `MAIL_HOST=smtp.gmail.com`
   - ❌ Wrong: `MAIL_HOST = smtp.gmail.com`

2. **No trailing spaces**: Make sure there are no spaces at the end of lines

3. **Quotes for values with spaces**: If a value contains spaces, wrap it in quotes:
   - ✅ Correct: `MAIL_FROM_NAME="Group 14 Inventory System"`
   - ❌ Wrong: `MAIL_FROM_NAME=Group 14 Inventory System` (without quotes)

4. **No empty lines with spaces**: Empty lines should be completely empty

5. **App Password format**: Gmail App Password is 16 characters, may include spaces - **MUST be quoted if it contains spaces**:
   - ✅ `MAIL_PASSWORD="abcd efgh ijkl mnop"` (with quotes - RECOMMENDED)
   - ✅ `MAIL_PASSWORD=abcdefghijklmnop` (without spaces, no quotes needed)
   - ❌ `MAIL_PASSWORD=abcd efgh ijkl mnop` (WITHOUT quotes - WILL CAUSE ERROR!)

### Important Notes:

1. **Gmail App Password**: 
   - You cannot use your regular Gmail password
   - You need to generate an "App Password" from your Google Account
   - Steps to generate App Password:
     1. Go to https://myaccount.google.com/
     2. Click on "Security" in the left sidebar
     3. Under "Signing in to Google", enable "2-Step Verification" if not already enabled
     4. After enabling 2-Step Verification, go back to Security
     5. Click on "App passwords" (you may need to search for it)
     6. Select "Mail" and "Other (Custom name)"
     7. Enter a name like "Inventory Management System"
     8. Click "Generate"
     9. Copy the 16-character password (spaces don't matter)
     10. Use this password in `MAIL_PASSWORD`

2. **MAIL_USERNAME**: Your full Gmail address (e.g., `yourname@gmail.com`)

3. **MAIL_FROM_ADDRESS**: Should match your `MAIL_USERNAME`

4. **MAIL_FROM_NAME**: Will appear as the sender name in emails

---

## 2. Deployment Steps on EC2

### Step 1: Backup Current Code (Optional but Recommended)

```bash
cd ~/G14_Inventory_Management_System
git add .
git commit -m "Backup before Gmail SMTP deployment"
git push origin main  # or your branch name
```

### Step 2: Pull Latest Code

```bash
cd ~/G14_Inventory_Management_System
git pull origin main  # or your branch name
```

### Step 3: Update .env File

Edit the `.env` file:

```bash
nano .env
```

Add or update the mail configuration as described in Section 1 above.

**⚠️ Common .env File Mistakes to Avoid:**

1. **Check for spaces around `=`**: 
   ```bash
   # Use this command to check for spaces around equals signs
   grep " = " .env
   # If any results appear, remove the spaces
   ```

2. **Check for trailing spaces**:
   ```bash
   # Remove trailing spaces from all lines
   sed -i 's/[[:space:]]*$//' .env
   ```

3. **Verify syntax**:
   ```bash
   # Test if .env can be parsed
   php -r "require 'vendor/autoload.php'; Dotenv\Dotenv::createImmutable(__DIR__)->load();"
   ```

4. **Example of correct format** (copy exactly, no extra spaces):
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-char-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   MAIL_FROM_NAME="Group 14 Inventory System"
   ```

Save and exit (Ctrl+X, then Y, then Enter).

### Step 4: Install Dependencies (if needed)

```bash
./vendor/bin/sail composer install --no-dev --optimize-autoloader
```

### Step 5: Clear All Caches

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
```

### Step 6: Rebuild Configuration Cache

```bash
./vendor/bin/sail artisan config:cache
```

### Step 7: Test Email Configuration (Optional)

You can test the email configuration by creating a test admin account through the admin panel, or by using Laravel Tinker:

```bash
./vendor/bin/sail artisan tinker
```

Then in Tinker:
```php
Mail::raw('Test email', function ($message) {
    $message->to('your-test-email@gmail.com')
            ->subject('Test Email');
});
```

---

## 3. Verification Steps

After deployment, verify the following:

### 3.1 Admin Account Creation
1. Log in as Super Admin
2. Go to `https://g14-inventory.myvnc.com/add/admin`
3. Fill in the form (no password field should be visible)
4. Submit the form
5. Check the admin's Gmail inbox for the account creation email with the random password

### 3.2 Forgot Password
1. Go to `https://g14-inventory.myvnc.com/forgot-password`
2. Enter a valid admin email address
3. Click "EMAIL PASSWORD RESET LINK"
4. You should be redirected to the login page
5. Check the email inbox for the password reset link

### 3.3 First Login Verification
1. Use the password from the admin creation email to log in
2. After successful login, check the database:
   ```bash
   ./vendor/bin/sail artisan tinker
   ```
   ```php
   $user = \App\Models\User::where('email', 'test-admin@gmail.com')->first();
   echo $user->email_verified_at; // Should show a timestamp
   ```

### 3.4 Registration Removal
1. Try to access `https://g14-inventory.myvnc.com/register` - should return 404
2. Check login page - "Sign up" link should not be visible

---

## 4. Troubleshooting

### Email Not Sending

1. **Check Gmail App Password**:
   - Ensure you're using an App Password, not your regular Gmail password
   - Verify the App Password is correct (16 characters)

2. **Check .env Configuration**:
   ```bash
   ./vendor/bin/sail artisan config:clear
   ./vendor/bin/sail artisan config:cache
   ```

3. **Check Laravel Logs**:
   ```bash
   ./vendor/bin/sail exec laravel.test tail -f storage/logs/laravel.log
   ```

4. **Test SMTP Connection**:
   ```bash
   ./vendor/bin/sail artisan tinker
   ```
   ```php
   try {
       Mail::raw('Test', function ($m) {
           $m->to('your-email@gmail.com')->subject('Test');
       });
       echo "Email sent successfully";
   } catch (\Exception $e) {
       echo "Error: " . $e->getMessage();
   }
   ```

### Common Errors

- **"Authentication failed"**: Check your App Password
- **"Connection timeout"**: Check firewall settings, ensure port 587 is open
- **"Could not authenticate"**: Verify 2-Step Verification is enabled on your Google account
- **"Failed to parse dotenv file. Encountered unexpected whitespace"**: 
  - Check for spaces around `=` signs (should be `KEY=value`, not `KEY = value`)
  - Remove trailing spaces from lines
  - Ensure quotes are properly closed
  - Run: `sed -i 's/[[:space:]]*$//' .env` to remove trailing spaces
  - Run: `sed -i 's/ = /=/g' .env` to remove spaces around equals signs

---

## 5. Security Notes

1. **Never commit .env file** to version control
2. **App Passwords** are more secure than regular passwords
3. **Email passwords** are randomly generated (32 characters) for security
4. **Registration removal** prevents unauthorized account creation

---

## 6. Rollback Instructions (if needed)

If you need to rollback:

```bash
cd ~/G14_Inventory_Management_System
git log --oneline  # Find the commit before Gmail SMTP changes
git checkout <commit-hash>
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan config:cache
```

---

## 7. Support

If you encounter any issues:
1. Check Laravel logs: `./vendor/bin/sail exec laravel.test tail -f storage/logs/laravel.log`
2. Verify .env configuration matches the requirements
3. Test email sending using Tinker (see Step 7 above)
4. Ensure Gmail App Password is correctly configured

---

**Last Updated**: 2026-01-07
**Version**: 1.0

