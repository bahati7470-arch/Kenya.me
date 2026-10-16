<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\PaymentMatchingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DarajaCallbackController
{
    public function validation(string $token): JsonResponse
    {
        Business::query()->where('webhook_token', $token)->firstOrFail();

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function confirmation(Request $request, string $token, PaymentMatchingService $matching): JsonResponse
    {
        $business = Business::query()->where('webhook_token', $token)->firstOrFail();
        $validated = validator($request->all(), [
            'TransID' => ['required', 'string', 'max:32'],
            'TransAmount' => ['required', 'numeric', 'gt:0'],
            'BillRefNumber' => ['nullable', 'string', 'max:64'],
            'MSISDN' => ['nullable', 'string', 'max:32'],
            'TransTime' => ['required', 'date_format:YmdHis'],
        ])->validate();

        $matching->ingest($business, [
            'receipt_number' => $validated['TransID'],
            'amount' => $validated['TransAmount'],
            'bill_ref_number' => $validated['BillRefNumber'] ?? null,
            'phone' => $validated['MSISDN'] ?? null,
            'transacted_at' => Carbon::createFromFormat('YmdHis', $validated['TransTime'], 'Africa/Nairobi'),
        ], $request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}