<?php
use Paparee\BaleNawasara\App\Models\HelpdeskForm;

use Livewire\WithoutUrlPagination;
use function Livewire\Volt\{computed, usesPagination, state, uses, updating, hydrate, on};

uses([WithoutUrlPagination::class]);

usesPagination();

state(['query']);

updating([
    'query' => fn() => $this->resetPage(),
]);

hydrate(fn() => $this->dispatch('paginated'));

$availableTickets = computed(function () {
    $searchTerm = htmlspecialchars($this->query, ENT_QUOTES, 'UTF-8');
    //$hash = hash('sha256', $searchTerm);
    return HelpdeskForm::where('ticket_number', 'like', '%' . $searchTerm . '%')
        ->orWhere('name', 'like', '%' . $searchTerm . '%')
        ->orWhere('nip', 'like', '%' . $searchTerm . '%')
        ->orWhere('phone', 'like', '%' . $searchTerm . '%')
        ->orWhere('description', 'like', '%' . $searchTerm . '%')
        ->orWhere('pic', 'like', '%' . $searchTerm . '%')
        ->orWhere('status', 'like', '%' . $searchTerm . '%')
        ->orderByDesc('ticket_number')
        ->paginate(100);
});

on([
    'refresh-ticket-list' => function () {
        return $this->availableTickets;
    },
]);

?>

<div>
    <x-bale.table :links="$this->availableTickets" header>

        <x-slot name="thead">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Ticket') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('name') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('description') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('pic') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Status') }}s
                        </span>
                    </div>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4">
                    <span class="sr-only">Edit</span>
                </th>
            </tr>
        </x-slot>

        <x-slot name="tbody">

            @foreach ($this->availableTickets as $ticket)
                <tr wire:key='record-{{ $ticket->id }}' class="hover:bg-gray-50 dark:hover:bg-slate-700/50" x-data="{
                            openTicketDetailModal() {
                                        $wire.dispatch('openBaleModal', { id: 'helpdeskDetailModal' });
                                        $wire.dispatch('setData', { ticketId: @js($ticket->id)});
                                        this.$dispatch('ticket-data', {
                                        modalTitle: 'Ticket Detail',
                                        ticketData: @js($ticket),
                            });
                                    }
                                }">
                    <td class="w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 cursor-pointer max-w-0 sm:w-auto sm:max-w-none"
                        @click="openTicketDetailModal">
                        <div class="flex items-center text-sm text-gray-800 dark:text-gray-200 ">
                            {{ $ticket->ticket_number }}
                        </div>

                        <dl class="font-normal lg:hidden">
                            <dt class="sr-only">Job</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $ticket->name }}
                                </span>
                            </dd>

                            <dt class="sr-only lg:hidden">Office</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Office
                                    {{ Illuminate\Support\Str::limit($ticket->description, 5) }}</span>
                            </dd>

                            <dt class="sr-only md:hidden">Office</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Office
                                    {{ $ticket->pic }}</span>
                            </dd>
                            <dt class="sr-only md:hidden">Office</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                <span class="block text-xs text-gray-500">Office
                                    {{ $ticket->status }}</span>
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        {{ $ticket->name }}
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        <span
                            class="block text-sm text-gray-500">{{ Illuminate\Support\Str::words($ticket->description, 5) }}</span>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">
                        <span class="block text-sm text-gray-500">{{ $ticket->pic }}</span>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        <span class="block text-sm text-gray-500">{{ $ticket->status }}</span>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-sm font-medium text-right ">
                        <div class="hs-dropdown relative inline-block [--placement:bottom|left]">
                            <button id="hs-table-dropdown-{{ $ticket->id }}" type="button"
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
                                aria-labelledby="hs-table-dropdown-{{ $ticket->id }}">
                                {{-- <div class="py-2 first:pt-0 last:pb-0">

                                    <a href="{{ route('contacts.edit', $ticket->id) }}" wire:navigate.hover
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-800 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300">
                                        Edit
                                    </a>

                                </div> --}}

                                {{-- <div class="py-2 first:pt-0 last:pb-0">
                                    <button @click="openContactDeleteModal"
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

    {{-- <x-bale.modal modalId="dnsRecordModal" size="xl">
        <livewire:nawasara.pages.dns.modal.record-detail-modal />
    </x-bale.modal> --}}
</div>