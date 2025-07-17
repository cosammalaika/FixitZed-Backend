<?php

use App\Livewire\Role\RoleCreate;
use App\Livewire\Role\RoleEdit;
use App\Livewire\Role\RoleIndex;
use App\Livewire\Role\RoleShow;
use App\Livewire\Service\ServiceEdit;
use App\Livewire\Service\ServiceShow;
use App\Livewire\Service\ServiceCreate;
use App\Livewire\Service\ServiceIndex;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserShow;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('users', UserIndex::class)->name('users.index');
    Route::get('users/create', UserCreate::class)->name('users.create');
    Route::get('users/{id}/edit', UserEdit::class)->name('users.edit');
    Route::get('users/{id}', UserShow::class)->name('users.show');

    Route::get('services', ServiceIndex::class)->name('services.index')->middleware("permission:view.services|create.services|edit.services|show.services|delete.services");
    Route::get('services/create', ServiceCreate::class)->name('services.create');
    Route::get('services/{id}/edit', ServiceEdit::class)->name('services.edit');
    Route::get('services/{id}', ServiceShow::class)->name('services.show');

    Route::get('role', RoleIndex::class)->name('role.index');
    Route::get('role/create', RoleCreate::class)->name(name: 'roles.create');
    Route::get('role/{id}/edit', RoleEdit::class)->name('roles.edit');
    Route::get('role/{id}', RoleShow::class)->name('roles.show');


    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
