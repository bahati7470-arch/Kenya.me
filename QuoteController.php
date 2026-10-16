<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quote;
use App\Services\InvoiceReferenceGenerator;
use App\Services\QuotePricingCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuoteController
{
    public function index(Request $request): View
    {
        $quotes = Quote::query()
            ->with('customer')
            ->where('business_id', $request->user()->business_id)
            ->latest()
            ->paginate(15);

        return view('quotes.index', ['quotes' => $quotes]);
    }

    public function create(): View
    {
        return view('quotes.create');
    }

    public function store(Request $request, QuotePricingCalculator $pricing): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'quantity' => ['required', 'decimal:0,2', 'min:0.01', 'max:999999'],
            'unit_price' => ['required', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'markup_percent' => ['nullable', 'decimal:0,2', 'min:0', 'max:1000'],
            'discount_percent' => ['nullable', 'decimal:0,2', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $business = $request->user()->business;
        $customer = DB::transaction(function () use ($validated, $business, $pricing): Customer {
            $customerQuery = $business->customers();

            if (!empty($validated['customer_phone'])) {
                $customerQuery->where('phone', $validated['customer_phone']);
            } elseif (!empty($validated['customer_email'])) {
                $customerQuery->where('email', $validated['customer_email']);
            } else {
                $customerQuery->whereRaw('1 = 0');
            }

            $customer = $customerQuery->first() ?? $business->customers()->create([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
                'email' => $validated['customer_email'] ?? null,
            ]);

            $quantity = $validated['quantity'];
            $unitPrice = $validated['unit_price'];
            $markup = $validated['markup_percent'] ?? '0';
            $discount = $validated['discount_percent'] ?? '0';
            $amount = $pricing->total($quantity, $unitPrice, $markup, $discount);

            if (\Brick\Math\BigDecimal::of($amount)->isGreaterThan('9999999999.99')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'unit_price' => 'The calculated quote exceeds the maximum supported invoice amount.',
                ]);
            }

            $business->quotes()->create([
                'customer_id' => $customer->id,
                'quote_number' => 'KMQ-'.now()->format('ym').'-'.Str::upper(Str::random(8)),
                'description' => $validated['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'markup_percent' => $markup,
                'discount_percent' => $discount,
                'amount' => $amount,
                'status' => 'sent',
                'valid_until' => $validated['valid_until'] ?? null,
            ]);

            return $customer;
        });

        $quote = Quote::query()->where('business_id', $business->id)->where('customer_id', $customer->id)->latest('id')->firstOrFail();

        return redirect()->route('quotes.show', $quote);
    }

    public function show(Request $request, Quote $quote): View
    {
        abort_unless($quote->business_id === $request->user()->business_id, 404);

        return view('quotes.show', ['quote' => $quote->load('customer', 'invoice')]);
    }

    public function convert(Request $request, Quote $quote, InvoiceReferenceGenerator $references): RedirectResponse
    {
        abort_unless($quote->business_id === $request->user()->business_id, 404);

        $invoice = DB::transaction(function () use ($quote, $references): Invoice {
            $lockedQuote = Quote::query()->whereKey($quote->id)->lockForUpdate()->firstOrFail();

            if ($lockedQuote->invoice_id) {
                return Invoice::query()->findOrFail($lockedQuote->invoice_id);
            }

            $validUntil = $lockedQuote->getRawOriginal('valid_until');
            abort_if($validUntil !== null && $validUntil < today()->toDateString(), 422, 'This quote has expired.');

            $invoice = Invoice::query()->create([
                'business_id' => $lockedQuote->business_id,
                'customer_id' => $lockedQuote->customer_id,
                'mpesa_reference' => $references->generate(),
                'description' => $lockedQuote->description,
                'amount' => $lockedQuote->amount,
                'status' => 'unpaid',
            ]);

            $lockedQuote->update(['status' => 'converted', 'invoice_id' => $invoice->id]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice);
    }
}