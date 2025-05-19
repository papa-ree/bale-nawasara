<?php

use function Livewire\Volt\{title, mount};
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Paparee\BaleNawasara\App\Services\MikrotikService;
use Paparee\BaleNawasara\App\Traits\PaginationCollection;
use Livewire\WithoutUrlPagination;
use function Livewire\Volt\{computed, usesPagination, state, uses, updating, hydrate, on};

uses([WithoutUrlPagination::class]);

title('IP Publics');

mount(function () {
    if (session()->has('saved')) {
        LivewireAlert::title(session('saved.title'))->toast()->position('top-end')->success()->show();
    }
});

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
    if (cache()->get('mikrotik_arp_list')) {
        $ip = collect(cache()->get('mikrotik_arp_list', collect()))->map(function ($item) {
            return (object) $item;
        });
    } else {
        $mikrotik = new MikroTikService();

        $arpList = $mikrotik->getArpList();
        Cache::put('mikrotik_arp_list', $arpList, now()->addMinutes(config('bale-nawasara.mikrotik.cache_lifetime')));

        $ip = collect(cache()->get('mikrotik_arp_list', collect()))->map(function ($item) {
            return (object) $item;
        });
    }
    return $ip;
});

?>

<div>
    <x-bale.table header customHeader>
        <x-slot name="customHeader">
            halo
        </x-slot>

        <x-slot name="thead">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('IP Address') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Interface') }}
                        </span>
                    </div>
                </th>
                <th scope="col"
                    class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Comment') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4">
                    <span class="sr-only">Edit</span>
                </th>
            </tr>
        </x-slot>

        <x-slot name="tbody">
            @foreach ($this->availableAddresses as $key => $address)
                <tr wire:key='address-{{ $key }}' class="hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    x-data="{}">
                    <td class="w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 max-w-0 sm:w-auto sm:max-w-none">
                        <div @click="openPermissionModal"
                            class="flex items-center text-sm text-gray-800 cursor-pointer dark:text-gray-200">
                            @if ($address->dynamic != 'true')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-zap-icon lucide-zap text-emerald-300">
                                    <path
                                        d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-gray-200 lucide lucide-zap-icon lucide-zap">
                                    <path
                                        d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
                                </svg>
                            @endif
                            {{ $address->address }}
                        </div>
                        <dl class="font-normal lg:hidden">
                            <dt class="sr-only">Page Slug</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{-- @foreach ($address->roles as $role)
                                        <div @click="openPermissionModal"
                                            class="inline-block px-2 py-1 truncate cursor-pointer text-xs rounded-full {{ $this->getRoleColor($loop->index) }}">
                                            {{ $role->address }}
                                        </div>
                                    @endforeach --}}
                                </span>
                            </dd>
                            <dt class="sr-only sm:hidden">Created At</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Created At
                                    {{ $address->interface }}</span>
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        {{-- @foreach ($address->roles as $role)
                            <span
                                class="inline-block truncate px-2 py-1 text-xs rounded-full {{ $this->getRoleColor($loop->index) }}">
                                {{ $role->address }}
                            </span>
                        @endforeach --}}
                        {{ $address->interface }}
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">
                        <span class="block text-sm text-gray-500">{{ $address->comment ?? null }}</span>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-sm font-medium text-right ">
                        <div class="hs-dropdown relative inline-block [--placement:bottom|left]">
                            <button id="{{ $key . $address->address }}" type="button"
                                class="hs-dropdown-toggle py-1.5 px-2 inline-flex justify-center items-center gap-2 rounded-lg text-gray-700 align-middle disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-emerald-300 transition-all text-sm dark:text-neutral-400 dark:hover:text-white dark:focus:ring-offset-gray-800">
                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1" />
                                    <circle cx="19" cy="12" r="1" />
                                    <circle cx="5" cy="12" r="1" />
                                </svg>
                            </button>
                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden divide-y divide-gray-200 min-w-40 z-10 bg-white shadow-2xl rounded-lg p-2 mt-2 dark:divide-neutral-700 dark:bg-neutral-800 dark:border dark:border-neutral-700"
                                aria-labelledby="{{ $key . $address->address }}">
                                {{-- <div class="py-2 first:pt-0 last:pb-0">

                                    <button x-data
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-800 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                                        @click="openPermissionModal">
                                        Edit
                                    </button>

                                </div>

                                <div class="py-2 first:pt-0 last:pb-0">
                                    <button @click="openPermissionDeleteModal"
                                        class="flex items-center w-full px-3 py-2 text-sm text-red-600 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-red-500 dark:hover:bg-neutral-700">
                                        Delete
                                    </button>
                                </div> --}}
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot>

    </x-bale.table>
</div>
