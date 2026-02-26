<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $actor = auth()->user();

        if (! $actor) {
            abort(403);
        }

        if (! method_exists($actor, 'hasRole') || ! $actor->hasRole(['Super Admin', 'Admin'])) {
            return $this->forbiddenResponse($request);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'] ?? [])
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return $this->validationLikeResponse(
                $request,
                'Please select at least one valid notification.'
            );
        }

        $existingIds = Notification::query()
            ->whereIn('id', $ids->all())
            ->pluck('id');

        $deleted = 0;
        if ($existingIds->isNotEmpty()) {
            $deleted = Notification::query()
                ->whereIn('id', $existingIds->all())
                ->delete();
        }

        $missing = $ids->count() - $existingIds->count();
        $message = $this->buildMessage($deleted, $missing);

        if (function_exists('log_user_action') && $deleted > 0) {
            log_user_action(
                'bulk deleted notifications',
                "Deleted {$deleted} notifications" . ($missing > 0 ? " ({$missing} missing)." : '.')
            );
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'deleted' => $deleted,
                'missing' => max(0, $missing),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function buildMessage(int $deleted, int $missing): string
    {
        $parts = ["Deleted {$deleted} notification" . ($deleted === 1 ? '' : 's') . '.'];

        if ($missing > 0) {
            $parts[] = "{$missing} " . ($missing === 1 ? 'was' : 'were') . ' already missing.';
        }

        return implode(' ', $parts);
    }

    private function forbiddenResponse(Request $request): JsonResponse|RedirectResponse
    {
        $message = 'Only Admin or Super Admin can bulk delete notifications.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->with('error', $message);
    }

    private function validationLikeResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'ids' => [$message],
                ],
            ], 422);
        }

        return back()->with('error', $message);
    }
}
