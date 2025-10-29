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
        $lang = request()->query('lang', 'en'); // Default English
        return $this->messages[$key][$lang] ?? $this->messages[$key]['en'];
    }

    private $popupQuotes = [
        'id' => [
            "Setiap hari adalah kesempatan baru untuk bersyukur.",
            "Kamu cukup, bahkan ketika kamu merasa tidak.",
            "Hidupmu berharga, jangan ragu untuk percaya itu.",
            "Tersenyumlah, karena dunia butuh cahayamu.",
            "Kegagalan hanyalah langkah menuju keberhasilan.",
            "Percaya pada proses, semua akan indah pada waktunya.",
            "Hujan membawa pelangi, begitu juga ujian membawa pelajaran.",
            "Kamu lebih kuat dari yang kamu kira.",
            "Setiap langkah kecil tetap berarti.",
            "Kamu layak mendapatkan cinta dan kedamaian.",
            "Syukuri hal-hal kecil, karena di sanalah kebahagiaan tumbuh.",
            "Tenang, kamu sedang menuju tempat yang lebih baik.",
            "Beristirahat bukan berarti menyerah.",
            "Setiap hari adalah bab baru dalam hidupmu.",
            "Kamu pantas mendapatkan hal-hal baik.",
            "Bersyukurlah atas setiap napas yang kamu ambil.",
            "Teruslah berbuat baik, meski kecil.",
            "Jangan takut gagal, itu bagian dari belajar.",
            "Kamu adalah inspirasi bagi orang lain tanpa kamu sadari.",
            "Hidup bukan soal kesempurnaan, tapi kemajuan.",
            "Pelan tapi pasti, kamu akan sampai juga.",
            "Kebaikan selalu kembali kepada yang memberi.",
            "Setiap tantangan membuatmu lebih kuat.",
            "Kamu sedang tumbuh, dan itu luar biasa.",
            "Lihat sekelilingmu — masih banyak hal indah.",
            "Kamu pantas beristirahat dan merasa damai.",
            "Hari yang buruk bukan akhir dari cerita.",
            "Hargai dirimu sebagaimana kamu menghargai orang lain.",
            "Percaya bahwa hal baik sedang dalam perjalanan.",
            "Kamu adalah cahaya di tempat yang gelap.",
            "Senyum kecil bisa mengubah hari seseorang.",
            "Ketenangan datang dari hati yang bersyukur.",
            "Berani memulai berarti setengah perjalanan selesai.",
            "Kamu diciptakan dengan tujuan yang indah.",
            "Setiap pengalaman membentuk versi terbaik dirimu.",
            "Kamu cukup melakukan yang terbaik hari ini.",
            "Tidak ada langkah yang terlalu kecil jika itu ke arah mimpi.",
            "Kamu pantas merasa bahagia tanpa alasan tertentu.",
            "Biarkan dirimu berkembang dengan waktu.",
            "Kamu berhak atas kebahagiaanmu sendiri.",
            "Setiap pagi adalah awal yang baru.",
            "Keajaiban sering datang dalam bentuk sederhana.",
            "Cinta diri adalah bentuk keberanian tertinggi.",
            "Setiap hari kamu semakin kuat.",
            "Kamu tidak harus sempurna untuk dicintai.",
            "Berterima kasihlah, bahkan untuk hal kecil.",
            "Kamu melakukan yang terbaik — dan itu cukup.",
            "Setiap senyum adalah doa kecil untuk kebahagiaan.",
            "Percayalah, kamu sedang berada di jalur yang tepat.",
            "Hidup adalah tentang menikmati setiap momen.",
        ],
        'en' => [
            "Every day is a new chance to be grateful.",
            "You are enough, even when you feel you're not.",
            "Your life matters — believe in it.",
            "Smile, the world needs your light.",
            "Failure is just a step toward success.",
            "Trust the process; everything will bloom in time.",
            "Rain brings rainbows, just like challenges bring lessons.",
            "You’re stronger than you think.",
            "Every small step matters.",
            "You deserve love and peace.",
            "Be thankful for the little things — that’s where joy grows.",
            "Stay calm; you’re heading to better days.",
            "Resting doesn’t mean giving up.",
            "Each day is a new page in your story.",
            "You deserve good things.",
            "Be grateful for every breath you take.",
            "Keep doing good, even the small kind.",
            "Don’t fear failure — it’s part of learning.",
            "You inspire others without even knowing it.",
            "Life is about progress, not perfection.",
            "Slowly but surely, you’ll get there.",
            "Kindness always finds its way back.",
            "Every challenge makes you stronger.",
            "You are growing — and that’s beautiful.",
            "Look around — there’s still beauty everywhere.",
            "You deserve rest and peace.",
            "A bad day isn’t the end of your story.",
            "Value yourself as much as you value others.",
            "Believe that good things are on their way.",
            "You are a light in dark places.",
            "A simple smile can brighten someone’s day.",
            "Peace comes from a grateful heart.",
            "Being brave enough to start is already a win.",
            "You were created with a beautiful purpose.",
            "Every experience shapes your best self.",
            "Doing your best today is enough.",
            "No step is too small when it’s toward your dreams.",
            "You deserve to feel happy for no reason.",
            "Let yourself grow with time.",
            "You have the right to your own happiness.",
            "Every morning is a new beginning.",
            "Miracles often come in simple forms.",
            "Self-love is the greatest form of courage.",
            "Each day, you’re getting stronger.",
            "You don’t have to be perfect to be loved.",
            "Be thankful, even for the small things.",
            "You’re doing your best — and that’s enough.",
            "Every smile is a small prayer for joy.",
            "Trust that you’re on the right path.",
            "Life is about enjoying every moment.",
        ]
    ];

    private $carouselQuotes = [
        'id' => [
            "Kamu berharga sebagaimana adanya dirimu.",
            "Kebahagiaan datang dari hati yang bersyukur.",
            "Kamu bisa melalui hari ini dengan baik.",
            "Cahaya selalu muncul setelah gelap.",
            "Kamu tidak sendirian dalam perjalanan ini.",
            "Setiap hari adalah kesempatan untuk tumbuh.",
            "Bersikap baiklah, terutama pada dirimu sendiri.",
            "Tenang, semuanya akan baik-baik saja.",
            "Kamu pantas mendapatkan kedamaian batin.",
            "Langit cerah akan datang setelah badai.",
            "Percaya pada dirimu sendiri.",
            "Kamu kuat, bahkan saat merasa lemah.",
            "Bersyukurlah — selalu ada hal kecil yang indah.",
            "Setiap langkah adalah kemajuan.",
            "Kebaikan sekecil apa pun tetap berarti.",
            "Jangan lupakan betapa jauh kamu telah melangkah.",
            "Cintai prosesnya, bukan hanya hasilnya.",
            "Hidup adalah perjalanan, nikmati setiap momennya.",
            "Kamu sedang belajar, dan itu luar biasa.",
            "Kamu pantas merasa tenang hari ini.",
            "Setiap detik adalah kesempatan baru.",
            "Jangan biarkan masa lalu menahanmu.",
            "Beranilah untuk bermimpi besar.",
            "Hidupmu punya makna yang dalam.",
            "Kamu lebih dari cukup.",
            "Keajaiban datang saat kamu percaya.",
            "Bersyukurlah untuk hal-hal sederhana.",
            "Setiap momen memiliki makna tersendiri.",
            "Kamu pantas mendapatkan kebahagiaan.",
            "Ketenangan adalah kekuatan sejati.",
            "Kebaikan hatimu adalah kekuatanmu.",
            "Kamu sedang berada di jalur yang benar.",
            "Lihatlah sekelilingmu, dunia penuh keajaiban.",
            "Berhenti membandingkan; kamu unik.",
            "Kamu lebih kuat daripada rasa takutmu.",
            "Setiap hari adalah awal yang baru.",
            "Kamu pantas disayangi tanpa syarat.",
            "Bersyukurlah atas hal-hal yang kamu miliki.",
            "Satu langkah kecil bisa membawa perubahan besar.",
            "Kamu pantas untuk bahagia, hari ini dan setiap hari.",
            "Kebaikan selalu meninggalkan jejak yang indah.",
            "Jangan lupa istirahat; kamu manusia juga.",
            "Cinta dan ketulusan akan menemukan jalannya.",
            "Kamu membawa cahaya ke dunia ini.",
            "Hidupmu berharga dan bermakna.",
            "Kamu tidak sendiri, ada harapan di setiap langkah.",
            "Percaya pada keajaiban hari ini.",
            "Kamu pantas mendapatkan ketenangan batin.",
            "Nikmati perjalanan, bukan hanya tujuannya.",
            "Kamu sedang menjadi versi terbaikmu.",
        ],
        'en' => [
            "You are valuable just as you are.",
            "Happiness comes from a grateful heart.",
            "You can handle today just fine.",
            "Light always follows darkness.",
            "You are not alone on this journey.",
            "Every day is a chance to grow.",
            "Be kind, especially to yourself.",
            "Stay calm — everything will be okay.",
            "You deserve inner peace.",
            "Clear skies follow every storm.",
            "Believe in yourself.",
            "You are strong, even when you feel weak.",
            "Be thankful — there’s always beauty in small things.",
            "Every step is progress.",
            "Even the smallest kindness matters.",
            "Don’t forget how far you’ve come.",
            "Love the process, not just the result.",
            "Life is a journey — enjoy every moment.",
            "You’re learning, and that’s amazing.",
            "You deserve to feel peaceful today.",
            "Every second is a new opportunity.",
            "Don’t let the past hold you back.",
            "Dare to dream big.",
            "Your life has deep meaning.",
            "You are more than enough.",
            "Miracles happen when you believe.",
            "Be thankful for the simple things.",
            "Every moment has its meaning.",
            "You deserve happiness.",
            "Calmness is true strength.",
            "Your kindness is your strength.",
            "You are on the right path.",
            "Look around — the world is full of wonder.",
            "Stop comparing; you are unique.",
            "You are stronger than your fears.",
            "Every day is a new beginning.",
            "You deserve to be loved unconditionally.",
            "Be grateful for what you have.",
            "One small step can make a big change.",
            "You deserve happiness today and always.",
            "Kindness always leaves a beautiful mark.",
            "Don’t forget to rest; you’re human too.",
            "Love and sincerity always find their way.",
            "You bring light to this world.",
            "Your life is precious and meaningful.",
            "You are not alone — hope walks with you.",
            "Believe in today’s miracles.",
            "You deserve inner calm and peace.",
            "Enjoy the journey, not just the destination.",
            "You are becoming your best self.",
        ]
    ];

    public function getPopupQuote(Request $request)
    {
        $lang = $request->query('lang', 'en');
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

    public function getCarouselQuotes(Request $request)
    {
        $lang = $request->query('lang', 'en');
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
