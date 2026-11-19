<?php

namespace App\Livewire\Admin;

use App\Models\SeoPage;
use App\Models\ShelterSetting;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * SEO сторінок: Title, Description, H1 і SEO-текст для головної, каталогу, розділів видів і блогу,
 * а також підключення Google Search Console й Google Analytics.
 * Порожні поля не заміняють типових заголовків сайту, тож заповнювати можна поступово.
 */
class Seo extends Component
{
    #[Url(as: 'storinka')]
    public string $page = 'home';

    public array $form = [];

    public array $google = ['google_site_verification' => '', 'google_analytics_id' => ''];

    public ?string $flash = null;

    public function mount(): void
    {
        $this->loadPage();
        $settings = ShelterSetting::current();
        $this->google = [
            'google_site_verification' => (string) $settings->google_site_verification,
            'google_analytics_id' => (string) $settings->google_analytics_id,
        ];
    }

    public function updatedPage(): void
    {
        $this->flash = null;
        $this->resetValidation();
        $this->loadPage();
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.title' => ['nullable', 'string', 'max:160'], 'form.title_en' => ['nullable', 'string', 'max:160'],
            'form.description' => ['nullable', 'string', 'max:300'], 'form.description_en' => ['nullable', 'string', 'max:300'],
            'form.h1' => ['nullable', 'string', 'max:160'], 'form.h1_en' => ['nullable', 'string', 'max:160'],
            'form.text' => ['nullable', 'string', 'max:20000'], 'form.text_en' => ['nullable', 'string', 'max:20000'],
        ], [], [
            'form.title' => 'Title', 'form.description' => 'Description', 'form.h1' => 'H1', 'form.text' => 'SEO-текст',
        ])['form'];

        SeoPage::updateOrCreate(['key' => $this->page], array_map(fn ($v) => blank($v) ? null : trim($v), $data));
        $this->flash = 'SEO сторінки «'.SeoPage::pages()[$this->page].'» збережено.';
    }

    public function saveGoogle(): void
    {
        $data = $this->validate([
            // Код підтвердження – значення content з мета-тегу, який дає Search Console
            'google.google_site_verification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            'google.google_analytics_id' => ['nullable', 'string', 'max:30', 'regex:/^G-[A-Z0-9]+$/'],
        ], [
            'google.google_site_verification.regex' => 'Вставте лише код із content="…", без лапок і тегу.',
            'google.google_analytics_id.regex' => 'Ідентифікатор Google Analytics 4 має вигляд G-XXXXXXXXXX.',
        ])['google'];

        $settings = ShelterSetting::query()->first() ?? new ShelterSetting(['name' => config('app.name'), 'notification_email' => config('mail.from.address')]);
        $settings->fill(array_map(fn ($v) => blank($v) ? null : trim($v), $data))->save();
        $this->flash = 'Підключення Google збережено.';
    }

    private function loadPage(): void
    {
        if (! array_key_exists($this->page, SeoPage::pages())) {
            $this->page = 'home';
        }
        $seo = SeoPage::query()->where('key', $this->page)->first() ?? new SeoPage;
        $this->form = collect(SeoPage::TRANSLATABLE)
            ->flatMap(fn ($f) => [$f => (string) $seo->{$f}, $f.'_en' => (string) $seo->{$f.'_en'}])->all();
    }

    /** Адреса сторінки, яку зараз налаштовують, – для попереднього перегляду в пошуку. */
    public function pageUrl(string $locale = 'uk'): string
    {
        $url = app('url');

        return match (true) {
            $this->page === 'home' => $url->routeIn($locale, 'home'),
            $this->page === 'animals' => $url->routeIn($locale, 'animals.index'),
            $this->page === 'blog' => $url->routeIn($locale, 'blog.index'),
            default => $url->routeIn($locale, 'animals.species', substr($this->page, strlen('species:'))),
        };
    }

    public function render()
    {
        return view('livewire.admin.seo', ['pages' => SeoPage::pages()])->title('SEO');
    }
}
