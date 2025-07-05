<?php
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use function Livewire\Volt\{title, mount};
title('Nawasara | Overview');

mount(function () {
    if (session()->has('saved')) {
        LivewireAlert::title(session('saved.title'))->toast()->position('top-end')->success()->show();
    }
});
?>

<div>
    <livewire:nawasara.pages.overview.section.nawasara-summary-card />
</div>
