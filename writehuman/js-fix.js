// JavaScript fixes for WriterHuman proxy
(function() {
    'use strict';
    
    // Fix CORS issues for API calls
    const originalFetch = window.fetch;
    window.fetch = function(url, options) {
        if (typeof url === 'string') {
            // Fix API calls to point to original WriteHuman.ai
            if (url.includes('/api/') && !url.startsWith('https://writehuman.ai')) {
                url = url.replace(/^https?:\/\/[^\/]+/, 'https://writehuman.ai');
            }
        }
        return originalFetch(url, options);
    };

    // Fix XMLHttpRequest for API calls
    const originalOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
        if (typeof url === 'string' && url.includes('/api/') && !url.startsWith('https://writehuman.ai')) {
            url = url.replace(/^https?:\/\/[^\/]+/, 'https://writehuman.ai');
        }
        return originalOpen.call(this, method, url, async, user, password);
    };

    // Fix jQuery loading issues
    if (typeof jQuery === 'undefined') {
        // Create a simple jQuery-like object for basic functionality
        window.$ = window.jQuery = function(selector) {
            if (typeof selector === 'string') {
                return document.querySelectorAll(selector);
            }
            return selector;
        };
        
        // Add basic jQuery methods
        window.$.fn = window.$.prototype = {
            ready: function(callback) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', callback);
                } else {
                    callback();
                }
                return this;
            },
            on: function(event, callback) {
                if (this.length) {
                    this[0].addEventListener(event, callback);
                }
                return this;
            },
            click: function(callback) {
                return this.on('click', callback);
            }
        };
    }

    // Fix undefined app_version error
    if (typeof window.app_version === 'undefined') {
        window.app_version = '1.0.0';
    }

    // Fix undefined _id error
    if (typeof window._id === 'undefined') {
        window._id = 'proxy_' + Math.random().toString(36).substr(2, 9);
    }

    // Fix load function errors
    if (typeof window.load === 'undefined') {
        window.load = function(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
        };
    }

    // Fix CookieYes script errors
    if (typeof window.CookieYes === 'undefined') {
        window.CookieYes = {
            consent: 'yes',
            action: 'no'
        };
    }

    // Fix plugin errors
    if (typeof window.pluginAnalyzer === 'undefined') {
        window.pluginAnalyzer = function() {
            // Do nothing to prevent errors
        };
    }

    // Fix socket connection errors
    if (typeof window.socket === 'undefined') {
        window.socket = {
            onmessage: function() {},
            send: function() {}
        };
    }

    // Fix Flusk Monitor errors
    if (typeof window.FluskMonitor === 'undefined') {
        window.FluskMonitor = {
            log: function() {},
            error: function() {}
        };
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Fix any remaining script loading issues
        const scripts = document.querySelectorAll('script[src]');
        scripts.forEach(function(script) {
            script.addEventListener('error', function() {
                console.log('Script failed to load:', script.src);
            });
        });

        // Fix CSS loading issues
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        links.forEach(function(link) {
            link.addEventListener('error', function() {
                console.log('CSS failed to load:', link.href);
            });
        });
    });

})(); 