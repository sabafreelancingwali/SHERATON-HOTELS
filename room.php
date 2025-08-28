<?php require_once "db.php";
$id = (int)($_GET['id'] ?? 0);
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = max(1,(int)($_GET['guests'] ?? 1));
 
$stmt = $mysqli->prepare("SELECT r.*, h.name AS hotel_name, h.city, h.rating, h.description, h.image_url AS hotel_img
                          FROM rooms r JOIN hotels h ON h.id=r.hotel_id WHERE r.id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
if(!$room){ die("Room not found"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?=htmlspecialchars($room['title'])?> | <?=htmlspecialchars($room['hotel_name'])?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--brand:#8b5cf6;--ring:#e2e8f0;--bg:#f8fafc}
  body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto}
  .wrap{max-width:1000px;margin:0 auto;padding:16px}
  .card{background:#fff;border:1px solid var(--ring);border-radius:20px;box-shadow:0 10px 30px rgba(2,6,23,.06)}
  .grid{display:grid;grid-template-columns:1.2fr .8fr;gap:16px}
  .hero{width:100%;height:320px;object-fit:cover;border-radius:16px}
  .pill{display:inline-block;padding:8px 12px;border-radius:999px;border:1px solid var(--ring);background:#fff}
  @media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="wrap" style="display:flex;justify-content:space-between;align-items:center;">
  <div style="font-weight:800">Sheraton Hotels</div>
  <button class="pill" onclick="history.back()">Back</button>
</header>
 
<main class="wrap grid">
  <section class="card" style="padding:14px">
    <img class="hero" src="<?=htmlspecialchars($room['image_url'] ?: $room['hotel_img'])?>" alt="">
    <h2 style="margin:14px 4px 6px"><?=htmlspecialchars($room['title'])?></h2>
    <div style="color:#475569;margin:0 4px 10px">
      <?=htmlspecialchars($room['hotel_name'])?> · <?=htmlspecialchars($room['city'])?> · ⭐ <?=number_format((float)$room['rating'],1)?>
    </div>
    <p style="color:#334155;margin:6px 4px"><?=htmlspecialchars($room['description'])?></p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin:10px 4px">
      <?php foreach(array_slice(json_decode($room['amenities'] ?? '[]', true) ?: [],0,8) as $a){ echo '<span class="pill">'.htmlspecialchars($a).'</span>'; } ?>
    </div>
  </section>
 
  <aside class="card" style="padding:16px">
    <div style="font-size:14px;color:#475569">From</div>
    <div style="font-weight:900;font-size:28px;margin-bottom:8px">PKR <?=number_format((float)$room['price_per_night']*280,0)?></div>
 
    <label style="display:block;color:#475569;margin-top:8px">Check-in</label>
    <input type="date" id="checkin" value="<?=htmlspecialchars($checkin)?>" style="width:100%;height:42px;border:1px solid var(--ring);border-radius:12px;padding:0 10px">
    <label style="display:block;color:#475569;margin-top:8px">Check-out</label>
    <input type="date" id="checkout" value="<?=htmlspecialchars($checkout)?>" style="width:100%;height:42px;border:1px solid var(--ring);border-radius:12px;padding:0 10px">
    <label style="display:block;color:#475569;margin-top:8px">Guests</label>
    <input type="number" id="guests" min="1" max="<?= (int)$room['max_guests'] ?>" value="<?= (int)$guests ?>" style="width:100%;height:42px;border:1px solid var(--ring);border-radius:12px;padding:0 10px">
 
    <button class="pill" style="width:100%;margin-top:14px;background:#22c55e1a;border-color:#86efac;color:#166534"
      onclick="book()">Book Now</button>
  </aside>
</main>
 
<script>
  function book(){
    const cki = document.getElementById('checkin').value;
    const cko = document.getElementById('checkout').value;
    const g = document.getElementById('guests').value || 1;
    if(!cki || !cko){ alert('Please choose dates'); return; }
    window.location.href = `book.php?room_id=<?=$room['id']?>&checkin=${encodeURIComponent(cki)}&checkout=${encodeURIComponent(cko)}&guests=${encodeURIComponent(g)}`;
  }
</script>
</body>
</html>
