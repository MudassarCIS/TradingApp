# Deployment Notes

## Storage Symlink Setup

For logo uploads and other public storage files to work correctly on live servers, you must create a storage symlink:

```bash
php artisan storage:link
```

This command creates a symbolic link from `public/storage` to `storage/app/public`, allowing public access to uploaded files.

### Manual Setup (if symlink doesn't work)

If the `php artisan storage:link` command doesn't work on your server, you can manually create the symlink:

**Linux/Mac:**
```bash
ln -s /path/to/your/project/storage/app/public /path/to/your/project/public/storage
```

**Windows (PowerShell as Administrator):**
```powershell
New-Item -ItemType SymbolicLink -Path "public\storage" -Target "storage\app\public"
```

### Verify Symlink

After creating the symlink, verify it exists:
- Check that `public/storage` exists and points to `storage/app/public`
- Upload a logo through the admin panel
- Check that the logo displays correctly on homepage, login, and register pages

### File Permissions

Ensure proper file permissions on the storage directory:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Troubleshooting

If logos still don't display:
1. Check that the symlink exists: `ls -la public/storage`
2. Verify file permissions on `storage/app/public/logos/`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Clear cache: `php artisan cache:clear && php artisan config:clear`

