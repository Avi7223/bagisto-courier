<?php

namespace Rajibbinalam\BagistoCourier\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Rajibbinalam\BagistoCourier\Events\CourierStatusUpdated;
use Rajibbinalam\BagistoCourier\Models\CourierOrder;
use Rajibbinalam\BagistoCourier\Repositories\CourierOrderRepository;

class WebhookController extends Controller
{
    public function __construct(protected CourierOrderRepository $repository)
    {
    }

    /**
     * Handles SteadFast's "delivery_status" and "tracking_update" webhooks.
     * Set this URL in the SteadFast merchant panel:
     *   https://yourdomain.com/courier/webhook/steadfast
     */
    public function steadfast(Request $request): JsonResponse
    {
        if (! $this->verifySteadFastAuth($request)) {
            Log::channel('courier')->warning('SteadFast webhook: invalid/missing auth token');

            return response()->json(['status' => 'error', 'message' => 'Invalid consignment ID.'], 401);
        }

        Log::channel('courier')->info('SteadFast webhook received', $request->all());

        $consignmentId = (string) $request->input('consignment_id');
        $courierOrder  = $this->repository->findByConsignmentId($consignmentId, 'steadfast');

        if (! $courierOrder) {
            Log::channel('courier')->warning("SteadFast webhook: no matching order for consignment {$consignmentId}");

            return response()->json(['status' => 'error', 'message' => 'Invalid consignment ID.'], 404);
        }

        match ($request->input('notification_type')) {
            'delivery_status' => $this->handleDeliveryStatus($courierOrder, $request),
            'tracking_update'  => $this->handleTrackingUpdate($courierOrder, $request),
            default            => Log::channel('courier')->warning('SteadFast webhook: unknown notification_type', $request->all()),
        };

        return response()->json(['status' => 'success', 'message' => 'Webhook received successfully.']);
    }

    /**
     * Pathao has not published public webhook documentation at the time
     * this package was built. Once they do, mirror steadfast() above:
     * verify the signature/token, map their payload fields to our
     * normalized statuses, then call $this->repository->updateStatus(...).
     */
    public function pathao(Request $request): JsonResponse
    {
        Log::channel('courier')->info('Pathao webhook received (handler not yet implemented)', $request->all());

        return response()->json([
            'status'  => 'error',
            'message' => 'Pathao webhook handling is not implemented yet — see WebhookController::pathao().',
        ], 501);
    }

    protected function handleDeliveryStatus(CourierOrder $courierOrder, Request $request): void
    {
        $status   = $this->normalizeSteadFastStatus($request->input('status'));
        $previous = $courierOrder->status;

        $courierOrder = $this->repository->updateStatus($courierOrder, $status, $request->all());

        if ($previous !== $status) {
            event(new CourierStatusUpdated($courierOrder, $previous, $status));
        }
    }

    protected function handleTrackingUpdate(CourierOrder $courierOrder, Request $request): void
    {
        $meta = array_merge($courierOrder->meta ?? [], [
            'last_tracking_message' => $request->input('tracking_message'),
            'last_tracking_at'      => $request->input('updated_at'),
        ]);

        $courierOrder->update(['meta' => $meta]);
    }

    /**
     * SteadFast sends "Authorization: Bearer {your_api_key}". We compare
     * it against the API key saved in Configure > Sales > Courier Settings.
     * If no key is configured yet, we don't block (useful while testing),
     * but we log a warning so this doesn't go unnoticed in production.
     */
    protected function verifySteadFastAuth(Request $request): bool
    {
        $expected = function_exists('core')
            ? core()->getConfigData('sales.courier.general.steadfast_api_key')
            : config('courier.credentials.steadfast.api_key');

        if (empty($expected)) {
            Log::channel('courier')->warning('SteadFast webhook: no api_key configured to verify against');

            return true;
        }

        $provided = str_replace('Bearer ', '', (string) $request->header('Authorization'));

        return hash_equals((string) $expected, $provided);
    }

    protected function normalizeSteadFastStatus(?string $raw): string
    {
        return match (strtolower((string) $raw)) {
            'delivered'              => 'delivered',
            'partial_delivered'      => 'delivered',
            'cancelled', 'canceled'  => 'cancelled',
            'pending', 'unknown'     => 'pending',
            default                  => 'pending',
        };
    }
}
