<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceReferenceGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', 'in:unpaid,partial,paid,overdue'],
        ]);

        $invoices = Invoice::query()
            ->with('customer')
            ->where('business_id', $request->user()->business_id)
            ->when($validated['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('mpesa_reference', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                            $customerQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when(($validated['status'] ?? null) === 'overdue', fn (Builder $query) => $query
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereDate('due_date', '<', today()))
            ->when(isset($validated['status']) && $validated['status'] !== 'overdue', fn (Builder $query) => $query->where('status', $validated['status']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', ['invoices' => $invoices]);
    }

    public function create(): View
    {
        return view('invoices.create');
    }

    public function store(Request $request, InvoiceReferenceGenerator $references): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $business = $request->user()->business;

        $invoice = DB::transaction(function () use ($validated, $references, $business): Invoice {
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

            return $customer->invoices()->create([
                'business_id' => $business->id,
                'mpesa_reference' => $references->generate(),
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'status' => 'unpaid',
                'due_date' => $validated['due_date'] ?? null,
            ]);
        });

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($invoice->business_id === $request->user()->business_id, 404);

        return view('invoices.show', ['invoice' => $invoice->load('customer')]);
    }
}