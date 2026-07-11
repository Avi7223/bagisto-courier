<x-admin::layouts>
    <x-slot:title>
        Send Order #{{ $order->increment_id }} to Courier
    </x-slot>

    @php
        $selectedCourier = old('courier', $defaultCourier);
    @endphp

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
            Send Order #{{ $order->increment_id }} to Courier
        </p>

        <a href="{{ route('admin.sales.orders.view', $order->id) }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
            Back
        </a>
    </div>

    <div class="mt-4 max-w-2xl">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
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
                            <option value="{{ $code }}" @selected($selectedCourier === $code)>{{ ucfirst($code) }}</option>
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
                    <input