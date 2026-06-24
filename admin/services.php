<?php
$page_title = "Kelola Layanan";
$page_subtitle = "Daftar layanan treatment Beauty Sari Salon yang ditawarkan kepada pelanggan.";
include_once 'includes/header.php';

// Handle Add, Edit, Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_service') {
        $name = trim($_POST['name']);
        $duration = intval($_POST['duration']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $image_path = 'assets/icons/faciel.png'; // Default placeholder
        
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_name = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed_exts)) {
                $target_dir = '../assets/uploads/services/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $new_file_name = uniqid('service_', true) . '.' . $ext;
                $target_file = $target_dir . $new_file_name;
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = 'assets/uploads/services/' . $new_file_name;
                }
            } else {
                $error_msg = "Format file tidak didukung! Gunakan format JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        }
        
        if (!isset($error_msg)) {
            if (empty($name) || $duration <= 0 || $price <= 0) {
                $error_msg = "Semua input wajib diisi dengan benar!";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO services (name, duration, price, image_path, description) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $duration, $price, $image_path, $description]);
                    $success_msg = "Layanan baru berhasil ditambahkan.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal menambah layanan: " . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'edit_service') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $duration = intval($_POST['duration']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $image_path = trim($_POST['old_image_path']); // Default to old path
        
        // Handle file upload if new one is selected
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_name = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed_exts)) {
                $target_dir = '../assets/uploads/services/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $new_file_name = uniqid('service_', true) . '.' . $ext;
                $target_file = $target_dir . $new_file_name;
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = 'assets/uploads/services/' . $new_file_name;
                }
            } else {
                $error_msg = "Format file tidak didukung! Gunakan format JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        }
        
        if (!isset($error_msg)) {
            if (empty($name) || $duration <= 0 || $price <= 0 || empty($image_path)) {
                $error_msg = "Semua input wajib diisi dengan benar!";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE services SET name = ?, duration = ?, price = ?, image_path = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $duration, $price, $image_path, $description, $id]);
                    $success_msg = "Detail layanan berhasil diperbarui.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal memperbarui layanan: " . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'delete_service') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = "Layanan berhasil dihapus.";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus layanan: " . $e->getMessage();
        }
    }
}

// Fetch all services
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data layanan: " . $e->getMessage());
}
?>

<!-- Alert boxes -->
<?php if (isset($success_msg)): ?>
    <div style="background-color: #effbf4; color: #4caf50; padding: 12px 20px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
    </div>
<?php endif; ?>
<?php if (isset($error_msg)): ?>
    <div style="background-color: #ffebee; color: #f44336; padding: 12px 20px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
    </div>
<?php endif; ?>

<!-- Top Action buttons -->
<div class="actions-row" style="justify-content: flex-end;">
  <button class="btn-admin-primary" id="openAddModalBtn"><i class="fa-solid fa-circle-plus"></i> Tambah Layanan</button>
</div>

<!-- Services table -->
<div class="admin-table-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Foto / Icon</th>
        <th>Nama Layanan</th>
        <th>Durasi</th>
        <th>Harga</th>
        <th>Deskripsi Singkat</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td>
            <img class="table-thumb" src="../<?php echo htmlspecialchars($s['image_path']); ?>" alt="<?php echo htmlspecialchars($s['name']); ?>" onerror="this.src='../assets/icons/faciel.png'">
          </td>
          <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
          <td><i class="fa-regular fa-clock" style="color: var(--primary-color);"></i> <?php echo $s['duration']; ?> menit</td>
          <td><strong>Rp <?php echo number_format($s['price'], 0, ',', '.'); ?></strong></td>
          <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <?php echo htmlspecialchars($s['description']); ?>
          </td>
          <td>
            <div style="display: flex; gap: 8px;">
              <!-- Edit Button (Uses JS to prefill the modal) -->
              <button class="btn-action-icon edit edit-service-btn" 
                      data-id="<?php echo $s['id']; ?>"
                      data-name="<?php echo htmlspecialchars($s['name']); ?>"
                      data-duration="<?php echo $s['duration']; ?>"
                      data-price="<?php echo $s['price']; ?>"
                      data-image="<?php echo htmlspecialchars($s['image_path']); ?>"
                      data-desc="<?php echo htmlspecialchars($s['description']); ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              
              <!-- Delete Form -->
              <form action="services.php" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                <input type="hidden" name="action" value="delete_service">
                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                <button type="submit" class="btn-action-icon delete"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ADD SERVICE MODAL -->
<div class="modal-backdrop" id="addModal">
  <div class="modal-box" style="width: 550px; max-height: 90vh; overflow-y: auto;">
    <div class="modal-header">
      <h3>Tambah Layanan Baru</h3>
      <button class="btn-close-modal" id="closeAddModalBtn"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="services.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add_service">
      
      <div class="admin-form-group">
        <label for="add_name">Nama Layanan</label>
        <input type="text" id="add_name" name="name" required placeholder="Contoh: Creambath Premium">
      </div>
      
      <div class="admin-form-group">
        <label for="add_duration">Durasi Perawatan (Menit)</label>
        <input type="number" id="add_duration" name="duration" required placeholder="Contoh: 60" min="1">
      </div>
      
      <div class="admin-form-group">
        <label for="add_price">Harga (Rp)</label>
        <input type="number" id="add_price" name="price" required placeholder="Contoh: 120000" min="1">
      </div>

      <div class="admin-form-group">
        <label for="add_image_file">Upload Foto / Gambar Layanan</label>
        <input type="file" id="add_image_file" name="image_file" accept="image/*">
        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Pilih file gambar langsung dari HP/Komputer Anda.</p>
      </div>

      <div class="admin-form-group">
        <label for="add_desc">Deskripsi Layanan</label>
        <textarea id="add_desc" name="description" placeholder="Jelaskan detail treatment di sini..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-admin-secondary" id="cancelAddBtn">Batal</button>
        <button type="submit" class="btn-admin-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT SERVICE MODAL -->
<div class="modal-backdrop" id="editModal">
  <div class="modal-box" style="width: 550px; max-height: 90vh; overflow-y: auto;">
    <div class="modal-header">
      <h3>Edit Layanan</h3>
      <button class="btn-close-modal" id="closeEditModalBtn"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="services.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_service">
      <input type="hidden" name="id" id="edit_id">
      <input type="hidden" name="old_image_path" id="edit_old_image_path">
      
      <div class="admin-form-group">
        <label for="edit_name">Nama Layanan</label>
        <input type="text" id="edit_name" name="name" required>
      </div>
      
      <div class="admin-form-group">
        <label for="edit_duration">Durasi Perawatan (Menit)</label>
        <input type="number" id="edit_duration" name="duration" required min="1">
      </div>
      
      <div class="admin-form-group">
        <label for="edit_price">Harga (Rp)</label>
        <input type="number" id="edit_price" name="price" required min="1">
      </div>

      <div class="admin-form-group">
        <label>Foto Saat Ini</label>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
          <img id="edit_image_preview" src="" alt="Preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
          <span id="edit_image_path_label" style="font-size: 0.75rem; color: var(--text-muted); overflow-wrap: anywhere; word-break: break-all;"></span>
        </div>
        <label for="edit_image_file">Ganti Foto Layanan (Opsional)</label>
        <input type="file" id="edit_image_file" name="image_file" accept="image/*">
        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Biarkan kosong jika tidak ingin mengubah foto.</p>
      </div>

      <div class="admin-form-group">
        <label for="edit_desc">Deskripsi Layanan</label>
        <textarea id="edit_desc" name="description"></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-admin-secondary" id="cancelEditBtn">Batal</button>
        <button type="submit" class="btn-admin-primary">Perbarui</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Interaction Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Add Modal Elements
    const addModal = document.getElementById('addModal');
    const openAddModalBtn = document.getElementById('openAddModalBtn');
    const closeAddModalBtn = document.getElementById('closeAddModalBtn');
    const cancelAddBtn = document.getElementById('cancelAddBtn');

    // Edit Modal Elements
    const editModal = document.getElementById('editModal');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    
    // Form Inputs
    const editIdInput = document.getElementById('edit_id');
    const editNameInput = document.getElementById('edit_name');
    const editDurationInput = document.getElementById('edit_duration');
    const editPriceInput = document.getElementById('edit_price');
    const editOldImageInput = document.getElementById('edit_old_image_path');
    const editImagePreview = document.getElementById('edit_image_preview');
    const editImagePathLabel = document.getElementById('edit_image_path_label');
    const editDescInput = document.getElementById('edit_desc');

    // Open Add Modal
    if (openAddModalBtn) {
      openAddModalBtn.addEventListener('click', () => {
        addModal.classList.add('show');
      });
    }

    // Close Add Modal
    const closeAdd = () => addModal.classList.remove('show');
    if (closeAddModalBtn) closeAddModalBtn.addEventListener('click', closeAdd);
    if (cancelAddBtn) cancelAddBtn.addEventListener('click', closeAdd);

    // Open Edit Modal with Data
    const editButtons = document.querySelectorAll('.edit-service-btn');
    editButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        editIdInput.value = btn.getAttribute('data-id');
        editNameInput.value = btn.getAttribute('data-name');
        editDurationInput.value = btn.getAttribute('data-duration');
        editPriceInput.value = btn.getAttribute('data-price');
        
        const imgPath = btn.getAttribute('data-image');
        editOldImageInput.value = imgPath;
        editImagePreview.src = '../' + imgPath;
        editImagePathLabel.textContent = imgPath;
        
        editDescInput.value = btn.getAttribute('data-desc');
        
        editModal.classList.add('show');
      });
    });

    // Close Edit Modal
    const closeEdit = () => editModal.classList.remove('show');
    if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', closeEdit);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEdit);

    // Close on clicking backdrop
    window.addEventListener('click', (e) => {
      if (e.target === addModal) closeAdd();
      if (e.target === editModal) closeEdit();
    });
  });
</script>

<?php
include_once 'includes/footer.php';
?>
