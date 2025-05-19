<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div x-data="{
    init() {
            this.resetState();
        },
        resetState() {
            this.modalTitle = 'Record Details';
            this.recordName = '';
            this.recordType = '';
            this.recordContent = '';
            this.recordStatus = '';
            this.recordFailedReason = '';
        },
        handlePermissionData(detail) {
            this.modalTitle = detail.modalTitle;
            this.recordName = detail.recordData.name;
            this.recordType = detail.recordData.type;
            this.recordContent = detail.recordData.content;
            this.recordStatus = detail.recordStatus.uptime_status;
            this.recordFailedReason = detail.recordStatus.uptime_check_failure_reason;
        },
}" x-init="init()" @record-data.window="handlePermissionData($event.detail)"
    @modal-reset.window="init()">

    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
        <span x-text="modalTitle"></span>
    </h3>

    <div x-text="recordName"></div>
    <div x-text="recordType"></div>
    <div class="overflow-hidden text-pretty">
        <span x-text="recordContent"></span>
    </div>
    <div x-text="recordStatus" :class="recordStatus === 'down' ? 'text-red-500' : 'text-emerald-500'"></div>
    <div x-text="recordFailedReason"></div>

    {{-- <x-bale.modal-action class="mt-6">
        <x-bale.secondary-button label="Cancel" type="button" class="ml-3"
            wire:click="$dispatch('closeBaleModal', { id: 'dnsRecordModal' }); $wire.resetVal()" />
    </x-bale.modal-action> --}}

</div>
