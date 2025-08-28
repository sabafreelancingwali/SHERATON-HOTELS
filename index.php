<?php require_once "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sheraton Hotels | Find Your Stay</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root { --brand:#8b5cf6; --ink:#0f172a; --muted:#64748b; --card:#ffffff; --bg:#f8fafc; --ring:#e2e8f0; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,system-ui,Segoe UI,Roboto,Arial}
  header{position:sticky;top:0;background:#fff;border-bottom:1px solid var(--ring);z-index:10}
  .wrap{max-width:1100px;margin:0 auto;padding:16px}
  .brand{display:flex;gap:10px;align-items:center;font-weight:800;font-size:20px}
  .brand span{display:inline-block;width:12px;height:12px;border-radius:999px;background:var(--brand);box-shadow:0 0 0 6px rgba(139,92,246,.12)}
  .hero{padding:48px 0;display:grid;gap:24px}
  .card{background:var(--card);border:1px solid var(--ring);border-radius:20px;box-shadow:0 10px 30px rgba(2,6,23,.06)}
  .search{display:grid;grid-template-columns:1fr repeat(2,160px) 120px 120px;gap:10px;padding:14px}
  .search input,.search select,.search button{height:46px;border:1px solid var(--ring);border-radius:14px;padding:0 12px;font-size:14px;outline:none}
  .search button{background:var(--brand);border:none;color:#fff;font-weight:700;cursor:pointer;transition:transform .05s}
  .search button:active{transform:scale(.99)}
  .pill{display:inline-block;padding:8px 12px;border-radius:999px;border:1px solid var(--ring);background:#fff;color:var(--muted);font-size:12px}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
  .hotel{overflow:hidden}
  .hotel img{width:100%;height:170px;object-fit:cover}
  .hotel .pad{padding:12px}
  .rating{font-weight:700}
  .amen{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
  footer{padding:40px 0;color:var(--muted)}
  @media (max-width:900px){.search{grid-template-columns:1fr 1fr;}}
</style>
</head>
<body>
<header>
  <div class="wrap" style="display:flex;justify-content:space-between;align-items:center;">
    <div class="brand"><span></span> Sheraton Hotels</div>
    <div><span class="pill">Best Rate Guarantee</span> <span class="pill">24/7 Support</span></div>
  </div>
</header>
 
<main class="wrap hero">
  <div class="card">
    <form id="searchForm" class="search" onsubmit="goSearch(event)">
      <input type="text" name="city" placeholder="Where to? (e.g., Karachi)" required>
      <input type="date" name="checkin" required>
      <input type="date" name="checkout" required>
      <select name="guests" required>
        <option value="1">1 Guest</option><option value="2" selected>2 Guests</option>
        <option value="3">3 Guests</option><option value="4">4 Guests</option>
      </select>
      <button type="submit">Search</button>
    </form>
  </div>
 
  <h2 style="margin:0 6px;">Featured Sheraton</h2>
  <div class="grid">
    <?php
      $res = $mysqli->query("SELECT * FROM hotels ORDER BY rating DESC LIMIT 6");
      while($h = $res->fetch_assoc()):
    ?>
    <article class="card hotel">
      <img src="<?=htmlspecialchars($h['image_url'])?>" alt="<?=htmlspecialchars($h['name'])?>">
      <div class="pad">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div style="font-weight:800;"><?=htmlspecialchars($h['name'])?></div>
          <div class="rating">⭐ <?=number_format((float)$h['rating'],1)?></div>
        </div>
        <div style="color:#475569;margin-top:4px;"><?=htmlspecialchars($h['city'])?></div>
        <p style="color:#475569;margin:8px 0 0;"><?=htmlspecialchars(mb_strimwidth($h['description'],0,120,'…'))?></p>
        <div class="amen">
          <?php
            $amen = json_decode($h['amenities'] ?? '[]', true) ?: [];
            foreach(array_slice($amen,0,4) as $a){ echo '<span class="pill">'.htmlspecialchars($a).'</span>'; }
          ?>
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;">
          <button onclick="viewListings('<?=urlencode($h['city'])?>')" class="pill" style="cursor:pointer;background:#f1f5f9;border-color:#cbd5e1;">See rooms in <?=htmlspecialchars($h['city'])?></button>
          <button onclick="viewHotel('<?= (int)$h['id'] ?>')" class="pill" style="cursor:pointer;background:#eef2ff;border-color:#c7d2fe;color:#4338ca;">Hotel details</button>
        </div>
      </div>
    </article>
    <?php endwhile; ?>
  </div>
</main>
 
<footer class="wrap">
  © <?=date('Y')?> Sheraton Hotels (Clone Demo). For education use only.
</footer>
 
<script>
  function goSearch(e){
    e.preventDefault();
    const f = new FormData(document.getElementById('searchForm'));
    const qs = new URLSearchParams(f).toString();
    // JS redirection (no PHP header):
    window.location.href = "results.php?" + qs;
  }
  function viewListings(city){
    const params = new URLSearchParams({ city, checkin: new Date().toISOString().slice(0,10), checkout: new Date(Date.now()+86400000).toISOString().slice(0,10), guests: 2 });
    window.location.href = "results.php?" + params.toString();
  }
  function viewHotel(hotelId){
    window.location.href = "results.php?hotel_id=" + hotelId + "&checkin=" + new Date().toISOString().slice(0,10) + "&checkout=" + new Date(Date.now()+86400000).toISOString().slice(0,10) + "&guests=2";
  }
</script>
</body>
</html>
