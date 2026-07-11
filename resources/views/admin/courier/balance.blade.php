@php
    $result = session('balance_result');
@endphp

<div class="max-w-2xl mx-auto bg-white rounded p-6 mt-6 border">
    <h2 class="text-xl font-semibold mb-4">Courier API Balance</h2>

    @if ($result)
        <div class="mb-4 p-3 rounded {{ $result['success'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
            <strong>{{ ucfirst($result['courier']) }}:</strong>
            @if ($result['success'] && array_key_exists('charge', $result))
                Balance: {{ number_format($result['charge'] ?? 0, 2) }}
            @else
                {{ $result['message'] }}
            @endif
        </div>
    @endif

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Courier</th>
                <th class="py-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($availableCouriers as $code)
                <tr class="border-b">
                    <td class="py-3">
                        {{ ucfirst($code) }}
                        @if ($code === $defaultCourier)
                            <span class="text-xs text-blue-600">(default)</span>
                        @endif
                    </td>
                    <td class="py-3 text-right space-x-2">
                        <form action="{{ route('admin.courier.balance.test', $code) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Test Connection</button>
                        </form>
                        <form action="{{ route('admin.courier.balance.check', $code) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Check Balance</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-xs text-gray-400 mt-4">
        Note: not every courier's API exposes a balance endpoint. If a courier
        doesn't support it, you'll see a clear message instead of a number.
    </p>
</div>
