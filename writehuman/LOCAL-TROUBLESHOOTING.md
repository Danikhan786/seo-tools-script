# Local Server Troubleshooting Guide

## Quick Diagnostic

1. **Run Local Diagnostic**: Visit `http://localhost:8000/writehuman/local-diagnostic.php`
2. **Check Status**: Visit `http://localhost:8000/writehuman/status.php`
3. **Test Connection**: Visit `http://localhost:8000/writehuman/test.php`

## Common Local Server Issues

### 1. **Files Not Found (404 Errors)**
**Symptoms**: Browser shows "404 Not Found" or "File not found"
**Solutions**:
- Make sure all files are in the correct directory
- Check file permissions (should be readable by web server)
- Verify the directory structure matches the expected layout

### 2. **PHP Extensions Missing**
**Symptoms**: "cURL extension not available" or similar errors
**Solutions**:
- **XAMPP**: Enable extensions in `php.ini`
- **WAMP**: Enable extensions in PHP settings
- **MAMP**: Check PHP extensions in preferences
- **Built-in PHP server**: Install missing extensions

### 3. **URL Rewriting Not Working**
**Symptoms**: Internal pages show 404 errors
**Solutions**:
- **Apache**: Enable `mod_rewrite` module
- **Built-in PHP server**: Use `-t` flag to set document root
- **Nginx**: Add rewrite rules to configuration

### 4. **Cookie Issues**
**Symptoms**: "Invalid cookie configuration" or authentication errors
**Solutions**:
- Update `cookie.json` with fresh WriteHuman.ai cookies
- Make sure cookie file is readable
- Check JSON syntax in cookie file

### 5. **Network Connectivity Issues**
**Symptoms**: "Connection error" or timeout messages
**Solutions**:
- Check your internet connection
- Disable firewall temporarily for testing
- Try accessing `https://writehuman.ai` directly in browser
- Check if your local server can make outbound connections

### 6. **Port Conflicts**
**Symptoms**: Can't access the tool or server won't start
**Solutions**:
- Change port number (e.g., use 8080 instead of 8000)
- Check if another service is using the same port
- Use `netstat -an | grep :8000` to check port usage

## Server-Specific Solutions

### **Built-in PHP Server**
```bash
# Start server in the writehuman directory
cd writehuman
php -S localhost:8000

# Or specify document root
php -S localhost:8000 -t .
```

### **XAMPP**
1. Place files in `htdocs/writehuman/`
2. Access via `http://localhost/writehuman/`
3. Enable mod_rewrite in Apache configuration

### **WAMP**
1. Place files in `www/writehuman/`
2. Access via `http://localhost/writehuman/`
3. Enable mod_rewrite in Apache modules

### **MAMP**
1. Place files in `htdocs/writehuman/`
2. Access via `http://localhost:8888/writehuman/`
3. Check Apache configuration for mod_rewrite

## File Structure Check

Make sure your directory looks like this:
```
writehuman/
├── index.php
├── writerHuman.php
├── launch.php
├── test.php
├── status.php
├── local-diagnostic.php
├── error.php
├── js-fix.js
├── cookie.json
├── .htaccess
├── README.md
├── LOCAL-TROUBLESHOOTING.md
└── css/
    └── styles.css
```

## Testing Steps

1. **Basic Test**: Visit `http://localhost:8000/writehuman/launch.php`
2. **Status Check**: Click "Local Diagnostic" to run comprehensive tests
3. **Connection Test**: Click "Test Connection" to verify connectivity
4. **Launch Tool**: Click "Launch WriterHuman Tool" to start the proxy

## Error Messages and Solutions

| Error Message | Solution |
|---------------|----------|
| "Cookie file not found" | Check if `cookie.json` exists and is readable |
| "Invalid cookie configuration" | Update cookies or check JSON syntax |
| "Connection error" | Check internet connection and firewall |
| "cURL extension not available" | Enable cURL in PHP configuration |
| "404 Not Found" | Check file paths and URL rewriting |
| "Permission denied" | Check file permissions (should be 644 for files, 755 for directories) |

## Getting Help

If you're still having issues:

1. Run the local diagnostic tool
2. Check the browser console for JavaScript errors
3. Check the server error logs
4. Try a different local server (XAMPP, WAMP, MAMP)
5. Test with a different port number 