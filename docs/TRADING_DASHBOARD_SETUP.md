# Trading Dashboard Setup Guide

## Problem: Mixed Content (HTTPS → HTTP)

### Why Browsers Block HTTPS → HTTP Iframes

When a page is served over HTTPS, browsers enforce the **Mixed Content Policy** for security:

1. **Security Risk**: HTTP connections are unencrypted and vulnerable to man-in-the-middle attacks
2. **Data Integrity**: Content loaded over HTTP can be modified in transit
3. **User Privacy**: Sensitive data could be intercepted
4. **Browser Enforcement**: Modern browsers (Chrome, Firefox, Safari, Edge) block mixed content by default

**Result**: An HTTPS page cannot embed an HTTP iframe - the browser will block it and show a blank frame or error.

---

## Solution Options

### Option 1: Enable HTTPS on Embedded App (Recommended)

If you control the embedded application at `http://165.22.59.174:5173/`:

#### For Production (Vite/Vue/React App)

1. **Build the app for production**:
   ```bash
   npm run build
   ```

2. **Serve with HTTPS** using a web server (Nginx/Apache) with SSL certificate

3. **Update environment variable**:
   ```env
   TRADING_DASHBOARD_URL_PROD=https://trading-dashboard.tsgtrades.com/
   ```

#### For Development (Vite Dev Server)

Vite dev server on port 5173 can be configured for HTTPS:

1. **Install mkcert** (for local SSL certificates):
   ```bash
   # Windows (using Chocolatey)
   choco install mkcert
   
   # Or download from: https://github.com/FiloSottile/mkcert/releases
   ```

2. **Create local CA and certificate**:
   ```bash
   mkcert -install
   mkcert localhost 165.22.59.174
   ```

3. **Update Vite config** (`vite.config.js`):
   ```javascript
   import { defineConfig } from 'vite'
   import fs from 'fs'

   export default defineConfig({
     server: {
       https: {
         key: fs.readFileSync('./localhost+2-key.pem'),
         cert: fs.readFileSync('./localhost+2.pem'),
       },
       host: '165.22.59.174',
       port: 5173,
     },
   })
   ```

4. **Start dev server**:
   ```bash
   npm run dev
   ```

5. **Access via HTTPS**: `https://165.22.59.174:5173/`

---

### Option 2: Nginx Reverse Proxy (Production-Ready)

If you cannot modify the embedded app, use Nginx as an HTTPS reverse proxy.

#### Step 1: Install SSL Certificate

Use Let's Encrypt (free) or your existing certificate:

```bash
# Install certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Get certificate for subdomain
sudo certbot --nginx -d trading-dashboard.tsgtrades.com
```

#### Step 2: Nginx Configuration

Create `/etc/nginx/sites-available/trading-dashboard`:

```nginx
# HTTPS Reverse Proxy for Trading Dashboard
server {
    listen 80;
    server_name trading-dashboard.tsgtrades.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name trading-dashboard.tsgtrades.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/trading-dashboard.tsgtrades.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/trading-dashboard.tsgtrades.com/privkey.pem;
    
    # SSL Security Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers (Important for iframe embedding)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Allow iframe embedding from your domains
    add_header Content-Security-Policy "frame-ancestors 'self' https://tsgtrades.com https://demo.tsgtrades.com https://*.tsgtrades.com" always;

    # Proxy Settings
    location / {
        proxy_pass http://165.22.59.174:5173;
        proxy_http_version 1.1;
        
        # Headers for proper proxying
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $server_name;
        
        # WebSocket support (if needed)
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
        
        # Buffer settings
        proxy_buffering off;
        proxy_request_buffering off;
    }

    # Health check endpoint (optional)
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

#### Step 3: Enable Site and Reload Nginx

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/trading-dashboard /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

#### Step 4: Update Laravel Environment

For production:
```env
TRADING_DASHBOARD_URL_PROD=https://trading-dashboard.tsgtrades.com/
```

For demo:
```env
TRADING_DASHBOARD_URL_DEMO=https://trading-dashboard.demo.tsgtrades.com/
```

---

## Laravel Configuration

### Environment Variables

Add to your `.env` files:

**Local (.env)**:
```env
APP_ENV=local
TRADING_DASHBOARD_URL=http://165.22.59.174:5173/
TRADING_DASHBOARD_URL_LOCAL=http://165.22.59.174:5173/
```

**Production (.env)**:
```env
APP_ENV=production
TRADING_DASHBOARD_URL_PROD=https://trading-dashboard.tsgtrades.com/
```

**Demo (.env)**:
```env
APP_ENV=demo
TRADING_DASHBOARD_URL_DEMO=https://trading-dashboard.demo.tsgtrades.com/
```

### Configuration File

The configuration is already set up in `config/trading.php`:

```php
'dashboard_urls' => [
    'local' => env('TRADING_DASHBOARD_URL_LOCAL', 'http://165.22.59.174:5173/'),
    'production' => env('TRADING_DASHBOARD_URL_PROD', 'https://trading-dashboard.tsgtrades.com/'),
    'demo' => env('TRADING_DASHBOARD_URL_DEMO', 'https://trading-dashboard.demo.tsgtrades.com/'),
],
```

The controller automatically selects the correct URL based on `APP_ENV`.

---

## Common Pitfalls & Solutions

### 1. Vite Dev Server on Port 5173

**Issue**: Vite dev server runs on HTTP by default.

**Solutions**:
- Use HTTPS reverse proxy (Option 2 above)
- Configure Vite for HTTPS (see Option 1)
- Use `--host` flag: `npm run dev -- --host 165.22.59.174`

### 2. Content Security Policy (CSP)

**Issue**: External site blocks iframe embedding.

**Solution**: Configure CSP headers on the embedded app:
```
Content-Security-Policy: frame-ancestors 'self' https://tsgtrades.com https://demo.tsgtrades.com
```

Or in Nginx (if using reverse proxy):
```nginx
add_header Content-Security-Policy "frame-ancestors 'self' https://tsgtrades.com https://demo.tsgtrades.com" always;
```

### 3. X-Frame-Options Header

**Issue**: `X-Frame-Options: DENY` blocks all iframe embedding.

**Solution**: Set to `SAMEORIGIN` or remove the header:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
```

Or in the embedded app's server configuration, remove or modify:
```
X-Frame-Options: SAMEORIGIN
```

### 4. CORS vs Iframe Embedding

**Note**: CORS does NOT apply to iframes. CORS is for AJAX/fetch requests. Iframe embedding is controlled by:
- `X-Frame-Options` header
- `Content-Security-Policy: frame-ancestors` directive
- Mixed content policy (HTTPS → HTTP)

### 5. Browser Console Errors

**Common errors**:
- `Refused to display in a frame because it set 'X-Frame-Options' to 'deny'`
  - **Fix**: Configure X-Frame-Options on embedded app
  
- `Mixed Content: The page was loaded over HTTPS, but requested an insecure resource`
  - **Fix**: Use HTTPS for embedded app (reverse proxy or direct HTTPS)

- `Refused to frame because of Content Security Policy`
  - **Fix**: Update CSP `frame-ancestors` directive

### 6. Testing Locally

For local development with HTTP:
- Use `http://localhost:8000` (not HTTPS)
- Or configure local SSL certificates
- Or use a reverse proxy with self-signed certificates

---

## Final Iframe Code

The Blade template automatically uses the correct URL:

```blade
<iframe 
    src="{{ $dashboardUrl }}" 
    class="dashboard-iframe"
    title="Trades Dashboard"
    allow="fullscreen"
    allowfullscreen
    frameborder="0"
    scrolling="auto"
    referrerpolicy="no-referrer-when-downgrade">
</iframe>
```

**Attributes Explained**:
- `allow="fullscreen"` - Modern standard for fullscreen API
- `allowfullscreen` - Legacy attribute for compatibility
- `referrerpolicy="no-referrer-when-downgrade"` - Security best practice
- `frameborder="0"` - Remove border (deprecated but still used)
- `scrolling="auto"` - Allow scrolling if content exceeds iframe

---

## Verification Checklist

- [ ] Embedded app is accessible over HTTPS
- [ ] SSL certificate is valid and not expired
- [ ] `X-Frame-Options` allows embedding (or is removed)
- [ ] `Content-Security-Policy: frame-ancestors` includes your domains
- [ ] Nginx reverse proxy is configured correctly (if used)
- [ ] Environment variables are set correctly
- [ ] Laravel config cache is cleared: `php artisan config:clear`
- [ ] Browser console shows no mixed content errors
- [ ] Iframe loads without errors in production

---

## Troubleshooting

### Iframe is blank
1. Check browser console for errors
2. Verify embedded URL is accessible directly
3. Check network tab for failed requests
4. Verify SSL certificate is valid

### Mixed content error
1. Ensure embedded URL uses HTTPS
2. Check that all resources in embedded app use HTTPS
3. Verify reverse proxy is working correctly

### CSP/X-Frame-Options error
1. Check headers: `curl -I https://trading-dashboard.tsgtrades.com`
2. Update CSP or X-Frame-Options headers
3. Clear browser cache

---

## Security Best Practices

1. **Always use HTTPS in production**
2. **Restrict frame-ancestors** to specific domains
3. **Use strong SSL/TLS configuration** (TLS 1.2+)
4. **Monitor certificate expiration**
5. **Regular security audits** of embedded content
6. **Implement Content Security Policy** properly
7. **Use subdomain isolation** for embedded apps

---

## Support

For issues or questions:
1. Check browser console for specific error messages
2. Verify network requests in browser DevTools
3. Test embedded URL directly in browser
4. Review Nginx/application logs
5. Check Laravel logs: `storage/logs/laravel.log`

