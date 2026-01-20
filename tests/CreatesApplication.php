<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Boot the application for testing.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // Redirect storage and compiled views to writable tmp to avoid permission issues in CI/sandbox.
        $storage = sys_get_temp_dir().'/fixitzed-storage';
        if (! is_dir($storage)) {
            @mkdir($storage, 0777, true);
        }
        $app->useStoragePath($storage);
        foreach ([
            $storage.'/framework',
            $storage.'/framework/cache',
            $storage.'/framework/views',
            $storage.'/framework/sessions',
        ] as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
        }

        $compiled = sys_get_temp_dir().'/laravel-views';
        if (! is_dir($compiled)) {
            @mkdir($compiled, 0777, true);
        }
        putenv('VIEW_COMPILED_PATH='.$compiled);

        $app->make(Kernel::class)->bootstrap();

        // Force in-memory sqlite for isolated tests.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        // Avoid file logging in test runs (sandbox may block writes).
        $app['config']->set('logging.default', 'errorlog');
        $app['config']->set('view.compiled', $compiled);

        return $app;
    }
}
