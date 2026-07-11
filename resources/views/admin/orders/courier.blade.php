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
@endphp

<div class="box-shadow rounded bg-white p-4 mt-2 dark:bg-gray-900">
    <div class="flex items-center justify-between mb-3">
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

{{-- ===== Modal (hidden by default) ===== --}}
<div
    id="courier-create-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    onclick="if (event.target === this) { this.classList.add('hidden'); }"
>
    <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded bg-white p-6 dark:bg-gray-900">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                Send Order #{{ $order->increment_id }} to Courier
            </h2>

            <button
                type="button"
                onclick="document.getElementById('courier-create-modal').classList.add('hidden')"
                class="text-2xl leading-none text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white"
                aria-label="Close"
            >
                &times;
            </button>
        </div>

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
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Courier</label>
                <select name="courier" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    @foreach ($availableCouriers as $code)
                        <option value="{{ $code }}" @selected(old('courier', $defaultCourier) === $code)>{{ ucfirst($code) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Recipient Name</label>
                <input type="text" name="recipient_name" value="{{ old('recipient_name', $prefill['recipient_name']) }}" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" required>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Recipient Phone</label>
                <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $prefill['recipient_phone']) }}" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" required>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Recipient Address</label>
                <textarea name="recipient_address" rows="3" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" required>{{ old('recipient_address', $prefill['recipient_address']) }}</textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                <input type="text" name="recipient_city" value="{{ old('recipient_city', $prefill['recipient_city']) }}" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">COD Amount</label>
                <input type="number" step="0.01" name="cod_amount" value="{{ old('cod_amount', $prefill['cod_amount']) }}" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Item Description / Note</label>
                <input type="text" name="item_description" value="{{ old('item_description', $prefill['item_description']) }}" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                <input type="number" name="item_quantity" value="{{ old('item_quantity', $prefill['item_quantity']) }}" min="1" class="w-full rounded-md border px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button
                    type="button"
                    onclick="document.getElementById('courier-create-modal').classList.add('hidden')"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    Cancel
                </button>
                <button type="submit" class="primary-button">
                    Send to Courier
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('courier-create-modal')?.classList.remove('hidden');
        });
    </script>
@endif