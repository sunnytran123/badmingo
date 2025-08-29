<?php 
session_start();
require_once "config/database.php";
include 'includes/header.php';

// Lấy danh mục sản phẩm
$categorySql = "SELECT category_id, category_name FROM product_categories ORDER BY category_name";
$stmt = $conn->prepare($categorySql);
$stmt->execute();
$result = $stmt->get_result();
$categories = [];
while ($row = $result->fetch_assoc()) {
	$categories[] = $row;
}

// Xử lý bộ lọc
$filterCategory = isset($_GET['category']) && is_array($_GET['category']) ? array_map('intval', $_GET['category']) : [];
$filterPrice = isset($_GET['price']) && is_array($_GET['price']) ? $_GET['price'] : [];

$where = [];
$params = [];
$types = "";

// Lọc theo loại sản phẩm
if (!empty($filterCategory)) {
	$where[] = "p.category_id IN (" . implode(',', array_fill(0, count($filterCategory), '?')) . ")";
	foreach ($filterCategory as $catId) {
		$params[] = $catId;
		$types .= "i";
	}
}

// Lọc theo giá
if (!empty($filterPrice)) {
	$priceConditions = [];
	foreach ($filterPrice as $price) {
		if ($price == '1') {
			$priceConditions[] = "p.price < 500000";
		} elseif ($price == '2') {
			$priceConditions[] = "p.price >= 500000 AND p.price <= 1000000";
		} elseif ($price == '3') {
			$priceConditions[] = "p.price > 1000000";
		}
	}
	if (!empty($priceConditions)) {
		$where[] = "(" . implode(' OR ', $priceConditions) . ")";
	}
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Đếm tổng số sản phẩm
$countSql = "SELECT COUNT(*) AS total FROM products p $whereSql";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
	$countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$result = $countStmt->get_result();
$row = $result->fetch_assoc();
$totalProducts = $row['total'];
$totalPages = ceil($totalProducts / $limit);

// Lấy danh sách sản phẩm và hình ảnh chính từ cơ sở dữ liệu
$sql = "SELECT p.product_id, p.product_name, p.price, p.stock, pi.image_url
		FROM products p
		LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
		$whereSql
		ORDER BY p.product_id
		LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

// Thêm limit và offset vào mảng tham số
$params2 = $params;
$types2 = $types . "ii";
$params2[] = $limit;
$params2[] = $offset;

$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

?>

<h2 class="section-title">Cửa hàng thể thao</h2>

<div class="shop-intro">
	<img src="images/Olypic.png" alt="Cửa hàng thể thao">
	<div class="shop-intro-content">
	<h2>Chào mừng đến với Sunny Sport</h2>
	<p style="text-align:justify">Chúng tôi cung cấp đầy đủ các sản phẩm thể thao chất lượng cao, từ vợt cầu lông, giày thể thao đến các phụ kiện thiết bị chuyên nghiệp. Hãy khám phá bộ sưu tập của chúng tôi!</p>
	</div>
</div>

<!-- Bộ lọc và sản phẩm -->
<div class="shop-container">
	<form method="get" class="product-filter">
		<div class="filter-section">
			<h3>Loại sản phẩm</h3>
			<div class="filter-group">
				<?php foreach($categories as $cat): ?>
					<label class="filter-checkbox">
						<input type="checkbox" name="category[]" value="<?php echo $cat['category_id']; ?>" 
							   <?php if(in_array($cat['category_id'], $filterCategory)) echo 'checked'; ?>>
						<?php echo htmlspecialchars($cat['category_name']); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="filter-section">
			<h3>Giá</h3>
			<div class="filter-group">
				<label class="filter-checkbox">
					<input type="checkbox" name="price[]" value="1" <?php if(in_array('1', $filterPrice)) echo 'checked'; ?>>
					Dưới 500.000đ
				</label>
				<label class="filter-checkbox">
					<input type="checkbox" name="price[]" value="2" <?php if(in_array('2', $filterPrice)) echo 'checked'; ?>>
					500.000đ - 1.000.000đ
				</label>
				<label class="filter-checkbox">
					<input type="checkbox" name="price[]" value="3" <?php if(in_array('3', $filterPrice)) echo 'checked'; ?>>
					Trên 1.000.000đ
				</label>
			</div>
		</div>
		<button type="submit" class="filter-submit">Lọc</button>
	</form>

	<div class="products">
		<?php foreach($products as $product): ?>
		<a class="product" href="t.php?product_id=<?php echo $product['product_id']; ?>" style="text-decoration:none; color:inherit;">
			<?php
				$imgSrc = !empty($product['image_url']) ? 'images/' . $product['image_url'] : 'images/sport1.webp';
			?>
			<img src="<?php echo $imgSrc; ?>"
				 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
				 onerror="this.src='https://via.placeholder.com/300x200?text=<?php echo urlencode($product['product_name']); ?>'">
			<div class="product-info">
				<h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
				<p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</p>
				<p class="stock">Còn lại: <?php echo $product['stock']; ?> sản phẩm</p>
			</div>
		</a>
		<?php endforeach; ?>
	</div>
</div>

<!-- Phân trang -->
<div class="pagination" style="text-align:center; margin-bottom:40px;">
	<?php if ($totalPages > 1): ?>
		<?php for ($i = 1; $i <= $totalPages; $i++): ?>
			<a href="?<?php
				$query = $_GET;
				$query['page'] = $i;
				echo http_build_query($query);
			?>"
			style="display:inline-block;padding:8px 16px;margin:0 2px;border-radius:5px;
			background:<?php echo $i==$page?'#007bff':'#f8f9fa'; ?>;color:<?php echo $i==$page?'#fff':'#333'; ?>;text-decoration:none;">
				<?php echo $i; ?>
			</a>
		<?php endfor; ?>
	<?php endif; ?>
</div>

<style>
.shop-intro { display:flex; align-items:center; gap:20px; margin-bottom:40px; max-height:250px; overflow:hidden; }
.shop-intro img { width:250px; object-fit:cover; border-radius:8px; }
.shop-intro-content { flex:1; }
.shop-container { display:flex; flex-direction:row; gap:20px; align-items:flex-start; }
.product-filter { background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; min-height:300px; overflow-y:auto; }
.filter-section { display:flex; flex-direction:column; gap:10px; margin-bottom:20px; }
.filter-section h3 { font-size:16px; margin-bottom:5px; color:#333; }
.filter-group { display:flex; flex-direction:column; gap:8px; }
.filter-checkbox { display:flex; align-items:center; gap:8px; font-size:14px; color:#333; }
.filter-checkbox input { width:16px; height:16px; cursor:pointer; }
.filter-submit { background:#007bff; color:white; padding:8px 15px; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:background 0.3s ease; width:100%; }
.filter-submit:hover { background:#0056b3; }
.products { display:grid; grid-template-columns: repeat(3, 300px); gap:15px; margin-bottom:40px; flex:1; }
.product { background:white; border-radius:15px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; text-align:center; min-height:380px; max-height:none; }
.product:hover { transform: translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.1); }
.product img { width:100%; height:300px; object-fit:cover; transition: transform 0.3s ease; }
.product:hover img { transform: scale(1.05); }
.product-info { padding:10px; min-height:120px; height:auto; overflow:visible; }
.product-info h3 { font-size:12px; font-weight:bold; margin-bottom:2px; color:#333; min-height:24px; display:flex; align-items:center; justify-content:center; }
.price { color:#dc3545; font-weight:bold; font-size:12px; margin-bottom:2px; }
.stock { color:#28a745; font-size:10px; margin-bottom:2px; }
@media (max-width: 768px) {
	.shop-container { flex-direction:column; }
	.product-filter { width:100%; min-height:auto; }
	.products { grid-template-columns: 1fr; }
}
</style>

<?php include 'includes/footer.php'; ?> 