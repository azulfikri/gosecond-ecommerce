<x-filament::widget>
    <x-filament::card>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Pesanan Hari Ini --}}
            <div class="flex items-center space-x-4 bg-green-50 p-4 rounded-lg shadow-sm">
                <x-heroicon-o-shopping-bag class="w-8 h-8 text-green-600" />
                <div>
                    <div class="text-lg font-bold text-green-700">{{ $this->todayOrders }}</div>
                    <div class="text-sm text-gray-500">Pesanan Hari Ini</div>
                </div>
            </div>

            {{-- Pending Orders --}}
            <div class="flex items-center space-x-4 bg-yellow-50 p-4 rounded-lg shadow-sm">
                <x-heroicon-o-clock class="w-8 h-8 text-yellow-600" />
                <div>
                    <div class="text-lg font-bold text-yellow-700">{{ $this->pendingOrders }}</div>
                    <div class="text-sm text-gray-500">Pesanan Pending</div>
                </div>
            </div>

            {{-- Stok Hampir Habis --}}
            <div class="flex items-center space-x-4 bg-red-50 p-4 rounded-lg shadow-sm">
                <x-heroicon-o-exclamation-circle class="w-8 h-8 text-red-600" />
                <div>
                    <div class="text-lg font-bold text-red-700">{{ $this->lowStock }}</div>
                    <div class="text-sm text-gray-500">Stok Hampir Habis</div>
                </div>
            </div>

            {{-- User Baru --}}
            <div class="flex items-center space-x-4 bg-blue-50 p-4 rounded-lg shadow-sm">
                <x-heroicon-o-user-plus class="w-8 h-8 text-blue-600" />
                <div>
                    <div class="text-lg font-bold text-blue-700">{{ $this->newUsers }}</div>
                    <div class="text-sm text-gray-500">User Baru Hari Ini</div>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
