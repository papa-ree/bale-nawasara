<?php

use function Livewire\Volt\{title, mount, computed, state, rules};
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Paparee\BaleNawasara\App\Models\NawasaraAccessToken;

title('Create Token');
state(['token_name', 'user_name', 'user_uuid']);

$availableUsers = computed(function () {
    return User::get(['uuid', 'name']);
});

rules(
    fn() => [
        'token_name' => 'required|string|min:3|max:50',
        'user_uuid' => 'required',
    ],
);

$createToken = function (LivewireAlert $alert) {
    $this->authorize('user management');

    $this->validate();

    DB::beginTransaction();

    try {
        $this->dispatch('disabling-button', params: true);

        $user = User::whereUuid($this->user_uuid)->first();

        NawasaraAccessToken::createTokenWithPlainText($user, $this->token_name, ['wago:send-message']);

        DB::commit();
        session()->flash('saved', [
            'title' => 'Token Created',
        ]);

        $this->redirect('tokens', navigate: true);
    } catch (\Throwable $th) {
        $this->dispatch('disabling-button', params: false);

        DB::rollBack();
        info($th->getMessage());
        $alert->title('Something wrong!')->position('top-end')->error()->toast()->show();
    }
};

?>

<div>
    <x-bale.page-container>
        <form wire:submit='createToken'>
            <x-bale.input wire:model='token_name' label="Token Name" />

            <div class="mb-4 sm:mb-6">
                <x-bale.select-dropdown label="select user" x-data="{ userName: $wire.entangle('user_name') }">
                    <x-slot name="defaultValue">
                        <span x-text="userName == null ? 'Open this select menu' : userName"></span>
                    </x-slot>
                    @foreach ($this->availableUsers as $key => $user)
                        <label for="{{ 'user-' . $key }}"
                            class="flex w-full p-3 text-sm transition duration-200 ease-out bg-white hover:bg-gray-200 hover:rounded-lg dark:bg-neutral-900 hover:dark:border-neutral-700 dark:text-neutral-400"
                            wire:key="{{ 'user-' . $key }}" @click="userName='{{ $user->name }}'">
                            <span class="text-sm text-gray-500 dark:text-neutral-400">{{ $user->name }}</span>
                            <input type="radio" name="user_name" wire:model='user_uuid' value="{{ $user->uuid }}"
                                class="shrink-0 ms-auto mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                id="{{ 'user-' . $key }}">
                        </label>
                    @endforeach
                </x-bale.select-dropdown>
                <x-input-error for="user_name" />
            </div>

            <x-bale.button type='submit' />
        </form>

        {{-- <x-bale.input wire:model='plainToken' /> --}}
    </x-bale.page-container>
</div>
