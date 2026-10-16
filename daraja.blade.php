@extends('layouts.app')

@section('title', 'Daraja settings')

@section('content')
    <p class="eyebrow">Integrations</p>
    <h1>Safaricom Daraja</h1>
    <p class="intro">Configure C2B callback credentials and register your public HTTPS endpoints.</p>
    @if (session('status'))<p class="hint">{{ session('status') }}</p>@endif
    <div class="workspace">
        <section class="panel">
            <h2>Merchant credentials</h2>
            <form method="post" action="{{ route('settings.daraja.update') }}">
                @csrf
                @method('put')
                <div class="field-grid">
                    <div class="field field-wide"><label for="paybill_shortcode">Paybill shortcode</label><input id="paybill_shortcode" name="paybill_shortcode" inputmode="numeric" pattern="[0-9]{5,7}" value="{{ old('paybill_shortcode', $business->paybill_shortcode) }}" required>@error('paybill_shortcode')<p class="error">{{ $message }}</p>@enderror</div>
                    <div class="field field-wide"><label for="daraja_consumer_key">Consumer key</label><input id="daraja_consumer_key" name="daraja_consumer_key" autocomplete="off">@error('daraja_consumer_key')<p class="error">{{ $message }}</p>@enderror</div>
                    <div class="field field-wide"><label for="daraja_consumer_secret">Consumer secret</label><input id="daraja_consumer_secret" name="daraja_consumer_secret" type="password" autocomplete="new-password">@error('daraja_consumer_secret')<p class="error">{{ $message }}</p>@enderror</div>
                </div>
                @error('credentials')<p class="error">{{ $message }}</p>@enderror
                <button class="submit" type="submit">Save settings</button>
            </form>
        </section>
        <aside class="side-note">
            <h2>Callback endpoints</h2>
            <p>Environment: {{ ucfirst(config('daraja.environment')) }}</p>
            <p>Confirmation</p><span class="reference">{{ $confirmationUrl }}</span>
            <p style="margin-top:12px">Validation</p><span class="reference">{{ $validationUrl }}</span>
            @if (!$isHttps)<p class="error" style="margin-top:12px">A public HTTPS callback URL is required. Localhost URLs cannot receive Safaricom callbacks.</p>@endif
            @if ($business->daraja_registered_at)<p class="hint" style="margin-top:12px">Registered {{ $business->daraja_registered_at->format('d M Y H:i') }}</p>@endif
            @error('callback')<p class="error" style="margin-top:12px">{{ $message }}</p>@enderror
            @error('daraja')<p class="error" style="margin-top:12px">{{ $message }}</p>@enderror
            <form method="post" action="{{ route('settings.daraja.register') }}">
                @csrf
                <button class="submit" type="submit" @disabled(!$isHttps)>Register URLs</button>
            </form>
        </aside>
    </div>
@endsection