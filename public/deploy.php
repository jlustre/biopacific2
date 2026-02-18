<?php
// Production deployment script
echo "<pre>";

// 1. Clear routes cache
if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
    unlink(base_path('bootstrap/cache/routes-v7.php'));
    echo "✓ Removed routes-v7.php\n";
}

if (file_exists(base_path('bootstrap/cache/routes.php'))) {
    unlink(base_path('bootstrap/cache/routes.php'));
    echo "✓ Removed routes.php\n";
}

// 2. Run artisan commands
echo "\nRunning artisan commands...\n";
passthru('cd ' . base_path() . ' && php artisan route:clear');
echo "✓ route:clear\n";

passthru('php artisan config:clear');
echo "✓ config:clear\n";

passthru('php artisan cache:clear');
echo "✓ cache:clear\n";

passthru('php artisan optimize:clear');
echo "✓ optimize:clear\n";

// 3. Recache
echo "\nRecaching...\n";
passthru('php artisan route:cache');
echo "✓ route:cache\n";

passthru('php artisan config:cache');
echo "✓ config:cache\n";

// 4. Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache cleared\n";
}

echo "\n<strong>Deployment complete! Delete this file now.</strong>";
echo "</pre>";
