<?php

use Livewire\Volt\Component;
use Paparee\BaleNawasara\App\Services\MikrotikService;
use Livewire\Attributes\{Locked, On};
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Paparee\BaleNawasara\App\Jobs\SyncMikrotikBgpJob;

new class extends Component {

    #[Locked]
    public $id;

    public $comment;
    public $make_static;

    #[On('setIpData')]
    public function setIpData($data)
    {
        $this->id = $data['id'];
        $this->comment = $data['comment'];
        $this->make_static = $data['dynamic'] == 'true' ? false : true;
    }

    public function update(LivewireAlert $alert)
    {
        $mikrotik = new MikrotikService();
        $mikrotik->makeStaticArp($this->id, $this->comment);

        $arpList = $mikrotik->getArpLists();

        Cache::put('mikrotik_arp_list', $arpList);

        session()->flash('saved', [
            'title' => 'Update Success!',
        ]);

        $this->redirect('/network/ip-publics', navigate: true);
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
    },
    handleIpAddressData(detail) {
        this.ipId = detail.ipAddressData.id;
        this.ipAddress = detail.ipAddressData.address;
        this.ipDhcp = detail.ipAddressData.dhcp;
        this.ipComment = detail.ipAddressData.comment;
        this.ipIsDynamic = detail.ipAddressData.dynamic;
        this.ipInterface = detail.ipAddressData.interface;
        this.ipMac = detail.ipAddressData.mac;
    },
}" x-init="init()" @ip-address-data.window="handleIpAddressData($event.detail)">

    <form wire:submit='update' class="">
        <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
            IP Address Information
        </h3>

        <div class="space-y-4">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">IP Address</p>
                <p class="font-semibold text-gray-800 dark:text-white" x-text="ipAddress"></p>
            </div>

            <div>
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

        <x-bale.modal-action>
            <x-bale.button label="Save Change" type="submit" @click="useSpinner()" class="ml-3" />
            <x-bale.secondary-button label="Cancel" type="button"
                wire:click="$dispatch('closeBaleModal', { id: 'ipAddressDetailModal' }); $wire.resetVal()" />
        </x-bale.modal-action>
    </form>


</div>