<?php

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithoutUrlPagination;
use function Livewire\Volt\{computed, usesPagination, state, uses, updating, hydrate, on};

uses([WithoutUrlPagination::class]);

usesPagination();

state(['query']);

updating([
    'query' => fn() => $this->resetPage(),
]);

hydrate(fn() => $this->dispatch('paginated'));

on([
    'refresh-arp-list' => function () {
        return $this->availableRecords;
    },
]);

$availableAddresses = computed(function () {
    $query = strtolower($this->query);

    return collect(cache()->get('mikrotik_address_list', collect()))
        ->filter(function ($item) use ($query) {
            return str_contains(strtolower($item['address'] ?? ''), $query) ||
                str_contains(strtolower($item['network'] ?? ''), $query);
        })
        ->map(fn($item) => (object) $item);
});
?>

<div>
    <x-bale.table header>

        <x-slot name="thead">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Address') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Network') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Interface') }}
                        </span>
                    </div>
                </th>
                {{-- <th scope="col" class="relative py-3.5 pl-3 pr-4">
                    <span class="sr-only">Edit</span>
                </th> --}}
            </tr>
        </x-slot>

        <x-slot name="tbody">
            @foreach ($this->availableAddresses as $key => $address)
                <tr wire:key='address-{{ $key }}' class="hover:bg-gray-50 dark:hover:bg-slate-700/50" x-data="{
                                        openIpAddressDetailModal() {
                                            $wire.dispatch('openBaleModal', { id: 'ipAddressDetailModal' });

                                            let originalData = @js($address);
                                            let mappedData = {
                                                ...originalData,
                                                id: originalData['.id'] ?? null,
                                            };
                                            delete mappedData['.id'];
                                            this.$dispatch('ip-address-data', {
                                                modalTitle: 'IP Address Detail',
                                                ipAddressData: mappedData
                                            });
                                        },
                                    }">
                    <td class="w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 max-w-0 sm:w-auto sm:max-w-none">
                        <div @click="openIpAddressDetailModal"
                            class="flex items-center text-sm text-gray-800 cursor-pointer dark:text-gray-200">

                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-route-icon lucide-route {{$address->dynamic == 'true' ? 'text-gray-500' : 'text-emerald-400'}}">
                                <circle cx="6" cy="19" r="3" />
                                <path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15" />
                                <circle cx="18" cy="5" r="3" />
                            </svg>

                            <span class="ml-2">{{ $address->address }}</span>
                        </div>
                        <dl class="font-normal lg:hidden">
                            <dt class="sr-only">network</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $address->network }}
                                </span>
                            </dd>
                            <dt class="sr-only sm:hidden">interface</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">interface
                                    {{ $address->interface }}</span>
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        {{ $address->network }}
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">
                        <span class="block text-sm text-gray-500">{{ $address->interface ?? null }}</span>
                    </td>

                    {{-- <td class="py-4 pl-3 pr-4 text-sm font-medium text-right ">
                        <div class="hs-dropdown relative inline-block [--placement:bottom|left]">
                            <button id="{{ $key }}" type="button"
                                class="hs-dropdown-toggle py-1.5 px-2 inline-flex justify-center items-center gap-2 rounded-lg text-gray-700 align-middle disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-emerald-300 transition-all text-sm dark:text-neutral-400 dark:hover:text-white dark:focus:ring-offset-gray-800">
                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1" />
                                    <circle cx="19" cy="12" r="1" />
                                    <circle cx="5" cy="12" r="1" />
                                </svg>
                            </button>
                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden divide-y divide-gray-200 min-w-40 z-10 bg-white shadow-2xl rounded-lg p-2 mt-2 dark:divide-neutral-700 dark:bg-neutral-800 dark:border dark:border-neutral-700"
                                aria-labelledby="{{ $key }}">
                                <div class="py-2 first:pt-0 last:pb-0">

                                    <button x-data
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-800 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                                        @click="openIpAddressDetailModal">
                                        View Detail
                                    </button>

                                </div>
                            </div>
                        </div>
                    </td> --}}
                </tr>
            @endforeach
        </x-slot>

    </x-bale.table>
</div>