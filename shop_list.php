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
<div class="pagination" style="text-align:center; margin-bottom:40px; display:flex; justify-content:center; align-items:center;">
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
.cart-toggle { position: fixed; bottom: 110px; right: 30px; background: #007bff; color: white; border: none; border-radius: 50%; width: 60px; height: 60px; font-size: 20px; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; display:flex; align-items:center; justify-content:center; }
.cart-toggle:hover { background: #0056b3; transform: scale(1.1); }
@media (max-width: 768px) {
	.shop-container { flex-direction:column; }
	.product-filter { width:100%; min-height:auto; }
	.products { grid-template-columns: 1fr; }
}
</style>

<style>
.cart-sidebar { position: fixed; top: 0; right: -400px; width: 400px; height: 100vh; background: #fff; box-shadow: -5px 0 15px rgba(0,0,0,0.1); transition: right 0.3s ease; z-index: 1000; display: flex; flex-direction: column; }
.cart-sidebar.open { right: 0; }
.cart-header { background: #007bff; color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
.cart-header h3 { margin: 0; font-size: 18px; }
.close-cart { background: none; border: none; color: #fff; font-size: 18px; width: 28px; height: 28px; line-height: 1; cursor: pointer; display:inline-flex; align-items:center; justify-content:center; }
 .cart-items-header { padding: 10px 20px; border-bottom: 1px solid #eee; }
 .cart-items { flex: 1; padding: 16px 20px; overflow-y: auto; }
 .cart-item { display: grid; grid-template-columns: 24px 60px 1fr auto; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eee; }
 .cart-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
 .cart-item-name { font-weight: 600; font-size: 14px; }
 .cart-item-meta { color: #666; font-size: 12px; margin-top: 4px; }
 .qty-stepper { display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden; background:#fff; }
 .qty-btn { width: 32px; height: 32px; display:flex; align-items:center; justify-content:center; background:#ffffff; border:none; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; cursor:pointer; font-size:16px; line-height:1; color:#111827; }
 .qty-btn:first-child { border-left: none; }
 .qty-btn:last-child { border-right: none; }
 .qty-btn:hover { background:#f2f4f7; }
 .qty-input { width: 44px; text-align:center; border: none; outline: none; font-size:14px; color:#111827; background:#fff; }
 .cart-footer { padding: 16px 20px; border-top: 1px solid #eee; background: #f8f9fa; }
 .btn-checkout { width: 100%; background: #28a745; color: #fff; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
 .btn-checkout:hover { background: #1e7e34; }
 #cart-count { background: #dc3545; color: #fff; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; display: flex; align-items: center; justify-content: center; position: absolute; top: -5px; right: -5px; }
</style>

<div id="cart-sidebar" class="cart-sidebar">
    <div class="cart-header">
        <h3>Giỏ hàng</h3>
        <button onclick="closeCart()" class="close-cart">&times;</button>
    </div>
    <div class="cart-items-header">
    <label class="filter-checkbox"><input type="checkbox" id="select-all"> Chọn tất cả</label>
</div>
<div id="cart-items" class="cart-items"></div>
    <div class="cart-footer">
        <div class="cart-total"><strong>Tổng cộng: <span id="cart-total">0đ</span></strong></div>
        <button onclick="checkout()" class="btn-checkout">Thanh toán</button>
    </div>
</div>

<button id="cart-toggle" class="cart-toggle" onclick="toggleCart()" style="z-index:10001;">
    <i class="fas fa-shopping-cart"></i>
    <span id="cart-count">0</span>
</button>

<script>
function toggleCart(){
    var el = document.getElementById('cart-sidebar');
    el.classList.toggle('open');
}
function closeCart(){
    var el = document.getElementById('cart-sidebar');
    el.classList.remove('open');
}
function formatPrice(n){ try { return new Intl.NumberFormat('vi-VN').format(n); } catch(e){ return n; } }
function recalcTotals(){
    var total = 0;
    document.querySelectorAll('#cart-items .cart-item').forEach(function(row){
        var cb = row.querySelector('.ci-check');
        if (cb && cb.checked) {
            var price = parseFloat(row.getAttribute('data-price')||'0');
            var qtyEl = row.querySelector('.qty-input');
            var qty = parseInt(qtyEl && qtyEl.value ? qtyEl.value : '1');
            total += price * qty;
        }
    });
    var totalEl = document.getElementById('cart-total');
    if (totalEl) totalEl.textContent = formatPrice(total) + 'đ';
}
function loadCart(){
    fetch('cart.php', { method: 'POST', headers: { 'Content-Type':'application/x-www-form-urlencoded' }, body: 'action=get' })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (!data.success) return;
        var items = data.items || [];
        var wrap = document.getElementById('cart-items');
        wrap.innerHTML = '';
        items.forEach(function(it){
            var price = parseFloat(it.price || 0);
            var qty = parseInt(it.quantity || 0);
            var div = document.createElement('div');
            div.className = 'cart-item';
            div.setAttribute('data-price', price);
            var meta = [];
            if (it.color) meta.push('Màu: ' + it.color);
            if (it.size) meta.push('Size: ' + it.size);
            var imgSrc = it.image_url ? ('images/' + it.image_url) : 'images/sport1.webp';
            div.innerHTML =
                '<input type="checkbox" class="ci-check" data-id="'+ (it.cart_item_id||'') +'" data-pid="'+ it.product_id +'">' +
                '<img src="'+ imgSrc +'" alt="">' +
                '<div><div class="cart-item-name">' + (it.product_name || '') + '</div>' +
                '<div class="cart-item-meta">' + meta.join(' • ') + '</div>' +
                '<div class="qty-stepper" data-pid="'+ it.product_id +'" data-vid="'+ (it.variant_id||'') +'" data-color="'+ (it.color||'') +'" data-size="'+ (it.size||'') +'">' +
                    '<button class="qty-btn" data-delta="-1">-</button>' +
                    '<input class="qty-input" type="text" value="'+ qty +'" readonly>' +
                    '<button class="qty-btn" data-delta="1">+</button>' +
                '</div></div>' +
                '<div class="cart-item-price">' + formatPrice(price) + 'đ</div>';
            wrap.appendChild(div);
        });
        var cc = document.getElementById('cart-count'); if (cc) cc.textContent = items.length;
        recalcTotals();
    }).catch(function(){});
}
function checkout(){
    var selected = Array.from(document.querySelectorAll('#cart-items .ci-check:checked'))
                  .map(function(cb){ return parseInt(cb.getAttribute('data-pid')||'0'); })
                  .filter(function(v){ return v>0; });
    if (selected.length === 0) { alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán!'); return; }
    var params = new URLSearchParams();
    params.set('selected_ids', selected.join(','));
    window.location.href = 'thanhtoan.php?' + params.toString();
}

document.addEventListener('DOMContentLoaded', function(){
    loadCart();
    var selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('#cart-items .ci-check').forEach(function(cb){ cb.checked = selectAll.checked; });
            recalcTotals();
        });
    }
    document.getElementById('cart-items').addEventListener('click', function(e){
        var btn = e.target.closest('.qty-btn');
        if (btn) {
            var stepper = btn.closest('.qty-stepper');
            var delta = parseInt(btn.getAttribute('data-delta')) || 0;
            var input = stepper.querySelector('.qty-input');
            var qty = Math.max(1, (parseInt(input.value)||1) + delta);
            input.value = qty;
            var pid = parseInt(stepper.getAttribute('data-pid'));
            var vid = stepper.getAttribute('data-vid');
            var color = stepper.getAttribute('data-color');
            var size = stepper.getAttribute('data-size');
            var body = new URLSearchParams();
            body.append('action','update');
            body.append('product_id', pid);
            body.append('quantity', qty);
            if (vid) body.append('variant_id', vid);
            if (color) body.append('color', color);
            if (size) body.append('size', size);
            fetch('cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
              .then(function(r){ return r.json(); })
              .then(function(){ loadCart(); });
            return;
        }
        var cb = e.target.closest('.ci-check');
        if (cb) { recalcTotals(); return; }
    });
});
</script>

<?php include 'includes/footer.php'; ?>  