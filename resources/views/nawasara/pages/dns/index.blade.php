<?php
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use function Livewire\Volt\{title, mount};
title('DNS Records');

mount(function () {
    if (session()->has('saved')) {
        LivewireAlert::title(session('saved.title'))->toast()->position('top-end')->success()->show();
    }
});
?>

<div>
    {{-- <div x-data="{
        syncing: false,
        startSync() {
            this.syncing = true;
            fetch('/dns-records/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => this.checkStatus());
        },
        checkStatus() {
            fetch('/dns-records/status')
                .then(res => res.json())
                .then(data => {
                    this.syncing = data.syncing;
                    if (this.syncing) {
                        setTimeout(() => this.checkStatus(), 3000);
                    }
                });
        }
    }" x-init="checkStatus()">
        <button @click="startSync" x-bind:disabled="syncing" class="btn btn-primary">
            <span x-show="!syncing">Sinkronisasi Sekarang</span>
            <span x-show="syncing">Sedang Sinkronisasi...</span>
        </button>
    </div> --}}

    <livewire:nawasara.pages.dns.section.dns-record-table />
</div>

{{-- script for direct api --}}
{{-- <div x-data="dnsTable" x-init="fetchRecords()" class="max-w-4xl p-6 mx-auto">
    <div class="flex items-center justify-between mb-4">
        <input type="text" x-model.debounce.500ms="search" @input="fetchRecords" class="w-full max-w-xs input"
            placeholder="Cari DNS..." />

        <select x-model="perPage" @change="fetchRecords" class="ml-4 select select-bordered">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
        </select>
    </div>

    <div class="overflow-x-auto bg-white border rounded-lg shadow">
        <table class="table w-full text-sm text-left">
            <thead class="text-gray-700 border-b bg-gray-50">
                <tr>
                    <th @click="sort('name')" class="px-4 py-3 cursor-pointer">
                        Name <span x-text="sortBy === 'name' ? (sortDirection === 'asc' ? '↑' : '↓') : ''"></span>
                    </th>
                    <th @click="sort('type')" class="px-4 py-3 cursor-pointer">
                        Type <span x-text="sortBy === 'type' ? (sortDirection === 'asc' ? '↑' : '↓') : ''"></span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-center">Memuat...</td>
                    </tr>
                </template>

                <template x-for="record in records">
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2" x-text="record.name"></td>
                        <td class="px-4 py-2" x-text="record.type"></td>
                    </tr>
                </template>

                <template x-if="!loading && records.length === 0">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-center">Tidak ada data</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4 text-sm">
        <div>
            Halaman <span x-text="page"></span> dari <span x-text="lastPage"></span>
        </div>
        <div class="space-x-2">
            <button @click="prevPage" :disabled="page <= 1" class="btn btn-sm btn-outline">Sebelumnya</button>
            <button @click="nextPage" :disabled="page >= lastPage" class="btn btn-sm btn-outline">Berikutnya</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dnsTable', () => ({
            search: '',
            sortBy: 'name',
            sortDirection: 'asc',
            perPage: 20,
            page: 1,
            lastPage: 1,
            loading: false,
            records: [],

            async fetchRecords() {
                this.loading = true;
                const res = await fetch(
                    `/dns-records?search=${this.search}&page=${this.page}&perPage=${this.perPage}&sortBy=${this.sortBy}&sortDirection=${this.sortDirection}`
                );
                const data = await res.json();

                console.log(data.data);

                this.records = data.data;
                this.page = data.page;
                this.lastPage = data.lastPage;
                this.loading = false;
            },

            sort(column) {
                if (this.sortBy === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = column;
                    this.sortDirection = 'asc';
                }
                this.fetchRecords();
            },

            nextPage() {
                if (this.page < this.lastPage) {
                    this.page++;
                    this.fetchRecords();
                }
            },

            prevPage() {
                if (this.page > 1) {
                    this.page--;
                    this.fetchRecords();
                }
            }
        }))
    });
</script> --}}
