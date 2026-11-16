<div>
    <x-admin-nav />
    @if ($celebrate)
        <div class="mx-auto max-w-6xl w-full px-6 pt-6" role="status" wire:key="celebrate">
            <div class="relative rounded-2xl bg-sun-soft px-5 py-4 flex items-center gap-4">
                <span class="relative size-20 shrink-0"><x-confetti /><x-mascot.cheer class="lapa-pop relative size-full" label="Лапа радіє" /></span>
                <span class="flex flex-col">
                    <span class="font-display font-extrabold text-2xl">Ура! {{ $this->application()->animal->foundHomePhrase() }}</span>
                    <span class="text-muted">Інші активні анкети на цю тварину закрито автоматично.</span>
                </span>
            </div>
        </div>
    @else
        <x-flash :message="$flash" />
    @endif
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-6">
        <a href="{{ route('admin.applications.index') }}" class="font-semibold text-primary hover:underline">← Усі анкети</a>
        <div class="flex flex-wrap items-center gap-4">
            <h1 class="text-4xl">{{ $application->name }}</h1>
            <span class="chip {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
        </div>

        <div class="flex flex-wrap gap-6 items-start">
            <div class="grow-[999] basis-[480px] min-w-0 flex flex-col gap-6">
                <section class="card p-6 flex flex-col gap-4">
                    <h2 class="text-2xl">Контакти</h2>
                    {{-- Звʼязатися одним дотиком; канали, які людина обрала в анкеті, виділено --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Enums\ContactChannel::cases() as $channel)
                            @php($preferred = $application->contact_channels->contains($channel))
                            <a href="{{ $channel->link($application->phone) }}" @if ($channel !== \App\Enums\ContactChannel::Phone) target="_blank" rel="noopener" @endif
                               @class(['btn min-h-11 text-sm', 'btn-primary' => $preferred, 'btn-outline' => ! $preferred])>
                                {{ $channel->actionLabel() }}@if ($preferred)<span class="sr-only"> (обраний канал)</span>@endif
                            </a>
                        @endforeach
                        <a href="mailto:{{ $application->email }}?subject={{ rawurlencode('Анкета на '.$application->animal->name) }}" class="btn btn-outline min-h-11 text-sm">Написати лист</a>
                    </div>
                    <dl class="grid gap-4 grid-cols-[repeat(auto-fit,minmax(200px,1fr))]">
                        <div><dt class="text-sm text-muted">Телефон</dt><dd class="font-semibold"><a href="tel:{{ $application->phone }}" class="text-primary hover:underline">{{ $application->phone }}</a></dd></div>
                        <div><dt class="text-sm text-muted">E-mail</dt><dd class="font-semibold break-all"><a href="mailto:{{ $application->email }}" class="text-primary hover:underline">{{ $application->email }}</a></dd></div>
                        <div><dt class="text-sm text-muted">Зручний звʼязок</dt><dd class="font-semibold">{{ $application->contact_channels->map->label()->join(', ') }}</dd></div>
                        <div><dt class="text-sm text-muted">Область</dt><dd class="font-semibold">{{ $application->region->name }}</dd></div>
                        @if ($application->social_url)
                            <div><dt class="text-sm text-muted">Соцмережі</dt><dd class="font-semibold break-all"><a href="{{ $application->social_url }}" target="_blank" rel="noopener nofollow" class="text-primary hover:underline">{{ $application->social_url }}</a></dd></div>
                        @endif
                        <div><dt class="text-sm text-muted">Подано</dt><dd class="font-semibold">{{ $application->created_at->format('d.m.Y H:i') }}</dd></div>
                        <div><dt class="text-sm text-muted">Акаунт</dt><dd class="font-semibold">{{ $application->user ? 'Зареєстрований, пошту підтверджено' : 'Без реєстрації' }}</dd></div>
                    </dl>
                    @if ($application->comment)
                        <blockquote class="rounded-2xl bg-bg p-4 whitespace-pre-line">{{ $application->comment }}</blockquote>
                    @endif
                </section>

                <section class="card p-6 flex flex-col gap-4">
                    <h2 class="text-2xl">Статус</h2>
                    @error('transition')<div role="alert" class="rounded-2xl bg-danger-soft text-danger px-4 py-3 font-medium">{{ $message }}</div>@enderror
                    @if ($application->status->isFinal())
                        <p class="text-muted">Анкета закрита – статус більше не змінюється.</p>
                    @else
                        <label class="label">Коментар до зміни статусу <span class="font-normal text-sm text-muted">обовʼязково для відмови</span>
                            <textarea wire:model="transitionNote" rows="2" class="field font-normal py-3" placeholder="Наприклад: домовились про зустріч у суботу о 12:00"></textarea>
                            @error('transitionNote')<span class="error">{{ $message }}</span>@enderror
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($application->status->allowedTransitions() as $target)
                                <button type="button" wire:click="changeStatus('{{ $target->value }}')"
                                        @if ($target === \App\Enums\ApplicationStatus::Adopted) wire:confirm="Позначити, що {{ $application->animal->name }} прилаштовано за цією анкетою? Інші активні анкети на цю тварину буде закрито." @endif
                                        @class(['btn', 'btn-primary' => $target === \App\Enums\ApplicationStatus::Adopted, 'btn-outline' => ! in_array($target, [\App\Enums\ApplicationStatus::Adopted, \App\Enums\ApplicationStatus::Declined]), 'btn border-[1.5px] border-danger text-danger' => $target === \App\Enums\ApplicationStatus::Declined])>
                                    → {{ $target->label() }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="card p-6 flex flex-col gap-3">
                    <h2 class="text-2xl">Нотатки адміністратора</h2>
                    <textarea wire:model="notes" rows="4" class="field font-normal py-3" placeholder="Видно лише адміністраторам"></textarea>
                    <button type="button" wire:click="saveNotes" class="btn-outline self-start">Зберегти нотатки</button>
                </section>

                <section class="card p-6 flex flex-col gap-3">
                    <h2 class="text-2xl">Історія</h2>
                    <ol class="flex flex-col gap-3 border-l-2 border-line pl-5">
                        @foreach ($application->statusChanges as $change)
                            <li class="relative">
                                <span class="absolute -left-[27px] top-1.5 size-3 rounded-full bg-primary" aria-hidden="true"></span>
                                <span class="font-semibold">{{ $change->to_status->label() }}</span>
                                <span class="text-sm text-muted">· {{ $change->created_at->format('d.m.Y H:i') }}@if ($change->user) · {{ $change->user->name }}@endif</span>
                                @if ($change->note)<p class="text-muted">{{ $change->note }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>

            <aside class="grow basis-72 min-w-0 flex flex-col gap-4">
                <x-animal-card :animal="$application->animal" />
                <div class="card p-5 flex flex-col gap-3">
                    <h2 class="text-xl">Інші анкети на {{ $application->animal->name }}</h2>
                    @forelse ($others as $other)
                        <a href="{{ route('admin.applications.show', $other) }}" class="flex justify-between gap-2 hover:text-primary">
                            <span>{{ $other->name }}</span><span class="chip {{ $other->status->badgeClasses() }} text-xs">{{ $other->status->label() }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-muted">Інших анкет немає.</p>
                    @endforelse
                </div>
                <div class="card p-5 flex flex-col gap-3">
                    <h2 class="text-xl">Інші анкети цієї людини</h2>
                    @forelse ($samePerson as $other)
                        <a href="{{ route('admin.applications.show', $other) }}" class="flex justify-between gap-2 hover:text-primary" wire:key="same-{{ $other->id }}">
                            <span>{{ $other->animal->name }} <span class="text-sm text-muted">· {{ $other->created_at->format('d.m.Y') }}</span></span>
                            <span class="chip {{ $other->status->badgeClasses() }} text-xs">{{ $other->status->label() }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-muted">Це перша анкета від {{ $application->email }}.</p>
                    @endforelse
                    @if ($samePerson->isNotEmpty())
                        <a href="{{ route('admin.applications.index', ['email' => $application->email]) }}" class="text-sm font-semibold text-primary hover:underline">Усі анкети цієї людини →</a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
