<x-mail::message>
# Нова анкета на {{ $application->animal->name }}

**{{ $application->name }}**, {{ $application->region->name }}<br>
Телефон: {{ $application->phone }}<br>
E-mail: {{ $application->email }}<br>
Зручний звʼязок: {{ $application->contact_channels->map->label()->join(', ') }}

@if ($application->comment)
> {{ $application->comment }}
@endif

<x-mail::button :url="route('admin.applications.show', $application)">
Відкрити анкету
</x-mail::button>
</x-mail::message>
