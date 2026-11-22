@props(['methods'])
{{--
    Способи фінансової допомоги вкладками. Кожна вкладка показує свій шаблон (DonationType::view()),
    тож тут немає перевірок конкретних способів. На телефоні вкладки прокручуються вбік.
--}}
@if ($methods->isNotEmpty())
    @php($uid = 'donate-'.\Illuminate\Support\Str::random(6))
    <div x-data="{ active: 0 }" {{ $attributes->class('rounded-[2rem] overflow-hidden bg-card border border-line shadow-sm') }}>
        <div role="tablist" aria-label="{{ __('Способи допомогти') }}"
             class="flex gap-2 overflow-x-auto bg-primary p-2 [scrollbar-width:none]"
             x-on:keydown.right.prevent="active = (active + 1) % {{ $methods->count() }}; $nextTick(() => $el.querySelectorAll('[role=tab]')[active].focus())"
             x-on:keydown.left.prevent="active = (active - 1 + {{ $methods->count() }}) % {{ $methods->count() }}; $nextTick(() => $el.querySelectorAll('[role=tab]')[active].focus())">
            @foreach ($methods as $i => $method)
                <button type="button" role="tab" id="{{ $uid }}-tab-{{ $i }}" aria-controls="{{ $uid }}-panel-{{ $i }}"
                        x-on:click="active = {{ $i }}" x-bind:aria-selected="active === {{ $i }}" x-bind:tabindex="active === {{ $i }} ? 0 : -1"
                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                        class="shrink-0 rounded-full px-5 min-h-11 font-semibold whitespace-nowrap transition-colors"
                        x-bind:class="active === {{ $i }} ? 'bg-sun text-ink' : 'text-primary-ink hover:bg-white/15'"
                        @unless ($method->isShownInLocale('title')) lang="{{ app()->getLocale() === 'en' ? 'uk' : 'en' }}" @endunless>{{ $method->tabTitle() }}</button>
            @endforeach
        </div>
        @foreach ($methods as $i => $method)
            <div role="tabpanel" id="{{ $uid }}-panel-{{ $i }}" aria-labelledby="{{ $uid }}-tab-{{ $i }}" tabindex="0"
                 x-show="active === {{ $i }}" @if ($i > 0) hidden @endif x-bind:hidden="active !== {{ $i }}"
                 class="p-6 md:p-10 flex flex-col gap-5">
                @if ($note = $method->tr('note'))
                    <p class="text-lg text-muted max-w-2xl whitespace-pre-line" @unless ($method->isShownInLocale('note')) lang="{{ app()->getLocale() === 'en' ? 'uk' : 'en' }}" @endunless>{{ $note }}</p>
                @endif
                @include($method->type->view(), ['method' => $method])
            </div>
        @endforeach
    </div>
@endif
