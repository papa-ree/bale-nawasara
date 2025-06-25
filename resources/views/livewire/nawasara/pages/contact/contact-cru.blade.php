<?php

use function Livewire\Volt\{title, mount, computed, state, rules};
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Paparee\BaleNawasara\App\Models\PicContact;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

title('Add Contact');
state(['edit_mode' => false, 'contact', 'contact_name', 'contact_phone', 'contact_nip', 'contact_job', 'contact_office', 'recovery_email_address', 'use_recovery_email' => true, 'user_uuid', 'subdomains']);

mount(function ($contact) {
    if (!Auth::user()->can('contact update') && !Auth::user()->can('contact create')) {
        abort(403);
    }

    $this->contact = PicContact::with('subdomains')->find($contact);
    $this->subdomains = [];

    if ($this->contact) {
        $this->edit_mode = true;
        $this->contact_name = $this->contact->contact_name;
        $this->contact_phone = $this->contact->contact_phone;
        $this->contact_nip = $this->contact->contact_nip;
        $this->recovery_email_address = $this->contact->recovery_email_address;
        $this->use_recovery_email = $this->contact->recovery_email_address ?? false;
        $this->contact_job = $this->contact->contact_job;
        $this->contact_office = $this->contact->contact_office;
        foreach ($this->contact->subdomains as $subdomain) {
            array_push($this->subdomains, $subdomain->name);
        }
    }
});

$availableRecords = computed(function () {
    $query = DnsRecord::whereType('A')
        ->where(function ($q) {
            $q->whereNull('pic_contact_id');

            if ($this->edit_mode) {
                $q->orWhere('pic_contact_id', $this->contact->id);
            }
        })
        ->orderBy('name');

    return $query->pluck('name');
});

rules(
    fn() => [
        'contact_name' => 'required|string|min:3|max:50',
        'contact_phone' => 'required|numeric|digits_between:8,14',
        'contact_job' => 'required|string|min:3|max:100',
        'contact_office' => 'required|string|min:3|max:100',
        'recovery_email_address' => 'required_if:use_recovery_email,true|required_if:use_recovery_email,1',
    ],
)->messages([
    'recovery_email_address.required_if' => 'The recovery email address cannot be empty.',
]);

$store = function (LivewireAlert $alert) {
    $this->authorize('contact create');

    $this->validate();

    DB::beginTransaction();

    try {
        $this->dispatch('disabling-button', params: true);

        $contact = PicContact::create([
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_phone_hash' => $this->contact_phone,
            'contact_nip' => $this->contact_nip,
            'contact_nip_hash' => $this->contact_nip,
            'recovery_email_address' => $this->recovery_email_address,
            'recovery_email_address_hash' => $this->recovery_email_address,
            'contact_job' => $this->contact_job,
            'contact_office' => $this->contact_office,
            'user_uuid' => Auth::user()->uuid,
        ]);

        foreach ($this->subdomains as $subdomain) {
            DnsRecord::whereName($subdomain)->update(['pic_contact_id' => $contact->id]);
        }

        DB::commit();
        session()->flash('saved', [
            'title' => 'Contact Created',
        ]);

        $this->redirect('contacts', navigate: true);
    } catch (\Throwable $th) {
        $this->dispatch('disabling-button', params: false);

        DB::rollBack();
        info($th->getMessage());
        $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
    }
};

$update = function (LivewireAlert $alert) {
    $this->authorize('contact update');
    $this->validate();

    DB::beginTransaction();

    try {
        $this->dispatch('disabling-button', params: true);

        $this->contact->update([
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_phone_hash' => $this->contact_phone,
            'contact_nip' => $this->contact_nip,
            'contact_nip_hash' => $this->contact_nip,
            'recovery_email_address' => $this->recovery_email_address,
            'recovery_email_address_hash' => $this->recovery_email_address,
            'contact_job' => $this->contact_job,
            'contact_office' => $this->contact_office,
        ]);

        // Kosongkan dulu DNS yang sebelumnya pakai contact ini, tapi tidak lagi dipilih
        DnsRecord::where('pic_contact_id', $this->contact->id)
            ->whereNotIn('name', $this->subdomains)
            ->update(['pic_contact_id' => null]);

        // Perbarui subdomain yang dipilih
        foreach ($this->subdomains as $subdomain) {
            DnsRecord::whereName($subdomain)->update(['pic_contact_id' => $this->contact->id]);
        }

        DB::commit();
        session()->flash('saved', [
            'title' => 'Contact Updated',
        ]);

        $this->redirect('contacts', navigate: true);
    } catch (\Throwable $th) {
        $this->dispatch('disabling-button', params: false);

        DB::rollBack();
        info($th->getMessage());
        $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
    }
};

?>

<div>
    @assets
        <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.20.0"></script>
    @endassets

    <x-bale.page-container>
        <form wire:submit="{{ $edit_mode ? 'update' : 'store' }}">
            <div class="mb-4 sm:mb-6">
                <x-bale.input wire:model='contact_name' label="Name" />
                <x-input-error for="contact_name" />
            </div>

            <div class="mb-4 sm:mb-6">
                <x-bale.input wire:model='contact_nip' label="NIP" />
                {{-- <x-input-error for="contact_nip" /> --}}
            </div>

            <div class="mb-4 sm:mb-6">
                <x-bale.input wire:model='contact_phone' label="Phone" />
                <x-input-error for="contact_phone" />
            </div>

            <div class="mb-4 sm:mb-6">
                <x-bale.input wire:model='contact_job' label="Job" />
                <x-input-error for="contact_job" />
            </div>

            <div class="mb-4 sm:mb-6">
                <x-bale.input wire:model='contact_office' label="Office" />
                <x-input-error for="contact_office" />
            </div>

            <div class="block mb-4 md:flex md:w-1/2 gap-x-3 sm:mb-6" x-data="{ showEmail: $wire.entangle('use_recovery_email') }">
                <div class="w-full" wire:transition>
                    <label for="use_recovery_email"
                        class="flex items-center justify-between px-4 py-3 transition duration-200 border border-gray-200 rounded-lg dark:border-gray-700 hover:dark:bg-gray-700 hover:bg-gray-100">
                        <div>
                            <h3 class="text-sm font-medium text-gray-800 dark:text-white">Recovery Email Address</h3>
                        </div>
                        <input id="use_recovery_email" type="checkbox" wire:model='use_recovery_email'
                            class="w-5 h-5 text-blue-500 transition duration-200 dark:bg-gray-900 form-checkbox rounded-xl">
                    </label>
                    <x-input-error for="use_recovery_email" />
                </div>

                <div class="w-full" x-show="showEmail" wire:transition>
                    <x-bale.input type="email" wire:model='recovery_email_address'
                        placeholder="Please set recovery email address" />
                    <x-input-error for="recovery_email_address" />
                </div>
            </div>

            <div class="mb-4 sm:mb-6 lg:w-1/2">
                <div wire:ignore>
                    <x-label value="subdomains (optional)" />
                    <input name='tagify-subdomains' value="{{ json_encode($subdomains) }}"
                        class="block w-full px-3 py-2 text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400"
                        placeholder='please select subdomains'>
                </div>
            </div>
            <x-bale.modal-action>
                <x-bale.button type='submit' label="store" />
            </x-bale.modal-action>
        </form>

    </x-bale.page-container>

    @script
        <script>
            let input = document.querySelector('input[name="tagify-subdomains"]'),
                tagify = new Tagify(input, {
                    originalInputValueFormat: valuesArr => valuesArr.map(item => item.value),
                    whitelist: @json($this->availableRecords),
                    enforceWhitelist: true, // <- hanya bisa pilih dari whitelist
                    editTags: false, // <- tidak bisa edit tag dengan double click
                    // maxTags: 3,
                    dropdown: {
                        maxItems: 20,
                        classname: 'tags-look',
                        enabled: 0,
                        closeOnSelect: false
                    }
                });

            input.addEventListener('change', onChange);

            function onChange(e) {
                try {
                    // Ambil nilai dari Tagify sebagai array
                    let tags = tagify.value.map(item => item.value);
                    // Kirim ke Livewire sebagai array
                    @this.set('subdomains', tags);
                } catch (err) {
                    console.error("Tagify parsing error:", err);
                }
            }
        </script>
    @endscript
</div>
