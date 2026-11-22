<?php

namespace App\Enums;

use App\Rules\Iban;
use App\Rules\MonobankJarUrl;
use App\Support\Donations\DonationField;
use Closure;

/**
 * Способи фінансової допомоги притулку.
 *
 * Усе, чим способи відрізняються, описано тут: назва, поля з перевірками, які поля можна скопіювати,
 * шаблон показу на сайті. Форма в панелі (App\Livewire\Admin\Donations) і вкладки на сайті
 * будуються з цього опису, тож новий спосіб – це новий варіант enum і один шаблон у
 * resources/views/components/donations/.
 */
enum DonationType: string
{
    case Monobank = 'monobank';
    case Requisites = 'requisites';
    case PayPal = 'paypal';
    case Link = 'link';

    /** Назва способу в панелі. */
    public function label(): string
    {
        return match ($this) {
            self::Monobank => 'Банка Monobank',
            self::Requisites => 'Реквізити (ФОП, IBAN)',
            self::PayPal => 'PayPal',
            self::Link => 'Інше посилання',
        };
    }

    /** Підказка в панелі: що це і де взяти дані. */
    public function hint(): string
    {
        return match ($this) {
            self::Monobank => 'Кнопка «Поповнити банку» веде на сторінку банки, де можна заплатити карткою будь-якого банку, Apple Pay чи Google Pay.',
            self::Requisites => 'Реквізити для переказу з будь-якого банку. Біля кожного рядка на сайті буде кнопка «Копіювати».',
            self::PayPal => 'Для допомоги з-за кордону: адреса PayPal або посилання paypal.me.',
            self::Link => 'Будь-який інший сервіс оплати чи збору (Portmone, LiqPay, Patreon…): назва вкладки й посилання.',
        };
    }

    /** Назва вкладки на сайті, якщо адміністратор не задав власну. */
    public function defaultTitle(): string
    {
        return match ($this) {
            self::Monobank => 'Monobank',
            self::Requisites => __('За реквізитами'),
            self::PayPal => 'PayPal',
            self::Link => __('Інший спосіб'),
        };
    }

    /** Чи назва вкладки обовʼязкова (для «іншого посилання» без неї незрозуміло, що це за сервіс). */
    public function requiresTitle(): bool
    {
        return $this === self::Link;
    }

    /** @return list<DonationField> */
    public function fields(): array
    {
        return match ($this) {
            self::Monobank => [
                new DonationField('url', 'Посилання на банку', rules: [new MonobankJarUrl],
                    placeholder: 'https://send.monobank.ua/jar/…', hint: 'У застосунку Monobank: «Банка» → «Поділитися».', inputType: 'url'),
            ],
            self::Requisites => [
                new DonationField('recipient', 'Отримувач', placeholder: 'ФОП Іваненко Іван Іванович', copyable: true),
                new DonationField('iban', 'IBAN', rules: [new Iban], placeholder: 'UA21 3223 1300 0002 6007 2335 6600 1', copyable: true),
                new DonationField('code', 'ЄДРПОУ або ІПН', rules: ['regex:/^\d{8}(\d{2})?$/'],
                    placeholder: '12345678', hint: 'ЄДРПОУ організації – 8 цифр, ІПН ФОП – 10 цифр.', copyable: true),
                new DonationField('bank', 'Банк', required: false, placeholder: 'АТ «Універсал Банк»', copyable: true),
                new DonationField('purpose', 'Призначення платежу', required: false,
                    placeholder: 'Благодійна допомога на утримання тварин', hint: 'Банки приймають його українською, тож воно однакове для обох мов сайту.', copyable: true),
            ],
            self::PayPal => [
                new DonationField('account', 'Адреса PayPal або посилання paypal.me', rules: [self::paypalAccountRule()],
                    placeholder: 'shelter@example.com або https://paypal.me/…', copyable: true),
            ],
            self::Link => [
                new DonationField('url', 'Посилання', rules: ['url:https'], placeholder: 'https://…', inputType: 'url'),
            ],
        };
    }

    /** Шаблон вмісту вкладки на сайті. */
    public function view(): string
    {
        return 'components.donations.'.$this->value;
    }

    /** Приводить значення до єдиного вигляду перед збереженням (IBAN без пробілів, без зайвих пробілів по краях). */
    public function normalize(array $details): array
    {
        $details = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $details);
        if ($this === self::Requisites && isset($details['iban'])) {
            $details['iban'] = Iban::normalize($details['iban']);
        }

        return array_filter($details, fn ($v) => $v !== null && $v !== '');
    }

    private static function paypalAccountRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            $value = trim((string) $value);
            $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            $isLink = (bool) preg_match('~^https://(www\.)?paypal\.(me|com)/~i', $value);
            if (! $isEmail && ! $isLink) {
                $fail('Вкажіть адресу e-mail акаунта PayPal або посилання https://paypal.me/…');
            }
        };
    }
}
