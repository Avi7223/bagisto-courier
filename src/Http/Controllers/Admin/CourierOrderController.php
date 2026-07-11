<?php

namespace Rajibbinalam\BagistoCourier\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rajibbinalam\BagistoCourier\Actions\SyncCourierStatusAction;
use Rajibbinalam\BagistoCourier\DTO\OrderData;
use Rajibbinalam\BagistoCourier\Exceptions\CourierException;
use Rajibbinalam\BagistoCourier\Jobs\CreateCourierOrderJob;
use Rajibbinalam\BagistoCourier\Repositories\CourierOrderRepository;
use Rajibbinalam\BagistoCourier\Services\CourierManager;
use Webkul\Sales\Models\Order;

class CourierOrderController extends Controller
{
    public function __construct(
        protected CourierOrderRepository $repository,
        protected SyncCourierStatusAction $syncAction,
        protected CourierManager $manager,
    ) {
    }

    /**
     * Shows an editable preview of what will be sent to the courier,
     * pre-filled from the Bagisto order, before anything is actually sent.
     */
    public function create(int $orderId)
    {
        $order   = Order::findOrFail($orderId);
        $address = $order->shipping_address;

        $prefill = [
            'recipient_name'    => trim(($address->first_name ?? '') . ' ' . ($address->last_name ?? '')),
            'recipient_phone'   => $address->phone ?? '',
            'recipient_address' => trim(($address->address1[0] ?? '') . ', ' . ($address->city ?? '')),
            'recipient_city'    => $address->city ?? '',
            'cod_amount'        => $order->payment?->method === 'cashondelivery' ? (float) $order->grand_total : 0,
            'item_description'  => 'Order #' . $order->increment_id,
            'item_quantity'     => (int) $order->total_qty_ordered,
        ];

        return view('bagisto-courier::admin.orders.courier-create-form', [
            'order'             => $order,
            'prefill'           => $prefill,
            'defaultCourier'    => $this->manager->defaultCourierCode(),
            'availableCouriers' => $this->manager->availableCouriers(),
        ]);
    }

    /**
     * Takes the admin-reviewed/edited data and queues the actual courier
     * order creation. This is what the create-form above submits to.
     */
    public function store(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validate([
            'courier'           => 'required|string',
            'recipient_name'    => 'required|string|max:255',
            'recipient_phone'   => 'required|string|max:32',
            'recipient_address' => 'required|string|max:1000',
            'recipient_city'    => 'nullable|string|max:255',
            'cod_amount'        => 'nullable|numeric|min:0',
            'item_description'  => 'nullable|string|max:500',
            'item_quantity'     => 'nullable|integer|min:1',
        ]);

        $order = Order::findOrFail($orderId);

        $orderData = OrderData::fromArray([
            'order_id'                => $orderId,
            'invoice_or_order_number' => $order->increment_id,
            'recipient_name'          => $validated['recipient_name'],
            'recipient_phone'         => $validated['recipient_phone'],
            'recipient_address'       => $validated['recipient_address'],
            'recipient_city'          => $validated['recipient_city'] ?? null,
            'cod_amount'              => $validated['cod_amount'] ?? 0,
            'item_description'        => $validated['item_description'] ?? null,
            'item_quantity'           => $validated['item_quantity'] ?? 1,
        ]);

        CreateCourierOrderJob::dispatch($orderData->toArray(), $validated['courier']);

        session()->flash('success', 'Courier order is being created in the background. Refresh in a few seconds to see the tracking details.');

        return redirect()->back();
    }

    public function sync(int $orderId): RedirectResponse
    {
        $courierOrder = $this->repository->findByOrderId($orderId);

        if (! $courierOrder) {
            session()->flash('error', 'No courier order exists for this order yet.');

            return redirect()->back();
        }

        try {
            $this->syncAction->execute($courierOrder);
            session()->flash('success', 'Courier status refreshed.');
        } catch (CourierException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Manual order-status override. Bagisto normally only changes order
     * status through its invoice/shipment workflow — this bypasses that
     * and sets the status column directly, so use it deliberately.
     */
    public function updateOrderStatus(Request $request, int $orderId): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,pending_payment,processing,completed,canceled,closed,fraud',
        ]);

        Order::where('id', $orderId)->update([
            'status' => $request->status,
        ]);

        session()->flash('success', 'Order status updated.');

        return redirect()->back();
    }
}
