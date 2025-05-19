<?php

use Livewire\WithoutUrlPagination;
use function Livewire\Volt\{computed, usesPagination, state, uses, updating, hydrate, on};
use Paparee\BaleNawasara\App\Models\NawasaraAccessToken;

uses([WithoutUrlPagination::class]);

usesPagination();

state(['query']);

updating([
    'query' => fn() => $this->resetPage(),
]);

hydrate(fn() => $this->dispatch('paginated'));

$availableTokens = computed(function () {
    $searchTerm = htmlspecialchars($this->query, ENT_QUOTES, 'UTF-8');

    return NawasaraAccessToken::where('name', 'like', '%' . $searchTerm . '%')
        // ->whereNotIn('name', ['dev'])
        // ->where('name', 'like', '%' . $searchTerm . '%')
        ->orderBy('name')
        ->paginate(100);
});

on([
    'refresh-token-list' => function () {
        return $this->availableTokens;
    },
]);

?>

<div>
    {{-- @dump($this->availableTokens) --}}
    <x-bale.table :links="$this->availableTokens" header>

        <x-slot name="thead">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Token Name') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('User') }}
                        </span>
                    </div>
                </th>
                <th scope="col"
                    class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('API Key') }}
                        </span>
                    </div>
                </th>
                <th scope="col"
                    class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                    <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                            {{ __('Last Used') }}
                        </span>
                    </div>
                </th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4">
                    <span class="sr-only">Edit</span>
                </th>
            </tr>
        </x-slot>

        <x-slot name="tbody">
            @foreach ($this->availableTokens as $token)
                {{-- @dump($token->tokens) --}}
                <tr wire:key='token-{{ $token->id }}' class="hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    x-data="{
                        token: @js($token->token),
                        openTokenModal() {
                            $wire.dispatch('openBaleModal', { id: 'tokenModal' });
                            $wire.dispatch('open-token-modal', { tokenId: @js($token->id) });
                            // this.$dispatch('token-data', {
                            //     userId: '{{ $token->uuid }}',
                            //     userName: '{{ $token->name }}',
                            // });
                        }
                    }">
                    <td class="w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 max-w-0 sm:w-auto sm:max-w-none">
                        <a href="" wire:navigate.hover
                            class="block text-sm text-gray-800 cursor-pointer dark:text-gray-200">
                            {{ $token->name }}
                        </a>
                        <dl class="font-normal lg:hidden">
                            <dt class="sr-only">Page Slug</dt>
                            <dd class="mt-1 text-gray-700 truncate">
                                <span class="block text-xs text-gray-500 dark:text-gray-200">
                                    <div
                                        class="inline-block px-2 py-1 text-xs truncate bg-gray-100 rounded-full cursor-pointer">
                                        {{ $token->tokenable->name }}
                                    </div>
                                </span>
                            </dd>
                            <dt class="sr-only sm:hidden">Last Used At</dt>
                            <dd class="mt-1 text-gray-500 truncate sm:hidden">
                                @if ($token->last_used_at)
                                    <span class="block text-xs text-gray-500">Last Used At
                                        {{ $token->last_used_at->diffForHumans() }}
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">
                        <div class="block text-sm text-gray-500">{{ $token->tokenable->name }}</div>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">
                        <div class="block text-sm text-gray-500">
                            {{-- <input type="text" x-model="token" /> --}}
                            <button type="button" @click="openTokenModal"
                                class="inline-flex items-center px-3 py-2 text-xs text-gray-800 bg-white border border-gray-200 rounded-lg gap-x-2 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                Show Key
                                {{-- <svg class="text-gray-400 shrink-0 size-4 dark:text-neutral-600"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                                    <path
                                        d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                </svg> --}}
                            </button>
                        </div>
                    </td>

                    <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">
                        <span class="block text-sm text-gray-500">
                            @if ($token->last_used_at)
                                {{-- <div class="text-sm text-gray-400"> --}}
                                {{ __('Last used') }} {{ $token->last_used_at->diffForHumans() }}
                                {{-- </div> --}}
                            @endif
                        </span>
                    </td>

                    {{-- <td class="py-4 pl-3 pr-4 text-sm font-medium text-right ">
                        <div class="hs-dropdown relative inline-block [--placement:bottom|left]">
                            <button id="hs-table-dropdown-{{ $token->uuid }}" type="button"
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
                                aria-labelledby="hs-table-dropdown-{{ $token->uuid }}">
                                <div class="py-2 first:pt-0 last:pb-0">

                                    <a class="flex items-center w-full px-3 py-2 text-sm text-gray-800 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                                        href="{{ route('token-lists.edit', $token->uuid) }}" wire:navigate.hover>
                                        Edit
                                    </a>

                                </div>

                                <div class="py-2 first:pt-0 last:pb-0">
                                    <button @click="openUserDeleteModal"
                                        class="flex items-center w-full px-3 py-2 text-sm text-red-600 rounded-lg gap-x-3 hover:bg-gray-100 focus:ring-2 focus:ring-emerald-500 dark:text-red-500 dark:hover:bg-neutral-700">
                                        Delete
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
