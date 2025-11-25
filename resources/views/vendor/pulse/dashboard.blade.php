<x-pulse>
    <livewire:pulse.servers cols="full" />

    <livewire:pulse.usage cols="4" rows="2" />

    <livewire:pulse.queues cols="4" />

    <livewire:pulse.cache cols="4" />

    {{-- Custom: Emails Sent Widget --}}
    <livewire:pulse.mail-sent cols="8" />

    <livewire:pulse.slow-queries cols="8" />

    <livewire:pulse.slow-jobs cols="4" />

    <livewire:pulse.slow-requests cols="full" />

    <livewire:pulse.exceptions cols="6" />

    <livewire:pulse.slow-outgoing-requests cols="6" />
</x-pulse>
