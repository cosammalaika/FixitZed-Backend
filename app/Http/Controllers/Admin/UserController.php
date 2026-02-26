<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function destroy(User $user): RedirectResponse
    {
        $actor = auth()->user();

        if (! $actor) {
            abort(403);
        }

        if (! method_exists($actor, 'hasRole') || ! $actor->hasRole('Super Admin')) {
            return back()->with('error', 'Only Super Admin can delete users.');
        }

        if ((int) $actor->id === (int) $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return back()->with('error', 'Super Admin accounts cannot be deleted.');
        }

        $deletedUserId = $user->id;
        $deletedUserEmail = $user->email;

        $user->delete();

        if (function_exists('log_user_action')) {
            log_user_action(
                'deleted user',
                description: "Soft deleted user ID: {$deletedUserId}, Email: {$deletedUserEmail}"
            );
        }

        return back()->with('success', 'User deleted successfully.');
    }
}
