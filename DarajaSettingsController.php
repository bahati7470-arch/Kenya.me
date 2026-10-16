<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DarajaSettingsController
{
    public function show(Request $request): View
    {
        $business = $request->user()->business;
        $callbackBase = rtrim((string) config('daraja.callback_base_url'), '/');

        return view('settings.daraja', [
            'business' => $business,
            'confirmationUrl' => $callbackBase.'/webhooks/daraja/c2b/'.$business->webhook_token,
            'validationUrl' => $callbackBase.'/webhooks/daraja/c2b/'.$business->webhook_token.'/validation',
            'isHttps' => Str::startsWith($callbackBase, 'https://'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'paybill_shortcode' => ['required', 'string', 'regex:/^\d{5,7}$/'],
            'daraja_consumer_key' => ['nullable', 'string', 'max:255'],
            'daraja_consumer_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $business = $request->user()->business;
        $business->fill([
            'paybill_shortcode' => $validated['paybill_shortcode'],
            'webhook_token' => $business->webhook_token ?: Str::random(48),
        ]);

        foreach (['daraja_consumer_key', 'daraja_consumer_secret'] as $secret) {
            if (!empty($validated[$secret])) {
                $business->{$secret} = $validated[$secret];
            }
        }

        $business->save();

        return back()->with('status', 'Daraja settings saved.');
    }

    public function register(Request $request): RedirectResponse
    {
        $business = $request->user()->business;
        $baseUrl = rtrim((string) config('daraja.callback_base_url'), '/');

        if (!Str::startsWith($baseUrl, 'https://') || !filter_var(parse_url($baseUrl, PHP_URL_HOST), FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw ValidationException::withMessages([
                'callback' => 'Set DARAJA_CALLBACK_BASE_URL to a public HTTPS URL before registering callbacks.',
            ]);
        }

        if (!$business->paybill_shortcode || !$business->daraja_consumer_key || !$business->daraja_consumer_secret) {
            throw ValidationException::withMessages([
                'credentials' => 'Save your Paybill shortcode and Daraja consumer credentials first.',
            ]);
        }

        $environment = config('daraja.environment');
        $apiBase = rtrim((string) config('daraja.base_urls.'.$environment), '/');

        if (!$apiBase) {
            throw ValidationException::withMessages(['environment' => 'Choose sandbox or production in DARAJA_ENV.']);
        }

        try {
            $tokenResponse = Http::timeout(config('daraja.timeout'))
                ->withBasicAuth($business->daraja_consumer_key, $business->daraja_consumer_secret)
                ->acceptJson()
                ->get($apiBase.'/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            if (!$tokenResponse->successful() || !$tokenResponse->json('access_token')) {
                throw ValidationException::withMessages(['credentials' => 'Daraja did not return an access token. Check the selected environment and credentials.']);
            }

            $token = $tokenResponse->json('access_token');
            $registerResponse = Http::timeout(config('daraja.timeout'))
                ->withToken($token)
                ->acceptJson()
                ->post($apiBase.'/mpesa/c2b/v1/registerurl', [
                    'ShortCode' => $business->paybill_shortcode,
                    'ResponseType' => 'Completed',
                    'ConfirmationURL' => $baseUrl.'/webhooks/daraja/c2b/'.$business->webhook_token,
                    'ValidationURL' => $baseUrl.'/webhooks/daraja/c2b/'.$business->webhook_token.'/validation',
                ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['daraja' => 'Could not reach Safaricom Daraja. Check network access and try again.']);
        }

        if (!$registerResponse->successful() || (string) $registerResponse->json('ResponseCode') !== '0') {
            throw ValidationException::withMessages(['daraja' => $registerResponse->json('errorMessage', 'Safaricom rejected URL registration.')]);
        }

        $business->update(['daraja_registered_at' => now()]);

        return back()->with('status', 'Daraja accepted the C2B callback URLs.');
    }
}