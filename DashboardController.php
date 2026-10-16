<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MpesaPayment;
use App\Models\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $businessId = $request->user()->business_id;

        return view('dashboard', [
            'openInvoices' => Invoice::query()->where('business_id', $businessId)->whereIn('status', ['unpaid', 'partial'])->count(),
            'outstanding' => Invoice::query()->where('business_id', $businessId)->whereIn('status', ['unpaid', 'partial'])->sum('amount')
                - Invoice::query()->where('business_id', $businessId)->whereIn('status', ['unpaid', 'partial'])->sum('amount_paid'),
            'paymentsToday' => MpesaPayment::query()->where('business_id', $businessId)->whereDate('transacted_at', today())->sum('amount'),
            'unmatchedCount' => MpesaPayment::query()->where('business_id', $businessId)->whereIn('status', ['unmatched', 'exception'])->count(),
            'recentInvoices' => Invoice::query()->with('customer')->where('business_id', $businessId)->latest()->limit(8)->get(),
            'recentPayments' => MpesaPayment::query()->where('business_id', $businessId)->whereIn('status', ['unmatched', 'exception'])->latest('transacted_at')->limit(5)->get(),
            'quoteCount' => Quote::query()->where('business_id', $businessId)->where('status', 'sent')->count(),
        ]);
    }
}