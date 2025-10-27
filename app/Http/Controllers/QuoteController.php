<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuoteController extends Controller
{
    private $messages = [
        'popup_success' => [
            'id' => 'Quote popup berhasil diambil.',
            'en' => 'Popup quote retrieved successfully.',
        ],
        'carousel_success' => [
            'id' => 'Quote carousel berhasil diambil.',
            'en' => 'Carousel quotes retrieved successfully.',
        ],
    ];

    private function msg($key)
    {
        $lang = request()->query('lang', 'en'); // ← default ke ENGLISH
        return $this->messages[$key][$lang] ?? $this->messages[$key]['en'];
    }

    private $popupQuotes = [
        // (isi quotes kamu seperti sebelumnya)
        'id' => [/* ... */],
        'en' => [/* ... */]
    ];

    private $carouselQuotes = [
        'id' => [/* ... */],
        'en' => [/* ... */]
    ];

    // === GET 1 RANDOM POPUP QUOTE ===
    public function getPopupQuote(Request $request)
    {
        $lang = $request->query('lang', 'en'); // ← default EN
        $quotes = $this->popupQuotes[$lang] ?? $this->popupQuotes['en'];
        $quote = $quotes[array_rand($quotes)];

        return response()->json([
            'message' => $this->msg('popup_success'),
            'data' => [
                'text' => $quote,
                'lang' => $lang,
                'type' => 'popup',
            ],
        ]);
    }

    // === GET 5 RANDOM CAROUSEL QUOTES ===
    public function getCarouselQuotes(Request $request)
    {
        $lang = $request->query('lang', 'en'); // ← default EN
        $quotes = collect($this->carouselQuotes[$lang] ?? $this->carouselQuotes['en'])
            ->shuffle()
            ->take(5)
            ->values()
            ->map(fn($q) => ['text' => $q, 'lang' => $lang, 'type' => 'carousel']);

        return response()->json([
            'message' => $this->msg('carousel_success'),
            'data' => $quotes,
        ]);
    }
}
