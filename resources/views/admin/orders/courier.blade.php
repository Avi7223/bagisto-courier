{{--
    Include this partial inside Bagisto's admin order-view Blade template
    via a view override (see README > "Order Integration"):

        @include('bagisto-courier::admin.orders.courier', ['order' => $order])
--}}
@php
    $courierOrder = app(\Rajibbinalam\BagistoCourier\Repositories\CourierOrderRepository::class)->findByOrderId(
        $order->id,
    );
@endphp

<div class="bg-white rounded p-4 mt-4 border">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold">Courier</h3>

        @if (!$courierOrder)
            <a href="{{ route('admin.courier.create.form', $order->id) }}"
            onclick="window.location.href = '{{ route('admin.courier.create.form', $order->id) }}'; return false;"
            class="btn btn-lg btn-primary"
            >
            Create Courier Order
            </a>
        @else
            <form action="{{ route('admin.courier.sync', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    Refresh Status
                </button>
            </form>
        @endif
    </div>

    @if ($courierOrder)
        <table class="w-full text-sm mb-4">
            <tbody>
                <tr>
                    <td class="py-1 text-gray-500">Courier</td>
                    <td class="py-1 font-medium">{{ ucfirst($courierOrder->courier) }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-500">Tracking ID</td>
                    <td class="py-1 font-medium">{{ $courierOrder->tracking_number ?? $courierOrder->consignment_id }}
                    </td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-500">Courier Status</td>
                    <td class="py-1 font-medium capitalize">{{ str_replace('_', ' ', $courierOrder->status) }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-500">Last Synced</td>
                    <td class="py-1 font-medium">{{ $courierOrder->last_synced_at?->diffForHumans() ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p class="text-sm text-gray-500 mb-4">No courier order created yet for this order.</p>
    @endif

    <div class="border-t pt-3">
        <form action="{{ route('admin.courier.status.update', $order->id) }}" method="POST"
            class="flex items-center gap-2">
            @csrf
            <label class="text-sm text-gray-600">Order Status</label>
            <select name="status" class="border rounded px-2 py-1 text-sm">
                @foreach (['pending', 'pending_payment', 'processing', 'completed', 'canceled', 'closed', 'fraud'] as $status)
                    <option value="{{ $status }}" @selected($order->status === $status)>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
        </form>
        <p class="text-xs text-gray-400 mt-1">
            Directly overrides the order status and bypasses Bagisto's normal invoice/shipment workflow — use
            deliberately.
        </p>
    </div>
</div>
