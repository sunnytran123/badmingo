<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include 'config/database.php';

$message = '';

// Lấy danh sách danh mục
$stmt = $conn->prepare("SELECT category_id, category_name FROM product_categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $category_id = $_POST['category_id'] ?: null;
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    
    // Kiểm tra tên sản phẩm đã tồn tại
    $check = $conn->prepare("SELECT product_id FROM products WHERE product_name = ?");
    $check->bind_param("s", $product_name);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $message = "⚠️ Tên sản phẩm đã tồn tại!";
    } else {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        try {
            // Thêm sản phẩm mới
            $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, description, price, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisdi", $product_name, $category_id, $description, $price, $stock);
            
            if ($stmt->execute()) {
                $product_id = $conn->insert_id;
                
                // Xử lý upload hình ảnh
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'images/';
                    $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = strtolower(str_replace(' ', '-', $product_name)) . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                            // Thêm hình ảnh vào database
                            $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, alt_text, is_primary) VALUES (?, ?, ?, 1)");
                            $stmt->bind_param("iss", $product_id, $new_filename, $product_name);
                            $stmt->execute();
                        }
                    }
                }
                
                // Xử lý variants (màu sắc và kích thước)
                if (isset($_POST['variants']) && is_array($_POST['variants'])) {
                    $total_stock = 0;
                    
                    foreach ($_POST['variants'] as $variant) {
                        if (!empty($variant['size']) || !empty($variant['color'])) {
                            $size = trim($variant['size']);
                            $color = trim($variant['color']);
                            $variant_stock = intval($variant['stock']);
                            $variant_price = !empty($variant['price']) ? floatval($variant['price']) : $price;
                            
                            // Thêm variant vào database
                            $stmt = $conn->prepare("INSERT INTO product_variants (product_id, size, color, stock, price) VALUES (?, ?, ?, ?, ?)");
                            $stmt->bind_param("issdi", $product_id, $size, $color, $variant_stock, $variant_price);
                            $stmt->execute();
                            
                            $total_stock += $variant_stock;
                        }
                    }
                    
                    // Cập nhật tổng stock của sản phẩm
                    if ($total_stock > 0) {
                        $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
                        $stmt->bind_param("ii", $total_stock, $product_id);
                        $stmt->execute();
                    }
                }
                
                // Commit transaction
                $conn->commit();
                $message = "✅ Thêm sản phẩm thành công!";
                // Reset form
                $_POST = array();
            } else {
                throw new Exception("Lỗi khi thêm sản phẩm");
            }
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $conn->rollback();
            $message = "❌ Có lỗi xảy ra: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<h2 class="section-title">Thêm Sản phẩm mới</h2>

<div class="shop-container" style="display:flex; gap:20px; align-items:flex-start; padding:20px;">
    <!-- Sidebar -->
    <div class="product-filter" style="background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; min-height:500px;">
        <h3 style="font-size:16px; color:#333;">Menu Quản lý</h3>
        <ul style="list-style:none; padding:0;">
            <li><a href="admin.php?section=dashboard" style="display:block; padding:10px; color:#333; text-decoration:none;">Dashboard</a></li>
            <li><a href="admin.php?section=users" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Người dùng</a></li>
            <li><a href="admin.php?section=products" style="display:block; padding:10px; color:#333; text-decoration:none; background:#f8f9fa; border-radius:6px;">Quản lý Sản phẩm</a></li>
            <li><a href="admin.php?section=orders" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Đơn hàng</a></li>
            <li><a href="admin.php?section=bookings" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Đặt sân</a></li>
            <li><a href="admin.php?section=events" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Sự kiện</a></li>
            <li><a href="admin.php?section=forum" style="display:block; padding:10px; color:#333; text-decoration:none;">Quản lý Diễn đàn</a></li>
            <li><a href="admin.php?section=stats" style="display:block; padding:10px; color:#333; text-decoration:none;">Thống kê</a></li>
            <li><a href="admin.php?section=settings" style="display:block; padding:10px; color:#333; text-decoration:none;">Cấu hình</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="admin-content" style="flex:1; background:white; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3>Thêm Sản phẩm mới</h3>
            <a href="admin.php?section=products" class="filter-submit" style="text-decoration:none;">← Quay lại</a>
        </div>

        <?php if ($message): ?>
            <div style="padding:10px; margin-bottom:20px; border-radius:6px; background:<?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>; border:1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" style="max-width:800px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <!-- Thông tin cơ bản -->
                <div>
                    <h4 style="margin-bottom:15px; color:#333; border-bottom:2px solid #eee; padding-bottom:5px;">Thông tin cơ bản</h4>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Tên sản phẩm: *</label>
                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Danh mục:</label>
                        <select name="category_id" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Giá cơ bản (VNĐ): *</label>
                        <input type="number" name="price" value="<?php echo $_POST['price'] ?? ''; ?>" min="0" step="1000" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Tồn kho cơ bản: *</label>
                        <input type="number" name="stock" value="<?php echo $_POST['stock'] ?? '0'; ?>" min="0" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                </div>

                <!-- Hình ảnh và mô tả -->
                <div>
                    <h4 style="margin-bottom:15px; color:#333; border-bottom:2px solid #eee; padding-bottom:5px;">Hình ảnh & Mô tả</h4>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Hình ảnh sản phẩm:</label>
                        <input type="file" name="product_image" accept="image/*" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                        <small style="color:#666;">Hỗ trợ: JPG, JPEG, PNG, WEBP. Kích thước tối đa: 5MB</small>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Mô tả:</label>
                        <textarea name="description" rows="6" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Quản lý variants -->
            <div style="margin-bottom:20px;">
                <h4 style="margin-bottom:15px; color:#333; border-bottom:2px solid #eee; padding-bottom:5px;">
                    Quản lý Màu sắc & Kích thước
                    <button type="button" onclick="addVariant()" style="float:right; background:#28a745; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;">+ Thêm variant</button>
                </h4>
                
                <div id="variants-container">
                    <div class="variant-row" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:10px; padding:15px; background:#f8f9fa; border-radius:6px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Kích thước:</label>
                            <input type="text" name="variants[0][size]" placeholder="S, M, L, 39, 40..." style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Màu sắc:</label>
                            <input type="text" name="variants[0][color]" placeholder="Đỏ, Xanh, Đen..." style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Tồn kho:</label>
                            <input type="number" name="variants[0][stock]" value="0" min="0" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Giá (nếu khác):</label>
                            <input type="number" name="variants[0][price]" placeholder="Để trống nếu giống giá cơ bản" min="0" step="1000" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <button type="button" onclick="removeVariant(this)" style="background:#dc3545; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:12px;">Xóa</button>
                        </div>
                    </div>
                </div>
                
                <small style="color:#666; display:block; margin-top:10px;">
                    💡 <strong>Lưu ý:</strong> Nếu không thêm variant, sản phẩm sẽ sử dụng giá và tồn kho cơ bản. 
                    Nếu thêm variant, tổng tồn kho sẽ được tính từ các variant.
                </small>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="filter-submit" style="flex:1;">Thêm sản phẩm</button>
                <a href="admin.php?section=products" class="filter-submit" style="flex:1; text-align:center; text-decoration:none; background:#6c757d;">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
let variantCount = 1;

function addVariant() {
    const container = document.getElementById('variants-container');
    const newVariant = document.createElement('div');
    newVariant.className = 'variant-row';
    newVariant.style.cssText = 'display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:10px; padding:15px; background:#f8f9fa; border-radius:6px;';
    
    newVariant.innerHTML = `
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Kích thước:</label>
            <input type="text" name="variants[${variantCount}][size]" placeholder="S, M, L, 39, 40..." style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Màu sắc:</label>
            <input type="text" name="variants[${variantCount}][color]" placeholder="Đỏ, Xanh, Đen..." style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Tồn kho:</label>
            <input type="number" name="variants[${variantCount}][stock]" value="0" min="0" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; font-size:12px;">Giá (nếu khác):</label>
            <input type="number" name="variants[${variantCount}][price]" placeholder="Để trống nếu giống giá cơ bản" min="0" step="1000" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:12px;">
        </div>
        <div>
            <button type="button" onclick="removeVariant(this)" style="background:#dc3545; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:12px;">Xóa</button>
        </div>
    `;
    
    container.appendChild(newVariant);
    variantCount++;
}

function removeVariant(button) {
    const variantRow = button.closest('.variant-row');
    if (document.querySelectorAll('.variant-row').length > 1) {
        variantRow.remove();
    }
}
</script>

<style>
.section-title { font-size:24px; color:#333; margin-bottom:20px; }
.product-filter h3 { font-size:16px; color:#333; margin-bottom:10px; }
.product-filter ul li a:hover { background:#f2f4f7; border-radius:6px; }
.filter-submit { background:#007bff; color:white; padding:8px 15px; border:none; border-radius:6px; font-weight:600; cursor:pointer; text-align:center; }
.filter-submit:hover { background:#0056b3; }
.variant-row:hover { background:#e9ecef !important; }
@media (max-width: 768px) {
    .shop-container { flex-direction:column; }
    .product-filter { width:100%; min-height:auto; }
    .admin-content form > div { grid-template-columns:1fr; }
    .variant-row { grid-template-columns:1fr !important; gap:5px !important; }
}
</style>

<?php include 'includes/footer.php'; ?> 