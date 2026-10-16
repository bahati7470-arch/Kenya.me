<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentController
{
    public function invoice(Request $request, Invoice $invoice): Response
    {
        abort_unless($invoice->business_id === $request->user()->business_id, 404);

        return Pdf::loadView('documents.invoice', [
            'invoice' => $invoice->load('customer', 'business'),
        ])->download('invoice-'.$invoice->mpesa_reference.'.pdf');
    }

    public function quote(Request $request, Quote $quote): Response
    {
        abort_unless($quote->business_id === $request->user()->business_id, 404);

        return Pdf::loadView('documents.quote', [
            'quote' => $quote->load('customer', 'business'),
        ])->download('quote-'.$quote->quote_number.'.pdf');
    }
}