{{--
    Лічильник англійської версії: скільки двомовних полів сторінки з українським текстом уже мають переклад.
    Шукає поля [data-bi] у найближчому [data-bi-scope]; кнопка веде до першого неперекладеного поля.
--}}
<div {{ $attributes->class('flex flex-wrap items-center gap-2') }}
     x-data="{
        done: 0, total: 0,
        scope() { return this.$el.closest('[data-bi-scope]') ?? document },
        pairs() {
            return [...this.scope().querySelectorAll('[data-bi]:not([data-bi-optional])')]
                .map(b => ({ uk: b.querySelector('.bi-uk'), en: b.querySelector('.bi-en') }))
                .filter(p => p.uk && p.en && p.uk.value.trim() !== '')
        },
        count() {
            const pairs = this.pairs()
            this.total = pairs.length
            this.done = pairs.filter(p => p.en.value.trim() !== '').length
        },
        next() {
            const p = this.pairs().find(p => p.en.value.trim() === '')
            if (! p) return
            p.en.scrollIntoView({ behavior: 'smooth', block: 'center' })
            p.en.focus({ preventScroll: true })
        },
     }"
     x-init="
        setTimeout(() => count(), 60)
        scope().addEventListener('input', () => count())
        Livewire.hook('commit', ({ succeed }) => succeed(() => setTimeout(() => count(), 60)))
     "
     x-show="total > 0" x-cloak role="status" aria-live="polite">
    <span class="chip bg-success-soft text-success gap-1.5" x-show="done === total">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
        Англійська версія заповнена
    </span>
    <span class="chip bg-sun-soft text-accent" x-show="done < total">
        Англійська: <span class="mx-1" x-text="done + ' з ' + total"></span>
    </span>
    <button type="button" class="text-sm font-semibold text-primary hover:underline" x-show="done < total" x-on:click="next()">До неперекладеного →</button>
</div>
