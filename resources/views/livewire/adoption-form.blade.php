<div class="mx-auto max-w-5xl px-6 py-10 flex flex-wrap gap-8 items-start">
    <aside class="card overflow-hidden grow basis-72 max-w-sm">
        <div class="aspect-[4/3] bg-tint-1">
            @if ($animal->coverPhoto)
                <img src="{{ $animal->coverPhoto->thumbUrl() }}" alt="{{ $animal->coverPhoto->altFor($animal) }}" class="size-full object-cover">
            @endif
        </div>
        <div class="p-5 flex flex-col gap-1.5">
            <span class="text-sm text-muted">{{ __('Ви подаєте анкету на') }}</span>
            <span class="font-display font-extrabold text-2xl">{{ $animal->display_name }}</span>
            <span class="text-sm text-muted">{{ $animal->species->label() }} · {{ $animal->ageLabel() }} · {{ \App\Support\Ukrainian::place($animal->locality->name) }}</span>
            <a href="{{ route('animals.show', $animal) }}" class="mt-2 font-semibold text-primary hover:underline">{{ __('← Повернутися до сторінки') }}</a>
        </div>
    </aside>

    <section class="card p-6 md:p-8 grow-[999] basis-[480px] min-w-0">
        @if (! $submitted)
            <form wire:submit="submit" class="flex flex-col gap-5" novalidate>
                <div class="flex flex-col gap-1.5">
                    <h1 class="text-4xl">{{ __('Взяти додому') }}</h1>
                    <p class="text-muted">{{ __('Реєстрація не потрібна. Залиште контакти – адміністратор притулку звʼяжеться з вами обраним способом.') }}</p>
                </div>

                @error('form')
                    <div role="alert" class="rounded-2xl bg-danger-soft text-danger px-4 py-3 font-medium">{{ $message }}</div>
                @enderror

                {{-- Пастка для ботів: прихована від людей і від програм зчитування екрана --}}
                <div class="hidden" aria-hidden="true">
                    <label>{{ __('Ваш сайт') }} <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid gap-4 grid-cols-[repeat(auto-fit,minmax(220px,1fr))]">
                    <label class="label">{{ __('Ваше імʼя *') }}
                        <input type="text" wire:model="name" autocomplete="name" class="field font-normal" @error('name') aria-invalid="true" @enderror>
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </label>
                    <label class="label">{{ __('Телефон *') }}
                        <input type="tel" wire:model.blur="phone" autocomplete="tel" inputmode="tel" class="field font-normal" @error('phone') aria-invalid="true" @enderror>
                        @error('phone')<span class="error">{{ $message }}</span>@enderror
                    </label>
                    <label class="label">{{ __('Електронна пошта *') }}
                        <input type="email" wire:model="email" autocomplete="email" class="field font-normal" @error('email') aria-invalid="true" @enderror>
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </label>
                    <label class="label">{{ __('Область *') }}
                        <select wire:model="region_id" class="field font-normal" @error('region_id') aria-invalid="true" @enderror>
                            <option value="">{{ __('Оберіть область') }}</option>
                            @foreach ($regions as $r)<option value="{{ $r->id }}">{{ __($r->name) }}</option>@endforeach
                        </select>
                        @error('region_id')<span class="error">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="label">{{ __('Посилання на соцмережі') }} <span class="font-normal text-sm text-muted">{{ __('необовʼязково') }}</span>
                    <input type="url" wire:model="social_url" placeholder="https://" class="field font-normal">
                    @error('social_url')<span class="error">{{ $message }}</span>@enderror
                </label>

                <fieldset class="flex flex-col gap-2">
                    <legend class="font-semibold mb-2">{{ __('Зручний спосіб звʼязку *') }} <span class="font-normal text-sm text-muted">{{ __('можна декілька') }}</span></legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($channels as $ch)
                            <label @class(['btn border-[1.5px] cursor-pointer has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-primary',
                                           'bg-primary text-primary-ink border-primary' => in_array($ch->value, $contact_channels),
                                           'bg-card border-line' => ! in_array($ch->value, $contact_channels)])>
                                <input type="checkbox" wire:model.live="contact_channels" value="{{ $ch->value }}" class="sr-only">
                                {{ $ch->label() }}
                            </label>
                        @endforeach
                    </div>
                    @error('contact_channels')<span class="error">{{ $message }}</span>@enderror
                </fieldset>

                <label class="label">{{ __('Коментар') }}
                    <textarea wire:model="comment" rows="4" maxlength="2000" class="field font-normal py-3 resize-y" placeholder="{{ __('Розкажіть трохи про себе: житло, досвід з тваринами, хто живе з вами') }}"></textarea>
                    @error('comment')<span class="error">{{ $message }}</span>@enderror
                </label>

                @auth
                    <label class="flex items-start gap-2.5">
                        <input type="checkbox" wire:model="rememberContacts" class="mt-1 size-4.5 shrink-0 accent-primary">
                        <span>{{ __('Запамʼятати телефон і область у') }} <a href="{{ route('cabinet.profile') }}" target="_blank" class="text-primary underline">{{ __('моєму профілі') }}</a> {{ __('для наступних анкет') }}</span>
                    </label>
                @endauth

                <label class="flex items-start gap-2.5">
                    <input type="checkbox" wire:model="consent" class="mt-1 size-4.5 shrink-0 accent-primary">
                    <span>{{ __('Я погоджуюсь з') }} <a href="{{ route('privacy') }}" target="_blank" class="text-primary underline">{{ __('політикою конфіденційності') }}</a> {{ __('і даю згоду на обробку моїх персональних даних притулком *') }}</span>
                </label>
                @error('consent')<span class="error -mt-3">{{ $message }}</span>@enderror

                <button type="submit" class="btn-primary self-start min-h-14 px-9 text-lg" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">{{ __('Відправити анкету') }}</span>
                    <span wire:loading wire:target="submit">{{ __('Надсилаємо…') }}</span>
                </button>
            </form>
        @else
            {{-- Як «урок завершено» в Duolingo: великий персонаж по центру, під ним заголовок і одна головна дія --}}
            <div role="status" class="flex flex-col items-center text-center gap-4 py-4">
                <div class="relative size-52 flex items-center justify-center">
                    <span class="absolute inset-4 rounded-full bg-sun-soft" aria-hidden="true"></span>
                    <x-confetti :count="26" />
                    <x-mascot.cheer class="lapa-pop relative w-44" label="{{ __('Лапа радіє разом із вами') }}" />
                </div>
                <h1 class="text-3xl md:text-4xl">{{ __('Дякуємо! Анкету отримано') }}</h1>
                <p class="text-lg max-w-md"><strong>{{ $animal->display_name }}</strong> {{ __('уже чекає на знайомство з вами.') }}</p>
                <p class="text-muted max-w-md">{{ __('Адміністратор притулку звʼяжеться з вами найближчим часом обраним способом. Копію анкети ми надіслали на :email.', ['email' => $email]) }}</p>
                <p class="text-sm text-muted max-w-md">{{ __('Не бачите листа? Зачекайте кілька хвилин і перевірте папку «Спам» або «Промоакції». Якщо лист там, позначте його як «Не спам» – тоді наступні листи від притулку приходитимуть у «Вхідні».') }}</p>
                @guest
                    @if ($inCabinet)
                        {{-- Пошта належить наявному акаунту – анкета вже в кабінеті, пропонувати реєстрацію немає сенсу --}}
                        <div class="rounded-2xl bg-sun-soft p-5 flex flex-col items-center gap-2.5 max-w-md w-full mt-2">
                            <span class="font-semibold">{{ __('Анкета вже у вашому кабінеті') }}</span>
                            <span class="text-muted text-sm">{{ __('Увійдіть в акаунт, щоб стежити за її статусом.') }}</span>
                            <a href="{{ route('login') }}" class="btn-primary">{{ __('Увійти') }}</a>
                        </div>
                    @else
                    <div class="rounded-2xl bg-sun-soft p-5 flex flex-col items-center gap-2.5 max-w-md w-full mt-2">
                        <span class="font-semibold">{{ __('Хочете стежити за статусом анкети?') }}</span>
                        <span class="text-muted text-sm">{{ __('Увійдіть в акаунт з цією ж поштою – анкета вже чекатиме у вашому кабінеті. Акаунта ще немає? Створіть його: після підтвердження адреси анкета зʼявиться там автоматично.') }}</span>
                        <div class="flex flex-wrap justify-center gap-2">
                            <a href="{{ route('login') }}" class="btn-primary">{{ __('Увійти') }}</a>
                            <a href="{{ route('register') }}" class="btn-outline">{{ __('Створити акаунт') }}</a>
                        </div>
                    </div>
                    @endif
                @else
                    <a href="{{ route('cabinet') }}" class="btn-primary min-h-12 px-8 mt-2">{{ __('Перейти до кабінету') }}</a>
                @endguest
                <a href="{{ route('animals.index') }}" class="font-semibold text-primary hover:underline">{{ __('← До каталогу') }}</a>
            </div>
        @endif
    </section>
</div>
