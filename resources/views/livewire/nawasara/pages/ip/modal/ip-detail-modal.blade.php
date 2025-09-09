<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Locked, On};
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\DB;
use Paparee\BaleNawasara\App\Jobs\UpdateArpMikrotikBgpJob;
use Paparee\BaleNawasara\App\Models\KumaMonitor;
use Paparee\BaleNawasara\App\Models\IpPublic;
use Paparee\BaleNawasara\App\Services\MikrotikService;
use Paparee\BaleNawasara\App\Services\IpPublicService;
use Paparee\BaleNawasara\App\Services\KumaMonitorService;
use Paparee\BaleNawasara\App\Services\UptimeKumaService;

new class extends Component {

    #[Locked]
    public $id;

    public $comment;
    public $address;
    public $make_static;
    public $monitor;
    //public $uptime_status;

    #[On('setIpData')]
    public function setIpData($data)
    {
        // mikrotik id
        $this->id = $data['id'];
        $this->comment = $data['comment'];
        $this->address = $data['address'];
        $this->make_static = $data['dynamic'] == 'true' ? false : true;

        // monitor data
        $this->monitor = KumaMonitor::whereIpPublicId($this->id)->first();
    }

    public function update(LivewireAlert $alert)
    {
        DB::beginTransaction();

        try {
            $mikrotik = new MikrotikService();
            $mikrotik->makeStaticArp($this->id, $this->comment);

            // get data
            $ip_public = IpPublic::find($this->id);

            // update comment in ip_publics table
            $ip_public_service = new IpPublicService();
            $ip_public_service->updateComment($ip_public, $this->comment);

            if ($this->monitor) {
                $monitorService = new KumaMonitorService();
                $monitorService->updateComment($this->monitor, $this->comment);

                $uptimeKumaService = new UptimeKumaService();
                $uptimeKumaService->updateComment($this->monitor->kuma_id, $this->comment);
            }

            DB::commit();

            session()->flash('saved', [
                'title' => 'Update Success!',
            ]);

            $this->redirect('/network/ip-publics', navigate: true);
        } catch (\Throwable $th) {
            $this->dispatch('disabling-button', params: false);

            DB::rollBack();
            info($th->getMessage());
            $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
        }
    }

    public function updateIpPublicByAddress()
    {
        //dd($comment);
        try {
            $ip = IpPublic::where('address', $this->address)->firstOrFail();
            $ip->mikrotik_synced = false;
            $ip->comment = $this->comment;
            $ip->dynamic = $this->make_static ? 'false' : 'true';
            $ip->save();
            //dd($ip);

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function resetVal()
    {
        $this->reset();
        $this->resetValidation();
    }
};
?>

<div x-data="{
    ipId: '',
    ipAddress: '',
    ipDhcp: '',
    ipComment: '',
    ipIsDynamic: '',
    ipInterface: '',
    ipMac: '-',
    uptimeTimeout: '',
    uptimeInterval: '',
    uptimeRetryInterval: '',
    uptimeResendInterval: '',
    uptimeMaxRetry: '',
    uptimeStatus: '',
    uptimeCheckFailureReason: '',
    uptimeLastUpdate: '',
    uptimeKumaSynced: '',
    init() {
        this.resetState();
    },
    resetState() {
        this.ipId = '';
        this.ipAddress = '';
        this.ipDhcp = '';
        this.ipComment = '';
        this.ipIsDynamic = '';
        this.ipInterface = '';
        this.ipMac = '-';
        this.uptimeTimeout = '';
        this.uptimeInterval = '';
        this.uptimeRetryInterval = '';
        this.uptimeResendInterval = '';
        this.uptimeMaxRetry = '';
        this.uptimeStatus = '';
        this.uptimeCheckFailureReason = '';
        this.uptimeLastUpdate = '';
        this.uptimeKumaSynced = '';
    },
    handleIpAddressData(detail) {
        this.ipId = detail.ipAddressData.id;
        this.ipAddress = detail.ipAddressData.address;
        this.ipDhcp = detail.ipAddressData.dhcp;
        this.ipComment = detail.ipAddressData.comment;
        this.ipIsDynamic = detail.ipAddressData.dynamic;
        this.ipInterface = detail.ipAddressData.interface;
        this.ipMac = detail.ipAddressData.mac_addpress;
        this.uptimeTimeout = detail.monitorData.timeout;
        this.uptimeInterval = detail.monitorData.interval;
        this.uptimeRetryInterval = detail.monitorData.retry_interval;
        this.uptimeResendInterval = detail.monitorData.resend_interval;
        this.uptimeMaxRetry = detail.monitorData.max_retries;
        this.uptimeStatus = detail.monitorData.uptime_status;
        this.uptimeCheckFailureReason = detail.monitorData.uptime_check_failure_reason;
        this.uptimeLastUpdate = detail.monitorData.updated_at;
        this.uptimeKumaSynced = detail.monitorData.kuma_synced;
    },
}" x-init="init()" @ip-address-data.window="handleIpAddressData($event.detail)">

    <form wire:submit='update' class="mb-6">

        <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
            IP Address Information
        </h3>

        <div class="space-y-4">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">IP Address</p>
                <p class="font-semibold text-gray-800 dark:text-white" x-text="ipAddress"></p>
            </div>

            <div x-show="ipDhcp">
                <p class="text-sm text-gray-500 dark:text-gray-400">DHCP</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="ipDhcp"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Interface</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="ipInterface"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">MAC Address</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="ipMac"></p>
            </div>

            {{-- skeleton --}}
            <div class="space-y-4 animate-pulse" wire:loading>
                {{-- Make Static skeleteon --}}
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="w-16 h-4 bg-gray-300 rounded dark:bg-neutral-700"></div>
                        <div class="w-40 h-3 bg-gray-200 rounded dark:bg-neutral-600"></div>
                    </div>
                </div>

                {{-- Comment skeleton --}}
                <div class="flex items-center justify-between" x-show="ipIsDynamic='false'">
                    <div class="space-y-2">
                        <div class="w-16 h-3 bg-gray-200 rounded dark:bg-neutral-600"></div>
                        <div class="w-40 h-10 bg-gray-300 rounded sm:w-96 dark:bg-neutral-700"></div>
                    </div>
                </div>
            </div>

            <div class="space-y-4" wire:loading.remove>
                <div class="flex items-center justify-between">
                    <label for="static-ip" class="">
                        <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-300">
                            Static
                        </span>
                        <span id="static-ip-description" class="block text-sm text-gray-600 dark:text-neutral-500">
                            Make static
                        </span>
                    </label>
                    <div class="flex items-center h-5 mt-1">
                        <input id="static-ip" name="static-ip" type="checkbox" wire:model.live='make_static'
                            class="w-5 h-5 transition duration-200 border-gray-400 rounded-full text-emerald-400 focus:ring-emerald-500 checked:border-emerald-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-emerald-500 dark:checked:border-emerald-500 dark:focus:ring-offset-gray-800"
                            aria-describedby="static-ip-description">
                    </div>
                </div>
                @if($make_static)
                    <div>
                        <x-bale.input label="Comment" wire:model='comment' />
                    </div>
                @endif
            </div>
        </div>

        <h3 class="mt-10 mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
            Monitoring Status
        </h3>

        <div class="space-y-4">
            <template x-if="uptimeKumaSynced">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Uptime</p>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full"
                            :class="uptimeStatus ? 'bg-emerald-500' : 'bg-red-400'"></span>
                        <p class="font-semibold" x-text="uptimeStatus ? 'Up' : 'Down'"
                            :class="uptimeStatus ? 'text-emerald-500' : 'text-red-400'">
                        </p>
                    </div>

                    <p class="text-xs">
                        {{ __('Last Updated At ') }} <span x-text="uptimeLastUpdate"></span>
                    </p>

                    <div class="p-3 text-sm border-l-4 border-red-300 rounded-tr-xl rounded-br-xl bg-red-50"
                        x-show="!uptimeStatus">
                        <p class="text-red-500 dark:text-red-400" x-text="uptimeCheckFailureReason"></p>
                    </div>
                </div>
            </template>
        </div>

        <h3 class="mt-10 mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
            Monitoring Detail
        </h3>

        <div class="grid grid-cols-1 space-y-4 sm:grid-cols-3 gap-x-4">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Timeout</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="uptimeTimeout"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Interval</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="uptimeInterval"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Retry Interval</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="uptimeRetryInterval">
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Resend Interval</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="uptimeResendInterval">
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Max Retries</p>
                <p class="mt-1 font-semibold text-gray-800 dark:text-white" x-text="uptimeMaxRetry"></p>
            </div>
        </div>

        <x-bale.modal-action>
            <x-bale.button label="Save Change" type="submit" @click="useSpinner()" class="ml-3" />
            <x-bale.secondary-button label="Cancel" type="button"
                wire:click="$dispatch('closeBaleModal', { id: 'ipAddressDetailModal' }); $wire.resetVal()" />
        </x-bale.modal-action>

    </form>

</div>