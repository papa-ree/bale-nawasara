<?php

use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\WithoutUrlPagination;
use Paparee\BaleNawasara\App\Models\IpPublic;
use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Services\MikrotikService;

use function Livewire\Volt\{
    title,
    mount,
    computed,
    usesPagination,
    state,
    uses,
    updating,
    hydrate,
    on
};

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
    $query = strtolower($this->query);

    //$arp = cache()->get('mikrotik_arp_list', collect());
    $arpList = collect(cache()->get('mikrotik_arp_list', []));
    //return collect(cache()->get('mikrotik_arp_list', collect()))
    return collect($arpList)
        ->filter(function ($item) use ($query) {
            return str_contains(strtolower($item['address'] ?? ''), $query) ||
                str_contains(strtolower($item['comment'] ?? ''), $query);
        })
        ->map(function ($item) {
            // Cari ip_public berdasarkan address (atau id kalau mappingnya langsung)
            $ipPublic = IpPublic::where('address', $item['address'] ?? null)->first();

            // Default uptime status null
            $uptimeStatus = null;

            if ($ipPublic) {
                $monitor = KumaMonitor::where('hostname', $item['address'])->first();
                $uptimeStatus = $monitor ? $monitor->uptime_status : 'pending';
            }

            return (object) [
                'id' => $item['.id'] ?? null,
                'address' => $item['address'] ?? null,
                'comment' => $ipPublic->comment ?? null,
                'dynamic' => $ipPublic->dynamic ?? null,
                'interface' => $item['interface'] ?? null,
                'mac_address' => $item['mac-address'] ?? null,
                'uptime_status' => $uptimeStatus,
                'monitor' => $monitor,
            ];
        });
});
?>

<div>
    <x-bale.table header>

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
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('MAC Address') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">
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
                <tr wire:key='address-{{ $key }}' class="hover:bg-gray-50 dark:hover:bg-slate-700/50" x-data="{
                        openIpPublicDetailModal() {
                            $wire.dispatch('openBaleModal', { id: 'ipAddressDetailModal' });

                            // ip address data event
                            $wire.dispatch('setIpData', { data: {id: @js($address->id) ?? '', comment: @js($address->comment) ?? '', dynamic: @js($address->dynamic) ?? '', address: @js($address->address) ?? ''} });

                            // ip address data for modal
                            this.$dispatch('ip-address-data', {
                                modalTitle: 'IP Address Detail',
                                ipAddressData: @js($address),
                                monitorData: @js($address->monitor)
                            });
                        },
                        openIpPublicDeleteModal() {
                            $wire.dispatch('openBaleModal', { id: 'openIpPublicDeleteModal' });

                            this.$dispatch('ip-address-data', {
                                ipAddressData: @js($address)
                            });
                        }
                    }">
                    <td class="w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 max-w-0 sm:w-auto sm:max-w-none">
                        <div @click="openIpPublicDetailModal"
                            class="flex items-center text-sm text-gray-800 cursor-pointer dark:text-gray-200">

                            <span
                                class="inline-block w-3 h-3 rounded-full {{$address->uptime_status ? 'bg-emerald-500' : 'bg-red-400'}}"></span>

                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-shield-check-icon lucide-shield-check mx-2 {{$address->dynamic == 'true' ? 'text-gray-500' : 'text-emerald-400'}}">
                                <path
                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>

                            <span class="">{{ $address->address }}</span>
                        </div>

                        <dl class="font-normal lg:hidden">
                            <dt class="sr-only">Interface</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $address->interface }}
                                </span>
                            </dd>
                            <dt class="sr-only md:hidden">Mac Address</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Mac Address
                                    {{ $address->mac_address }}</span>
                            </dd>
                            <dt class="sr-only sm:hidden">Comment</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Comment
                                    {{ $address->interface }}</span>
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        {{ $address->interface }}
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">
                        {{ $address->mac_address }}
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">
                        <span class="block text-sm text-gray-500">{{ $address->comment ?? null }}</span>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-sm font-medium text-right ">
                        <div class="hs-dropdown relative inline-block [--placement:bottom|left]">
                            <button id="{{ $key . $address->address }}" type="button"
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
                                aria-labelledby="{{ $key . $address->address }}">
                                <div class="py-2 first:pt-0 last:pb-0">

                                    <button x-data
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-800 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                                        @click="openIpPublicDetailModal">
                                        View Detail
                                    </button>

                                </div>

                                <div class="py-2 first:pt-0 last:pb-0">
                                    <button @click="openIpPublicDeleteModal"
                                        class="flex items-center w-full px-3 py-2 text-sm text-red-600 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-red-500 dark:hover:bg-neutral-700">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot>

    </x-bale.table>
</div>