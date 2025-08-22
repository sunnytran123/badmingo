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
            <?php
        $categories = [];
        $catResult = $conn->query("SELECT category_id, category_name FROM product_categories");
        $categories = $catResult->fetch_all(MYSQLI_ASSOC);

        if ($catResult && $catResult->num_rows > 0) {
            while ($row = $catResult->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        ?>
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
        <div class="product">
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
                <div class="product-actions">
                    <button class="btn-add-cart" 
                        onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>', <?php echo $product['price']; ?>)">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                    <button class="btn-buy-now" 
                        onclick="buyNow(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>)">
                        <i class="fas fa-credit-card"></i> Mua ngay
                    </button>
                </div>
            </div>
        </div>
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

<!-- Giỏ hàng mini -->
<div id="cart-sidebar" class="cart-sidebar">
    <div class="cart-header">
        <h3>Giỏ hàng</h3>
        <button onclick="closeCart()" class="close-cart">&times;</button>
    </div>
    <div class="cart-items-header">
        <label class="filter-checkbox">
            <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
            Chọn tất cả
        </label>
    </div>
    <div id="cart-items" class="cart-items">
        <!-- Các sản phẩm trong giỏ hàng sẽ hiển thị ở đây -->
    </div>
    <div class="cart-footer">
        <div class="cart-total">
            <strong>Tổng cộng: <span id="cart-total">0đ</span></strong>
        </div>
        <button onclick="checkout()" class="btn-checkout">Thanh toán</button>
    </div>
</div>

<!-- Nút mở giỏ hàng -->
<button id="cart-toggle" class="cart-toggle" onclick="toggleCart()">
    <i class="fas fa-shopping-cart"></i>
    <span id="cart-count">0</span>
</button>

<style>
/* CSS cho phần giới thiệu */
.shop-intro {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
    max-height: 250px;
    overflow: hidden;
}

.shop-intro img {
    width: 250px;
    object-fit: cover;
    border-radius: 8px;
}

.shop-intro-content {
    flex: 1;
}

/* CSS cho bộ lọc và sản phẩm */
.shop-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
    align-items: flex-start;
}

/* CSS cho bộ lọc */
.product-filter {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 250px;
    min-height: 300px;
    overflow-y: auto;
}

.filter-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.filter-section h3 {
    font-size: 16px;
    margin-bottom: 5px;
    color: #333;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #333;
}

.filter-checkbox input {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.filter-submit {
    background: #007bff;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
    width: 100%;
}

.filter-submit:hover {
    background: #0056b3;
}

/* CSS cho sản phẩm */
.products {
    display: grid;
    grid-template-columns: repeat(3, 300px);
    gap: 15px;
    margin-bottom: 40px;
    flex: 1;
}

.product {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    min-height: 380px;
    max-height: none;
}

.product:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.product img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product:hover img {
    transform: scale(1.05);
}

.product-info {
    padding: 10px;
    min-height: 120px;
    height: auto;
    overflow: visible;
}

.product-info h3 {
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 2px;
    color: #333;
    min-height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.description {
    color: #666;
    font-size: 10px;
    margin-bottom: 2px;
    min-height: 24px;
    line-height: 1.1;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.price {
    color: #dc3545;
    font-weight: bold;
    font-size: 12px;
    margin-bottom: 2px;
}

.stock {
    color: #28a745;
    font-size: 10px;
    margin-bottom: 2px;
}

.product-actions {
    display: flex;
    gap: 5px;
    justify-content: space-between;
}

.btn-add-cart {
    background: #007bff;
    color: white;
    width: 40px;
    height: 30px;
    padding: 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.btn-add-cart:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.btn-buy-now {
    background: #28a745;
    color: white;
    flex: 1;
    height: 30px;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 12px;
    transition: background 0.3s ease, transform 0.2s ease;
}

.btn-buy-now:hover {
    background: #1e7e34;
    transform: translateY(-2px);
}

/* CSS cho giỏ hàng */
.cart-sidebar {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100vh;
    background: white;
    box-shadow: -5px 0 15px rgba(0,0,0,0.1);
    transition: right 0.3s ease;
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.cart-sidebar.open {
    right: 0;
}

.cart-header {
    background: #007bff;
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h3 {
    margin: 0;
    font-size: 20px;
}

.close-cart {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: auto;
}

.cart-items-header {
    padding: 10px 20px;
    border-bottom: 1px solid #eee;
}

.cart-items {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.cart-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.cart-item-info {
    flex: 1;
}

.cart-item-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.cart-item-price {
    color: #dc3545;
    font-weight: bold;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 5px;
}

.quantity-btn {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px 10px;
    cursor: pointer;
    width: auto;
}

.cart-footer {
    padding: 20px;
    border-top: 1px solid #eee;
    background: #f8f9fa;
}

.cart-total {
    margin-bottom: 15px;
    font-size: 18px;
}

.btn-checkout {
    width: 100%;
    background: #28a745;
    color: white;
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-checkout:hover {
    background: #1e7e34;
}

.cart-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.cart-toggle:hover {
    background: #0056b3;
    transform: scale(1.1);
}

#cart-count {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: -5px;
    right: -5px;
}

/* Responsive */
@media (max-width: 768px) {
    .cart-sidebar {
        width: 100%;
        right: -100%;
    }
    
    .shop-container {
        flex-direction: column;
    }
    
    .product-filter {
        width: 100%;
        min-height: auto;
    }
    
    .products {
        grid-template-columns: 1fr;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .filter-section {
        margin-bottom: 15px;
    }
}
</style>

<script>
// Sử dụng CSDL qua AJAX, KHÔNG dùng localStorage

let cart = [];
let cartTotal = 0;

// Lấy giỏ hàng từ server
function loadCart() {
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cart = data.items.map(item => ({
                id: item.product_id,
                name: item.product_name,
                price: parseFloat(item.price),
                quantity: parseInt(item.quantity),
                selected: false // Thêm thuộc tính selected
            }));
            updateCartDisplay();
        } else {
            cart = [];
            updateCartDisplay();
        }
    });
}

// Hiển thị giỏ hàng
function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const cartTotalElement = document.getElementById('cart-total');
    const selectAllCheckbox = document.getElementById('select-all');
    
    cartItems.innerHTML = '';
    cartTotal = 0;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p style="text-align: center; color: #666; margin-top: 50px;">Giỏ hàng trống</p>';
        selectAllCheckbox.checked = false;
    } else {
        cart.forEach((item, index) => {
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <input type="checkbox" class="cart-item-checkbox" 
                       onchange="toggleItemSelection(${index})" ${item.selected ? 'checked' : ''}>
                <img src="images/sport1.webp" 
                     alt="${item.name}" 
                     onerror="this.src='https://via.placeholder.com/60x60?text=${encodeURIComponent(item.name)}'">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatPrice(item.price)}đ</div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        <button class="quantity-btn" onclick="removeFromCart(${index})" style="margin-left: 10px; background: #dc3545; color: white;">Xóa</button>
                    </div>
                </div>
            `;
            cartItems.appendChild(itemElement);
            if (item.selected) {
                cartTotal += item.price * item.quantity;
            }
        });
        selectAllCheckbox.checked = cart.every(item => item.selected);
    }
    
    cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
    cartTotalElement.textContent = formatPrice(cartTotal);
}

// Chọn/bỏ chọn tất cả
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    cart.forEach(item => item.selected = selectAllCheckbox.checked);
    updateCartDisplay();
}

// Chọn/bỏ chọn từng sản phẩm
function toggleItemSelection(index) {
    cart[index].selected = !cart[index].selected;
    updateCartDisplay();
}

// Thêm vào giỏ hàng (gọi AJAX tới cart.php)
function addToCart(productId, productName, price) {
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=add&product_id=${productId}&quantity=1`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã thêm vào giỏ hàng!');
            loadCart();
        } else {
            alert(data.message);
        }
    });
}

// Mua ngay (chuyển thẳng tới thanh toán với sản phẩm được chọn)
function buyNow(productId, productName, price, stock) {
    const quantity = 1; // Hardcoded to 1 for buy now
    if (stock < quantity) {
        alert('Sản phẩm hết hàng!');
        return;
    }
    window.location.href = `thanhtoan.php?product_id=${productId}&quantity=${quantity}`;
}

// Cập nhật số lượng
function updateQuantity(index, change) {
    const item = cart[index];
    let newQty = item.quantity + change;
    if (newQty <= 0) {
        removeFromCart(index);
        return;
    }
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update&product_id=${item.id}&quantity=${newQty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadCart();
    });
}

// Xóa khỏi giỏ hàng
function removeFromCart(index) {
    const item = cart[index];
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove&product_id=${item.id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadCart();
    });
}

// Mở/đóng giỏ hàng
function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    cartSidebar.classList.toggle('open');
}

function closeCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    cartSidebar.classList.remove('open');
}

// Thanh toán
function checkout() {
    const selectedItems = cart.filter(item => item.selected);
    if (selectedItems.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán!');
        return;
    }
    const selectedIds = selectedItems.map(item => item.id).join(',');
    window.location.href = `thanhtoan.php?selected_ids=${selectedIds}`;
}

// Format giá tiền
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

// Hiển thị thông báo
function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 1001;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 2000);
}

// Thêm CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Khởi tạo hiển thị giỏ hàng khi trang load
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
});
</script>

<?php include 'includes/footer.php'; ?><?php 
