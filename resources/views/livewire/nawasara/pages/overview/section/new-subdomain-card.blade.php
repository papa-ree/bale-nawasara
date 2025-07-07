<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Paparee\BaleNawasara\App\Models\DnsRecord;

new class extends Component {
    #[Computed]
    public function newSubdomains()
    {
        return DnsRecord::whereBetween('created_on', [now()->subDays(7), now()->addDays(1)])
            ->orderByDesc('created_on')
            ->get();
    }
}; ?>

<div>
    <div class="p-6 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Uptime Monitor</h2>
            <a href="/dns" wire:navigate:hover
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
                        <th class="px-4 py-3">Subdomain</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Comment</th>
                        <th class="px-4 py-3">Created On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->newSubdomains as $subdomain)
                        <tr
                            class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $subdomain->name }}
                            </td>
                            <td class="px-4 py-3">{{ $subdomain->type }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">
                                    {{ $subdomain->comment }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $subdomain->created_on }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
