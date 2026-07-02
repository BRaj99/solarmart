<?php include "admin_common.php"; ?>
<?php adminPageStart('products', 'Products'); ?>
<h1>Products</h1>

<?php adminAlerts($message, $error); ?>

<div class="card">
    <h3><?php echo $editProduct ? "Edit Product" : "Add New Product"; ?></h3>
    <form class="admin-form" method="POST" enctype="multipart/form-data" action="admin_products.php">
        <input type="hidden" name="action" value="<?php echo $editProduct ? 'update' : 'add'; ?>">
        <?php if ($editProduct): ?>
            <input type="hidden" name="id" value="<?php echo (int)$editProduct['id']; ?>">
            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editProduct['image']); ?>">
        <?php endif; ?>
        <input type="text" name="name" placeholder="Product Name" required value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>">
        <input type="text" name="brand" placeholder="Brand" value="<?php echo htmlspecialchars($editProduct['brand'] ?? ''); ?>">
        <select name="category" required>
            <?php
            $categories = ['Panels', 'Inverters', 'Batteries', 'Lights', 'Kits', 'Accessories'];
            $selectedCategory = $editProduct['category'] ?? '';
            echo '<option value="">Select Category</option>';
            foreach ($categories as $cat) {
                $selected = ($selectedCategory === $cat) ? 'selected' : '';
                echo '<option '.$selected.' value="'.htmlspecialchars($cat).'">'.htmlspecialchars($cat).'</option>';
            }
            ?>
        </select>
        <input type="number" step="0.01" name="price" placeholder="Price" required value="<?php echo htmlspecialchars($editProduct['price'] ?? ''); ?>">
        <input type="number" name="stock" placeholder="Stock Quantity" required value="<?php echo htmlspecialchars($editProduct['stock'] ?? ''); ?>">
        <input type="text" name="sku" placeholder="SKU Code" required value="<?php echo htmlspecialchars($editProduct['sku'] ?? ''); ?>">
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.svg">
        <textarea name="description" placeholder="Product Description"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
        <button type="submit" class="normal"><?php echo $editProduct ? "Update Product" : "Add Product"; ?></button>
        <?php if ($editProduct): ?><a class="outline-btn" href="admin_products.php">Cancel Edit</a><?php endif; ?>
    </form>
</div>

<br>
<div class="card">
    <h3>Manage Products</h3>
    <div class="table-wrap">
        <table class="cart-table">
            <thead><tr><th>Image</th><th>Name</th><th>Description</th><th>Category</th><th>Price</th><th>Stock</th><th>SKU</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($products) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="" style="width:55px;height:45px;object-fit:contain"></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>Rs <?php echo number_format($product['price']); ?></td>
                        <td><?php echo (int)$product['stock']; ?></td>
                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                        <td>
                            <a class="small-btn edit-btn" href="admin_products.php?edit=<?php echo (int)$product['id']; ?>">Edit</a>
                            <form method="POST" action="admin_products.php" style="display:inline" onsubmit="return confirm('Delete this product?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                <button type="submit" class="small-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No products found. Add your first product above.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminPageEnd(); ?>
