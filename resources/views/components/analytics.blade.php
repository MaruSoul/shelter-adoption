@props(['id'])
{{--
    Google Analytics 4 зі згодою відвідувача: скрипт Google завантажується лише після натиску «Дозволити».
    Вибір зберігається в браузері; змінити його можна посиланням «Налаштування cookie» в підвалі.
--}}
<div x-data="{
        choice: null,
        init() {
            try { this.choice = localStorage.getItem('analytics-consent') } catch (e) {}
            if (this.choice === 'granted') this.load();
            window.addEventListener('analytics-settings', () => this.choice = null);
        },
        decide(value) {
            this.choice = value;
            try { localStorage.setItem('analytics-consent', value) } catch (e) {}
            if (value === 'granted') this.load();
        },
        load() {
            if (window.gtag) return;
            window.dataLayer = window.dataLayer || [];
            window.gtag = function () { dataLayer.push(arguments) };
            gtag('js', new Date());
            gtag('config', @js($id), { anonymize_ip: true });
            const s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(@js($id));
            document.head.appendChild(s);
        },
     }">
    <div x-show="choice === null" x-cloak role="region" aria-label="{{ __('Згода на аналітику') }}"
         class="fixed inset-x-3 bottom-3 z-40 mx-auto max-w-2xl card p-5 shadow-lg flex flex-wrap items-center gap-4">
        <p class="grow basis-72 text-sm">{{ __('Ми хочемо рахувати відвідування сайту (Google Analytics), щоб розуміти, що допомагає тваринам знаходити дім. Без вашої згоди нічого не збирається.') }}
            <a href="{{ route('privacy') }}" class="text-primary underline">{{ __('Детальніше') }}</a></p>
        <div class="flex gap-2">
            <button type="button" x-on:click="decide('denied')" class="btn-ghost min-h-10">{{ __('Відмовитись') }}</button>
            <button type="button" x-on:click="decide('granted')" class="btn-primary min-h-10">{{ __('Дозволити') }}</button>
        </div>
    </div>
</div>
