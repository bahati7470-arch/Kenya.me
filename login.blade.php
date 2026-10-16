@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <p class="eyebrow">Kenya.me workspace</p>
    <h1>Sign in</h1>
    <p class="intro">Access your invoices, quotations, and payment reconciliation.</p>
    <section class="panel" style="max-width:520px">
        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="field-grid">
                <div class="field field-wide">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field field-wide">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>
                <label class="field-wide"><input style="width:auto" type="checkbox" name="remember" value="1"> Keep me signed in</label>
            </div>
            <button class="submit" type="submit">Sign in</button>
        </form>
        <p class="hint">New to Kenya.me? <a href="{{ route('register') }}">Set up a business</a></p>
    </section>
@endsection