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
    return HelpdeskForm::where(function ($query) use ($searchTerm) {
        $query->where('ticket_number', 'like', '%' . $searchTerm . '%')
            ->orWhere('name', 'like', '%' . $searchTerm . '%')
            ->orWhere('nip', 'like', '%' . $searchTerm . '%')
            ->orWhere('phone', 'like', '%' . $searchTerm . '%')
            ->orWhere('description', 'like', '%' . $searchTerm . '%')
            ->orWhere('pic', 'like', '%' . $searchTerm . '%');
    })
        ->whereStatus('open')
        ->orderBy('created_at')
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
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('pic') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('description') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 text-right">
                    <div class="text-right">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Status') }}
                        </span>
                    </div>
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
                            <dt class="sr-only">Name</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $ticket->name }}
                                </span>
                                <p class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $ticket->phone }}
                                </p>
                                <p class="block text-xs text-gray-500 dark:text-gray-200">
                                    {{ $ticket->nip }}
                                </p>
                            </dd>

                            <dt class="sr-only md:hidden">PIC</dt>
                            <dd class="mt-1 text-gray-500 truncate md:hidden">
                                <span class="block text-xs text-gray-500">
                                    {{ $ticket->pic }}
                                </span>
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        <p>{{ $ticket->name }}</p>
                        <p>{{ $ticket->nip }}</p>
                        <a href="https://wa.me/{{$ticket->phone}}">{{ $ticket->phone }}</a>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">
                        <span class="block text-sm text-gray-500">{{ $ticket->pic }}</span>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-sm font-medium">
                        <span
                            class="block text-sm text-gray-500">{{ Illuminate\Support\Str::words($ticket->description, 5) }}</span>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-sm font-medium text-right">
                        <span class="block text-sm text-gray-500">{{ $ticket->status }}</span>
                    </td>

                </tr>
            @endforeach
        </x-slot>
    </x-bale.table>

    {{-- <x-bale.modal modalId="dnsRecordModal" size="xl">
        <livewire:nawasara.pages.dns.modal.record-detail-modal />
    </x-bale.modal> --}}
</div>