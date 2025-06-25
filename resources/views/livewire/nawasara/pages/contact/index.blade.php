<?php
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use function Livewire\Volt\{title, mount};
title('Contact');

mount(function () {
    if (session()->has('saved')) {
        LivewireAlert::title(session('saved.title'))->toast()->position('top-end')->success()->show();
    }
});
?>

<div>
    <x-bale.page-header title="PIC Contact">
        <x-slot name="action">
            <div class="flex flex-row gap-x-3">
                {{-- <x-bale.secondary-button label="Opname Stock" type="button" link
                    href="{{ route('items.opname-stock') }}" />
                <x-bale.secondary-button label="Add Stock" type="button" link href="{{ route('items.add-stock') }}" /> --}}
                <x-bale.button label="Add Contact" type="button" link href="{{ route('contacts.create', 'new') }}" />
            </div>
        </x-slot>
    </x-bale.page-header>

    <livewire:nawasara.pages.contact.section.contact-table />

    <x-bale.modal modalId="contactDeleteModal" size="xl" staticBackdrop>
        <livewire:nawasara.pages.contact.modal.contact-delete-confirmation-modal />
    </x-bale.modal>
</div>
