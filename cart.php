<?php
session_start();
include 'config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
	echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
	exit;
}

$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
$variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? intval($_POST['variant_id']) : null;
$color = isset($_POST['color']) ? trim($_POST['color']) : null;
$size = isset($_POST['size']) ? trim($_POST['size']) : null;

// Ensure variant columns exist (best-effort)
try {
	$hasVariantCol = false; $hasSizeCol = false; $hasColorCol = false;
	if ($res = $conn->query("SHOW COLUMNS FROM cart_items LIKE 'variant_id'")) { $hasVariantCol = $res->num_rows > 0; }
	if ($res = $conn->query("SHOW COLUMNS FROM cart_items LIKE 'size'")) { $hasSizeCol = $res->num_rows > 0; }
	if ($res = $conn->query("SHOW COLUMNS FROM cart_items LIKE 'color'")) { $hasColorCol = $res->num_rows > 0; }
	if (!$hasVariantCol) { $conn->query("ALTER TABLE cart_items ADD COLUMN variant_id INT NULL"); $hasVariantCol = true; }
	if (!$hasSizeCol) { $conn->query("ALTER TABLE cart_items ADD COLUMN size VARCHAR(50) NULL"); $hasSizeCol = true; }
	if (!$hasColorCol) { $conn->query("ALTER TABLE cart_items ADD COLUMN color VARCHAR(50) NULL"); $hasColorCol = true; }
} catch (Throwable $e) {
	// ignore if no permission; fallback to product-only cart
}

if ($action == 'add') {
	if ($product_id <= 0 || $quantity <= 0) {
		echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
		exit;
	}

	// If variant provided, validate it belongs to product and in stock (best-effort)
	if (!is_null($variant_id) && $variant_id > 0) {
		$vStmt = $conn->prepare("SELECT pv.variant_id, pv.product_id, pv.stock FROM product_variants pv WHERE pv.product_id = ? AND pv.variant_id = ?");
		if ($vStmt) {
			$vStmt->bind_param("ii", $product_id, $variant_id);
			$vStmt->execute();
			$vRes = $vStmt->get_result();
			$v = $vRes->fetch_assoc();
			if (!$v) {
				echo json_encode(['success' => false, 'message' => 'Biến thể không tồn tại']);
				exit;
			}
			if (intval($v['stock']) < $quantity) {
				echo json_encode(['success' => false, 'message' => 'Biến thể không đủ hàng']);
				exit;
			}
		}
	}

	// Insert or update
	if (isset($hasVariantCol) && $hasVariantCol) {
		// Use variant-awareness in cart
		$stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE user_id=? AND product_id=? AND (variant_id <=> ?) AND (COALESCE(size,'') <=> ?) AND (COALESCE(color,'') <=> ?)");
		$sizeVal = $size ?? '';
		$colorVal = $color ?? '';
		$stmt->bind_param("iiiss", $user_id, $product_id, $variant_id, $sizeVal, $colorVal);
		$stmt->execute();
		$result = $stmt->get_result();
		$item = $result->fetch_assoc();
		if ($item) {
			$stmt2 = $conn->prepare("UPDATE cart_items SET quantity=quantity+? WHERE cart_item_id=?");
			$stmt2->bind_param("ii", $quantity, $item['cart_item_id']);
			$stmt2->execute();
		} else {
			$stmt2 = $conn->prepare("INSERT INTO cart_items (user_id, product_id, variant_id, size, color, quantity) VALUES (?, ?, ?, ?, ?, ?)");
			$stmt2->bind_param("iiissi", $user_id, $product_id, $variant_id, $sizeVal, $colorVal, $quantity);
			$stmt2->execute();
		}
	} else {
		// Fallback: product-only cart
		$stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE user_id=? AND product_id=?");
		$stmt->bind_param("ii", $user_id, $product_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$item = $result->fetch_assoc();
		if ($item) {
			$stmt2 = $conn->prepare("UPDATE cart_items SET quantity=quantity+? WHERE cart_item_id=?");
			$stmt2->bind_param("ii", $quantity, $item['cart_item_id']);
			$stmt2->execute();
		} else {
			$stmt2 = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
			$stmt2->bind_param("iii", $user_id, $product_id, $quantity);
			$stmt2->execute();
		}
	}
	echo json_encode(['success' => true]);
	exit;
}

if ($action == 'remove') {
	if (isset($hasVariantCol) && $hasVariantCol && (!is_null($variant_id) || !empty($size) || !empty($color))) {
		$stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=? AND (variant_id <=> ?) AND (COALESCE(size,'') <=> ?) AND (COALESCE(color,'') <=> ?)");
		$sizeVal = $size ?? '';
		$colorVal = $color ?? '';
		$stmt->bind_param("iiiss", $user_id, $product_id, $variant_id, $sizeVal, $colorVal);
	} else {
		$stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=?");
		$stmt->bind_param("ii", $user_id, $product_id);
	}
	$stmt->execute();
	echo json_encode(['success' => true]);
	exit;
}

if ($action == 'update') {
	if ($quantity <= 0) { $quantity = 1; }
	if (isset($hasVariantCol) && $hasVariantCol && (!is_null($variant_id) || !empty($size) || !empty($color))) {
		$stmt = $conn->prepare("UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=? AND (variant_id <=> ?) AND (COALESCE(size,'') <=> ?) AND (COALESCE(color,'') <=> ?)");
		$sizeVal = $size ?? '';
		$colorVal = $color ?? '';
		$stmt->bind_param("iiiiss", $quantity, $user_id, $product_id, $variant_id, $sizeVal, $colorVal);
	} else {
		$stmt = $conn->prepare("UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?");
		$stmt->bind_param("iii", $quantity, $user_id, $product_id);
	}
	$stmt->execute();
	echo json_encode(['success' => true]);
	exit;
}

if ($action == 'get') {
	if (isset($hasVariantCol) && $hasVariantCol) {
		// Return variant-aware items with resolved price
		$stmt = $conn->prepare("SELECT ci.product_id, ci.variant_id, ci.size, ci.color, COALESCE(pv.price, p.price) AS price, p.product_name, ci.quantity
			FROM cart_items ci
			JOIN products p ON ci.product_id = p.product_id
			LEFT JOIN product_variants pv ON ci.variant_id = pv.variant_id AND pv.product_id = p.product_id
			WHERE ci.user_id=?");
		$stmt->bind_param("i", $user_id);
	} else {
		$stmt = $conn->prepare("SELECT ci.product_id, NULL as variant_id, NULL as size, NULL as color, p.price, p.product_name, ci.quantity
			FROM cart_items ci
			JOIN products p ON ci.product_id = p.product_id
			WHERE ci.user_id=?");
		$stmt->bind_param("i", $user_id);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	$items = [];
	while ($row = $result->fetch_assoc()) {
		$items[] = $row;
	}
	echo json_encode(['success' => true, 'items' => $items]);
	exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);