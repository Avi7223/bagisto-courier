@php
    $courierOrder = app(\Rajibbinalam\BagistoCourier\Repositories\CourierOrderRepository::class)
        ->findByOrderId($order->id);

    $courierManager = app(\Rajibbinalam\BagistoCourier\Services\CourierManager::class);
    $availableCouriers = $courierManager->availableCouriers();
    $defaultCourier = $courierManager->defaultCourierCode();

    $shippingAddress = $order->shipping_address;
    $prefill = [
        'recipient_name'    => trim(($shippingAddress->first_name ?? '') . ' ' . ($shippingAddress->last_name ?? '')),
        'recipient_phone'   => $shippingAddress->phone ?? '',
        'recipient_address' => trim(($shippingAddress->address1[0] ?? '') . ', ' . ($shippingAddress->city ?? '')),
        'recipient_city'    => $shippingAddress->city ?? '',
        'cod_amount'        => $order->payment?->method === 'cashondelivery' ? (float) $order->grand_total : 0,
        'item_description'  => 'Order #' . $order->increment_id,
        'item_quantity'     => (int) $order->total_qty_ordered,
    ];

    $inputClass = 'w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 transition-colors focus:border-gray-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-gray-500';
    $labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
@endphp

<div class="box-shadow rounded bg-white p-4 mt-2 dark:bg-gray-900">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white">Courier</h3>

        @if (! $courierOrder)
            <button
                type="button"
                onclick="document.getElementById('courier-create-modal').classList.remove('hidden')"
                class="primary-button"
            >
                Create Courier Order
            </button>
        @else
            <form action="{{ route('admin.courier.sync', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="secondary-button">
                    Refresh Status
                </button>
            </form>
        @endif
    </div>

    @if ($courierOrder)
        <table class="w-full text-sm">
            <tbody>
                <tr>
                    <td class="py-1 text-gray-500 dark:text-gray-400">Courier</td>
                    <td class="py-1 font-medium text-gray-800 dark:text-white">{{ ucfirst($courierOrder->courier) }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-500 dark:text-gray-400">Tracking ID</td>
                    <td class="py-1 font-medium text-gray-800 dark:text-white">{{ $courierOrder->tracking_number ?? $courierOrder->consignment_id }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-500 dark:text-gray-400">Courier Status</td>
                    <td class="py-1 font-medium capitalize text-gray-800 dark:text-white">{{ str_replace('_', ' ', $courierOrder->status) }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-500 dark:text-gray-400">Last Synced</td>
                    <td class="py-1 font-medium text-gray-800 dark:text-white">{{ $courierOrder->last_synced_at?->diffForHumans() ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">No courier order created yet for this order.</p>
    @endif
</div>

{{-- ===== Modal ===== --}}
<div
    id="courier-create-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
    onclick="if (event.target === this) { this.classList.add('hidden'); }"
>
    <div class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                    Send to Courier
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500">Order #{{ $order->increment_id }}</p>
            </div>

            <button
                type="button"
                onclick="document.getElementById('courier-create-modal').classList.add('hidden')"
                class="flex h-7 w-7 items-center justify-center rounded-full text-lg leading-none text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                aria-label="Close"
            >
                &times;
            </button>
        </div>

        {{-- Body --}}
        <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="courier-create-form" action="{{ route('admin.courier.create', $order->id) }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label class="{{ $labelClass }}">Courier</label>
                    <select name="courier" class="{{ $inputClass }}">
                        @foreach ($availableCouriers as $code)
                            <option value="{{ $code }}" @selected(old('courier', $defaultCourier) === $code)>{{ ucfirst($code) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelClass }}">Recipient Name</label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $prefill['recipient_name']) }}" class="{{ $inputClass }}" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Phone</label>
                        <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $prefill['recipient_phone']) }}" class="{{ $inputClass }}" required>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Address</label>
                    <textarea name="recipient_address" rows="2" class="{{ $inputClass }}" required>{{ old('recipient_address', $prefill['recipient_address']) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelClass }}">City</label>
                        <input type="text" name="recipient_city" value="{{ old('recipient_city', $prefill['recipient_city']) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">COD Amount</label>
                        <input type="number" step="0.01" name="cod_amount" value="{{ old('cod_amount', $prefill['cod_amount']) }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="grid grid-cols-[1fr_90px] gap-3">
                    <div>
                        <label class="{{ $labelClass }}">Item / Note</label>
                        <input type="text" name="item_description" value="{{ old('item_description', $prefill['item_description']) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Qty</label>
                        <input type="number" name="item_quantity" value="{{ old('item_quantity', $prefill['item_quantity']) }}" min="1" class="{{ $inputClass }}">
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3.5 dark:border-gray-800 dark:bg-gray-950">
            <button
                type="button"
                onclick="document.getElementById('courier-create-modal').classList.add('hidden')"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                Cancel
            </button>
            <button
                type="submit"
                form="courier-create-form"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
            >
                Send to Courier
            </button>
        </div>
    </div>
</div>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('courier-create-modal')?.classList.remove('hidden');
        });
    </script>
@endif