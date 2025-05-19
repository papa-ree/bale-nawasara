<?php

use function Livewire\Volt\{title, mount};
title('Create Token');

$store = function (LivewireAlert $alert) {
    $this->authorize('user management');

    $this->validate();

    DB::beginTransaction();

    try {
        $this->dispatch('disabling-button', params: true);

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        $user->assignRole($this->role_name);

        DB::commit();
        session()->flash('saved', [
            'title' => 'Changes Saved!',
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
            <x-bale.input wire:model='name' />

            <x-bale.button type='submit' />
        </form>

        <x-bale.input wire:model='plainToken' />
    </x-bale.page-container>
</div>
