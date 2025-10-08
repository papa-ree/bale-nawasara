<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Locked, On, Computed};
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\DB;
use Paparee\BaleNawasara\App\Models\HelpdeskForm;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    #[Locked]
    public $ticketId;

    public $ticket;
    public $pic;
    public $status;

    #[On('setData')]
    public function getTicketData($ticketId)
    {
        $this->ticket = HelpdeskForm::find($ticketId);
        $this->pic = $this->ticket->pic;
        $this->status = $this->ticket->status;
    }

    public function assign(LivewireAlert $alert)
    {
        DB::beginTransaction();

        try {

            $this->ticket->update(
                [
                    'pic' => Auth::user()->name,
                    'status' => 'handled'
                ]
            );

            $this->dispatch('setData', ticketId: $this->ticket->id);

            DB::commit();
            $alert->title('Success')->position('top-end')->success()->toast()->show();
            $this->dispatch('refresh-ticket-list');

        } catch (\Throwable $th) {
            $this->dispatch('disabling-button', params: false);

            DB::rollBack();
            info($th->getMessage());
            $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
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
    init() {
        this.resetState();
    },
    resetState() {
        this.ticketNumber = '';
        this.customerName = '';
        this.nip = '';
        this.phone = '';
        this.description = '';
    },
    handleIpAddressData(detail) {
        this.ticketNumber = detail.ticketData.ticket_number;
        this.customerName = detail.ticketData.name;
        this.nip = detail.ticketData.nip;
        this.phone = detail.ticketData.phone;
        this.description = detail.ticketData.description;
    },
}" x-init="init()" @ticket-data.window="handleIpAddressData($event.detail)">

    <form wire:submit='assign' class="mb-6">

        <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b-2 border-gray-300 dark:text-gray-300">
            Ticket Information
        </h3>

        <div class="space-y-4">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ticket Number</p>
                <p class="font-semibold text-gray-800 dark:text-white" x-text="ticketNumber"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Customer Name</p>
                <p class="font-semibold text-gray-800 dark:text-white" x-text="customerName"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">NIP</p>
                <p class="font-semibold text-gray-800 dark:text-white" x-text="nip"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                <a :href="`https://wa.me/` + phone" target="_blank" class="font-semibold text-gray-800 dark:text-white"
                    x-text="phone"></a>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                <p class="font-semibold text-gray-800 dark:text-white" x-text="description"></p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">PIC</p>
                <p class="font-semibold text-gray-800 dark:text-white">{{$pic ?? '-'}}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-semibold text-gray-800 dark:text-white">{{$status ?? 'loading'}}</p>
            </div>

        </div>

        <x-bale.modal-action>
            @if ($pic)
                <a :href="`https://wa.me/` + phone" target="_blank" link label="Go to Whatsapp" type="button"
                    wire:loading.remove
                    wire:click="$dispatch('closeBaleModal', { id: 'helpdeskDetailModal' }); $wire.resetVal()"
                    class="flex items-center px-4 py-3 ml-3 text-sm antialiased tracking-wide text-center text-white capitalize transition-all duration-500 border border-blue-300 rounded-lg select-none dark:border-blue-300/70 bg-gradient-to-tl from-emerald-300 via-blue-500 to-teal-500 bg-size-200 bg-pos-0 hover:bg-pos-100" />
                Go to Whatsapp
                </a>
            @else
                <x-bale.button label="Assign" type="submit" class="ml-3" wire:loading.remove />
            @endif
            <x-bale.secondary-button label="Cancel" type="button" disabled
                wire:click="$dispatch('closeBaleModal', { id: 'helpdeskDetailModal' }); $wire.resetVal(); $dispatch('refresh-ticket-list')" />
        </x-bale.modal-action>

    </form>

</div>