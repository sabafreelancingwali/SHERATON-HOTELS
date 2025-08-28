<?php require_once "db.php";
$id = (int)($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("SELECT b.*, r.title, h.name AS hotel_name FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN hotels h ON h.id=r.hotel_id WHERE b.id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$bk = $stmt->get_result()->fetch_assoc();
if(!$bk){ die("Booking not found"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Booking Confirmed #<?= (int)$bk['id'] ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--ring:#e2e8f0;--brand:#8b5cf6}
  body{margin:0;background:#f8fafc;font-family:Inter,system-ui,Segoe UI,Roboto}
  .wrap{max-width:700px;margin:0 auto;padding:16px}
  .card{background:#fff;border:1px solid var(--ring);border-radius:20px;box-shadow:0 10px 30px rgba(2,6,23,.06);padding:18px}
  .pill{display:inline-block;padding:10px 14px;border-radius:999px;border:1px solid var(--ring);background:#fff;font-weight:700}
</style>
</head>
<body>
<header class="wrap" style="display:flex;justify-content:space-between;align-items:center;">
  <div style="font-weight:800">Sheraton Hotels</div>
  <button class="pill" onclick="window.location.href='index.php'">Home</button>
</header>
 
<main class="wrap">
  <div class="card">
    <div style="font-size:14px;color:#10b981;font-weight:800;">✓ Booking Confirmed</div>
    <h2 style="margin:6px 0;">#<?= (int)$bk['id'] ?> — <?=htmlspecialchars($bk['hotel_name'])?></h2>
    <div style="color:#475569"><?=htmlspecialchars($bk['title'])?></div>
 
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
      <div><b>Guest:</b> <?=htmlspecialchars($bk['guest_name'])?></div>
      <div><b>Email:</b> <?=htmlspecialchars($bk['email'])?></div>
      <div><b>Check-in:</b> <?=htmlspecialchars($bk['checkin'])?></div>
      <div><b>Check-out:</b> <?=htmlspecialchars($bk['checkout'])?></div>
      <div><b>Guests:</b> <?= (int)$bk['guests'] ?></div>
      <div><b>Booked:</b> <?=htmlspecialchars($bk['created_at'])?></div>
    </div>
 
    <div style="margin-top:16px;display:flex;gap:8px">
      <button class="pill" onclick="window.print()">Print</button>
      <button class="pill" style="background:#eef2ff;border-color:#c7d2fe;color:#3730a3" onclick="window.location.href='index.php'">Book Another</button>
    </div>
  </div>
</main>
</body>
</html>
