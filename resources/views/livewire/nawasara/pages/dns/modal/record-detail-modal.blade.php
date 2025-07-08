<?php

use Livewire\Volt\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Paparee\BaleNawasara\App\Models\NawasaraMonitor;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;

new class extends Component {
    public $uptime_check_enabled;
    public $certificate_check_enabled;

    #[Locked]
    public $monitorId;

    public $monitor;

    #[On('getMonitorSslStatus')]
    public function getMonitorSslStatus($monitorId)
    {
        // dump($id);
        $this->monitor = NawasaraMonitor::find($monitorId);

        if ($this->monitor) {
            $this->uptime_check_enabled = $this->monitor->uptime_check_enabled;
            $this->certificate_check_enabled = $this->monitor->certificate_check_enabled;
        }
    }

    public function update(LivewireAlert $alert, $record_id)
    {
        // dump($this, $record_id);
        DB::beginTransaction();

        try {
            $this->dispatch('disabling-button', params: true);

            $nawasara = NawasaraMonitor::whereDnsRecordId($record_id)->first();

            if (isset($this->uptime_check_enabled)) {
                $nawasara->update([
                    'uptime_check_enabled' => $this->uptime_check_enabled,
                ]);
            }

            if (isset($this->certificate_check_enabled)) {
                $nawasara->update([
                    'certificate_check_enabled' => $this->certificate_check_enabled,
                ]);
            }

            DB::commit();

            $this->dispatch('closeBaleModal', id: 'dnsRecordModal');

            $alert->title('Update Success!')->position('top-end')->success()->toast()->show();

            $this->dispatch('disabling-button', params: false);

            // session()->flash('saved', [
            //     'title' => 'Update Success!',
            // ]);

            // $this->redirect('dns', navigate: true);
        } catch (\Throwable $th) {
            $this->dispatch('disabling-button', params: false);

            DB::rollBack();
            info($th->getMessage());
            $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
        }
    }
};
?>

<div x-data="{
    init() {
            this.resetState();
        },
        resetState() {
            this.modalTitle = 'Record Details';
            this.recordId = '';
            this.recordName = '';
            this.recordType = '';
            this.recordContent = '';
            this.recordProxied = '';
            this.monitorId = '';
            this.monitorUptimeStatus = '';
            this.monitorUptimeFailedReason = '';
            this.monitorUptimeCheckEnabled = null;
            this.monitorUptimeLastCheck = null;
            this.monitorSslStatus = '';
            this.monitorSslFailedReason = '';
            this.monitorSslCheckEnabled = null;
            this.monitorSslExpirationDate = null;
            this.monitorSslCheckFailureReason = null;
            this.contactId = null;
            this.contactName = null;
            this.contactPhone = null;
            this.contactRecoveryEmail = null;
        },
        handlePermissionData(detail) {
            this.modalTitle = detail.modalTitle;
            this.recordId = detail.recordData.id;
            this.recordName = detail.recordData.name;
            this.recordType = detail.recordData.type;
            this.recordContent = detail.recordData.content;
            this.recordProxied = detail.recordData.proxied;
            this.monitorId = detail.recordStatus.id;
            this.monitorUptimeStatus = detail.recordStatus.uptime_status;
            this.monitorUptimeFailedReason = detail.recordStatus.uptime_check_failure_reason;
            this.monitorUptimeCheckEnabled = detail.recordStatus.uptime_check_enabled;
            this.monitorUptimeLastCheck = detail.recordStatus.uptime_last_check_date;
            this.monitorSslStatus = detail.recordStatus.certificate_status;
            this.monitorSslFailedReason = detail.recordStatus.certificate_check_failure_reason;
            this.monitorSslCheckEnabled = detail.recordStatus.certificate_check_enabled;
            this.monitorSslExpirationDate = detail.recordStatus.certificate_expiration_date;
            this.monitorSslCheckFailureReason = detail.recordStatus.certificate_check_failure_reason;
            this.contactId = detail.recordContact.pic_contact_id;
            this.contactName = detail.recordContact.contact_name;
            this.contactPhone = detail.recordContact.contact_phone;
            this.contactRecoveryEmail = detail.recordContact.recovery_email_address;
        },
}" x-init="init()" @record-data.window="handlePermissionData($event.detail)"
    @modal-reset.window="init()">

    <form>

        <div class="flex flex-col gap-8 lg:flex-row">
            {{-- <!-- Left Column - DNS Record Info --> --}}
            <div class="flex-1">
                <div class="mb-8 sm:mb-12 sm:pr-10">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
                        DNS Record
                        Information</h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Record Type</p>
                            <p class="font-semibold text-gray-800 dark:text-white">A</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="recordName"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="recordName + '.ponorogo.go.id'">
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Content</p>
                            <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="recordContent"></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Proxied</p>
                            <p class="mt-1 font-semibold text-gray-800 dark:text-white"
                                x-text="recordProxied ? 'Yes' : 'No'">
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sm:pr-10" x-show="monitorId">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
                        Monitoring
                        Status</h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Uptime</p>
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full"
                                    :class="monitorUptimeStatus == 'down' ? 'bg-red-400' : 'bg-emerald-500'"></span>
                                <p class="font-semibold" x-text="monitorUptimeStatus"
                                    :class="monitorUptimeStatus == 'down' ? 'text-red-400' : 'text-emerald-500'">
                                </p>
                            </div>
                            <p class="text-xs" x-show="monitorUptimeStatus == 'up'">
                                {{ __('Last Check At ') }} <span x-text="monitorUptimeLastCheck"></span>
                            </p>
                            <div class="p-3 text-sm border-l-4 border-red-300 rounded-tr-xl rounded-br-xl bg-red-50"
                                x-show="monitorUptimeStatus == 'down'">
                                <p class="text-red-500 dark:text-red-400" x-text="monitorUptimeFailedReason"></p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">SSL</p>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-block w-3 h-3 rounded-full"
                                    :class="monitorSslStatus == 'invalid' ?
                                        'bg-red-400' : 'bg-emerald-500'"></span>
                                <p class="font-semibold" x-text="monitorSslStatus"
                                    :class="monitorSslStatus == 'invalid' ?
                                        'text-red-400' : 'text-emerald-500'">
                                </p>
                            </div>
                            <p class="text-xs" x-show="monitorSslStatus == 'valid'">
                                {{ __('Valid until ') }} <span x-text="monitorSslExpirationDate"></span>
                            </p>
                            <div class="p-3 text-sm border-l-4 border-red-300 rounded-tr-xl rounded-br-xl bg-red-50"
                                x-show="monitorSslStatus == 'invalid'">
                                <p class="text-red-500 dark:text-red-400" x-text="monitorSslCheckFailureReason"></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- <!-- Right Column - Status and Contact --> --}}
            <div class="flex-1" x-show="monitorId">

                {{-- PIC contact --}}
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
                        PIC Contact
                    </h3>

                    {{-- contact detail --}}
                    <div class="space-y-4" x-show="contactName">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="contactName"></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="contactPhone"></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Recovery Email</p>
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="contactRecoveryEmail"></p>
                        </div>

                    </div>

                    {{-- assign button --}}
                    <div class="" x-show="!contactName">
                        <x-bale.secondary-button label="assign pic" class="justify-center w-full text-center" />
                    </div>
                </div>

                {{-- form --}}
                <div class="block">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
                        Monitoring Setting
                    </h3>

                    <div class="space-y-4 animate-pulse" wire:loading>
                        <!-- Skeleton uptime -->
                        <div class="flex items-center justify-between">
                            <div class="space-y-2">
                                <div class="w-40 h-4 bg-gray-300 rounded dark:bg-neutral-700"></div>
                                <div class="h-3 bg-gray-200 rounded w-60 dark:bg-neutral-600"></div>
                            </div>
                            {{-- <div class="w-5 h-5 mt-1 bg-gray-300 rounded-full dark:bg-neutral-700"></div> --}}
                        </div>

                        <!-- Skeleton ssl -->
                        <div class="flex items-center justify-between">
                            <div class="space-y-2">
                                <div class="w-32 h-4 bg-gray-300 rounded dark:bg-neutral-700"></div>
                                <div class="h-3 bg-gray-200 rounded w-52 dark:bg-neutral-600"></div>
                            </div>
                            {{-- <div class="w-5 h-5 mt-1 bg-gray-300 rounded-full dark:bg-neutral-700"></div> --}}
                        </div>
                    </div>

                    <div class="space-y-4" wire:loading.remove>
                        {{-- uptime --}}
                        <div class="flex items-center justify-between">
                            <label for="uptime-monitor" class="">
                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-300">Uptime
                                    Monitor</span>
                                <span id="uptime-monitor-description"
                                    class="block text-sm text-gray-600 dark:text-neutral-500">
                                    Enable uptime monitoring
                                </span>
                                {{-- {{ $uptime_check_enabled }} --}}
                            </label>
                            <div class="flex items-center h-5 mt-1">
                                <input id="uptime-monitor" name="uptime-monitor" type="checkbox"
                                    wire:model='uptime_check_enabled'
                                    class="w-5 h-5 transition duration-200 border-gray-400 rounded-full text-emerald-400 focus:ring-emerald-500 checked:border-emerald-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-emerald-500 dark:checked:border-emerald-500 dark:focus:ring-offset-gray-800"
                                    aria-describedby="uptime-monitor-description">
                            </div>
                        </div>

                        {{-- ssl --}}
                        <div class="flex items-center justify-between">
                            <label for="ssl-check" class="">
                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-300">
                                    SSL Check
                                </span>
                                <span id="ssl-check-description"
                                    class="block text-sm text-gray-600 dark:text-neutral-500">
                                    Enable SSL certificate validation
                                </span>
                                {{-- {{ $certificate_check_enabled }} --}}
                            </label>
                            <div class="flex items-center h-5 mt-1">
                                <input id="ssl-check" name="ssl-check" type="checkbox"
                                    wire:model='certificate_check_enabled'
                                    class="w-5 h-5 transition duration-200 border-gray-400 rounded-full text-emerald-400 focus:ring-emerald-500 checked:border-emerald-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-emerald-500 dark:checked:border-emerald-500 dark:focus:ring-offset-gray-800"
                                    aria-describedby="ssl-check-description">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-bale.modal-action class="mt-6">
            <x-bale.button label="Save Change" type="button" class="ml-3"
                @click='$wire.update(recordId), useSpinner()' />
            <x-bale.secondary-button label="Cancel" type="button" class=""
                wire:click="$dispatch('closeBaleModal', { id: 'dnsRecordModal' })" />
        </x-bale.modal-action>
    </form>

</div>
