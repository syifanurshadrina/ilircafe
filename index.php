<?php
// === SISTEM KOMENTAR FUNGSIONAL ===
$commentFile = 'comments.json';
$feedbackMsg = '';

// 1. Logika hapus komentar
if (isset($_GET['delete_comment'])) {
    $indexToDelete = (int)$_GET['delete_comment'];
    if (file_exists($commentFile)) {
        $currentComments = json_decode(file_get_contents($commentFile), true) ?? [];
        
        // Kita menggunakan array_reverse untuk tampilan, jadi kita perlu menghitung indeks aslinya
        // Jika $allComments adalah hasil array_reverse, maka indeks ke-0 adalah item terakhir
        $reversedComments = array_reverse($currentComments);
        
        if (isset($reversedComments[$indexToDelete])) {
            // Hapus berdasarkan ID unik atau buat mapping sederhana
            // Cara termudah: cari item di array asli berdasarkan data yang cocok
            $itemToDelete = $reversedComments[$indexToDelete];
            
            foreach ($currentComments as $key => $comment) {
                if ($comment === $itemToDelete) {
                    unset($currentComments[$key]);
                    break;
                }
            }
            
            file_put_contents($commentFile, json_encode(array_values($currentComments), JSON_PRETTY_PRINT));
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// 2. Logika simpan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_suggestion'])) {
    $name = htmlspecialchars(trim($_POST['name'] ?? 'Anonymous'));
    $suggestion = htmlspecialchars(trim($_POST['suggestion'] ?? ''));
    $rating = (int)($_POST['rating'] ?? 5);

    if (!empty($name) && !empty($suggestion)) {
        $currentComments = [];
        if (file_exists($commentFile)) {
            $currentComments = json_decode(file_get_contents($commentFile), true) ?? [];
        }
        
        $currentComments[] = [
            'name' => $name,
            'suggestion' => $suggestion,
            'rating' => $rating,
            'date' => date('d M Y, H:i')
        ];
        
        file_put_contents($commentFile, json_encode($currentComments, JSON_PRETTY_PRINT));
        $feedbackMsg = "Terima kasih, saran Anda berhasil dikirim!";
    }
}

// 3. Ambil data untuk ditampilkan
$allComments = [];
if (file_exists($commentFile)) {
    $allComments = array_reverse(json_decode(file_get_contents($commentFile), true) ?? []);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilir Cafe - Blog Review</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        @import url('https://fonts.cdnfonts.com/css/brittany-signature-script');
                
        @font-face {
            font-family: 'Brittany Signature';
            src: url('fonts/BrittanySignature.woff2') format('woff2'),
                 url('fonts/BrittanySignature.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        /* Latar Belakang Menyatu seperti desain */
        body {
            background-color: #fdfaf6;
            color: #2c2520;
            line-height: 1.6;
        }

        .logo {
            color: #c39a6b;
            font-family: 'Times New Roman', Times, serif;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s;
        }

        nav a:hover { color: #c39a6b; }

        .visit-btn {
            border: 1px solid #c39a6b;
            color: #fff;
            background-color: #c39a6b;
            padding: 8px 16px;
            text-decoration: none;
            font-size: 13px;
            text-transform: uppercase;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .visit-btn:hover {
            background-color: #fff;
            color: #c39a6b;
        }

        /* HERO BANNER */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('header.jpeg') no-repeat center center/cover;
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 10%;
            color: #fff;
            position: relative;
        }

        .hero-tag {
            color: #c39a6b;
            font-family: 'Brittany Signature Script', 'Brittany Signature', cursive;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .hero h1 {
            font-size: 42px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: 400;
            max-width: 700px;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        .hero .from-cafe {
            font-family: 'Brittany Signature Script', 'Brittany Signature', cursive;
            color: #c39a6b;
            font-size: 40px;
        }

        .hero-meta {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            background: rgba(12, 26, 48, 0.9);
            padding: 12px 25px;
            border-radius: 15px;
            max-width: fit-content;
            border-left: none;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .meta-item i {
            font-size: 26px;
            color: #c39a6b;
        }

        .meta-text {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }

        .meta-title {
            font-weight: bold;
            color: #ffffff;
            font-size: 13px;
        }

        .meta-sub {
            color: #c39a6b;
            font-size: 12px;
        }

        .meta-divider {
            width: 1px;
            height: 28px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 0 10px;
        }

        .swiper { 
            width: 100%; 
            height: 350px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
        }

        .swiper-slide img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        /* CONTAINER UTAMA (Menghilangkan kotak putih per bagian) */
        .container {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 60px;
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        /* MAIN CONTENT LAYOUT */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 70px; /* Jarak yang luas antar paragraf */
        }

        .section-block {
            /* Dihapus background putih dan shadow agar menyatu dengan background body */
            background: transparent; 
            padding: 0;
            border-radius: 0;
            box-shadow: none;
        }

        /* GRID LAYOUT ALTERNATIF */
        .grid-left-text {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 30px;
            align-items: start;
        }

        .grid-right-text {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 30px;
            align-items: center;
        }

        .grid-quote-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: center;
        }

        /* TYPOGRAPHY BAGIAN (HEADER & ANGKA) */
        .section-header {
            display: flex;
            align-items: baseline;
            position: relative;
            margin-bottom: 10px;
        }

        .num-title {
            font-size: 85px;
            color: #f1ebd8;
            font-family: 'Times New Roman', serif;
            line-height: 0.7;
            margin-right: 15px;
            position: relative;
            z-index: 0;
        }

        .text-content {
            position: relative;
            z-index: 1;
        }

        .header-text-group {
            display: flex;
            flex-direction: column;
            position: absolute;
            left: 65px; /* Menggeser teks agar menimpa angka sedikit */
            top: 5px;
        }

        .section-tag {
            font-size: 11px;
            text-transform: uppercase;
            color: #b58d60;
            letter-spacing: 2px;
            font-weight: bold;
        }

        .section-heading {
            font-size: 26px;
            color: #1a1411;
            font-family: 'Times New Roman', serif;
            margin-top: 15px;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        p {
            font-size: 14px;
            color: #555;
            text-align: justify;
            margin-bottom: 12px;
            line-height: 1.7;
        }

        /* GAMBAR */
        .img-wrapper {
            position: relative;
            width: 100%;
            margin-top: 50px;
        }

        .section-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
            z-index: 2;
        }

        /* DOT PATTERN DECORATION (Untuk gambar 1) */
        .dots-pattern::before {
            content: '';
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 60px;
            height: 60px;
            background-image: radial-gradient(#b58d60 2px, transparent 2px);
            background-size: 12px 12px;
            z-index: 1;
        }

        /* CUP ICON STYLING */
        .cup-icon-container {
            float: right;
            margin-left: 15px;
            margin-bottom: 10px;
        }

        /* QUOTE BOX */
        .quote-box {
            background-color: #f2eadf;
            border-radius: 12px;
            padding: 40px 25px;
            text-align: center;
            font-size: 32px;
            color: #b58d60;
            font-family: 'Brittany Signature Script', 'Brittany Signature', cursive;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            min-height: 180px;
        }

        .quote-box::before {
            content: "“";
            font-size: 60px;
            font-family: 'Times New Roman', serif;
            color: #b58d60;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            line-height: 1;
        }

        /* =========================================
           SIDEBAR STYLING - REVISI BARU
           ========================================= */
        .sidebar {
            align-self: start; /* Mencegah sidebar meregang sampai bawah container */
            margin-bottom: 10px; /* Sisakan jarak 10px di bagian bawah */
            position: relative;
        }

        .sidebar-wrapper {
            background-color: #fdfaf6; /* Background color tunggal menyatu */
            border: 1px solid #efe8df;
            border-radius: 15px;
            padding-bottom: 25px;
            margin-top: 25px; /* Memberi ruang agar title pertama bisa dinaikkan */
        }

        .widget-title {
            background-color: #121b2b; /* Warna dark blue/navy dari desain */
            border-radius: 20px;
            color: #fff;
            padding: 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Title Pertama: Ditarik ke atas agar tidak ada warna background di atasnya */
        #title-1 {
            width: 90%;
            margin: -18px auto 25px auto; 
            position: relative;
            z-index: 2;
        }

        /* Title 2 & 3: Lebarnya tidak full sama dengan background color */
        #title-2, #title-3 {
            width: 90%;
            margin: 0 auto 25px auto;
        }

        /* Konten dalam sidebar dibuat lebih kecil lebarnya */
        .sidebar-inner-content {
            padding: 0 35px; /* Lebar konten berkurang agar tidak sejajar title */
        }

        .sidebar-divider {
            border: 0;
            border-top: 1px solid #eaddd3; /* Garis tipis solid seperti gambar */
            margin: 25px 35px 30px 35px;
        }

        /* AUTHOR PROFILE & SPARKLES */
        .author-card {
            text-align: center;
        }

        .author-img-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .author-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #b58d60;
        }

        /* Icon Sparkles */
        .sparkle {
            position: absolute;
            color: #c39a6b;
            font-size: 20px;
        }
        .sparkle.s1 { top: 5px; left: -15px; }
        .sparkle.s2 { bottom: 15px; right: -15px; font-size: 16px; }

        .author-name {
            font-size: 20px;
            color: #1a1411;
            font-family: 'Times New Roman', serif;
            margin-bottom: 2px;
        }

        .author-class { font-size: 12px; color: #888; margin-bottom: 12px; }
        .author-desc { font-size: 13px; color: #666; margin-bottom: 15px; text-align: center; }
        
        .social-icons { display: flex; justify-content: center; gap: 15px; }
        .social-icons a { color: #b58d60; text-decoration: none; font-size: 16px; }

        /* FORM KOMENTAR */
        .suggestion-desc {
            font-size: 12px; 
            margin-bottom: 15px; 
            text-align: center; 
            color: #555;
            line-height: 1.5;
        }

        .form-group { margin-bottom: 15px; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0d8cf;
            border-radius: 8px;
            font-size: 13px;
            background-color: #fff;
            font-family: inherit;
        }
        .form-group textarea { height: 90px; resize: none; }

        .rating-stars {
            color: #d1bfae;
            font-size: 22px;
            margin: 10px 0 20px;
            text-align: center;
            cursor: pointer;
        }
        .rating-stars i.active { color: #b58d60; }

        .submit-btn {
            width: 100%;
            background-color: #bfa280;
            color: #fff;
            border: none;
            padding: 14px;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover { background-color: #9c8061; }

        .comments-list {
            margin-top: 20px;
            max-height: 250px;
            overflow-y: auto;
            border-top: 1px solid #e0d8cf;
            padding-top: 15px;
        }
        .comment-item {
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 12px;
            border: 1px solid #f0eade;
        }
        .comment-item h5 { color: #121b2b; margin-bottom: 3px; font-size: 13px; }
        .comment-item p { margin-bottom: 0; color: #666; text-align: left; }

        /* REKOMENDASI WIDGET */
        .rec-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        .rec-img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .rec-text { font-size: 13px; font-weight: bold; color: #1a1411; text-decoration: none; line-height: 1.4; font-family: 'Times New Roman', serif; text-align: left; }
        .rec-text:hover { color: #b58d60; }

        /* FOOTER */
        footer {
            background-color: #1a1411;
            text-align: center;
            color: #a59a92;
            padding: 40px 10%;
            font-size: 13px;
            border-top: 3px solid #b58d60;
        }
        .footer-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center; }
        .footer-info h4 { color: #fff; margin-bottom: 5px; font-size: 14px; letter-spacing: 1px; text-align: center; }

        /* RESPONSIVE MENGALIR */
        /* =========================================
            RESPONSIVE MENGALIR (TAMPILAN HP)
            ========================================= */
            @media (max-width: 768px) {
                /* 1. Layout Utama Container */
                .container { 
                    grid-template-columns: 1fr; /* Mengubah 2 kolom menjadi 1 kolom (atas-bawah) */
                    gap: 40px; 
                    margin: 20px auto;
                    padding: 0 20px; 
                }

                /* 2. Hero Section (Header) */
                .hero { 
                    height: auto; 
                    padding: 60px 5%; 
                    text-align: center;
                    align-items: center;
                }
                .hero-tag { font-size: 22px; }
                .hero h1 { font-size: 32px; text-align: center; }
                .hero .from-cafe { font-size: 30px; display: block; margin-top: 5px; }
                
                .hero-meta { 
                    flex-direction: column; /* Susun info jam dan lokasi ke bawah */
                    padding: 20px; 
                    gap: 15px; 
                    border-radius: 12px;
                    align-items: center;
                }
                .meta-item { flex-direction: column; text-align: center; gap: 8px; }
                .meta-item i { font-size: 24px; }
                .meta-title { font-size: 13px; }
                .meta-divider { 
                    width: 40px; 
                    height: 1px; 
                    margin: 5px 0; 
                }

                /* 3. Main Content & Grid Layout */
                .main-content { gap: 50px; }
                .grid-left-text, .grid-right-text, .grid-quote-split { 
                    grid-template-columns: 1fr; 
                    gap: 20px; 
                }
                
                /* OVERRIDE: Menimpa style inline pada HTML agar tidak melebar di HP */
                .text-content { width: 100% !important; }
                .section-img { 
                    width: 100% !important; 
                    height: 250px !important; 
                }
                
                /* Atur ulang urutan (Gambar/Slider selalu di atas teks) */
                .grid-left-text .img-wrapper { order: 1; margin-top: 0; }
                .grid-left-text .text-content { order: 2; }
                
                .grid-right-text .swiper { order: 1; }
                .grid-right-text .text-content { order: 2; }

                .grid-quote-split .text-content { order: 1; }
                .grid-quote-split .quote-box { order: 2; margin-top: 10px; }

                /* 4. Typography Content */
                .section-heading { font-size: 22px; margin-top: 5px; margin-bottom: 10px; }
                .num-title { font-size: 55px; }
                .header-text-group { left: 40px; top: 0; }
                p { font-size: 14px; margin-bottom: 15px; }

                /* 5. Gambar & Ornamen */
                .swiper { height: 250px; }
                .dots-pattern::before { 
                    right: 0px; /* Mencegah titik-titik keluar dari layar HP */
                    bottom: -10px; 
                }
                .cup-icon-container {
                    transform: scale(0.8);
                    margin-left: 10px;
                    margin-bottom: 5px;
                }
                .quote-box { 
                    font-size: 24px; 
                    padding: 40px 20px 20px; 
                    min-height: 140px; 
                }

                /* 6. Sidebar & Widget */
                .sidebar { margin-top: 20px; }
                #title-1 { width: 80%; margin: -18px auto 20px auto; }
                #title-2, #title-3 { width: 85%; }
                
                .sidebar-inner-content { padding: 0 20px; }
                .sidebar-divider { margin: 25px 20px; }
                
                .form-group input, .form-group textarea { padding: 12px; }
                .submit-btn { padding: 14px; font-size: 14px; }
                
                .rec-item { flex-direction: column; align-items: flex-start; gap: 10px; }
                .rec-img { width: 100%; height: 120px; border-radius: 8px; }
                .rec-text { font-size: 14px; }

                /* 7. Footer */
                footer { padding: 40px 20px; }
                .footer-grid { 
                    grid-template-columns: 1fr; 
                    gap: 30px; 
                }
            }
    </style>
</head>
<body>

    <section class="hero">
        <div class="hero-tag">Cafe Review</div>
        <h1>
            The Charm of Musi River <br>and Ampera Bridge <br> 
            <span class="from-cafe">from Ilir Cafe</span>
        </h1>

        <div class="hero-meta">
            <div class="meta-item">
                <i class="fa-solid fa-clock"></i>
                <div class="meta-text">
                    <span class="meta-title">Open Daily</span>
                    <span class="meta-sub">4 PM - 11 PM</span>
                </div>
            </div>

            <div class="meta-divider"></div>

            <div class="meta-item">
                <i class="fa-solid fa-location-dot"></i>
                <div class="meta-text">
                    <span class="meta-title">16 Ilir, Ilir Timur I District</span>
                    <span class="meta-sub">Palembang</span>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        
        <main class="main-content">
            
            <article class="section-block">
                <div class="grid-left-text">
                    <div class="text-content">
                        <div class="section-header">
                            <span class="num-title">01</span>
                            <div class="header-text-group">
                            </div>
                        </div>
                        <h2 class="section-heading">A Beauty That Shines from Afternoon to Night</h2>
                        <p>Ampera Bridge and Musi River are the lifeblood and iconic symbols of pride in Palembang. The view of the river dividing the city, combined with the grandeur of Ampera Bridge, attracts both tourists and locals with its beauty.</p>
                        <p>This beauty shines even brighter from late afternoon into the evening when the bridge’s lights illuminate the surrounding area, creating a stunning panorama.</p>
                        <p>For those who wish to experience this beauty, Ilir Cafe which is located at 16 Ilir, Ilir Timur I District, Palembang, is the perfect choice.</p>
                    </div>
                    <div class="img-wrapper dots-pattern">
                        <img src="gambar1.jpeg" alt="View of Ampera Bridge from Ilir Cafe" class="section-img" style="height: 400px;">
                    </div>
                </div>
            </article>

            <article class="section-block">
                <div class="grid-left-text">
                    <div class="text-content" style="width: 220px;">
                        <div class="section-header">
                            <span class="num-title">02</span>
                            <div class="header-text-group">
                            </div>
                        </div>
                        <h2 class="section-heading">A Rooftop Escape with the Best View</h2>
                        <p>Located on the third floor, Ilir Cafe features a semi-outdoor rooftop concept offering a direct view of Musi River and Ampera Bridge.</p>
                        <p>The comfortable seating area provides an opportunity to relax and enjoy the fresh air while watching the boats come and go. The gentle breeze adds a cool and comfortable atmosphere, helping you unwind from the hustle and bustle of daily life.</p>
                    </div>
                    <div class="img-wrapper">
                        <img src="gambar2.1.jpeg" alt="Rooftop Atmosphere" class="section-img" style="width: 500px; height: 400px;">
                    </div>
                </div>
            </article>

            <article class="section-block">
                <div class="grid-right-text">
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide"><img src="gambar2.jpeg" alt="Food 1"></div>
                            <div class="swiper-slide"><img src="gambar3.jpeg" alt="Food 2"></div>
                        </div>
                    </div>
                    <div class="text-content">
                        <div class="section-header">
                            <span class="num-title">03</span>
                        </div>
                        <h2 class="section-heading" style="margin-top: -10px;">Good Food, Great Moments</h2>
                        
                        <div class="cup-icon-container">
                            <svg width="40" height="60" viewBox="0 0 100 120" xmlns="http://www.w3.org/2000/svg">
                                <g stroke="#b58d60" stroke-width="3" fill="#fdfaf6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 35 Q50 45 85 35" fill="none" />
                                    <path d="M10 35 L90 35" />
                                    <path d="M20 35 L30 110 Q50 115 70 110 L80 35" />
                                    <path d="M65 5 L55 35" fill="none" />
                                    <path d="M35 60 Q50 68 65 60" fill="none" stroke-width="2" />
                                    <path d="M40 75 Q50 82 60 75" fill="none" stroke-width="2" />
                                </g>
                            </svg>
                        </div>

                        <p>It’s not just about the view, Ilir Cafe also offers a variety of food and drinks to enjoy. These include drinks like Kopi Aren, Avocado Latte, and Musi Sunset, as well as dishes such as Banana Spring Rolls, Black Pepper Chicken Rice, Padang-Style Shrimp, and various other options.</p>
                        <p>Prices range from just Rp 12,000 to Rp 30,000.</p>
                    </div>
                </div>
            </article>

            <article class="section-block">
                <div class="grid-left-text">
                    <div class="text-content">
                        <div class="section-header">
                            <span class="num-title">04</span>
                        </div>
                        <h2 class="section-heading" style="margin-top: -10px;">Evening Magic Over the Musi River</h2>
                        <p>The cozy atmosphere is one of Ilir Cafe’s main attractions. The relaxed ambiance is perfect for gathering with friends or family. As evening approaches, you can also enjoy the sunset view that paints the sky above the Musi River.</p>
                        <p>The illuminated Ampera Bridge adds a warmer, more enchanting atmosphere, creating a truly memorable experience.</p>
                    </div>
                    <div class="img-wrapper">
                        <img src="gambar4.jpeg" alt="Evening Ampera Bridge View" class="section-img">
                    </div>
                </div>
            </article>

            <article class="section-block" style="margin-bottom: 20px;">
                <div class="grid-quote-split">
                    <div class="text-content">
                        <div class="section-header">
                            <span class="num-title">05</span>
                            <div class="header-text-group">
                            </div>
                        </div>
                        <h2 class="section-heading">A Place to Relax and Remember</h2>
                        <p>With its comfortable atmosphere, affordable food and drinks, and direct views of the Musi River and Ampera Bridge, Ilir Cafe not only delights the palate but also the eyes. So, what are you waiting for? Come visit Ilir Cafe now!</p>
                    </div>
                    <div class="quote-box">
                        Good view, good food, good mood.
                    </div>
                </div>
            </article>

        </main>

        <aside class="sidebar">
            <div class="sidebar-wrapper">
                
                <div class="widget-title" id="title-1">ABOUT THE AUTHOR</div>
                
                <div class="sidebar-inner-content">
                    <div class="author-card">
                        <div class="author-img-wrapper">
                            <i class="fa-solid fa-sparkles sparkle s1" style="font-family: 'Font Awesome 6 Free'; font-weight: 900; content: '\e05d';">✨</i>
                            <i class="fa-solid fa-sparkles sparkle s2" style="font-family: 'Font Awesome 6 Free'; font-weight: 900; content: '\e05d';">✨</i>
                            <img src="gambar1.jpeg" alt="Feliza Fitri Alisa" class="author-img">
                        </div>
                        <h3 class="author-name">Feliza Fitri Alisa</h3>
                        <p class="author-desc">Simple things become much more enjoyable when you appreciate them." Born and raised in Palembang and currently studying at Politeknik Negeri Sriwijaya. Passionate about creativity, nature, and meaningful experiences. Enjoy watching movies, spending time with cats, and embracing the calmness of a starry night. Believes that even the simplest moments can leave the most lasting impressions when viewed with gratitude and an open mind.</p>
                        <div class="social-icons">
                            <a href="https://www.instagram.com/ycbjlssa/" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=felizafitri3@gmail.com" target="_blank" rel="noopener noreferrer"><i class="fa-regular fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <hr class="sidebar-divider">

                    <div class="widget-title" id="title-2">SUGGESTION BOX</div>

                    <div class="sidebar-inner-content">
                        <p class="suggestion-desc">Feel free to share your thoughts, suggestions, or impressions in the box below.</p>
                        
                        <?php if($feedbackMsg): ?>
                            <div style="background: #e6f4ea; color: #1e4620; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 12px; text-align: center; border: 1px solid #cce8d6;">
                                <i class="fa-solid fa-circle-check"></i> <?= $feedbackMsg ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="#suggestion-area" id="suggestion-area">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="form-group">
                                <textarea name="suggestion" placeholder="Your Suggestion" required></textarea>
                            </div>
                            <div style="font-size: 12px; text-align: center; margin-bottom: 5px; color: #555;">Rate Your Experience</div>
                            
                            <div class="rating-stars" id="star-rating-ui">
                                <i class="fa-solid fa-star active" data-val="1"></i>
                                <i class="fa-solid fa-star active" data-val="2"></i>
                                <i class="fa-solid fa-star active" data-val="3"></i>
                                <i class="fa-solid fa-star active" data-val="4"></i>
                                <i class="fa-solid fa-star active" data-val="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="rating-input" value="5">
                            
                            <button type="submit" name="submit_suggestion" class="submit-btn">Submit</button>
                        </form>

                        <?php if(!empty($allComments)): ?>
                        <div class="comments-list">
                            <div style="font-size: 11px; font-weight: bold; margin-bottom: 10px; color: #888; text-transform: uppercase;">Recent Feedbacks</div>
                            <?php 
                            // Menggunakan index $i agar bisa dikirim ke logika delete_comment
                            foreach($allComments as $i => $c): 
                            ?>
                                <div class="comment-item">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h5><?= $c['name'] ?></h5>
                                        <div>
                                            <a href="?delete_comment=<?= $i ?>" onclick="return confirm('Hapus komentar ini?')" style="color: red; font-size: 10px; text-decoration: none; margin-right: 8px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                            <?php for($j=0; $j<$c['rating']; $j++) echo '<i class="fa-solid fa-star" style="color:#b58d60; font-size:9px;"></i>'; ?>
                                        </div>
                                    </div>
                                    <p>"<?= $c['suggestion'] ?>"</p>
                                    <span style="font-size: 10px; color: #aaa;"><?= $c['date'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                <hr class="sidebar-divider">

                <div class="widget-title" id="title-3">YOU MIGHT ALSO LIKE</div>
                
                <div class="sidebar-inner-content">
                    <div class="rec-item">
                        <img src="https://ik.imagekit.io/tvlk/blog/2020/01/38026178_1998521570170689_2968611837999841280_n.jpg?tr=q-70,c-at_max,w-500,h-250,dpr-2" alt="Cafe Hits" class="rec-img">
                        <a href="https://www.traveloka.com/id-id/explore/destination/ini-10-tempat-nongkrong-di-palembang-paling-hits/16577" class="rec-text">Ini 10 Tempat Nongkrong di Palembang Paling Hits</a>
                    </div>

                    <div class="rec-item">
                        <img src="https://static.tacdn.com/assets/s/2651d377.svg" alt="Tripadvisor" class="rec-img">
                        <a href="https://www.tripadvisor.co.id/Restaurants-g608501-zfp6-Palembang_South_Sumatra_Sumatra.html" class="rec-text">Restoran dengan Area Duduk Luar Ruangan di Palembang</a>
                    </div>

                    <div class="rec-item">
                        <img src="https://beritapress.id/wp-content/uploads/2025/08/thumbnail-253.jpg.webp" alt="beritapress" class="rec-img">
                        <a href="https://beritapress.id/10-cafe-hits-di-palembang-dari-pinggir-musi-hingga-rooftop-city-view/#google_vignette" class="rec-text">10 Cafe Hits di Palembang dari Pinggir Musi hingga Rooftop City View</a>
                    </div>
                </div>

            </div>
        </aside>

    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-info">
                <h4>OPEN DAILY</h4>
                <p style="color: #a59a92; text-align: center; margin-bottom: 0%;">4 PM - 11 PM</p>
            </div>
            <div class="footer-info">
                <h4>LOCATION</h4>
                <a href="https://maps.app.goo.gl/FkTLzUdVo4Ji9Fn47" target="_blank" rel="noopener noreferrer" style="color: #a59a92; text-align: center;">16 Ilir, Ilir Timur I District, Palembang</a>
            </div>
            <div class="footer-info">
                <h4>SOCIAL MEDIA</h4>
                <a href="https://www.instagram.com/ilircafe/" target="_blank" rel="noopener noreferrer" style="color: #a59a92; text-align: center;">@ILIR.CAFE</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper', {
            loop: true,
            autoplay: { delay: 3000, disableOnInteraction: false },
            effect: 'fade',
        });

        // Interaksi Bintang pada Form Saran
        const stars = document.querySelectorAll('#star-rating-ui i');
        const ratingInput = document.getElementById('rating-input');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                let val = this.getAttribute('data-val');
                ratingInput.value = val;
                
                // Reset semua bintang
                stars.forEach(s => {
                    s.classList.remove('active');
                    s.style.color = '#d1bfae';
                });
                
                // Nyalakan bintang sesuai klik
                for(let i = 0; i < val; i++) {
                    stars[i].classList.add('active');
                    stars[i].style.color = '#b58d60';
                }
            });
        });
    </script>

</body>
</html>