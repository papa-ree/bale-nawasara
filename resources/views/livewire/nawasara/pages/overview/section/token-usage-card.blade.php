<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Paparee\BaleNawasara\App\Models\NawasaraAccessToken;

new class extends Component {
    #[Computed]
    public function tokenUsage()
    {
        return NawasaraAccessToken::withSum('dailyHits', 'hit_count')
            ->whereNotIn('name', ['dev'])
            ->orderByDesc('daily_hits_sum_hit_count')
            ->take(3)
            ->get();
    }
}; ?>

<div class="p-6 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Token Usage</h2>
        <a href="/email" wire:navigate:hover
            class="flex items-center px-3 py-1 text-sm rounded-lg text-primary-500 dark:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="ml-1 lucide lucide-chevron-right">
                <path d="m9 18 6-6-6-6" />
            </svg>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3">Token Name</th>
                    <th class="px-4 py-3">Today Hit</th>
                    <th class="px-4 py-3">Total Hit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->tokenUsage as $token)
                    <tr
                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            {{ $token->name }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $token->todayHits() }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $token->totalHits() }}
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>
