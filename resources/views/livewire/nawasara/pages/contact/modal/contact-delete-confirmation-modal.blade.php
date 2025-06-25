<?php

use Livewire\Volt\Component;
use Paparee\BaleNawasara\App\Models\PicContact;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;

new class extends Component {
    #[Locked]
    public $contact_id;

    #[Locked]
    public $contact_name;

    public function deleteContact(LivewireAlert $alert, $contact_id)
    {
        DB::beginTransaction();

        $this->contact_id = $contact_id;

        try {
            $this->delete();
            DB::commit();

            $this->dispatch('closeBaleModal', id: 'contactDeleteModal');
            $this->dispatch('refresh-contact-list');

            $alert->title('Contact Deleted!')->position('top-end')->success()->toast()->show();
        } catch (\Throwable $th) {
            $this->dispatch('message-failed');
            DB::rollBack();
            info($th->getMessage());

            $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
        }
    }

    private function delete()
    {
        $contact = PicContact::findOrFail($this->contact_id);

        // Kosongkan relasi dari DNS
        DnsRecord::where('pic_contact_id', $this->contact_id)->update([
            'pic_contact_id' => null,
        ]);

        // Hapus contact
        $contact->delete();

        return 1;
    }
};
?>

<div x-data="{
    contactId: '',
    contactName: '',
    init() {
        this.resetState();
    },
    resetState() {
        this.contactId = '';
        this.contactName = '';
    },
    handleContactData(detail) {
        this.contactId = detail.contactId;
        this.contactName = detail.contactName;
    },
}" x-init="init()" @contact-data.window="handleContactData($event.detail)">

    <div class="sm:flex sm:items-start">
        <div
            class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                Delete <span x-text="contactName"></span> contact?
            </h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500 dark:text-white">
                    Are you sure you want to delete
                    this item? All
                    of your data will be permanently removed
                    from our servers forever. This action cannot be undone.
                </p>
            </div>
        </div>
    </div>

    <x-bale.modal-action>
        <x-bale.secondary-button label="Cancel" wire:click="$dispatch('closeBaleModal', { id: 'contactDeleteModal' })"
            class="ml-3" />
        <x-bale.danger-button label="Gaskeun!" @click="$wire.deleteContact(contactId); useSpinner()" />
    </x-bale.modal-action>

</div>
