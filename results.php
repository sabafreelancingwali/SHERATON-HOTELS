<?php require_once "db.php";
$city = $_GET['city'] ?? '';
$hotelId = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = max(1, (int)($_GET['guests'] ?? 1));
$sort = $_GET['sort'] ?? 'price_asc';
$minPrice = isset($_GET['min']) ? (float)$_GET['min'] : 0;
$maxPrice = isset($_GET['max']) ? (float)$_GET['max'] : 9999;
$rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
 
$order = "r.price_per_night ASC";
if ($sort === 'price_desc') $order = "r.price_per_night DESC";
if ($sort === 'best') $order = "h.rating DESC, r.price_per_night ASC";
 
$sql = "SELECT r.*, h.name AS hotel_name, h.city, h.rating AS hotel_rating, h.image_url AS hotel_img
        FROM rooms r
        JOIN hotels h ON h.id = r.hotel_id
        WHERE r.max_guests >= ? AND r.price_per_night BETWEEN ? AND ? AND h.rating >= ?";
$params = [$guests, $minPrice, $maxPrice, $rating];
$types = "iddd";
 
if ($hotelId) { $sql .= " AND h.id = ?"; $types.="i"; $params[]=$hotelId; }
elseif ($city !== '') { $sql .= " AND h.city LIKE ?"; $types.="s"; $params[]="%$city%"; }
 
$sql .= " ORDER BY $order";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rooms = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Available Rooms | Sheraton Hotels</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--brand:#8b5cf6;--ink:#0f172a;--muted:#475569;--ring:#e2e8f0;--card:#fff;--bg:#f8fafc}
  body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,system-ui,Segoe UI,Roboto}
  .wrap{max-width:1100px;margin:0 auto;padding:16px}
  header{position:sticky;top:0;background:#fff;border-bottom:1px solid var(--ring);z-index:10}
  .grid{display:grid;grid-template-columns:280px 1fr;gap:18px}
  .card{background:var(--card);border:1px solid var(--ring);border-radius:20px;box-shadow:0 10px 30px rgba(2,6,23,.06)}
  .filters{padding:14px;position:sticky;top:70px}
  .filters h3{margin:6px 0 12px}
  .filters input,.filters select{width:100%;height:40px;border:1px solid var(--ring);border-radius:12px;padding:0 10px;margin:6px 0}
  .pill{display:inline-block;padding:8px 12px;border-radius:999px;border:1px solid var(--ring);background:#fff;color:#000;font-size:12px}
  .rooms{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
  .hotel{overflow:hidden}
  .hotel img{width:100%;height:160px;object-fit:cover}
  .pad{padding:12px}
  .muted{color:var(--muted)}
  .topbar{display:flex;justify-content:space-between;align-items:center;margin:12px 0}
  @media(max-width:980px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<header>
  <div class="wrap" style="display:flex;justify-content:space-between;align-items:center;">
    <div style="font-weight:800">Sheraton Hotels</div>
    <button class="pill" onclick="window.location.href='index.php'">New Search</button>
  </div>
</header>
 
<main class="wrap" style="padding-top:16px;">
  <div class="topbar">
    <div>
      <div style="font-size:14px" class="muted">
        Results for <b><?=htmlspecialchars($city ?: 'Selected Hotel')?></b> ·
        <?=htmlspecialchars($checkin ?: '—')?> → <?=htmlspecialchars($checkout ?: '—')?> ·
        Guests: <?= (int)$guests ?>
      </div>
    </div>
    <div>
      <select id="sort" class="pill" onchange="applySort()">
        <option value="price_asc" <?=$sort==='price_asc'?'selected':''?>>Price: Low to High</option>
        <option value="price_desc" <?=$sort==='price_desc'?'selected':''?>>Price: High to Low</option>
        <option value="best" <?=$sort==='best'?'selected':''?>>Best Rated</option>
      </select>
    </div>
  </div>
 
  <div class="grid">
    <aside class="card filters">
      <h3>Filters</h3>
      <label class="muted">Min Price</label>
      <input type="number" id="min" value="<?=htmlspecialchars($minPrice)?>">
      <label class="muted">Max Price</label>
      <input type="number" id="max" value="<?=htmlspecialchars($maxPrice)?>">
      <label class="muted">Min Rating</label>
      <select id="rating">
        <?php for($r=0;$r<=5;$r+=1): ?>
          <option value="<?=$r?>" <?=$rating==$r?'selected':''?>><?=$r?>+ stars</option>
        <?php endfor; ?>
      </select>
      <button class="pill" style="width:100%;margin-top:8px;background:#eef2ff;border-color:#c7d2fe;color:#3730a3" onclick="applyFilters()">Apply</button>
    </aside>
 
    <section>
      <div class="rooms">
        <?php if ($rooms->num_rows === 0): ?>
          <div class="card" style="padding:20px">No rooms found. Try adjusting filters.</div>
        <?php endif; ?>
        <?php while($r = $rooms->fetch_assoc()): ?>
          <article class="card hotel">
            <img src="<?=htmlspecialchars($r['image_url'] ?: $r['hotel_img'])?>" alt="">
            <div class="pad">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div style="font-weight:800"><?=htmlspecialchars($r['title'])?></div>
                <div class="pill">⭐ <?=number_format((float)$r['hotel_rating'],1)?></div>
              </div>
              <div class="muted" style="margin-top:4px">
                <?=htmlspecialchars($r['hotel_name'])?> — <?=htmlspecialchars($r['city'])?>
              </div>
              <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                <?php
                  $amen = json_decode($r['amenities'] ?? '[]', true) ?: [];
                  foreach(array_slice($amen,0,4) as $a){ echo '<span class="pill">'.htmlspecialchars($a).'</span>'; }
                ?>
              </div>
              <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:end;">
                <div>
                  <div class="muted">Per night</div>
                  <div style="font-weight:900;font-size:22px">PKR <?=number_format((float)$r['price_per_night']*280,0)?></div>
                </div>
                <div style="display:flex;gap:8px;">
                  <button class="pill" onclick="viewRoom(<?= (int)$r['id'] ?>)">View</button>
                  <button class="pill" style="background:#22c55e1a;border-color:#86efac;color:#166534" onclick="bookNow(<?= (int)$r['id'] ?>)">Book</button>
                </div>
              </div>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
    </section>
  </div>
</main>
 
<script>
  function queryMerge(k,v){
    const url = new URL(window.location.href);
    if(v===null) url.searchParams.delete(k); else url.searchParams.set(k,v);
    return url.search;
  }
  function applySort(){ window.location.search = queryMerge('sort', document.getElementById('sort').value); }
  function applyFilters(){
    const min = document.getElementById('min').value || 0;
    const max = document.getElementById('max').value || 9999;
    const rating = document.getElementById('rating').value || 0;
    let s = queryMerge('min', min);
    history.replaceState(null,'', s);
    s = queryMerge('max', max);
    history.replaceState(null,'', s);
    s = queryMerge('rating', rating);
    window.location.search = s;
  }
  function viewRoom(roomId){
    const url = new URL(window.location.href);
    const checkin = url.searchParams.get('checkin') || '';
    const checkout = url.searchParams.get('checkout') || '';
    const guests = url.searchParams.get('guests') || 2;
    window.location.href = `room.php?id=${roomId}&checkin=${encodeURIComponent(checkin)}&checkout=${encodeURIComponent(checkout)}&guests=${encodeURIComponent(guests)}`;
  }
  function bookNow(roomId){
    const url = new URL(window.location.href);
    const checkin = url.searchParams.get('checkin') || '';
    const checkout = url.searchParams.get('checkout') || '';
    const guests = url.searchParams.get('guests') || 2;
    window.location.href = `book.php?room_id=${roomId}&checkin=${encodeURIComponent(checkin)}&checkout=${encodeURIComponent(checkout)}&guests=${encodeURIComponent(guests)}`;
  }
</script>
</body>
</html>
