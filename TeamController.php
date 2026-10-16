<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamController
{
    public function index(Request $request): View
    {
        return view('team.index', [
            'users' => User::query()->where('business_id', $request->user()->business_id)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:manager,accountant,staff'],
            'password' => ['required', 'string', 'min:10'],
        ]);

        $request->user()->business->users()->create($validated);

        return back()->with('status', 'Team member added. Share their sign-in details securely.');
    }
}