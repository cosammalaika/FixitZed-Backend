# Horizon Deployment Notes

We recommend running Redis + Laravel Horizon for queue processing.

1. Install Redis and ensure `QUEUE_CONNECTION=redis` in `.env`.
2. Configure `config/horizon.php` supervisors – example:

```
'supervisor-production' => [
    'connection' => 'redis',
    'queue' => ['default'],
    'balance' => 'auto',
    'processes' => 10,
    'tries' => 3,
],
```

3. Add a systemd unit (`/etc/systemd/system/horizon.service`):

```
[Unit]
Description=Laravel Horizon
After=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/fixitzed/artisan horizon

[Install]
WantedBy=multi-user.target
```

4. Reload systemd and enable Horizon:

```
sudo systemctl daemon-reload
sudo systemctl enable --now horizon
```

Adjust `processes` / queues based on workload. Use `php artisan horizon:status` to confirm workers are running.
