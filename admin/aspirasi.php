<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$db = new Database();
$conn = $db->getConnection();

// Filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

// Build query
$sql = "SELECT a.*, k.nama_kategori, s.nama, s.kelas 
        FROM aspirasi a 
        JOIN kategori k ON a.id_kategori = k.id_kategori
        JOIN siswa s ON a.nis = s.nis 
        WHERE 1=1";

if (!empty($filter_status)) {
    $status = $db->escape($filter_status);
    $sql .= " AND a.status = '$status'";
}

if (!empty($filter_kategori)) {
    $kategori = $db->escape($filter_kategori);
    $sql .= " AND a.id_kategori = '$kategori'";
}

if (!empty($filter_tanggal)) {
    $tanggal = $db->escape($filter_tanggal);
    $sql .= " AND DATE(a.tanggal_dibuat) = '$tanggal'";
}

$sql .= " ORDER BY a.tanggal_dibuat DESC";

$result = $conn->query($sql);
$kategori_list = getKategori();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Aspirasi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning">
                <i class="bi bi-list-check"></i> Daftar Aspirasi
            </h2>
            <a href="filter.php" class="btn btn-outline-warning">
                <i class="bi bi-funnel"></i> Filter Lanjutan
            </a>
        </div>
        
        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <i class="bi bi-filter"></i> Filter Data
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" <?php echo ($filter_status == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="Proses" <?php echo ($filter_status == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                            <option value="Selesai" <?php echo ($filter_status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?php echo $kat['id_kategori']; ?>" 
                                <?php echo ($filter_kategori == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                <?php echo $kat['nama_kategori']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo $filter_tanggal; ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="card">
            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i> Data Aspirasi
                    <span class="badge bg-light text-dark ms-2">
                        <?php echo $result->num_rows; ?> Data
                    </span>
                </h5>
                <button onclick="exportToCSV('dataTable', 'aspirasi_<?php echo date('Y-m-d'); ?>')" 
                        class="btn btn-light btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </button>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo str_pad($row['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_dibuat'])); ?></td>
                                    <td><?php echo $row['nama']; ?></td>
                                    <td><?php echo $row['kelas']; ?></td>
                                    <td><?php echo $row['nama_kategori']; ?></td>
                                    <td><?php echo $row['lokasi']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail_aspirasi.php?id=<?php echo $row['id_aspirasi']; ?>" 
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3">Tidak ada data</h4>
                        <p class="text-muted">Tidak ada aspirasi yang sesuai dengan filter.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
</body>
</html>