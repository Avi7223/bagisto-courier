@php
    $selectedCourier = old('courier', $defaultCourier);
@endphp

<div class="max-w-2xl mx-auto bg-white rounded p-6 mt-4 border">
    <h2 class="text-xl font-semibold mb-4">Send Order #{{ $order->increment_id }} to Courier</h2>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-600">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.courier.create', $order->id) }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Courier</label>
            <select name="courier" class="w-full border rounded px-3 py-2">
                @foreach ($availableCouriers as $code)
                    <option value="{{ $code }}" @selected($selectedCourier === $code)>{{ ucfirst($code) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Recipient Name</label>
            <input type="text" name="recipient_name" value="{{ old('recipient_name', $prefill['recipient_name']) }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Recipient Phone</label>
            <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $prefill['recipient_phone']) }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Recipient Address</label>
            <textarea name="recipient_address" class="w-full border rounded px-3 py-2" required>{{ old('recipient_address', $prefill['recipient_address']) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">City</label>
            <input type="text" name="recipient_city" value="{{ old('recipient_city', $prefill['recipient_city']) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">COD Amount</label>
            <input type="number" step="0.01" name="cod_amount" value="{{ old('cod_amount', $prefill['cod_amount']) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Item Description / Note</label>
            <input type="text" name="item_description" value="{{ old('item_description', $prefill['item_description']) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Quantity</label>
            <input type="number" name="item_quantity" value="{{ old('item_quantity', $prefill['item_quantity']) }}" class="w-full border rounded px-3 py-2" min="1">
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-lg btn-primary">Send to Courier</button>
        </div>
    </form>
</div>
