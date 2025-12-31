# Trading Dashboard - Quick Reference

## Problem Summary

**Mixed Content Error**: HTTPS pages cannot embed HTTP iframes. Browsers block this for security.

## Quick Solution

### For Production (Recommended: Nginx Reverse Proxy)

1. **Set up Nginx reverse proxy** (see `TRADING_DASHBOARD_SETUP.md` for full config)
2. **Add to `.env`**:
   ```env
   TRADING_DASHBOARD_URL_PROD=https://trading-dashboard.tsgtrades.com/
   ```
3. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

### For Local Development

Keep using HTTP (works with `http://localhost:8000`):
```env
TRADING_DASHBOARD_URL_LOCAL=http://165.22.59.174:5173/
```

## Environment Variables

Add these to your `.env` files:

```env
# Local Development
TRADING_DASHBOARD_URL_LOCAL=http://165.22.59.174:5173/

# Production
TRADING_DASHBOARD_URL_PROD=https://trading-dashboard.tsgtrades.com/

# Demo
TRADING_DASHBOARD_URL_DEMO=https://trading-dashboard.demo.tsgtrades.com/
```

## Nginx Config (Minimal)

```nginx
server {
    listen 443 ssl http2;
    server_name trading-dashboard.tsgtrades.com;

    ssl_certificate /etc/letsencrypt/live/trading-dashboard.tsgtrades.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/trading-dashboard.tsgtrades.com/privkey.pem;

    # Allow iframe embedding
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Content-Security-Policy "frame-ancestors 'self' https://tsgtrades.com https://demo.tsgtrades.com" always;

    location / {
        proxy_pass http://165.22.59.174:5173;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Common Issues

| Error | Solution |
|-------|----------|
| Mixed content blocked | Use HTTPS for embedded app |
| X-Frame-Options deny | Set to SAMEORIGIN or remove |
| CSP frame-ancestors | Add your domain to CSP header |
| Blank iframe | Check browser console, verify URL accessibility |

## Testing

1. **Local**: `http://localhost:8000/customer/trading/dashboard` (HTTP works)
2. **Production**: `https://tsgtrades.com/customer/trading/dashboard` (needs HTTPS)
3. **Demo**: `https://demo.tsgtrades.com/customer/trading/dashboard` (needs HTTPS)

## Files Modified

- `config/trading.php` - Configuration file
- `app/Http/Controllers/Customer/TradingController.php` - Controller logic
- `resources/views/customer/trading/dashboard.blade.php` - Blade template

See `TRADING_DASHBOARD_SETUP.md` for complete documentation.

