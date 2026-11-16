@props(['pool'])
{{-- Гра «Хто мій друг?»: коло прокручується і показує випадкову тварину, яка шукає дім. --}}
<div
    x-data="{
        pool: @js($pool),
        current: null,
        spinning: false,
        last: -1,
        tint: 0,
        tints: ['bg-tint-2', 'bg-tint-4', 'bg-tint-3', 'bg-tint-1'],
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
                this.tint = (this.tint + 1) % this.tints.length;
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
                <defs><path id="lottery-ring" d="M26.7 126.7 A 78 78 0 1 1 173.3 126.7"/></defs>
                <text font-family="Nunito, sans-serif" font-weight="800" font-size="15" fill="#1E1A2E" letter-spacing="1">
                    <textPath href="#lottery-ring" textLength="296" lengthAdjust="spacing">• ХТО МІЙ ДРУГ? • ГРА-ЗНАЙОМСТВО •</textPath>
                </text>
            </svg>
            <div class="absolute inset-0 flex items-start justify-center pt-[30%]">
                <button type="button" @click="spin()" :disabled="spinning"
                        class="btn bg-card text-ink shadow-lg min-h-14 px-8 text-lg font-display font-extrabold hover:bg-tint-1 relative z-10">
                    <span x-text="spinning ? 'Шукаємо…' : 'Хто мій друг?'">Хто мій друг?</span>
                </button>
            </div>
            <x-mascot.peek class="absolute -bottom-[3%] left-1/2 -translate-x-1/2 w-[62%]" label="Лапа визирає з кола" />
        </div>

        {{-- Результат --}}
        <div class="col-start-1 row-start-1 w-full flex items-center justify-center" aria-live="polite">
            <template x-if="current">
                <div class="lottery-pop relative w-[82%] sm:w-[80%] mt-16 mb-10">
                    <div class="card overflow-hidden shadow-2xl shadow-primary/15 -rotate-2">
                        <a :href="current.url" class="block aspect-[4/3] bg-tint-1">
                            <img :src="current.photo" :alt="current.name" class="size-full object-cover">
                        </a>
                        <div class="p-5 flex flex-col gap-3" :class="tints[tint]">
                            <div>
                                <p class="font-display font-extrabold text-3xl" x-text="current.name"></p>
                                <p class="text-muted" x-text="current.meta"></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a :href="current.adopt" class="btn-primary">Взяти додому</a>
                                <a :href="current.url" class="btn bg-card text-ink hover:bg-surface">Детальніше</a>
                            </div>
                        </div>
                    </div>
                    <x-mascot.cheer class="absolute -left-6 -top-14 w-24 sm:-left-10 sm:w-28 rotate-[-8deg] pointer-events-none" label="Лапа радіє" />
                    <button type="button" @click="spin()"
                            class="absolute -right-3 -bottom-6 sm:-right-6 size-16 rounded-full bg-card shadow-lg flex flex-col items-center justify-center text-primary hover:bg-tint-1"
                            aria-label="Ще раз">
                        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                        <span class="text-[11px] font-semibold leading-none mt-0.5">Ще раз</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
