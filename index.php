<?php
// Smart Laptop Repair Assistant - Pure PHP Version (Single File)
// Features:
// - Sample dataset of laptop issues
// - Preprocessing: normalize, tokenize, simple stemming
// - Similarity search (TF-IDF + Cosine)
// - Responsive dark UI using only HTML + CSS (no JS required for core logic)

// --------------------------------------
// Utility functions
function normalize_text($text) {
    $text = strtolower($text);
    $text = preg_replace('/https?:\/\/\S+/', ' ', $text);
    $text = preg_replace('/[^a-z0-9\p{L}\s]+/u', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function stem_word($word) {
    $word = strtolower($word);
    $suffixes = ['lah','kah','nya','kan','an','i','ing','ed','s'];
    foreach ($suffixes as $s) {
        if (substr($word, -strlen($s)) === $s && strlen($word) > strlen($s)+2) {
            return substr($word, 0, -strlen($s));
        }
    }
    return $word;
}

function tokenize_and_stem($text) {
    $stopwords = ["dan","di","ke","yang","untuk","dengan","pada","adalah","ini","itu","a","an","the","of","in","on","at","is"];
    $normalized = normalize_text($text);
    $tokens = array_filter(explode(' ', $normalized));
    $out = [];
    foreach ($tokens as $t) {
        if (!in_array($t, $stopwords) && strlen($t) > 1) {
            $out[] = stem_word($t);
        }
    }
    return $out;
}

function build_tf($tokens) {
    $tf = [];
    $len = max(count($tokens), 1);
    foreach ($tokens as $t) {
        if (!isset($tf[$t])) $tf[$t] = 0;
        $tf[$t]++;
    }
    foreach ($tf as $k => $v) $tf[$k] = $v / $len;
    return $tf;
}

function cosine_similarity($a, $b) {
    $dot = 0; $na = 0; $nb = 0;
    foreach ($a as $k => $v) {
        $dot += $v * ($b[$k] ?? 0);
        $na += $v * $v;
    }
    foreach ($b as $k => $v) $nb += $v * $v;
    if ($na == 0 || $nb == 0) return 0;
    return $dot / (sqrt($na) * sqrt($nb));
}

// --------------------------------------
// Sample dataset
$data = [
    ["id"=>"1","issue"=>"Laptop tidak menyala","description"=>"Tidak ada lampu indikator dan tidak merespon tombol power","solution"=>"Periksa charger, baterai, motherboard"],
    ["id"=>"2","issue"=>"Layar blank","description"=>"Layar hitam setelah boot","solution"=>"Coba monitor eksternal, cek kabel LCD"],
    ["id"=>"3","issue"=>"Baterai cepat habis","description"=>"Battery drain tinggi","solution"=>"Kalibrasi baterai atau ganti baterai"],
    ["id"=>"4","issue"=>"Kipas berisik","description"=>"Fan speed tinggi terus","solution"=>"Bersihkan fan, ganti thermal paste"],
    ["id"=>"5","issue"=>"Keyboard tidak berfungsi","description"=>"Beberapa tombol mati","solution"=>"Bersihkan keyboard atau ganti modul keyboard"],

    ["id"=>"6","issue"=>"Blue Screen (BSOD)","description"=>"Muncul layar biru error saat digunakan","solution"=>"Periksa driver, RAM, update Windows"],
    ["id"=>"7","issue"=>"Touchpad tidak berfungsi","description"=>"Cursor tidak bergerak ketika disentuh","solution"=>"Aktifkan touchpad dan update driver"],
    ["id"=>"8","issue"=>"Overheat saat gaming","description"=>"Laptop cepat panas ketika bermain game","solution"=>"Bersihkan fan dan gunakan cooling pad"],
    ["id"=>"9","issue"=>"HDD berbunyi","description"=>"Suara klik dari harddisk","solution"=>"Backup data dan ganti HDD"],
    ["id"=>"10","issue"=>"SSD tidak terbaca","description"=>"SSD tidak muncul di BIOS","solution"=>"Coba port lain atau update firmware"],

    ["id"=>"11","issue"=>"WiFi sering putus","description"=>"Koneksi wifi disconnect berkali-kali","solution"=>"Update driver WiFi atau reset router"],
    ["id"=>"12","issue"=>"Bluetooth tidak aktif","description"=>"Bluetooth tidak muncul di Device Manager","solution"=>"Install ulang driver bluetooth"],
    ["id"=>"13","issue"=>"Speaker tidak berbunyi","description"=>"Tidak ada suara meski volume penuh","solution"=>"Periksa driver audio, coba headset"],
    ["id"=>"14","issue"=>"Webcam tidak terdeteksi","description"=>"Kamera tidak muncul","solution"=>"Periksa privacy settings dan driver webcam"],
    ["id"=>"15","issue"=>"Port USB tidak berfungsi","description"=>"USB tidak membaca perangkat","solution"=>"Periksa driver USB dan bersihkan port"],

    ["id"=>"16","issue"=>"Laptop restart sendiri","description"=>"Laptop restart tanpa peringatan","solution"=>"Cek RAM, power supply, suhu CPU"],
    ["id"=>"17","issue"=>"Laptop mati tiba-tiba","description"=>"Tiba-tiba mati saat digunakan","solution"=>"Cek overheat atau kerusakan motherboard"],
    ["id"=>"18","issue"=>"Aplikasi sering crash","description"=>"App berhenti bekerja","solution"=>"Update aplikasi, cek RAM, scan malware"],
    ["id"=>"19","issue"=>"Windows gagal booting","description"=>"Stuck di logo Windows","solution"=>"Startup repair atau install ulang"],
    ["id"=>"20","issue"=>"BIOS tidak bisa diakses","description"=>"Tidak merespon tombol BIOS","solution"=>"Reset BIOS melalui jumper"],

    ["id"=>"21","issue"=>"Charging tidak masuk","description"=>"Charger terhubung tapi tidak mengisi","solution"=>"Ganti charger atau cek IC charging"],
    ["id"=>"22","issue"=>"Battery not detected","description"=>"Baterai tidak terbaca Windows","solution"=>"Reseat baterai atau ganti modul baterai"],
    ["id"=>"23","issue"=>"Keyboard mengetik sendiri","description"=>"Tombol menekan tanpa input","solution"=>"Bersihkan keyboard atau ganti fleksibel"],
    ["id"=>"24","issue"=>"Touchpad bergerak sendiri","description"=>"Cursor bergerak acak","solution"=>"Bersihkan permukaan atau matikan palm check"],
    ["id"=>"25","issue"=>"Layar bergaris","description"=>"Ada garis horizontal/vertikal","solution"=>"Ganti kabel LCD atau panel LCD"],

    ["id"=>"26","issue"=>"Layar flickering","description"=>"Layar berkedip","solution"=>"Update driver GPU dan cek fleksibel LCD"],
    ["id"=>"27","issue"=>"Brightness tidak bisa diubah","description"=>"Brightness stuck","solution"=>"Reinstall driver display"],
    ["id"=>"28","issue"=>"GPU overheating","description"=>"VGA sangat panas","solution"=>"Ganti thermal paste dan bersihkan heatsink"],
    ["id"=>"29","issue"=>"FPS drop parah","description"=>"Game tiba-tiba lag","solution"=>"Update driver GPU dan matikan background apps"],
    ["id"=>"30","issue"=>"Fan tidak berputar","description"=>"Kipas mati total","solution"=>"Ganti fan atau cek soket fan"],

    ["id"=>"31","issue"=>"Motherboard short","description"=>"Tercium bau gosong atau short","solution"=>"Service motherboard atau ganti komponen"],
    ["id"=>"32","issue"=>"RAM tidak terbaca","description"=>"Hanya terbaca setengah","solution"=>"Bersihkan slot RAM atau ganti modul"],
    ["id"=>"33","issue"=>"RAM error beep","description"=>"Laptop tidak menyala dan berbunyi beep","solution"=>"Reseat RAM atau ganti RAM"],
    ["id"=>"34","issue"=>"HDD 100% usage","description"=>"Disk usage tinggi terus","solution"=>"Disable Windows search dan update driver"],
    ["id"=>"35","issue"=>"Selalu boot ke BIOS","description"=>"Tidak masuk OS","solution"=>"Cek bootloader dan drive OS"],

    ["id"=>"36","issue"=>"Windows activation hilang","description"=>"Lisensi tidak terbaca","solution"=>"Login Microsoft account atau reaktivasi"],
    ["id"=>"37","issue"=>"Sistem terlalu lambat","description"=>"Laptop jadi lemot","solution"=>"Upgrade ke SSD dan tambah RAM"],
    ["id"=>"38","issue"=>"Program error 0xc000","description"=>"Aplikasi tidak bisa dibuka","solution"=>"Reinstall aplikasi atau cek file system"],
    ["id"=>"39","issue"=>"Touchscreen tidak merespon","description"=>"Layar sentuh mati","solution"=>"Kalibrasi dan reinstall driver touchscreen"],
    ["id"=>"40","issue"=>"Bau hangus dari laptop","description"=>"Bau terbakar dari ventilasi","solution"=>"Matikan segera dan periksa PSU/motherboard"],

    ["id"=>"41","issue"=>"Port HDMI tidak bekerja","description"=>"Tidak ada output ke monitor","solution"=>"Update GPU dan cek kabel HDMI"],
    ["id"=>"42","issue"=>"Port LAN mati","description"=>"Kabel LAN terhubung tapi tidak connect","solution"=>"Reinstall driver LAN"],
    ["id"=>"43","issue"=>"Blue screen nvlddmkm.sys","description"=>"Error driver NVIDIA","solution"=>"Rollback driver NVIDIA"],
    ["id"=>"44","issue"=>"Overheat idle","description"=>"Panas meski tidak dipakai","solution"=>"Cek background task dan ganti thermal paste"],
    ["id"=>"45","issue"=>"Charger panas berlebih","description"=>"Adaptor sangat panas","solution"=>"Ganti adaptor dengan yang original"],

    ["id"=>"46","issue"=>"Baterai mengembang","description"=>"Baterai menggembung","solution"=>"Segera ganti baterai"],
    ["id"=>"47","issue"=>"Audio crackling","description"=>"Suara pecah","solution"=>"Update driver audio dan cek speaker"],
    ["id"=>"48","issue"=>"Keyboard delay","description"=>"Input keyboard lambat","solution"=>"Matikan filter keys dan cek driver"],
    ["id"=>"49","issue"=>"File system corrupted","description"=>"Sistem rusak","solution"=>"Jalankan CHKDSK dan SFC"],
    ["id"=>"50","issue"=>"Virus/Malware","description"=>"Banyak pop-up dan aplikasi aneh","solution"=>"Scan antivirus dan reset Windows"],

    ["id"=>"51","issue"=>"Fan selalu 100%","description"=>"Kipas selalu maksimal","solution"=>"Update BIOS dan bersihkan heatsink"],
    ["id"=>"52","issue"=>"Bios corrupt","description"=>"Tidak bisa masuk BIOS","solution"=>"Flash ulang BIOS"],
    ["id"=>"53","issue"=>"Touchpad terlalu sensitif","description"=>"Cursor bergerak cepat","solution"=>"Atur sensitivity di control panel"],
    ["id"=>"54","issue"=>"Bluetooth sering disconnect","description"=>"Bluetooth putus-putus","solution"=>"Ganti driver bluetooth"],
    ["id"=>"55","issue"=>"Laptop sering freeze","description"=>"Laptop hang","solution"=>"Cek SSD/RAM dan update driver"],

    ["id"=>"56","issue"=>"Aroma plastik terbakar","description"=>"Bau plastik dari laptop","solution"=>"Sumber panas berlebih, periksa motherboard"],
    ["id"=>"57","issue"=>"Fan klik-klik","description"=>"Bunyi klik pada fan","solution"=>"Ganti fan"],
    ["id"=>"58","issue"=>"Keyboard double input","description"=>"Mengetik huruf dua kali","solution"=>"Ganti keyboard"],
    ["id"=>"59","issue"=>"Trackpad ghost touch","description"=>"Touchpad klik sendiri","solution"=>"Matikan tap-to-click atau ganti modul"],
    ["id"=>"60","issue"=>"WiFi no internet","description"=>"Ada wifi tapi no internet","solution"=>"Reset IP dan DNS"],

    ["id"=>"61","issue"=>"Sensor baterai error","description"=>"Persentase baterai tidak akurat","solution"=>"Kalibrasi baterai"],
    ["id"=>"62","issue"=>"Driver konflik","description"=>"Driver bentrok setelah update","solution"=>"Rollback driver"],
    ["id"=>"63","issue"=>"CPU throttling","description"=>"Speed CPU turun drastis","solution"=>"Bersihkan fan dan update BIOS"],
    ["id"=>"64","issue"=>"GPU throttling","description"=>"GPU turun performa","solution"=>"Ganti thermal pad GPU"],
    ["id"=>"65","issue"=>"Laptop lambat boot","description"=>"Booting lama","solution"=>"Upgrade SSD dan perbaiki startup"],

    ["id"=>"66","issue"=>"Black screen setelah sleep","description"=>"Tidak muncul display","solution"=>"Disable fast boot atau update driver"],
    ["id"=>"67","issue"=>"Keyboard RGB mati","description"=>"Lampu RGB tidak menyala","solution"=>"Install software bawaan keyboard"],
    ["id"=>"68","issue"=>"Logo Windows looping","description"=>"Tidak masuk desktop","solution"=>"Automatic repair atau install ulang"],
    ["id"=>"69","issue"=>"Auto shutdown","description"=>"Laptop mati sendiri saat panas","solution"=>"Ganti thermal paste"],
    ["id"=>"70","issue"=>"Charging port longgar","description"=>"Port charger goyang","solution"=>"Solder ulang port DC"],

    ["id"=>"71","issue"=>"Layarnya glitch","description"=>"Visual glitch atau artefak","solution"=>"Cek GPU dan kabel LCD"],
    ["id"=>"72","issue"=>"Suhu idle tinggi","description"=>"CPU panas saat idle","solution"=>"Bersihkan fan dan cek background apps"],
    ["id"=>"73","issue"=>"Windows update error","description"=>"Gagal install update","solution"=>"Reset Windows update"],
    ["id"=>"74","issue"=>"SSD slow write","description"=>"Kecepatan tulis lambat","solution"=>"Update firmware SSD"],
    ["id"=>"75","issue"=>"Laptop berbau asap","description"=>"Ada asap dari laptop","solution"=>"Matikan segera dan servis"],

    ["id"=>"76","issue"=>"Baterai tidak penuh","description"=>"Stuck di 80%","solution"=>"Matikan battery protection di BIOS"],
    ["id"=>"77","issue"=>"WiFi tidak muncul","description"=>"Tidak tersedia di device manager","solution"=>"Reinstall wireless driver"],
    ["id"=>"78","issue"=>"LCD backlight mati","description"=>"Layar gelap tetapi gambar ada","solution"=>"Ganti lampu backlight atau inverter"],
    ["id"=>"79","issue"=>"Laptop tidak bisa dimatikan","description"=>"Shutdown tapi tetap menyala","solution"=>"Disable fast startup"],
    ["id"=>"80","issue"=>"Thermal throttle berat","description"=>"CPU turun ke 0.8GHz","solution"=>"Bersihkan heatsink dan flash BIOS"]
];


// Preprocess dataset
$docs_tokens = [];
foreach ($data as $d) {
    $docs_tokens[] = tokenize_and_stem($d["issue"] . " " . $d["description"] . " " . $d["solution"]);
}

// Build IDF
date_default_timezone_set("Asia/Jakarta");
$N = count($docs_tokens);
$df = [];
foreach ($docs_tokens as $tokens) {
    foreach (array_unique($tokens) as $t) {
        if (!isset($df[$t])) $df[$t] = 0;
        $df[$t]++;
    }
}
$idf = [];
foreach ($df as $term => $count) {
    $idf[$term] = log($N / ($count + 1)) + 1;
}

// Build TF-IDF docs
$tfidf_docs = [];
foreach ($docs_tokens as $tokens) {
    $tf = build_tf($tokens);
    $vec = [];
    foreach ($tf as $k => $v) $vec[$k] = $v * ($idf[$k] ?? 1);
    $tfidf_docs[] = $vec;
}

// Process query
$query = $_POST['query'] ?? '';
$results = [];

if ($query) {
    $q_tokens = tokenize_and_stem($query);
    $q_tf = build_tf($q_tokens);
    $q_vec = [];
    foreach ($q_tf as $k => $v) $q_vec[$k] = $v * ($idf[$k] ?? 1);

    foreach ($tfidf_docs as $i => $doc_vec) {
        $score = cosine_similarity($q_vec, $doc_vec);
        $results[] = ["data"=>$data[$i], "score"=>$score];
    }

    usort($results, fn($a,$b)=>$b['score'] <=> $a['score']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Smart Laptop Repair Assistant</title>
<style>
    body { background:#0d0d0d; color:#e0e0e0; font-family:Arial; padding:30px; }
    .box { background:#1a1a1a; padding:20px; border-radius:10px; max-width:900px; margin:auto; }
    input { width:80%; padding:10px; border-radius:8px; border:1px solid #333; background:#111; color:white; }
    button { padding:10px 18px; background:#d90000; border:none; border-radius:8px; color:white; cursor:pointer; }
    .result { margin-top:15px; padding:15px; background:#111; border:1px solid #333; border-radius:8px; }
    .score { color:#888; font-size:12px; }
</style>
</head>
<body>
<div class="box">
    <h1>Smart Laptop Repair Assistant (PHP)</h1>
    <p>Website AI sederhana untuk mendiagnosis kerusakan laptop menggunakan TF‑IDF & Similarity.</p>

    <form method="POST">
        <input type="text" name="query" placeholder="Masukkan pertanyaan..." value="<?=htmlspecialchars($query)?>">
        <button>Cari</button>
    </form>

    <?php if ($query): ?>
        <h3>Hasil Pencarian:</h3>
        <?php foreach ($results as $r): if ($r['score'] <= 0) continue; ?>
            <div class="result">
                <div class="score">Score: <?=number_format($r['score'],3)?></div>
                <h4><?=$r['data']['issue']?></h4>
                <p><?=$r['data']['description']?></p>
                <b>Solusi:</b> <?=$r['data']['solution']?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
