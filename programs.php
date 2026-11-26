<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/performance.php';

$page_title = 'GP and Skill Development Training';
include __DIR__ . '/includes/header.php';
?>

<main class="container py-5" lang="bn">
    <h1 class="mb-4">স্বাস্থ্যসেবা ও দক্ষতা উন্নয়ন</h1>
    <div class="row g-4">
        <section class="col-md-6" id="gp">
            <div class="card h-100 p-4">
                <h2 class="h4">TMTH GP</h2>
                <p>সাধারণ স্বাস্থ্যসমস্যায় প্রাথমিক পরামর্শ ও প্রয়োজন হলে পরবর্তী চিকিৎসার জন্য কোথায় যোগাযোগ করবেন, সে বিষয়ে দিকনির্দেশনা পেতে TMTH-এর সঙ্গে যোগাযোগ করুন।</p>
                <p>অ্যাপয়েন্টমেন্ট ও চিকিৎসকের সময় জানতে ফোন করুন।</p>
                <a class="btn btn-primary mt-auto align-self-start" href="tel:+8801859135478">GP সেবা সম্পর্কে ফোন করুন</a>
            </div>
        </section>
        <section class="col-md-6" id="training">
            <div class="card h-100 p-4">
                <h2 class="h4">দক্ষতা উন্নয়ন প্রশিক্ষণ</h2>
                <p>TMTH-এর কম্পিউটারের প্রাথমিক দক্ষতা বিষয়ক প্রশিক্ষণে আগ্রহী হলে আমাদের জানান। পরবর্তী ব্যাচ, সময়সূচি ও অংশগ্রহণের নিয়ম সম্পর্কে ফোনে জানতে পারবেন।</p>
                <a class="btn btn-primary mt-auto align-self-start" href="tel:+8801859135478">প্রশিক্ষণ সম্পর্কে ফোন করুন</a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
