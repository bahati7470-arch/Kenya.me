@extends('layouts.app')

@section('title', 'Set up a business')

@section('content')
    <p class="eyebrow">Get started</p>
    <h1>Set up your workspace</h1>
    <p class="intro">Create a Kenya.me workspace for your business. Your records are isolated from other businesses.</p>
    <section class="panel" style="max-width:620px">
        <form method="post" action="{{ route('register.store') }}">
            @csrf
            <div class="field-grid">
                <div class="field field-wide">
                    <label for="business_name">Business name</label>
                    <input id="business_name" name="business_name" value="{{ old('business_name') }}" required maxlength="160" autocomplete="organization">
                    @error('business_name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="name">Your name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="160" autocomplete="name">
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password">
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="10" autocomplete="new-password">
                </div>
            </div>
            <button class="submit" type="submit">Create workspace</button>
        </form>
    </section>
@endsection