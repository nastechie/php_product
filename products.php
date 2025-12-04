<?php
include "db.php";
include "layout/header.php";
include "layout/sidebar.php";

/* ---------------------------
  CRUD: Add / Update / Delete
----------------------------*/
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $image = "";
        if (!empty($_FILES['product_image']['name'])) {
            $image = time() . "_" . basename($_FILES['product_image']['name']);
            move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/" . $image);
        }

        $name  = $conn->real_escape_string($_POST['product_name']);
        $cat   = $conn->real_escape_string($_POST['category']);
        $desc  = $conn->real_escape_string($_POST['description']);
        $qty   = (int)$_POST['qty'];
        $price = (float)$_POST['unit_price'];
        $status= $conn->real_escape_string($_POST['status']);

        $sql = "INSERT INTO tblproduct (product_name,category,description,qty,unit_price,product_image,status)
                VALUES ('$name','$cat','$desc',$qty,$price,'$image','$status')";
        if ($conn->query($sql)) {
            header("Location: products.php?success=added");
            exit;
        } else {
            $error = $conn->error;
        }
    }

    if ($action === 'update') {
        $id = (int)$_POST['product_id'];
        if (!empty($_FILES['product_image']['name'])) {
            $image = time() . "_" . basename($_FILES['product_image']['name']);
            move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/" . $image);
        } else {
            $row = $conn->query("SELECT product_image FROM tblproduct WHERE product_id=$id")->fetch_assoc();
            $image = $row['product_image'];
        }

        $name  = $conn->real_escape_string($_POST['product_name']);
        $cat   = $conn->real_escape_string($_POST['category']);
        $desc  = $conn->real_escape_string($_POST['description']);
        $qty   = (int)$_POST['qty'];
        $price = (float)$_POST['unit_price'];
        $status= $conn->real_escape_string($_POST['status']);

        $sql = "UPDATE tblproduct SET product_name='$name', category='$cat', description='$desc',
                qty=$qty, unit_price=$price, product_image='$image', status='$status' WHERE product_id=$id";
        if ($conn->query($sql)) {
            header("Location: products.php?success=updated");
            exit;
        } else {
            $error = $conn->error;
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['product_id'];
        if ($conn->query("DELETE FROM tblproduct WHERE product_id=$id")) {
            header("Location: products.php?success=deleted");
            exit;
        } else {
            $error = $conn->error;
        }
    }
}

/* ---------------------------
  Fetch data
----------------------------*/
$products = $conn->query("SELECT * FROM tblproduct ORDER BY product_id DESC");

$successMsg = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') $successMsg = "Product added successfully";
    if ($_GET['success'] === 'updated') $successMsg = "Product updated successfully";
    if ($_GET['success'] === 'deleted') $successMsg = "Product deleted successfully";
}
?>

<!-- Page container -->
<div class="max-w-screen-2xl mx-auto">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">Products</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your product catalog</p>
    </div>

    <div class="flex items-center gap-3 w-full sm:w-auto">

      <!-- View toggle -->
      <div class="flex bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-1">
        <button id="gridBtn"
          class="px-3 py-1.5 rounded-md text-sm font-medium bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 shadow-sm">
          Grid
        </button>

        <button id="listBtn"
          class="px-3 py-1.5 rounded-md text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
          List
        </button>
      </div>

      <!-- FIXED ADD BUTTON (visible now!) -->
      <button onclick="openAdd()"
        class="px-4 py-2 bg-[#9FEF00] hover:brightness-90 text-black rounded-lg font-medium transition-colors whitespace-nowrap">
        + Add
      </button>

    </div>
  </div>

  <!-- Success/Error messages -->
  <?php if($successMsg): ?>
    <div class="mb-4 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-500/20 text-sm"><?= $successMsg ?></div>
  <?php endif; ?>

  <?php if($error): ?>
    <div class="mb-4 p-4 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-800 dark:text-rose-200 border border-rose-200 dark:border-rose-500/20 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- GRID VIEW -->
  <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php while($p = $products->fetch_assoc()):
        // prepare JSON safe string for embedding
        $pdata = json_encode($p, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
    ?>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden hover:border-slate-300 dark:hover:border-slate-600 transition-all hover:shadow-md">

        <!-- Image -->
        <div class="h-48 bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden">
          <?php if(!empty($p['product_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($p['product_image']) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($p['product_name']) ?>">
          <?php else: ?>
            <div class="text-slate-400 text-sm">No image</div>
          <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="p-5">
          <div class="flex items-start justify-between gap-2">
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                <?= htmlspecialchars($p['category']) ?>
              </div>

              <div class="text-lg font-semibold text-slate-900 dark:text-slate-100 mt-1">
                <?= htmlspecialchars($p['product_name']) ?>
              </div>

              <div class="text-sm text-slate-600 dark:text-slate-400 mt-2 line-clamp-2">
                <?= htmlspecialchars(mb_strimwidth($p['description'],0,80,'...')) ?>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Price</div>
              <div class="text-xl font-semibold text-[#9FEF00]">$<?= number_format($p['unit_price'],2) ?></div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Stock: <?= (int)$p['qty'] ?></div>
            </div>

            <div class="flex flex-col gap-2">
              <!-- EDIT: open Edit modal and fill fields using data-product attribute -->
              <button
                type="button"
                class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-[#9FEF00] rounded-lg text-xs font-medium transition-colors"
                data-product='<?= htmlspecialchars($pdata, ENT_QUOTES) ?>'
                onclick="openEditFromBtn(this)"
              >
                Edit
              </button>

              <button onclick="openDelete(<?= $p['product_id'] ?>)"
                class="px-3 py-1.5 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-lg text-xs font-medium transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>

      </div>
    <?php endwhile; ?>
  </div>

  <!-- LIST VIEW -->
  <div id="listView" class="hidden space-y-3">
    <?php
    $rows = $conn->query("SELECT * FROM tblproduct ORDER BY product_id DESC");
    while($r = $rows->fetch_assoc()):
        $rdata = json_encode($r, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
    ?>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 flex items-center gap-4 hover:border-slate-300 dark:hover:border-slate-600 transition-all">
        <div class="w-20 h-20 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700 flex-shrink-0">
          <?php if(!empty($r['product_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($r['product_image']) ?>" alt="<?= htmlspecialchars($r['product_name']) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">No Image</div>
          <?php endif; ?>
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide"><?= htmlspecialchars($r['category']) ?></div>
              <div class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-0.5 truncate"><?= htmlspecialchars($r['product_name']) ?></div>
              <div class="text-sm text-slate-600 dark:text-slate-400 mt-2 line-clamp-1"><?= htmlspecialchars(mb_strimwidth($r['description'],0,140,'...')) ?></div>
            </div>
            <div class="text-right flex-shrink-0">
              <div class="text-xs text-slate-500 dark:text-slate-400">Price</div>
              <div class="text-lg font-semibold text-[#9FEF00]">$<?= number_format($r['unit_price'],2) ?></div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Stock: <?= (int)$r['qty'] ?></div>
            </div>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <button
              type="button"
              class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-[#9FEF00] rounded-lg text-xs font-medium transition-colors"
              data-product='<?= htmlspecialchars($rdata, ENT_QUOTES) ?>'
              onclick="openEditFromBtn(this)"
            >
              Edit
            </button>

            <button onclick="openDelete(<?= $r['product_id'] ?>)" class="px-3 py-1.5 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-lg text-xs font-medium transition-colors">Delete</button>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>

<!-- ================= ADD MODAL ================= -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
  <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-2xl m-4">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Add Product</h3>
      <button onclick="closeAdd()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 text-xl">✕</button>
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="action" value="add">

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Image upload section -->
        <div class="flex flex-col items-center">
          <div class="relative mb-4">
            <img id="addPreview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' class='w-32 h-32 text-slate-300' fill='currentColor' viewBox='0 0 20 20'%3E%3Cpath d='M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z'/%3E%3C/svg%3E" class="w-32 h-32 rounded-lg object-cover border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700"/>
          </div>
          <label class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Product image</label>
          <input id="addFile" type="file" name="product_image" accept="image/*" class="mt-2 text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:px-3 file:py-1 file:rounded file:bg-slate-100 dark:file:bg-slate-700 file:border-0 file:text-xs file:font-medium"/>
        </div>

        <!-- Form fields -->
        <div class="md:col-span-2 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name *</label>
              <input name="product_name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Category</label>
              <input name="category" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Quantity *</label>
              <input type="number" name="qty" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Price *</label>
              <input type="number" name="unit_price" step="0.01" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none resize-none"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
            <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
        <button type="button" onclick="closeAdd()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-medium text-sm transition-colors">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-[#9FEF00] hover:bg-[#8FE500] text-black font-medium text-sm transition-colors">Create Product</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= EDIT MODAL ================= -->
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
  <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-2xl m-4">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Edit Product</h3>
      <button onclick="closeEdit()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 text-xl">✕</button>
    </div>

    <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="action" value="update">
      <input type="hidden" id="edit_product_id" name="product_id" value="">

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Image upload section -->
        <div class="flex flex-col items-center">
          <div class="relative mb-4">
            <img id="editPreview" src="https://via.placeholder.com/160" class="w-32 h-32 rounded-lg object-cover border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700"/>
          </div>
          <label class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Product image</label>
          <input id="editFile" type="file" name="product_image" accept="image/*" class="mt-2 text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:px-3 file:py-1 file:rounded file:bg-slate-100 dark:file:bg-slate-700 file:border-0 file:text-xs file:font-medium"/>
        </div>

        <!-- Form fields -->
        <div class="md:col-span-2 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name *</label>
              <input id="edit_name" name="product_name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Category</label>
              <input id="edit_category" name="category" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Quantity *</label>
              <input id="edit_qty" type="number" name="qty" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Price *</label>
              <input id="edit_price" type="number" name="unit_price" step="0.01" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none"/>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
            <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none resize-none"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
            <select id="edit_status" name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent outline-none">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
        <button type="button" onclick="closeEdit()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-medium text-sm transition-colors">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-[#9FEF00] hover:bg-[#8FE500] text-black font-medium text-sm transition-colors">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= DELETE MODAL ================= -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
  <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl w-full max-w-sm p-6 shadow-2xl m-4">
    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Delete product?</h3>
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">This action cannot be undone. The product will be permanently deleted.</p>

    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" id="deleteProductId" name="product_id">

      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeDelete()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-medium text-sm transition-colors">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium text-sm transition-colors">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= SCRIPTS ================= -->
<script>
// view toggle
const gridBtn = document.getElementById('gridBtn');
const listBtn = document.getElementById('listBtn');
const gridView = document.getElementById('gridView');
const listView = document.getElementById('listView');

gridBtn?.addEventListener('click', ()=> {
  gridView.classList.remove('hidden'); 
  listView.classList.add('hidden');
  gridBtn.classList.add('bg-white', 'dark:bg-slate-700', 'shadow-sm');
  gridBtn.classList.remove('text-slate-600', 'dark:text-slate-400');
  listBtn.classList.remove('bg-white', 'dark:bg-slate-700', 'shadow-sm');
  listBtn.classList.add('text-slate-600', 'dark:text-slate-400');
});

listBtn?.addEventListener('click', ()=> {
  listView.classList.remove('hidden'); 
  gridView.classList.add('hidden');
  listBtn.classList.add('bg-white', 'dark:bg-slate-700', 'shadow-sm');
  listBtn.classList.remove('text-slate-600', 'dark:text-slate-400');
  gridBtn.classList.remove('bg-white', 'dark:bg-slate-700', 'shadow-sm');
  gridBtn.classList.add('text-slate-600', 'dark:text-slate-400');
});

// add modal functions + preview
function openAdd(){ document.getElementById('addModal').classList.remove('hidden'); }
function closeAdd(){ document.getElementById('addModal').classList.add('hidden'); }
const addFile = document.getElementById('addFile');
const addPreview = document.getElementById('addPreview');
addFile?.addEventListener('change', function(e){
  const f = e.target.files[0];
  if(!f) return;
  addPreview.src = URL.createObjectURL(f);
});

// edit modal: open and populate from button dataset
function openEditFromBtn(btn){
  try {
    const raw = btn.getAttribute('data-product');
    if(!raw) return;
    const p = JSON.parse(raw);

    // fill form fields
    document.getElementById('edit_product_id').value = p.product_id;
    document.getElementById('edit_name').value = p.product_name || '';
    document.getElementById('edit_category').value = p.category || '';
    document.getElementById('edit_qty').value = p.qty || 0;
    document.getElementById('edit_price').value = p.unit_price || 0;
    document.getElementById('edit_description').value = p.description || '';
    document.getElementById('edit_status').value = p.status || 'active';

    // update preview
    const preview = document.getElementById('editPreview');
    if(p.product_image && p.product_image.length){
      preview.src = 'uploads/' + p.product_image;
    } else {
      preview.src = 'https://via.placeholder.com/160';
    }

    // clear file input (so upload is optional)
    const editFile = document.getElementById('editFile');
    if(editFile) editFile.value = '';

    // show modal
    document.getElementById('editModal').classList.remove('hidden');
    // scroll to top of modal (in case)
    document.getElementById('editModal').scrollIntoView({behavior:'smooth', block:'center'});
  } catch (err) {
    console.error('Failed to open edit modal', err);
    alert('Unable to open edit modal. See console for details.');
  }
}

function closeEdit(){ document.getElementById('editModal').classList.add('hidden'); }

// edit preview change
const editFile = document.getElementById('editFile');
const editPreview = document.getElementById('editPreview');
editFile?.addEventListener('change', function(e){
  const f = e.target.files[0];
  if(!f) return;
  editPreview.src = URL.createObjectURL(f);
});

// delete modal
function openDelete(id){
  document.getElementById('deleteProductId').value = id;
  document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDelete(){
  document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php include "layout/footer.php"; ?>
