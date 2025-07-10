# WriterHuman Proxy Tool

This is a PHP proxy tool that allows you to access WriteHuman.ai through your local server with authenticated sessions.

## ✅ Fixed Issues

The tool has been updated to work properly like the StealthWriter tool:

1. **Proper proxy handling** - Now includes proxy configuration support
2. **Session timer** - 30-minute session timer with countdown
3. **Watermark** - Custom watermark with WhatsApp link
4. **Better URL proxying** - Improved URL replacement logic
5. **Error handling** - Better error handling and validation
6. **Cookie management** - Proper cookie handling and validation

## 📁 File Structure

```
writehuman/
├── writerHuman.php      # Main proxy script (FIXED)
├── index.php           # Entry point
├── launch.php          # Launcher page
├── test.php            # Configuration test
├── access.php          # Access control (ADDED)
├── cookie.json         # Cookie and proxy configuration
├── .htaccess           # URL rewriting rules
├── css/
│   └── styles.css      # Custom styling
└── README.md           # This file
```

## 🚀 How to Use

1. **Access the launcher**: Visit `launch.php` in your browser
2. **Test configuration**: Click "Test Connection" to verify setup
3. **Launch tool**: Click "Launch WriterHuman Tool" to start the proxy
4. **Use normally**: The tool will work exactly like the original WriteHuman.ai

## ⚙️ Configuration

The `cookie.json` file contains:
- **Cookie data**: Your authenticated session cookies
- **Proxy settings**: Optional proxy configuration
- **User agent**: Browser user agent string
- **Target URL**: WriteHuman.ai URL

## 🔧 Features

- ✅ **Session Management**: Automatic cookie handling
- ✅ **Proxy Support**: Optional proxy configuration
- ✅ **Session Timer**: 30-minute session with countdown
- ✅ **Custom Watermark**: Branded interface
- ✅ **Error Handling**: Proper error messages
- ✅ **URL Proxying**: All URLs properly proxied
- ✅ **CSS Injection**: Custom styling support

## 🐛 Troubleshooting

If you encounter issues:

1. **Check test.php**: Verify configuration is correct
2. **Check cookie.json**: Ensure valid cookie data
3. **Check file permissions**: Ensure PHP can read files
4. **Check .htaccess**: Ensure URL rewriting works

## 📞 Support

For issues or questions, check the test files or contact support. 