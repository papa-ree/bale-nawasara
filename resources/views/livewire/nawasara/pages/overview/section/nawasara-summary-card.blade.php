<?php

use function Livewire\Volt\{computed, placeholder, state};

$summary = computed(function () {
    return cache()->get('nawasara_summary') ?? [];
});

state(['monitored_subdomains' => fn() => $this->summary['monitored_subdomains'] ?? 'N/A']);
state(['new_monitored_subdomains' => fn() => $this->summary['new_monitored_subdomains'] ?? 'N/A']);
state(['disabled_monitor' => fn() => $this->summary['disabled_monitor'] ?? 'N/A']);
state(['valid_ssl' => fn() => $this->summary['valid_ssl'] ?? 'N/A']);
state(['ssl_expiring' => fn() => $this->summary['ssl_expiring'] ?? 'N/A']);
state(['pic_contacts' => fn() => $this->summary['pic_contacts'] ?? 'N/A']);
state(['new_pic_contacts' => fn() => $this->summary['new_pic_contacts'] ?? 'N/A']);
state(['whatsapp_sent_today' => fn() => $this->summary['whatsapp_sent_today'] ?? 'N/A']);
state(['dns_records' => fn() => $this->summary['dns_records'] ?? 'N/A']);
state(['last_sync_dns_record' => fn() => $this->summary['last_sync_dns_record'] ?? 'N/A']);
state(['uptime_monitor' => fn() => $this->summary['uptime_monitor'] ?? 'N/A']);
?>

<div>
    <main class="flex-grow">
        {{-- <!-- Overview Section --> --}}
        <div class="mb-8">
            <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Nawasara Overview</h1>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-6">
                {{-- <!-- Monitored Subdomains Card --> --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-blue-500 lucide lucide-globe dark:text-blue-400">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="2" y1="12" x2="22" y2="12" />
                                <path
                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Active</span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">Monitored Subdomains
                    </h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $monitored_subdomains }}
                    </p>
                    <div class="flex items-center mt-4 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-arrow-up-right">
                            <path d="M7 7h10v10" />
                            <path d="M7 17 17 7" />
                        </svg>
                        <span
                            class="ml-1 text-green-600 dark:text-green-400">{{ $new_monitored_subdomains > 0 ? $new_monitored_subdomains . ' added recently' : 'no new monitored' }}</span>
                    </div>
                </div>

                {{-- <!-- Valid SSL Card --> --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="text-green-500 lucide lucide-shield-check dark:text-green-400">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $ssl_expiring > 0 ? 'text-yellow-800 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400' : 'text-emerald-800 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400' }}">{{ $ssl_expiring }}
                            Expiring</span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">Valid SSL
                        Certificates</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $valid_ssl }}</p>
                    <div class="flex items-center mt-4 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-alert-circle">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <span
                            class="ml-1 {{ $ssl_expiring > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $ssl_expiring }}
                            expiring in 30 days</span>
                    </div>
                </div>

                {{-- <!-- PIC Contacts Card --> --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="text-purple-500 lucide lucide-users dark:text-purple-400">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Updated</span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">PIC Contacts</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $pic_contacts }}</p>
                    <div class="flex items-center mt-4 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-user-plus">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <line x1="19" y1="8" x2="19" y2="14" />
                            <line x1="22" y1="11" x2="16" y2="11" />
                        </svg>
                        <span class="ml-1 text-primary-600 dark:text-primary-400">{{ $new_pic_contacts }} new
                            contacts</span>
                    </div>
                </div>

                {{-- <!-- WhatsApp Messages Card --> --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="text-green-500 lucide lucide-message-circle dark:text-green-400">
                                <path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900/30 dark:text-blue-400">Live</span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">WhatsApp Sent Today
                    </h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $whatsapp_sent_today }}</p>
                    <div class="flex items-center mt-4 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-trending-up">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                            <polyline points="16 7 22 7 22 13" />
                        </svg>
                        {{-- <span class="ml-1 text-green-600 dark:text-green-400">24% increase</span> --}}
                    </div>
                </div>

                {{-- <!-- DNS Records Card --> --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="text-blue-500 lucide lucide-refresh-ccw dark:text-blue-400">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                                <path d="M16 16h5v5" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Synced</span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">DNS Records Synced
                    </h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $dns_records }}</p>
                    <div class="flex items-center mt-4 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-clock">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span class="ml-1 text-gray-500 dark:text-gray-400">Last sync:
                            {{ $last_sync_dns_record }}</span>
                    </div>
                </div>

                {{-- Uptime Monitor --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-activity-icon lucide-activity dark:text-emerald-400 text-emerald-500">
                                <path
                                    d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs rounded-full text-emerald-800 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400">
                            {{ number_format(($uptime_monitor['up'] / $monitored_subdomains) * 100) }}%
                            Up
                        </span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">Uptime Status</h3>
                    <p class="text-2xl font-semibold">
                        <span class="text-emerald-400 dark:text-emerald-300">{{ $uptime_monitor['up'] }} up</span>
                        /
                        <span class="text-red-400 dark:text-red-300">{{ $uptime_monitor['down'] }} down</span>
                    </p>
                    <div class="w-full h-2 mt-4 bg-red-400 rounded-full dark:bg-red-700">
                        <div class="h-2 rounded-full bg-emerald-400"
                            style="width: {{ ($uptime_monitor['up'] / $monitored_subdomains) * 100 }}%"></div>
                    </div>
                </div>

                {{-- disabled monitor --}}
                <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-gray-100 dark:bg-gray-900/30 rounded-xl">
                            {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="text-gray-500 lucide lucide-monitor-off dark:text-gray-400">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                                <path d="M16 16h5v5" />
                            </svg> --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="text-gray-500 lucide dark:text-gray-400 lucide-monitor-off-icon lucide-monitor-off">
                                <path d="M17 17H4a2 2 0 0 1-2-2V5c0-1.5 1-2 1-2" />
                                <path d="M22 15V5a2 2 0 0 0-2-2H9" />
                                <path d="M8 21h8" />
                                <path d="M12 17v4" />
                                <path d="m2 2 20 20" />
                            </svg>
                        </div>
                        <span
                            class="px-2 py-1 text-xs text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900/30 dark:text-gray-400">Disabled</span>
                    </div>
                    <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">Disabled Uptime Monitor
                    </h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $disabled_monitor }}</p>
                    {{-- <div class="flex items-center mt-4 text-xs"> --}}
                    {{-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-clock">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg> --}}
                    {{-- <span class="ml-1 text-gray-500 dark:text-gray-400">Last sync:
                            {{ $last_sync_dns_record }}</span> --}}
                    {{-- </div> --}}
                </div>

                {{-- <!-- MikroTik IP Card -->
                    <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-indigo-500 lucide lucide-map-pin dark:text-indigo-400">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </div>
                            <span
                                class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Active</span>
                        </div>
                        <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">MikroTik IP Mapped
                        </h3>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">12</p>
                        <div class="flex items-center mt-4 text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-crosshair">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="22" y1="12" x2="18" y2="12" />
                                <line x1="6" y1="12" x2="2" y2="12" />
                                <line x1="12" y1="6" x2="12" y2="2" />
                                <line x1="12" y1="22" x2="12" y2="18" />
                            </svg>
                            <span class="ml-1 text-gray-500 dark:text-gray-400">All online</span>
                        </div>
                    </div>

                    <!-- Email Accounts Card -->
                    <div class="p-5 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-red-500 lucide lucide-mail dark:text-red-400">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                            </div>
                            <span
                                class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Stable</span>
                        </div>
                        <h3 class="mt-4 mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">WHM Email Accounts
                        </h3>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">86</p>
                        <div class="flex items-center mt-4 text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-inbox">
                                <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                                <path
                                    d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                            </svg>
                            <span class="ml-1 text-gray-500 dark:text-gray-400">15 unread messages</span>
                        </div>
                    </div> --}}

            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
            <!-- Uptime Monitor Table -->
            <livewire:nawasara.pages.overview.section.new-subdomain-card />

            {{-- <!-- Token Usage Table -->
            <div class="p-6 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Token Usage</h2>
                    <button
                        class="flex items-center px-3 py-1 text-sm rounded-lg text-primary-500 dark:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                        View All
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="ml-1 lucide lucide-chevron-right">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="text-xs text-gray-700 uppercase dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3">Token</th>
                                <th class="px-4 py-3">Usage Today</th>
                                <th class="px-4 py-3">Limit</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">aBcD1234eFgH</td>
                                <td class="px-4 py-3">42</td>
                                <td class="px-4 py-3">100</td>
                                <td class="px-4 py-3"><span
                                        class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">OK</span>
                                </td>
                            </tr>
                            <tr
                                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">iJkL5678mNoP</td>
                                <td class="px-4 py-3">87</td>
                                <td class="px-4 py-3">100</td>
                                <td class="px-4 py-3"><span
                                        class="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Nearing
                                        limit</span></td>
                            </tr>
                            <tr
                                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">qRsT9012uVwX</td>
                                <td class="px-4 py-3">98</td>
                                <td class="px-4 py-3">100</td>
                                <td class="px-4 py-3"><span
                                        class="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Nearing
                                        limit</span></td>
                            </tr>
                            <tr
                                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">yZaB3456cDeF</td>
                                <td class="px-4 py-3">12</td>
                                <td class="px-4 py-3">100</td>
                                <td class="px-4 py-3"><span
                                        class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">OK</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">gHiJ7890kLmN</td>
                                <td class="px-4 py-3">100</td>
                                <td class="px-4 py-3">100</td>
                                <td class="px-4 py-3"><span
                                        class="px-2 py-1 text-xs text-red-800 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">Limit
                                        reached</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </div>

        <!-- Table Section -->

        <!-- Side Panel with Charts and Recent Activity -->
        {{-- <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <!-- Activity Logs -->
            <div class="grid grid-cols-1 gap-6 xl:col-span-2 md:grid-cols-2">
                <!-- Resource Chart -->
                <div class="p-6 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Resource Usage</h2>
                    <div class="flex items-end justify-between h-64 px-6 py-4 chart-pattern">
                        <div class="flex flex-col items-center">
                            <div class="w-10 bg-primary-500 rounded-t-md" style="height: 35%;"></div>
                            <span class="mt-2 text-xs text-gray-500 dark:text-gray-400">CPU</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 bg-blue-500 rounded-t-md" style="height: 55%;"></div>
                            <span class="mt-2 text-xs text-gray-500 dark:text-gray-400">Memory</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 bg-green-500 rounded-t-md" style="height: 42%;"></div>
                            <span class="mt-2 text-xs text-gray-500 dark:text-gray-400">Network</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 bg-purple-500 rounded-t-md" style="height: 25%;"></div>
                            <span class="mt-2 text-xs text-gray-500 dark:text-gray-400">Disk</span>
                        </div>
                    </div>
                    <div class="flex justify-between mt-4 text-sm">
                        <span class="font-medium text-primary-500">35% CPU</span>
                        <span class="font-medium text-blue-500">55% Memory</span>
                        <span class="font-medium text-green-500">42% Network</span>
                        <span class="font-medium text-purple-500">25% Disk</span>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="p-6 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recent Activity</h2>
                        <button
                            class="px-3 py-1 text-sm rounded-lg text-primary-500 dark:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700">View
                            All</button>
                    </div>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="p-2 mr-3 bg-blue-100 rounded-full dark:bg-blue-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-blue-500 lucide lucide-refresh-cw dark:text-blue-400">
                                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                                    <path d="M21 3v5h-5" />
                                    <path d="M3 12a9 9 0 0 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                    <path d="M8 16H3v5" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">DNS records updated</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Updated 24 DNS records for
                                    monitoring</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">10 minutes ago</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="p-2 mr-3 bg-green-100 rounded-full dark:bg-green-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-green-500 lucide lucide-message-circle dark:text-green-400">
                                    <path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">WhatsApp notifications</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sent notifications to 12 PIC
                                    contacts</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">1 hour ago</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="p-2 mr-3 bg-yellow-100 rounded-full dark:bg-yellow-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-yellow-500 lucide lucide-shield-alert dark:text-yellow-400">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    <path d="M12 8v4" />
                                    <path d="M12 16h.01" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">SSL Warning</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">6 certificates expiring in 30
                                    days</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">2 hours ago</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="p-2 mr-3 bg-purple-100 rounded-full dark:bg-purple-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="text-purple-500 lucide lucide-user-plus dark:text-purple-400">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <line x1="19" y1="8" x2="19" y2="14" />
                                    <line x1="22" y1="11" x2="16" y2="11" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">New contacts</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">2 PIC contacts added to system
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">4 hours ago</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Message Stats -->
            <div class="p-6 transition-all bg-white shadow-md dark:bg-gray-800 rounded-2xl hover:shadow-lg">
                <h2 class="mb-6 text-xl font-bold text-gray-900 dark:text-white">Message Statistics</h2>
                <div class="flex items-center justify-center my-8">
                    <div class="relative w-48 h-48">
                        <!-- Message Type Chart -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div
                                class="flex items-center justify-center w-40 h-40 rounded-full bg-blue-500/10 dark:bg-blue-900/20">
                                <div
                                    class="flex items-center justify-center w-32 h-32 rounded-full bg-blue-500/20 dark:bg-blue-900/30">
                                    <div
                                        class="flex items-center justify-center w-24 h-24 rounded-full bg-blue-500/30 dark:bg-blue-900/40">
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-gray-900 dark:text-white">84%</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Success</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <svg class="w-full h-full" viewBox="0 0 100 100">
                            <!-- Notification Segment -->
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#0ea5e9"
                                stroke-width="7" stroke-dasharray="60 100" stroke-dashoffset="0"
                                transform="rotate(-90 50 50)" />

                            <!-- Alerts Segment -->
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#8b5cf6"
                                stroke-width="7" stroke-dasharray="20 100" stroke-dashoffset="-60"
                                transform="rotate(-90 50 50)" />

                            <!-- Reminders Segment -->
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#10b981"
                                stroke-width="7" stroke-dasharray="15 100" stroke-dashoffset="-80"
                                transform="rotate(-90 50 50)" />

                            <!-- Others Segment -->
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#ef4444"
                                stroke-width="7" stroke-dasharray="5 100" stroke-dashoffset="-95"
                                transform="rotate(-90 50 50)" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 mr-2 bg-blue-500 rounded-full"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Notifications</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">128</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 mr-2 bg-purple-500 rounded-full"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Alerts</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">42</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 mr-2 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Reminders</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">32</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 mr-2 bg-red-500 rounded-full"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Others</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">8</span>
                    </div>
                </div>
            </div>
        </div> --}}
    </main>
</div>
