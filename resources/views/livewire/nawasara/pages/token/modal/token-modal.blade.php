<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Paparee\BaleNawasara\App\Models\NawasaraAccessToken;
use Illuminate\Support\Facades\Crypt;

new class extends Component {
    public $token;
    public $plain_text_token;

    #[On('open-token-modal')]
    public function tokenData($tokenId)
    {
        $this->token = NawasaraAccessToken::find($tokenId);
        $this->plain_text_token = $this->token->plain_text_token ? Crypt::decryptString($this->token->plain_text_token) : 'please regenerate token';
    }

    public function generateToken()
    {
        $this->token->regenerate();
        $this->plain_text_token = Crypt::decryptString($this->token->plain_text_token);
    }
}; ?>

<div>
    <div class="relative mb-2" x-data="{
        plainToken: $wire.entangle('plain_text_token').live,
        tooltipText: 'Copy',
        showCopyIcon: true
    }">
        <div class="hs-tooltip">
            <div class="rounded-lg cursor-pointer sm:flex hs-tooltip-toggle group"
                @click="$clipboard(plainToken); tooltipText = 'Copied'; setTimeout(() => { tooltipText = 'Copy'; showCopyIcon = true; }, 2000); showCopyIcon = !showCopyIcon">
                <input type="text" x-model="plainToken" disabled
                    class="py-2.5 sm:py-3 px-4 pe-11 block w-full border-gray-200 group-hover:bg-gray-100 transition duration-300 -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg sm:text-sm relative focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                <span wire:ignore
                    class="py-2.5 sm:py-3 px-4 inline-flex items-center min-w-fit w-full border border-gray-200 bg-gray-50 sm:text-sm text-gray-500 -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:w-auto sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg dark:bg-neutral-700 dark:border-neutral-700 dark:text-neutral-400">
                    <i data-lucide="clipboard"
                        class="h-5 text-gray-700 transition-all duration-300 group-hover:rotate-12"
                        :class="{ 'hidden': !showCopyIcon }"></i>
                    <i data-lucide="check"
                        class="hidden h-5 transition-all duration-300 text-emerald-500 group-hover:rotate-12"
                        :class="{ 'hidden': showCopyIcon }"></i>
                </span>
                <span
                    class="absolute z-10 invisible inline-block px-2 py-1 text-xs font-medium text-white transition-opacity bg-gray-900 rounded-md opacity-0 hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible shadow-2xs dark:bg-neutral-700"
                    role="tooltip" x-text="tooltipText">

                </span>
            </div>
        </div>

        <div wire:loading class="absolute top-0 rounded-lg start-0 size-full bg-white/50 dark:bg-neutral-800/40">
        </div>

        <div wire:loading class="absolute transform -translate-x-1/2 -translate-y-1/2 top-1/2 start-1/2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-loader-circle-icon size-6 lucide-loader-circle animate-spin text-emerald-400">
                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
        </div>
    </div>

    <x-bale.modal-action>
        <x-bale.secondary-button label="Cancel" type="button" class="ml-3"
            wire:click="$dispatch('closeBaleModal', { id: 'tokenModal' }); $wire.set('plain_text_token', null)" />
        <x-bale.button label="generate access token" type="button" wire:click='generateToken' />
    </x-bale.modal-action>
</div>
