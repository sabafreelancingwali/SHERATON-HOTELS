?php require_once "db.php";
$room_id = (int)($_GET['room_id'] ?? 0);
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = max(1,(int)($_GET['guests'] ?? 1));
 
$stmt = $mysqli->prepare("SELECT r.title, r.price_per_night, h.name AS hotel_name FROM rooms r JOIN hotels h ON h.id=r.hotel_id WHERE r.id=?");
$stmt->bind_param("i",$room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
if(!$room){ die("Room not found"); }
 
$createdId = 0;
$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $guest_name = trim($_POST['guest_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $checkin_p = $_POST['checkin'] ?? $checkin;
  $checkout_p = $_POST['checkout'] ?? $checkout;
  $guests_p = max(1,(int)($_POST['guests'] ?? $guests));
 
  if($guest_name && $email && $checkin_p && $checkout_p){
    $ins = $mysqli->prepare("INSERT INTO bookings (room_id, guest_name, email, phone, checkin, checkout, guests) VALUES (?,?,?,?,?,?,?)");
    $ins->bind_param("isssssi", $room_id, $guest_name, $email, $phone, $checkin_p, $checkout_p, $guests_p);
    if($ins->execute()){
      $createdId = $ins->insert_id;
      echo "<script>window.location.href='confirm.php?id={$createdId}';</script>";
      exit;
    } else { $err = "Booking failed. Please try again."; }
  } else { $err = "Please fill required fields."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book: <?=htmlspecialchars($room['title'])?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--ring:#e2e8f0}
  body{margin:0;background:#f8fafc;font-family:Inter,system-ui,Segoe UI,Roboto}
  .wrap{max-width:800px;margin:0 auto;padding:16px}
  .card{background:#fff;border:1px solid var(--ring);border-radius:20px;box-shadow:0 10px 30px rgba(2,6,23,.06);padding:16px}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  input{height:44px;border:1px solid var(--ring);border-radius:12px;padding:0 10px;width:100%}
  .pill{display:inline-block;padding:10px 14px;border-radius:999px;border:1px solid var(--ring);background:#fff;font-weight:700}
  @media(max-width:860px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="wrap" style="display:flex;justify-content:space-between;align-items:center;">
  <div style="font-weight:800">Sheraton Hotels</div>
  <button class="pill" onclick="history.back()">Back</button>
</header>
 
<main class="wrap">
  <h2>Booking — <?=htmlspecialchars($room['hotel_name'])?> / <?=htmlspecialchars($room['title'])?></h2>
  <form method="post" class="card">
    <?php if($err): ?><div style="background:#fee2e2;border:1px solid #fecaca;padding:10px;border-radius:12px;margin-bottom:10px;color:#7f1d1d"><?=$err?></div><?php endif; ?>
    <div class="grid">
      <div>
        <label>Name*</label>
        <input type="text" name="guest_name" required>
      </div>
      <div>
        <label>Email*</label>
        <input type="email" name="email" required>
      </div>
      <div>
        <label>Phone</label>
        <input type="text" name="phone">
      </div>
      <div>
        <label>Guests</label>
        <input type="number" name="guests" min="1" value="<?= (int)$guests ?>">
      </div>
      <div>
        <label>Check-in*</label>
        <input type="date" name="checkin" value="<?=htmlspecialchars($checkin)?>" required>
      </div>
      <div>
        <label>Check-out*</label>
        <input type="date" name="checkout" value="<?=htmlspecialchars($checkout)?>" required>
      </div>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px">
      <div>Price/Night: <b>PKR <?=number_format((float)$room['price_per_night']*280,0)?></b></div>
      <button class="pill" type="submit" style="background:#22c55e1a;border-color:#86efac;color:#166534">Confirm Booking</button>
    </div>
  </form>
</main>
</body>
</html>
