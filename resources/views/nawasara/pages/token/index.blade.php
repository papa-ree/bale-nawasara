<?php

use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use function Livewire\Volt\{title, mount};
title('Access Token');

mount(function () {
    if (session()->has('saved')) {
        LivewireAlert::title(session('saved.title'))->toast()->position('top-end')->success()->show();
    }
});

?>

<div>
    <x-bale.page-header title="User Lists">
        <x-slot name="action">
            <x-bale.button label="Create Access Token" type="button" link href="{{ route('tokens.create') }}" />
        </x-slot>
    </x-bale.page-header>

    <livewire:nawasara.pages.token.section.token-table />

    <x-bale.modal modalId="tokenModal" size="2xl" staticBackdrop>
        <livewire:nawasara.pages.token.modal.token-modal />
    </x-bale.modal>
</div>
