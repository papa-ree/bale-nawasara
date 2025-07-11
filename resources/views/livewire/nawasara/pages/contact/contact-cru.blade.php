<?php

use function Livewire\Volt\{title, mount, computed, state, rules};
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Paparee\BaleNawasara\App\Models\PicContact;
use Paparee\BaleNawasara\App\Models\DnsRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Paparee\BaleInv\App\Services\SatkerService;
use Illuminate\Support\Str;

title('Add Contact');
state(['edit_mode' => false, 'is_assign', 'assign_subdomain', 'contact', 'contact_name', 'contact_phone', 'contact_nip', 'contact_job', 'contact_office', 'recovery_email_address', 'use_recovery_email' => true, 'user_uuid', 'subdomains']);

mount(function ($contact) {
    // dump(Str::contains(url()->current(), 'assign'));
    if (!Auth::user()->can('contact update') && !Auth::user()->can('contact create')) {
        abort(403);
    }

    $this->is_assign = Str::contains(url()->current(), 'assign');

    $this->subdomains = [];

    if ($this->is_assign) {
        array_push($this->subdomains, $contact);
        // $this->subdomains = $contact;
    } else {
        $this->contact = PicContact::with('subdomains')->find($contact);

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

$availableLocations = computed(function () {
    if (cache()->get('nawasara_instansi')) {
        return cache()->get('nawasara_instansi', collect());
    } else {
        $satker = new SatkerService();
        $satker->getLocations();

        return cache()->get('nawasara_instansi', collect());
    }
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
            $subdomain_full = $subdomain . '.ponorogo.go.id';
            DnsRecord::whereName($subdomain_full)->update(['pic_contact_id' => $contact->id]);
        }

        DB::commit();
        $this->dispatch('refresh-dns-record-list');
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
    // dd($this);
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
            $subdomain_full = $subdomain . '.ponorogo.go.id';
            DnsRecord::whereName($subdomain_full)->update(['pic_contact_id' => $this->contact->id]);
        }

        DB::commit();
        $this->dispatch('refresh-dns-record-list');
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

            <div class="w-full mb-4 lg:w-1/2 md:w-3/4 gap-x-3 sm:mb-6">
                <x-bale.select-dropdown label="select location" x-data="{ contactLocations: $wire.entangle('contact_office') }">
                    <x-slot name="defaultValue">
                        <span x-text="contactLocations == null ? 'Open this select menu' : contactLocations"></span>
                    </x-slot>
                    @foreach ($this->availableLocations as $key => $location)
                        <label for="{{ $key . $location['id'] }}"
                            class="flex w-full p-3 text-sm transition duration-200 ease-out bg-white hover:bg-gray-200 hover:rounded-lg dark:bg-neutral-900 hover:dark:border-neutral-700 dark:text-neutral-400"
                            wire:key="{{ $key . $location['id'] }}" @click="title='{{ $location['description'] }}'">
                            <span class="text-sm text-gray-500 dark:text-neutral-400">{{ $location['description'] }}

                            </span>
                            <input type="radio" name="contact_office" wire:model='contact_office'
                                value="{{ $location['description'] }}"
                                class="shrink-0 ms-auto mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                id="{{ $key . $location['id'] }}">
                        </label>
                    @endforeach
                </x-bale.select-dropdown>
                <x-input-error for="contact_office" />
            </div>

            <div class="block w-full mb-4 md:flex lg:w-1/2 md:w-3/4 gap-x-3 sm:mb-6" x-data="{ showEmail: $wire.entangle('use_recovery_email') }">
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

            <div class="mb-4 sm:mb-6 lg:w-1/2" x-data="{ isAssign: $wire.entangle('is_assign'), subdomain: $wire.entangle('subdomains') }">
                @if ($this->is_assign)
                    <div class="select-none" x-show="isAssign">
                        <x-label value="subdomains" class="mb-2" />
                        <span
                            class="px-3 hover:bg-gray-300 transition py-1.5 items-center bg-gray-200 dark:bg-gray-700 rounded-md"
                            x-text="subdomain">
                        </span>
                    </div>
                @endif
                <div wire:ignore x-show="!isAssign">
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
