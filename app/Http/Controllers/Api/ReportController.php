<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResolvesPerPage;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    use ResolvesPerPage;

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'type' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string'],
            'target_id' => ['nullable', 'integer'],
        ]);

        $report = Report::create([
            'user_id' => $user->id,
            'type' => $data['type'] ?? 'user',
            'subject' => $data['subject'],
            'message' => $data['message'],
            'target_user_id' => $data['target_id'] ?? null,
            'status' => 'open',
        ]);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function index(Request $request): JsonResponse
    {
        abort_if(!$this->isAdmin($request->user()), 403);
        $perPage = $this->resolvePerPage($request);
        $reports = Report::with(['reporter', 'target'])->orderByDesc('id')->paginate($perPage);
        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function update(Report $report, Request $request): JsonResponse
    {
        abort_if(!$this->isAdmin($request->user()), 403);
        $data = $request->validate([
            'status' => ['nullable', 'string', 'in:open,reviewed,action_taken,closed'],
            'action' => ['nullable', 'string', 'in:none,warn,suspend,ban'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $report->fill([
            'status' => $data['status'] ?? $report->status,
            'action' => $data['action'] ?? $report->action,
        ]);

        if (!empty($data['action']) && $report->target_user_id) {
            $defaultDays = (int) Setting::get('admin.reports.default_action_duration_days', 7);
            $this->applyAction($report->target_user_id, $data['action'], $data['days'] ?? $defaultDays);
            $report->status = $report->status === 'open' ? 'action_taken' : $report->status;
            if (in_array($data['action'], ['suspend', 'ban'], true)) {
                $report->resolved_at = now();
            }
        }

        if (($data['status'] ?? null) === 'closed') {
            $report->resolved_at = now();
        }

        $report->save();
        return response()->json(['success' => true, 'data' => $report->fresh()]);
    }

    protected function isAdmin(User $user): bool
    {
        // Flexible checks depending on available schema/traits
        if (property_exists($user, 'is_admin') && $user->is_admin) return true;
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super-admin') || $user->hasRole('owner');
        }
        $role = strtolower((string) ($user->role ?? ''));
        if (in_array($role, ['admin', 'superadmin', 'super-admin'], true)) return true;
        if (isset($user->roles) && is_iterable($user->roles)) {
            foreach ($user->roles as $r) {
                $name = strtolower((string) ($r->name ?? $r ?? ''));
                if (in_array($name, ['admin', 'superadmin', 'super-admin', 'owner'], true)) return true;
            }
        }
        return false;
    }

    protected function applyAction(int $userId, string $action, ?int $days = null): void
    {
        $user = User::find($userId);
        if (!$user) return;

        if ($action === 'ban') {
            if (Schema::hasColumn('users', 'banned_at')) {
                $user->banned_at = now();
                $user->save();
            } elseif (Schema::hasColumn('users', 'status')) {
                $user->status = 'banned';
                $user->save();
            }
        } elseif ($action === 'suspend') {
            $defaultDays = (int) setting('admin.reports.default_action_duration_days', 7);
            $until = now()->addDays(max(1, (int) ($days ?? $defaultDays)));
            if (Schema::hasColumn('users', 'suspended_until')) {
                $user->suspended_until = $until;
                $user->save();
            } elseif (Schema::hasColumn('users', 'status')) {
                $user->status = 'suspended';
                $user->save();
            }
        }
    }
}
