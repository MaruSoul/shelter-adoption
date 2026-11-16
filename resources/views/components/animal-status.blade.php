@props(['status'])
<span {{ $attributes->class(['chip',
    'bg-sun text-sun-ink' => $status === \App\Enums\AnimalStatus::Moderation,
    'bg-surface text-primary' => $status === \App\Enums\AnimalStatus::Available,
    'bg-success-soft text-success' => $status === \App\Enums\AnimalStatus::Adopted,
    'bg-danger-soft text-danger' => $status === \App\Enums\AnimalStatus::Rejected,
    'bg-line text-muted' => $status === \App\Enums\AnimalStatus::Archived]) }}>{{ $status->label() }}</span>
