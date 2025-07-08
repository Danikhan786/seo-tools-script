# 🔄 How to Refresh StealthWriter.ai Cookies

## Why Cookies Expire
StealthWriter.ai authentication tokens have expiration dates for security reasons. When they expire, you'll see a login page instead of the actual content.

## 🔍 Check if Cookies are Expired

1. **Visit the authentication test page:**
   ```
   http://localhost:8000/auth_test.php
   ```

2. **Look for these signs:**
   - ❌ "Authentication Failed: Redirected to login page"
   - ❌ "Warning: Redirected to login page - cookies may be expired"
   - ✅ "Authentication Working: Humanizer page accessible"

## 🔄 How to Refresh Cookies

### Step 1: Get Fresh Cookies
1. **Open a new incognito/private browser window**
2. **Go to:** https://app.stealthwriter.ai
3. **Login with your credentials**
4. **Navigate to the humanizer page:** https://app.stealthwriter.ai/humanizer

### Step 2: Export Cookies
1. **Press F12** to open Developer Tools
2. **Go to Application tab** (Chrome) or **Storage tab** (Firefox)
3. **Click on Cookies** → **https://app.stealthwriter.ai**
4. **Copy these important cookies:**
   - `sb-vqdtifewupwhdypyimkf-auth-token`
   - `sb-vqdtifewupwhdypyimkf-auth-token-code-verifier`
   - `_gcl_au`
   - `intercom-device-id-esc25l7u`
   - `intercom-id-esc25l7u`
   - `intercom-session-esc25l7u`

### Step 3: Update cookie.json
1. **Open:** `cookie.json`
2. **Replace the cookie values** in the `cookie_data` section
3. **Save the file**

### Step 4: Test the Proxy
1. **Visit:** http://localhost:8000/auth_test.php
2. **Confirm:** "Authentication Working: Humanizer page accessible"
3. **Launch proxy:** http://localhost:8000/index.php

## 🚨 Important Notes

- **Never share your cookies** - they contain your login credentials
- **Use incognito mode** when getting fresh cookies
- **Test immediately** after updating cookies
- **Keep cookies secure** - don't commit them to public repositories

## 🔧 Quick Cookie Export Script

You can also use browser extensions like:
- **Cookie Editor** (Chrome/Firefox)
- **EditThisCookie** (Chrome)

These extensions can export cookies in JSON format, making it easier to update your `cookie.json` file.

## 📞 Need Help?

If you're still having issues:
1. Check the authentication test page
2. Verify all required cookies are present
3. Ensure you're logged into StealthWriter.ai in the same browser
4. Try clearing browser cache and cookies, then re-login 