@php
    $key = Illuminate\Support\Str::random();
@endphp

<div class="flex justify-end w-full mt-6">
    <x-filament::button
        wire:click="save"
        type="submit"
        color="primary"
        size="lg"
        icon="heroicon-o-check"
        class="w-full sm:w-auto"
        :key="$key"
    >
        Save All Changes
    </x-filament::button>
</div>
