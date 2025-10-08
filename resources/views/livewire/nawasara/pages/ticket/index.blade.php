<?php
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use function Livewire\Volt\{title, mount};
title('Helpdesk');

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
                {{-- <x-bale.button label="Add Contact" type="button" link
                    href="{{ route('contacts.create', 'new') }}" /> --}}
            </div>
        </x-slot>
    </x-bale.page-header>

    <livewire:nawasara.pages.ticket.section.ticket-table />

    <x-bale.modal modalId="helpdeskDetailModal" size="xl" staticBackdrop>
        <livewire:nawasara.pages.ticket.modal.ticket-detail-modal />
    </x-bale.modal>
</div>