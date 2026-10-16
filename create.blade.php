@extends('layouts.app')

@section('title', 'New quotation')

@section('content')
    <p class="eyebrow">Sales</p>
    <h1>Create a quotation</h1>
    <p class="intro">Set a unit rate and quantity, then apply optional markup and discount rules.</p>
    <section class="panel" style="max-width:800px">
        <form method="post" action="{{ route('quotes.store') }}">
            @csrf
            <div class="field-grid">
                <div class="field"><label for="customer_name">Customer name</label><input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>@error('customer_name')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="customer_phone">Phone</label><input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone') }}">@error('customer_phone')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="customer_email">Email <span class="hint">Optional</span></label><input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}">@error('customer_email')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="valid_until">Valid until</label><input id="valid_until" name="valid_until" type="date" min="{{ today()->toDateString() }}" value="{{ old('valid_until') }}">@error('valid_until')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field field-wide"><label for="description">Description</label><textarea id="description" name="description" required>{{ old('description') }}</textarea>@error('description')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="quantity">Quantity / hours</label><input id="quantity" name="quantity" type="number" min="0.01" step="0.01" value="{{ old('quantity', '1') }}" required>@error('quantity')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="unit_price">Rate per unit (KES)</label><input id="unit_price" name="unit_price" type="number" min="0.01" step="0.01" value="{{ old('unit_price') }}" required>@error('unit_price')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="markup_percent">Markup (%)</label><input id="markup_percent" name="markup_percent" type="number" min="0" max="1000" step="0.01" value="{{ old('markup_percent', '0') }}"></div>
                <div class="field"><label for="discount_percent">Discount (%)</label><input id="discount_percent" name="discount_percent" type="number" min="0" max="100" step="0.01" value="{{ old('discount_percent', '0') }}"></div>
            </div>
            <button class="submit" type="submit">Save quotation</button>
        </form>
    </section>
@endsection