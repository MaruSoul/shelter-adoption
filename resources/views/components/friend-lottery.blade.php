@props(['pool'])
{{-- Гра «Хто мій друг?»: коло прокручується і показує випадкову тварину, яка шукає дім. --}}
<div
    x-data="{
        pool: @js($pool),
        current: null,
        spinning: false,
        last: -1,
        spin() {
            if (this.spinning) return;
            this.current = null;
            this.spinning = true;
            let i = Math.floor(Math.random() * this.pool.length);
            if (this.pool.length > 1 && i === this.last) i = (i + 1) % this.pool.length;
            this.last = i;
            new Image().src = this.pool[i].photo;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            setTimeout(() => {
                this.current = this.pool[i];
                this.spinning = false;
            }, reduce ? 0 : 1100);
        },
    }"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    {{-- Коло і картка лежать в одній клітинці сітки: блок бере висоту більшого з них, тож картка не налазить на сусідні секції --}}
    <div class="relative mx-auto w-full max-w-[520px] grid place-items-center">
        {{-- Коло з кнопкою --}}
        <div x-show="!current" class="col-start-1 row-start-1 relative w-full aspect-square rounded-full bg-sun overflow-hidden shadow-xl shadow-sun/30">
            <svg viewBox="0 0 200 200" class="absolute inset-0 size-full" :class="spinning && 'lottery-spinning'" aria-hidden="true">
                <defs><path id="lottery-ring" x-ref="ringPath" d="M26.7 126.7 A 78 78 0 1 1 173.3 126.7"/></defs>
                {{--
                    Напис має рівно лягти на дугу будь-якою мовою. textLength на textPath Firefox не підтримує
                    (кінець напису просто обрізається), тому розмір і розрядку підбираємо скриптом за фактичною
                    довжиною тексту; без скрипта лишається дрібніший шрифт, що вміщається.
                --}}
                <text x-ref="ringText" font-family="Nunito, sans-serif" font-weight="800" font-size="11" fill="#1E1A2E" letter-spacing="1"
                      x-init="
                        const fit = () => {
                            const t = $refs.ringText, length = $refs.ringPath.getTotalLength() * 0.985
                            t.setAttribute('font-size', 15); t.setAttribute('letter-spacing', 0)
                            let width = t.getComputedTextLength()
                            if (width > length) { t.setAttribute('font-size', (15 * length / width).toFixed(2)); width = t.getComputedTextLength() }
                            t.setAttribute('letter-spacing', Math.max(0, (length - width) / t.textContent.trim().length).toFixed(2))
                        }
                        document.fonts ? document.fonts.ready.then(fit) : fit()
                      ">
                    <textPath href="#lottery-ring">{{ __('• ХТО МІЙ ДРУГ? • ГРА-ЗНАЙОМСТВО •') }}</textPath>
                </text>
            </svg>
            <div class="absolute inset-0 flex items-start justify-center pt-[30%]">
                <button type="button" @click="spin()" :disabled="spinning"
                        class="btn bg-card text-ink shadow-lg min-h-14 px-8 text-lg font-display font-extrabold hover:bg-tint-1 relative z-10">
                    <span x-text="spinning ? @js(__('Шукаємо…')) : @js(__('Хто мій друг?'))">{{ __('Хто мій друг?') }}</span>
                </button>
            </div>
            <x-mascot.peek class="absolute -bottom-[3%] left-1/2 -translate-x-1/2 w-[62%]" label="{{ __('Лапа визирає з кола') }}" />
        </div>

        {{-- Результат --}}
        <div class="col-start-1 row-start-1 w-full flex items-center justify-center" aria-live="polite">
            <template x-if="current">
                <div class="lottery-pop relative w-[82%] sm:w-[80%] mt-16 mb-10">
                    <div class="card overflow-hidden shadow-2xl shadow-primary/15 -rotate-2">
                        <div class="relative">
                            <a :href="current.url" class="block aspect-[4/3] bg-tint-1">
                                <img :src="current.photo" :alt="current.name" class="size-full object-cover">
                            </a>
                            {{-- Сердечко там само, де й на картках каталогу --}}
                            <button type="button" x-on:click="$store.favorites.toggle(current.id)"
                                    x-bind:aria-pressed="$store.favorites.has(current.id) ? 'true' : 'false'"
                                    x-bind:aria-label="($store.favorites.has(current.id) ? @js(__('Прибрати з обраних:')) + ' ' : @js(__('Додати в обрані:')) + ' ') + current.name"
                                    class="fav-btn absolute right-3 top-3 size-11 rounded-full bg-card/85 backdrop-blur shadow hover:bg-card flex items-center justify-center transition-transform active:scale-90">
                                <svg viewBox="0 0 24 24" class="size-6 text-danger" aria-hidden="true">
                                    <path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 8 3.4 4.5 6.9 4.5c2 0 3.6 1.1 5.1 3 1.5-1.9 3.1-3 5.1-3 3.5 0 5.5 3.5 4.2 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"
                                          fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"
                                          x-bind:fill="$store.favorites.has(current.id) ? 'currentColor' : 'none'"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-5 flex flex-col gap-3 bg-sun-soft">
                            <div>
                                <p class="font-display font-extrabold text-3xl" x-text="current.name"></p>
                                <p class="text-muted" x-text="current.meta"></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a :href="current.adopt" class="btn-primary">{{ __('Взяти додому') }}</a>
                                <a :href="current.url" class="btn bg-card text-ink hover:bg-surface">{{ __('Детальніше') }}</a>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="spin()"
                            class="absolute -right-3 -bottom-6 sm:-right-6 size-16 rounded-full bg-card shadow-lg flex flex-col items-center justify-center text-primary hover:bg-tint-1"
                            aria-label="{{ __('Ще раз') }}">
                        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                        <span class="text-[11px] font-semibold leading-none mt-0.5">{{ __('Ще раз') }}</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
