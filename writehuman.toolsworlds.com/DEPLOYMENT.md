# WriteHuman Tool Deployment Guide

This tool is now configured similar to the semrush.toolsworlds.com tool for easy deployment on live servers.

## File Structure

```
writehuman/
├── index.php              # Main entry point (updated)
├── include.php            # Core logic and functions (new)
├── writerHuman.php        # Special case handler (updated)
├── access.php             # Access control
├── cookie.json            # Proxy and cookie configuration
├── token.php              # Token handling
├── .htaccess              # URL rewriting rules
├── css/
│   └── styles.css         # Custom styling
└── DEPLOYMENT.md          # This file
```

## Key Changes Made

1. **Separated Logic**: Moved core functionality from `writerHuman.php` to `include.php`
2. **Updated Entry Point**: `index.php` now follows the same pattern as semrush tool
3. **Special Case Handling**: `writerHuman.php` now only handles WriteHuman.ai specific resources
4. **Error Handling**: Added proper error reporting and initialization

## Deployment Steps

1. **Upload Files**: Upload all files to your web server
2. **Configure Domain**: Set the domain to `writerhuman.toolsworlds.com`
3. **Update cookie.json**: Ensure the proxy configuration is correct
4. **Set Permissions**: Make sure files are readable by web server
5. **Test Access**: Verify the tool works with your domain

## Configuration Files

### cookie.json
Contains proxy settings and cookie data for WriteHuman.ai:
```json
{
  "WRITERHUMAN_PROXY": {
    "proxy": {
      "targeturl": "https://writehuman.ai",
      "ip": "your-proxy-ip",
      "port": "your-proxy-port",
      "username": "your-proxy-username",
      "password": "your-proxy-password",
      "useragent": "your-user-agent"
    },
    "cookie_data": {
      "session_id": "your-session-id",
      "other_cookies": "values"
    }
  }
}
```

### .htaccess
Handles URL rewriting and redirects for proper routing.

## Features

- **Proxy Support**: Routes through configured proxy
- **Session Management**: Handles WriteHuman.ai sessions
- **Resource Handling**: Properly handles CDN, API, and package resources
- **Watermark**: Adds session timer and branding
- **Error Handling**: Proper error reporting and handling

## Troubleshooting

1. **Domain Issues**: Ensure domain matches `writerhuman.toolsworlds.com`
2. **Proxy Errors**: Check proxy configuration in `cookie.json`
3. **Session Issues**: Verify cookie data is current
4. **Resource Loading**: Check if CDN/API resources are accessible

## License

License expires on: 2025-06-03 + 1 year
Domain restriction: writerhuman.toolsworlds.com 