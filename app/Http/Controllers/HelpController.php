<?php

namespace App\Http\Controllers;

use App\Models\DonationMethod;
use App\Models\SeoPage;
use App\Models\ShelterSetting;
use Illuminate\View\View;

/** Сторінка «Як допомогти»: способи фінансової допомоги, звіти й інші способи підтримати притулок. */
class HelpController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.help', [
            'methods' => DonationMethod::query()->shown()->get(),
            'shelter' => ShelterSetting::current(),
            'seo' => SeoPage::for('help'),
        ]);
    }
}
