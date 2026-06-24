<?php
$page_title = "Kelola Karyawan";
$page_subtitle = "Daftar karyawan (stylist) Beauty Sari Salon dan performa mereka.";
include_once 'includes/header.php';

// Handle Add, Edit, Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_employee') {
        $name = trim($_POST['name']);
        $specialty = trim($_POST['specialty']);
        $rating = floatval($_POST['rating']);
        $experience = trim($_POST['experience']);
        $image_path = 'assets/img/karyawan-rida.png'; // Default placeholder
        
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_name = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed_exts)) {
                $target_dir = '../assets/uploads/employees/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $new_file_name = uniqid('employee_', true) . '.' . $ext;
                $target_file = $target_dir . $new_file_name;
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = 'assets/uploads/employees/' . $new_file_name;
                }
            } else {
                $error_msg = "Format file tidak didukung! Gunakan format JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        }
        
        if (!isset($error_msg)) {
            if (empty($name) || empty($specialty) || $rating < 0 || $rating > 5) {
                $error_msg = "Semua input wajib diisi dengan benar! Rating harus di antara 0 dan 5.";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO employees (name, specialty, rating, experience, image_path) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $specialty, $rating, $experience, $image_path]);
                    $success_msg = "Karyawan baru berhasil ditambahkan.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal menambah karyawan: " . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'edit_employee') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $specialty = trim($_POST['specialty']);
        $rating = floatval($_POST['rating']);
        $experience = trim($_POST['experience']);
        $image_path = trim($_POST['old_image_path']); // Default to old path
        
        // Handle file upload if new one is selected
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_name = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed_exts)) {
                $target_dir = '../assets/uploads/employees/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $new_file_name = uniqid('employee_', true) . '.' . $ext;
                $target_file = $target_dir . $new_file_name;
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_path = 'assets/uploads/employees/' . $new_file_name;
                }
            } else {
                $error_msg = "Format file tidak didukung! Gunakan format JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        }
        
        if (!isset($error_msg)) {
            if (empty($name) || empty($specialty) || $rating < 0 || $rating > 5 || empty($image_path)) {
                $error_msg = "Semua input wajib diisi dengan benar! Rating harus di antara 0 dan 5.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE employees SET name = ?, specialty = ?, rating = ?, experience = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$name, $specialty, $rating, $experience, $image_path, $id]);
                    $success_msg = "Detail karyawan berhasil diperbarui.";
                } catch (PDOException $e) {
                    $error_msg = "Gagal memperbarui data karyawan: " . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'delete_employee') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = "Karyawan berhasil dihapus.";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus karyawan: " . $e->getMessage();
        }
    }
}

// Fetch all employees
try {
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY id ASC");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data karyawan: " . $e->getMessage());
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
  <button class="btn-admin-primary" id="openAddModalBtn"><i class="fa-solid fa-user-plus"></i> Tambah Karyawan</button>
</div>

<!-- Employees table -->
<div class="admin-table-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Foto Profil</th>
        <th>Nama Karyawan</th>
        <th>Spesialisasi</th>
        <th>Rating Keahlian</th>
        <th>Pengalaman Kerja</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($employees as $emp): ?>
        <tr>
          <td>
            <img class="table-thumb" style="border-radius: 50%; width: 45px; height: 45px; object-fit: cover;" src="../<?php echo htmlspecialchars($emp['image_path']); ?>" alt="<?php echo htmlspecialchars($emp['name']); ?>" onerror="this.src='../assets/img/karyawan-rida.png'">
          </td>
          <td><strong><?php echo htmlspecialchars($emp['name']); ?></strong></td>
          <td><?php echo htmlspecialchars($emp['specialty']); ?></td>
          <td>
            <div style="color: #ff9800; font-weight: 600; display: flex; align-items: center; gap: 4px;">
              <i class="fa-solid fa-star"></i> <?php echo number_format($emp['rating'], 1); ?> / 5.0
            </div>
          </td>
          <td><?php echo htmlspecialchars($emp['experience']); ?></td>
          <td>
            <div style="display: flex; gap: 8px;">
              <!-- Edit Button -->
              <button class="btn-action-icon edit edit-employee-btn" 
                      data-id="<?php echo $emp['id']; ?>"
                      data-name="<?php echo htmlspecialchars($emp['name']); ?>"
                      data-specialty="<?php echo htmlspecialchars($emp['specialty']); ?>"
                      data-rating="<?php echo $emp['rating']; ?>"
                      data-experience="<?php echo htmlspecialchars($emp['experience']); ?>"
                      data-image="<?php echo htmlspecialchars($emp['image_path']); ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              
              <!-- Delete Form -->
              <form action="employees.php" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                <input type="hidden" name="action" value="delete_employee">
                <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">
                <button type="submit" class="btn-action-icon delete"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ADD EMPLOYEE MODAL -->
<div class="modal-backdrop" id="addModal">
  <div class="modal-box" style="width: 550px; max-height: 90vh; overflow-y: auto;">
    <div class="modal-header">
      <h3>Tambah Karyawan Baru</h3>
      <button class="btn-close-modal" id="closeAddModalBtn"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="employees.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add_employee">
      
      <div class="admin-form-group">
        <label for="add_name">Nama Karyawan</label>
        <input type="text" id="add_name" name="name" required placeholder="Contoh: Ayu Lestari">
      </div>
      
      <div class="admin-form-group">
        <label for="add_specialty">Spesialisasi</label>
        <input type="text" id="add_specialty" name="specialty" required placeholder="Contoh: Hair Coloring Specialist">
      </div>
      
      <div class="admin-form-group">
        <label for="add_rating">Rating Keahlian (0.0 - 5.0)</label>
        <input type="number" id="add_rating" name="rating" required step="0.1" min="0" max="5" placeholder="Contoh: 4.8">
      </div>

      <div class="admin-form-group">
        <label for="add_experience">Pengalaman Kerja</label>
        <input type="text" id="add_experience" name="experience" placeholder="Contoh: 4 tahun pengalaman">
      </div>

      <div class="admin-form-group">
        <label for="add_image_file">Upload Foto Profil Karyawan</label>
        <input type="file" id="add_image_file" name="image_file" accept="image/*">
        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Pilih file gambar langsung dari HP/Komputer Anda.</p>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-admin-secondary" id="cancelAddBtn">Batal</button>
        <button type="submit" class="btn-admin-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT EMPLOYEE MODAL -->
<div class="modal-backdrop" id="editModal">
  <div class="modal-box" style="width: 550px; max-height: 90vh; overflow-y: auto;">
    <div class="modal-header">
      <h3>Edit Karyawan</h3>
      <button class="btn-close-modal" id="closeEditModalBtn"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="employees.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_employee">
      <input type="hidden" name="id" id="edit_id">
      <input type="hidden" name="old_image_path" id="edit_old_image_path">
      
      <div class="admin-form-group">
        <label for="edit_name">Nama Karyawan</label>
        <input type="text" id="edit_name" name="name" required>
      </div>
      
      <div class="admin-form-group">
        <label for="edit_specialty">Spesialisasi</label>
        <input type="text" id="edit_specialty" name="specialty" required>
      </div>
      
      <div class="admin-form-group">
        <label for="edit_rating">Rating Keahlian (0.0 - 5.0)</label>
        <input type="number" id="edit_rating" name="rating" required step="0.1" min="0" max="5">
      </div>

      <div class="admin-form-group">
        <label for="edit_experience">Pengalaman Kerja</label>
        <input type="text" id="edit_experience" name="experience">
      </div>

      <div class="admin-form-group">
        <label>Foto Saat Ini</label>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
          <img id="edit_image_preview" src="" alt="Preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 1px solid var(--border-color);">
          <span id="edit_image_path_label" style="font-size: 0.75rem; color: var(--text-muted); overflow-wrap: anywhere; word-break: break-all;"></span>
        </div>
        <label for="edit_image_file">Ganti Foto Profil (Opsional)</label>
        <input type="file" id="edit_image_file" name="image_file" accept="image/*">
        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Biarkan kosong jika tidak ingin mengubah foto.</p>
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
    const editSpecialtyInput = document.getElementById('edit_specialty');
    const editRatingInput = document.getElementById('edit_rating');
    const editExperienceInput = document.getElementById('edit_experience');
    const editOldImageInput = document.getElementById('edit_old_image_path');
    const editImagePreview = document.getElementById('edit_image_preview');
    const editImagePathLabel = document.getElementById('edit_image_path_label');

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
    const editButtons = document.querySelectorAll('.edit-employee-btn');
    editButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        editIdInput.value = btn.getAttribute('data-id');
        editNameInput.value = btn.getAttribute('data-name');
        editSpecialtyInput.value = btn.getAttribute('data-specialty');
        editRatingInput.value = btn.getAttribute('data-rating');
        editExperienceInput.value = btn.getAttribute('data-experience');
        
        const imgPath = btn.getAttribute('data-image');
        editOldImageInput.value = imgPath;
        editImagePreview.src = '../' + imgPath;
        editImagePathLabel.textContent = imgPath;
        
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
