<?php

namespace App\Console\Commands;

use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FixitzedTraceRequest extends Command
{
    protected $signature = 'fixitzed:trace-request {requestId}';

    protected $description = 'Inspect a service request and its visibility to fixers.';

    public function handle(): int
    {
        $id = (int) $this->argument('requestId');

        $request = ServiceRequest::with(['service.fixers', 'fixer', 'declines'])
            ->find($id);

        if (! $request) {
            $this->error("Request {$id} not found.");
            return self::FAILURE;
        }

        $expiryMinutes = (int) Setting::get('requests.expiry_minutes', 15);
        $cutoff = $expiryMinutes > 0 ? Carbon::now()->subMinutes($expiryMinutes) : null;

        $this->info('Service Request');
        $this->line(json_encode([
            'id' => $request->id,
            'service_id' => $request->service_id,
            'status' => $request->status,
            'fixer_id' => $request->fixer_id,
            'customer_id' => $request->customer_id,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'scheduled_at' => $request->scheduled_at,
            'expiry_minutes' => $expiryMinutes,
            'expiry_cutoff' => $cutoff,
        ], JSON_PRETTY_PRINT));

        $eligible = Fixer::query()
            ->where('status', 'approved')
            ->whereHas('services', fn ($q) => $q->where('services.id', $request->service_id))
            ->get();

        $this->info("\nEligible fixers for service {$request->service_id}: " . $eligible->count());

        foreach ($eligible as $fixer) {
            $wouldAppear = $this->wouldAppearInFeed($request, $fixer, $cutoff);
            $this->line(json_encode([
                'fixer_id' => $fixer->id,
                'user_id' => $fixer->user_id,
                'would_appear_in_feed' => $wouldAppear,
                'is_assigned' => $request->fixer_id === $fixer->id,
                'has_declined' => $request->declines->where('fixer_id', $fixer->id)->isNotEmpty(),
            ], JSON_PRETTY_PRINT));
        }

        return self::SUCCESS;
    }

    protected function wouldAppearInFeed(ServiceRequest $request, Fixer $fixer, ?Carbon $cutoff): bool
    {
        if ($cutoff && $request->status === 'pending' && $request->created_at < $cutoff) {
            return false;
        }

        if ($request->declines->where('fixer_id', $fixer->id)->isNotEmpty()) {
            return false;
        }

        if ($request->fixer_id === $fixer->id) {
            return true;
        }

        $eligible = $fixer->services()
            ->where('services.id', $request->service_id)
            ->exists();

        return $request->fixer_id === null && $eligible;
    }
}
