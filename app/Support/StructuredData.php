<?php

namespace App\Support;

use App\Models\Animal;
use App\Models\Post;
use App\Models\ShelterSetting;

/**
 * Розмітка schema.org у форматі JSON-LD: допомагає пошуковикам показувати
 * притулок, статті й навігаційні ланцюжки у видачі.
 */
final class StructuredData
{
    /** Притулок як організація (тип AnimalShelter) з контактами й соцмережами. */
    public static function shelter(ShelterSetting $shelter): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'AnimalShelter',
            'name' => $shelter->tr('name'),
            'description' => $shelter->tr('tagline'),
            'url' => route('home'),
            'logo' => url('/logo.png'),
            'telephone' => $shelter->phone,
            'email' => $shelter->email,
            'address' => $shelter->address ? ['@type' => 'PostalAddress', 'streetAddress' => $shelter->tr('address'), 'addressCountry' => 'UA'] : null,
            'sameAs' => array_column($shelter->socialLinks(), 'url') ?: null,
        ]);
    }

    /** @param  array<string, string>  $items  назва => адреса */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->keys()->map(fn (string $name, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $name,
                'item' => $items[$name],
            ])->all(),
        ];
    }

    public static function animalBreadcrumbs(Animal $animal): array
    {
        return self::breadcrumbs([
            __('Головна') => route('home'),
            $animal->species->label('name_plural') => route('animals.index', ['species' => $animal->species->slug]),
            $animal->display_name => route('animals.show', $animal),
        ]);
    }

    public static function article(Post $post, ShelterSetting $shelter): array
    {
        $publisher = ['@type' => 'Organization', 'name' => $shelter->tr('name'), 'logo' => ['@type' => 'ImageObject', 'url' => url('/logo.png')]];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->tr('title'),
            'description' => $post->tr('excerpt'),
            'image' => $post->coverUrl() ? url($post->coverUrl()) : null,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'articleSection' => $post->category->label(),
            'timeRequired' => 'PT'.$post->readingMinutes().'M',
            // Мова, якою стаття фактично показана: англійська лише тоді, коли перекладено все
            'inLanguage' => app()->getLocale() === 'en' && $post->englishState() === 'full' ? 'en' : 'uk',
            'mainEntityOfPage' => route('blog.show', $post),
            'author' => $publisher,
            'publisher' => $publisher,
        ]);
    }
}
