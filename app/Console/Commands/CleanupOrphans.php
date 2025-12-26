<?php

namespace App\Console\Commands;

use App\Models\FixerService;
use App\Models\FixerSubscription;
use App\Models\FixerWallet;
use App\Models\ServiceRequestDecline;
use App\Models\WalletLedger;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOrphans extends Command
{
    protected $signature = 'fixitzed:cleanup-orphans';

    protected $description = 'Remove orphaned fixer and user related records to keep referential integrity';

    public function handle(): int
    {
        $this->info('Cleaning orphaned records...');

        $counts = [];

        $counts['fixer_wallets'] = FixerWallet::whereDoesntHave('fixer')->delete();
        $counts['fixer_subscriptions'] = FixerSubscription::whereDoesntHave('fixer')->delete();
        $counts['wallet_ledgers'] = WalletLedger::whereDoesntHave('fixer')->delete();
        $counts['fixer_service'] = DB::table('fixer_service')
            ->whereNotIn('fixer_id', DB::table('fixers')->select('id'))
            ->orWhereNotIn('service_id', DB::table('services')->select('id'))
            ->delete();
        $counts['service_request_declines'] = ServiceRequestDecline::whereDoesntHave('fixer')->delete();

        $this->table(['Table', 'Removed'], collect($counts)->map(function ($removed, $table) {
            return ['Table' => $table, 'Removed' => $removed];
        })->values());

        // Optionally remove users without fixer or customer/fixer relations if fully orphaned
        $orphanUsers = User::whereDoesntHave('fixer')
            ->whereDoesntHave('serviceRequests')
            ->whereDoesntHave('serviceRequestDeclines')
            ->whereDoesntHave('notifications')
            ->get();

        $deletedUsers = 0;
        foreach ($orphanUsers as $user) {
            $user->delete();
            $deletedUsers++;
        }

        $this->info("Deleted orphan users: {$deletedUsers}");

        $this->info('Cleanup complete.');

        return self::SUCCESS;
    }
}
