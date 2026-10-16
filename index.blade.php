@extends('layouts.app')

@section('title', 'Team access')

@section('content')
    <p class="eyebrow">Workspace access</p>
    <h1>Team members</h1>
    <p class="intro">Manage who can work in {{ auth()->user()->business->name }}.</p>
    @if (session('status'))<p class="hint">{{ session('status') }}</p>@endif
    <div class="workspace">
        <section class="panel">
            <h2>Current access</h2>
            <div class="summary">
                @foreach ($users as $user)
                    <div class="summary-row"><span>{{ $user->name }}<br>{{ $user->email }}</span><strong>{{ ucfirst($user->role) }}</strong></div>
                @endforeach
            </div>
        </section>
        <aside class="side-note">
            <h2>Add a team member</h2>
            <form method="post" action="{{ route('team.store') }}">
                @csrf
                <div class="field-grid">
                    <div class="field field-wide"><label for="name">Name</label><input id="name" name="name" value="{{ old('name') }}" required>@error('name')<p class="error">{{ $message }}</p>@enderror</div>
                    <div class="field field-wide"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<p class="error">{{ $message }}</p>@enderror</div>
                    <div class="field field-wide"><label for="role">Role</label><select id="role" name="role" required><option value="manager">Manager</option><option value="accountant">Accountant</option><option value="staff">Staff</option></select>@error('role')<p class="error">{{ $message }}</p>@enderror</div>
                    <div class="field field-wide"><label for="password">Temporary password</label><input id="password" name="password" type="password" minlength="10" required>@error('password')<p class="error">{{ $message }}</p>@enderror</div>
                </div>
                <button class="submit" type="submit">Add member</button>
            </form>
        </aside>
    </div>
@endsection