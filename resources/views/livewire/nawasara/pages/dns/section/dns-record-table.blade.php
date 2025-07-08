<?php
use Paparee\BaleNawasara\App\Models\DnsRecord;

use Livewire\WithoutUrlPagination;
use function Livewire\Volt\{computed, usesPagination, state, uses, updating, hydrate, on};

uses([WithoutUrlPagination::class]);

usesPagination();

state(['query']);

updating([
    'query' => fn() => $this->resetPage(),
]);

hydrate(fn() => $this->dispatch('paginated'));

$availableRecords = computed(function () {
    $searchTerm = htmlspecialchars($this->query, ENT_QUOTES, 'UTF-8');

    return DnsRecord::where('name', 'like', '%' . $searchTerm . '%')
        ->orderBy('name')
        ->paginate(100);
});

on([
    'refresh-dns-record-list' => function () {
        return $this->availableRecords;
    },
]);

?>

<div>
    <x-bale.table :links="$this->availableRecords" header>

        <x-slot name="thead">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Name') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Content') }}
                        </span>
                    </div>
                </th>
                <th scope="col"
                    class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Proxied Status') }}
                        </span>
                    </div>
                </th>
                <th scope="col"
                    class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Monitor status') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4">
                    <span class="sr-only">Edit</span>
                </th>
            </tr>
        </x-slot>

        <x-slot name="tbody">
            @foreach ($this->availableRecords as $record)
                <tr wire:key='record-{{ $record->id }}' class="hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    x-data="{
                        openDnsRecordModal() {
                            $wire.dispatch('openBaleModal', { id: 'dnsRecordModal' });
                            $wire.dispatch('getMonitorSslStatus', { monitorId: @js($record->monitor ? $record->monitor->id : '') ?? '' });
                            this.$dispatch('record-data', {
                                modalTitle: 'Record Detail',
                                recordData: @js($record),
                                recordStatus: @js($record->monitor ? $record->monitor : ''),
                                recordContact: @js($record->contact ?? ''),
                            });
                        },
                    }">
                    <td class="w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 max-w-0 sm:w-auto sm:max-w-none">
                        <div class="flex items-center text-sm text-gray-800 dark:text-gray-200 ">
                            <div class="inline-block hs-tooltip [--placement:right]">
                                @if ($record->monitor)
                                    @if ($record->monitor->uptime_status === 'up')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                                            class="size-4 hs-tooltip-toggle text-emerald-400">
                                            <path fill-rule="evenodd"
                                                d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm3.844-8.791a.75.75 0 0 0-1.188-.918l-3.7 4.79-1.649-1.833a.75.75 0 1 0-1.114 1.004l2.25 2.5a.75.75 0 0 0 1.15-.043l4.25-5.5Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span
                                            class="absolute z-10 invisible inline-block px-2 py-1 text-xs font-medium text-white transition-opacity bg-gray-900 rounded-md opacity-0 hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible shadow-2xs dark:bg-neutral-700"
                                            role="tooltip">
                                            Up
                                        </span>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                                            class="text-red-400 size-4 hs-tooltip-toggle">
                                            <path fill-rule="evenodd"
                                                d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm2.78-4.22a.75.75 0 0 1-1.06 0L8 9.06l-1.72 1.72a.75.75 0 1 1-1.06-1.06L6.94 8 5.22 6.28a.75.75 0 0 1 1.06-1.06L8 6.94l1.72-1.72a.75.75 0 1 1 1.06 1.06L9.06 8l1.72 1.72a.75.75 0 0 1 0 1.06Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span
                                            class="absolute z-10 invisible inline-block max-w-xs px-2 py-1 text-xs font-medium text-white transition-opacity bg-gray-900 rounded-md opacity-0 hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible shadow-2xs dark:bg-neutral-700"
                                            role="tooltip">
                                            {{ $record->monitor->uptime_check_failure_reason }}
                                        </span>
                                    @endif
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                                        class="text-gray-300 size-4 hs-tooltip-toggle">
                                        <path fill-rule="evenodd"
                                            d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span
                                        class="absolute z-10 invisible inline-block px-2 py-1 text-xs font-medium text-white transition-opacity bg-gray-900 rounded-md opacity-0 hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible shadow-2xs dark:bg-neutral-700"
                                        role="tooltip">
                                        No Monitor
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center ml-3 cursor-pointer gap-x-2" x-data="{ showExternalLink: false }"
                                @mouseenter="showExternalLink=true" @mouseleave="showExternalLink=false">
                                <div @click="openDnsRecordModal">{{ $record->name }}</div>
                                <a href="{{ $record->type === 'A' ? 'https://' . $record->name . '.ponorogo.go.id' : '#' }}"
                                    target="blank_" class="" x-show="showExternalLink">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-external-link-icon lucide-external-link">
                                        <path d="M15 3h6v6" />
                                        <path d="M10 14 21 3" />
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                    </svg>
                                </a>
                            </div>

                        </div>
                        <dl class="font-normal lg:hidden">
                            <dt class="sr-only">Page Slug</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $record->content }}

                                    {{-- @foreach ($record->roles as $role)
                                        <div @click="openDnsRecordModal"
                                            class="inline-block px-2 py-1 truncate cursor-pointer text-xs rounded-full {{ $this->getRoleColor($loop->index) }}">
                                            {{ $role->name }}
                                        </div>
                                    @endforeach --}}
                                </span>
                            </dd>
                            <dt class="sr-only md:hidden">Proxied Status</dt>
                            <dd class="mt-1 text-gray-500 truncate md:hidden">
                                <span class="block text-xs text-gray-500">Proxied Status
                                    <div class="flex items-center gap-x-2">
                                        @if ($record->proxied)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="text-orange-400 lucide lucide-cloud-check-icon lucide-cloud-check">
                                                <path d="m17 15-5.5 5.5L9 18" />
                                                <path d="M5 17.743A7 7 0 1 1 15.71 10h1.79a4.5 4.5 0 0 1 1.5 8.742" />
                                            </svg>
                                            Proxied
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-cloud-alert-icon lucide-cloud-alert">
                                                <path d="M12 12v4" />
                                                <path d="M12 20h.01" />
                                                <path d="M17 18h.5a1 1 0 0 0 0-9h-1.79A7 7 0 1 0 7 17.708" />
                                            </svg>
                                            DNS Only
                                        @endif
                                    </div>
                                </span>
                            </dd>
                            <dt class="sr-only sm:hidden">Monitor Status</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Monitor Status
                                    <div class="flex items-center gap-x-2">
                                        @if ($record->monitor)
                                            <div
                                                class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-full text-xs font-medium {{ $record->monitor->uptime_check_enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800/30 dark:text-emerald-500' : 'bg-gray-100 text-gray-800 dark:bg-white/30 dark:text-white' }}">
                                                Uptime
                                            </div>
                                            <div
                                                class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-full text-xs font-medium {{ $record->monitor->certificate_check_enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800/30 dark:text-emerald-500' : 'bg-gray-100 text-gray-800 dark:bg-white/30 dark:text-white' }}">
                                                SSL
                                            </div>
                                        @else
                                            <div
                                                class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium border border-gray-200 bg-white text-gray-800 shadow-2xs dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                                No Monitor Service
                                            </div>
                                        @endif
                                    </div>
                                </span>
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        {{-- @foreach ($record->ip as $tag)
                            <span class="inline-block px-2 py-1 text-xs truncate rounded-full">
                            </span>
                            @endforeach --}}
                        {{ Illuminate\Support\Str::limit($record->content, 15) }}
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">
                        <div class="flex items-center gap-x-2">
                            @if ($record->proxied)
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-orange-400 lucide lucide-cloud-check-icon lucide-cloud-check">
                                    <path d="m17 15-5.5 5.5L9 18" />
                                    <path d="M5 17.743A7 7 0 1 1 15.71 10h1.79a4.5 4.5 0 0 1 1.5 8.742" />
                                </svg>
                                Proxied
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-cloud-alert-icon lucide-cloud-alert">
                                    <path d="M12 12v4" />
                                    <path d="M12 20h.01" />
                                    <path d="M17 18h.5a1 1 0 0 0 0-9h-1.79A7 7 0 1 0 7 17.708" />
                                </svg>
                                DNS Only
                            @endif
                        </div>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">
                        <div class="flex items-center gap-x-2">
                            @if ($record->monitor)
                                <div
                                    class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-full text-xs font-medium {{ $record->monitor->uptime_check_enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800/30 dark:text-emerald-500' : 'bg-gray-100 text-gray-800 dark:bg-white/30 dark:text-white' }}">
                                    Uptime
                                </div>
                                <div
                                    class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-full text-xs font-medium {{ $record->monitor->certificate_check_enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800/30 dark:text-emerald-500' : 'bg-gray-100 text-gray-800 dark:bg-white/30 dark:text-white' }}">
                                    SSL
                                </div>
                            @else
                                <div
                                    class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium border border-gray-200 bg-white text-gray-800 shadow-2xs dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                    No Monitor Service
                                </div>
                            @endif
                        </div>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-sm font-medium text-right ">
                        <div class="hs-dropdown relative inline-block [--placement:bottom|left]">
                            <button id="hs-table-dropdown-{{ $record->id }}" type="button"
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
                                aria-labelledby="hs-table-dropdown-{{ $record->id }}">
                                <div class="py-2 first:pt-0 last:pb-0">

                                    <button x-data
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-800 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                                        @click="openDnsRecordModal">
                                        Details
                                    </button>

                                </div>

                                {{-- <div class="py-2 first:pt-0 last:pb-0">
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
