<?php

namespace Rajibbinalam\BagistoCourier\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Rajibbinalam\BagistoCourier\Exceptions\CourierException;
use Rajibbinalam\BagistoCourier\Services\CourierManager;

class CourierBalanceController extends Controller
{
    public function __construct(protected CourierManager $manager)
    {
    }

    /**
     * A small standalone admin page listing every configured courier with
     * a "Check Balance" button next to each — visit it at:
     *   {admin_url}/courier/balance
     */
    public function index()
    {
        return view('bagisto-courier::admin.courier.balance', [
            'availableCouriers' => $this->manager->availableCouriers(),
            'defaultCourier'    => $this->manager->defaultCourierCode(),
        ]);
    }

    /**
     * AJAX/form target: fetches live balance from the courier's API and
     * redirects back with the result flashed into the session.
     */
    public function check(string $code)
    {
        try {
            $response = $this->manager->balance($code);

            if ($response->success) {
                session()->flash('balance_result', [
                    'courier' => $code,
                    'success' => true,
                    'charge'  => $response->charge,
                    'message' => $response->message,
                ]);
            } else {
                session()->flash('balance_result', [
                    'courier' => $code,
                    'success' => false,
                    'message' => $response->message,
                ]);
            }
        } catch (CourierException $e) {
            session()->flash('balance_result', [
                'courier' => $code,
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.courier.balance.index');
    }

    /**
     * Verifies API credentials work (auth/token check) without creating
     * any real consignment — safe to click any time.
     */
    public function test(string $code)
    {
        try {
            $response = $this->manager->testConnection($code);

            session()->flash('balance_result', [
                'courier' => $code,
                'success' => $response->success,
                'message' => $response->success
                    ? ('Connection OK — ' . $response->message)
                    : $response->message,
            ]);
        } catch (CourierException $e) {
            session()->flash('balance_result', [
                'courier' => $code,
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.courier.balance.index');
    }
}
