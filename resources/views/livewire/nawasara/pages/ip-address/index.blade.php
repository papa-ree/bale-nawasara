<?php
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use function Livewire\Volt\{title, mount};
title('Nawasara | IP Address');

mount(function () {
    if (session()->has('saved')) {
        LivewireAlert::title(session('saved.title'))->toast()->position('top-end')->success()->show();
    }
});
?>

<div>
    <livewire:nawasara.pages.ip-address.section.ip-address-table />

    {{-- <x-bale.modal modalId="ipAddressDetailModal" size="2xl" staticBackdrop>
        <livewire:nawasara.pages.ip.modal.ip-detail-modal />
    </x-bale.modal>

    <x-bale.modal modalId="openIpPublicDeleteModal" size="2xl" staticBackdrop>
        <livewire:nawasara.pages.ip.modal.ip-delete-confirmation-modal />
    </x-bale.modal> --}}
</div>