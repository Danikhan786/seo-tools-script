# StealthWriter.ai Proxy Tool

This is a PHP-based proxy tool that allows you to access [StealthWriter.ai](https://app.stealthwriter.ai/) through your own domain while maintaining authentication and session management.

## Features

- ✅ **Authentication Proxy**: Uses your StealthWriter.ai login cookies to maintain authenticated sessions
- ✅ **URL Rewriting**: Automatically rewrites URLs to work through your proxy domain
- ✅ **Session Management**: Maintains user sessions and authentication tokens
- ✅ **Custom Styling**: Includes custom CSS to hide certain elements and add branding
- ✅ **Session Timer**: Built-in session timer with 30-minute limit
- ✅ **Watermark**: Customizable watermark with WhatsApp channel link

## Setup Instructions

### 1. File Structure
```
stealth writer tool/
├── index.php              # Main proxy entry point
├── stealthWriter.php      # Core proxy logic
├── cookie.json            # Cookie configuration
├── test.php              # Configuration test file
├── .htaccess             # URL rewriting rules
├── css/
│   └── styles.css        # Custom styling
└── README.md             # This file
```

### 2. Configuration

The main configuration is in `cookie.json`. The file has been pre-configured with your StealthWriter.ai cookies:

```json
{
    "STEALTHWRITER_PROXY": {
        "tool_name": "StealthWriter.ai Proxy",
        "cookie_data": {
            "_gcl_au": "your_cookie_value",
            "sb-vqdtifewupwhdypyimkf-auth-token": "your_auth_token",
            // ... other cookies
        },
        "proxy": {
            "targeturl": "https://app.stealthwriter.ai",
            "useragent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36..."
        }
    }
}
```

### 3. Testing

1. **Test Configuration**: Visit `test.php` to verify your cookie configuration
2. **Launch Proxy**: Visit `index.php` to access StealthWriter.ai through the proxy

### 4. Usage

Once configured, users can access StealthWriter.ai through your domain:

- **Main Access**: `https://yourdomain.com/` (redirects to StealthWriter.ai)
- **Direct Access**: `https://yourdomain.com/index.php`

## Customization

### Modifying Cookies

To update the authentication cookies:

1. Export fresh cookies from your browser's developer tools
2. Update the `cookie_data` section in `cookie.json`
3. Test the connection using `test.php`

### Custom Styling

Edit `css/styles.css` to:
- Hide specific elements (navigation, footer, etc.)
- Add custom branding
- Modify the watermark appearance
- Change session timer styling

### Session Management

The tool includes:
- 30-minute session timer
- Automatic session expiration
- Session time display in top-right corner

## Security Features

- SSL certificate verification disabled (for development)
- Custom user agent spoofing
- Cookie-based authentication
- URL rewriting for security

## Troubleshooting

### Common Issues

1. **Authentication Failed**
   - Check if cookies are expired
   - Update `cookie.json` with fresh cookies
   - Verify target URL is correct

2. **CSS Not Loading**
   - Check file permissions
   - Verify `.htaccess` configuration
   - Clear browser cache

3. **Session Expired**
   - Refresh cookies from browser
   - Update `cookie.json`
   - Test with `test.php`

### Testing

Use `test.php` to:
- Verify cookie configuration
- Test connection to StealthWriter.ai
- Check HTTP response codes
- Validate user agent settings

## Browser Requirements

- Modern browser with JavaScript enabled
- Cookies enabled
- No ad blockers interfering with requests

## Support

For issues or questions:
1. Check the test file first
2. Verify cookie expiration
3. Test connection manually
4. Review error logs

## Legal Notice

This tool is for educational and personal use only. Ensure you comply with StealthWriter.ai's terms of service and your local laws regarding web scraping and proxy usage.

---

**Last Updated**: December 2024
**Version**: 1.0
**Target**: StealthWriter.ai 