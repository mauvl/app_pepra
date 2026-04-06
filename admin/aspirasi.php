<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) && is_array($_GET['kategori']) ? $_GET['kategori'] : [];
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : '';
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : '';

// Pagination
$per_page = isset($_GET['per_page']) && in_array($_GET['per_page'], [10, 20, 50, 100]) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Ambil semua kategori
$kategori_list = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);

// Fungsi membangun WHERE clause
function buildWhere($status, $kategori, $tanggal_dari, $tanggal_sampai) {
    $where = " WHERE 1=1";
    $params = [];
    $types = "";
    if (!empty($status)) {
        $where .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    if (!empty($kategori)) {
        $placeholders = implode(',', array_fill(0, count($kategori), '?'));
        $where .= " AND a.id_kategori IN ($placeholders)";
        foreach ($kategori as $kat) {
            $params[] = $kat;
            $types .= "i";
        }
    }
    if (!empty($tanggal_dari)) {
        $where .= " AND DATE(a.tanggal_dibuat) >= ?";
        $params[] = $tanggal_dari;
        $types .= "s";
    }
    if (!empty($tanggal_sampai)) {
        $where .= " AND DATE(a.tanggal_dibuat) <= ?";
        $params[] = $tanggal_sampai;
        $types .= "s";
    }
    return [$where, $params, $types];
}

list($where, $params, $types) = buildWhere($filter_status, $filter_kategori, $filter_tanggal_dari, $filter_tanggal_sampai);

// Hitung total
$sql_count = "SELECT COUNT(*) as total FROM aspirasi a $where";
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_data = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $per_page);

// Ambil data
$sql_data = "SELECT a.*, k.nama_kategori, s.nama, s.kelas 
             FROM aspirasi a 
             JOIN kategori k ON a.id_kategori = k.id_kategori
             JOIN siswa s ON a.nis = s.nis 
             $where 
             ORDER BY a.tanggal_dibuat ASC 
             LIMIT ? OFFSET ?";
$params_data = array_merge($params, [$per_page, $offset]);
$types_data = $types . "ii";
$stmt_data = $conn->prepare($sql_data);
if (!empty($params_data)) {
    $stmt_data->bind_param($types_data, ...$params_data);
}
$stmt_data->execute();
$result = $stmt_data->get_result();

// Export Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $sql_export = "SELECT a.id_aspirasi, a.tanggal_dibuat, s.nama, s.kelas, k.nama_kategori, a.lokasi, a.deskripsi, a.status, a.feedback
                   FROM aspirasi a 
                   JOIN kategori k ON a.id_kategori = k.id_kategori
                   JOIN siswa s ON a.nis = s.nis 
                   $where 
                   ORDER BY a.tanggal_dibuat ASC";
    $stmt_export = $conn->prepare($sql_export);
    if (!empty($params)) {
        $stmt_export->bind_param($types, ...$params);
    }
    $stmt_export->execute();
    $export_result = $stmt_export->get_result();
    
    $filename = "Laporan_Aspirasi_" . date('Y-m-d_H-i-s');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo "<html><head><meta charset='UTF-8'><title>Laporan Aspirasi</title></head><body>";
    echo "<h2>PEPRA - Sistem Pengaduan Sarana Sekolah</h2>";
    echo "<h3>Laporan Data Aspirasi</h3>";
    // Info filter
    $filter_info = [];
    if (!empty($filter_tanggal_dari) && !empty($filter_tanggal_sampai)) $filter_info[] = "Tanggal " . date('d/m/Y', strtotime($filter_tanggal_dari)) . " s.d " . date('d/m/Y', strtotime($filter_tanggal_sampai));
    elseif (!empty($filter_tanggal_dari)) $filter_info[] = "Mulai tanggal " . date('d/m/Y', strtotime($filter_tanggal_dari));
    elseif (!empty($filter_tanggal_sampai)) $filter_info[] = "Sampai tanggal " . date('d/m/Y', strtotime($filter_tanggal_sampai));
    if (!empty($filter_status)) $filter_info[] = "Status: $filter_status";
    if (!empty($filter_kategori)) {
        $kat_names = [];
        foreach ($kategori_list as $k) {
            if (in_array($k['id_kategori'], $filter_kategori)) $kat_names[] = $k['nama_kategori'];
        }
        $filter_info[] = "Kategori: " . implode(', ', $kat_names);
    }
    echo "<p><strong>Filter yang diterapkan:</strong> " . (empty($filter_info) ? "Semua data" : implode(' | ', $filter_info)) . "</p>";
    echo "<p><strong>Dicetak oleh:</strong> " . $_SESSION['nama'] . " (" . $_SESSION['role'] . ") pada " . date('d/m/Y H:i:s') . "</p>";
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<thead><tr style='background-color:#f2f2f2;'><th>No</th><th>ID</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Kategori</th><th>Lokasi</th><th>Deskripsi</th><th>Status</th><th>Feedback</th></tr></thead><tbody>";
    $no = 1;
    while ($row = $export_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$no}</td>";
        echo "<td>{$row['id_aspirasi']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['tanggal_dibuat'])) . "</td>";
        echo "<td>{$row['nama']}</td>";
        echo "<td>{$row['kelas']}</td>";
        echo "<td>{$row['nama_kategori']}</td>";
        echo "<td>{$row['lokasi']}</td>";
        echo "<td>{$row['deskripsi']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['feedback']}</td>";
        echo "</tr>";
        $no++;
    }
    echo "</tbody><tfoot><tr><td colspan='10'><strong>Total data: " . ($no-1) . " aspirasi</strong></td></tr></tfoot></table>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Aspirasi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            background-color: #fff;
        }
        .checkbox-group .form-check {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/logo.png" alt="Logo" class="navbar-logo me-2">
                PEPRA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="aspirasi.php"><i class="bi-list-check"></i> Aspirasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_akun.php"><i class="bi bi-people"></i> Kelola Akun</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?php echo $_SESSION['nama']; ?></a>
                        <ul class="dropdown-menu"><li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li></ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary"><i class="bi bi-list-check"></i> Daftar Aspirasi</h2>
            <a href="?export=excel&<?php echo http_build_query(array_merge($_GET, ['export'=>null])); ?>" class="btn btn-success"><i class="bi bi-file-excel"></i> Export ke Excel</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light text-dark"><i class="bi bi-funnel"></i> Filter Data</div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="Menunggu" <?php echo ($filter_status == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="Proses" <?php echo ($filter_status == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                                <option value="Selesai" <?php echo ($filter_status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="Duplikat" <?php echo ($filter_status == 'Duplikat') ? 'selected' : ''; ?>>Duplikat</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" class="form-control" value="<?php echo $filter_tanggal_dari; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" class="form-control" value="<?php echo $filter_tanggal_sampai; ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <div class="checkbox-group">
                            <?php foreach ($kategori_list as $kat): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kategori[]" value="<?php echo $kat['id_kategori']; ?>" id="kat_<?php echo $kat['id_kategori']; ?>" <?php echo in_array($kat['id_kategori'], $filter_kategori) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="kat_<?php echo $kat['id_kategori']; ?>"><?php echo $kat['nama_kategori']; ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Centang lebih dari satu untuk memilih beberapa kategori</small>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Terapkan Filter</button>
                            <a href="aspirasi.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i> Reset</a>
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2">
                            <label class="form-label mb-0">Tampilkan:</label>
                            <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
                                <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="text-muted small">data per halaman</span>
                        </div>
                    </div>
                    <input type="hidden" name="page" value="1">
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table"></i> Data Aspirasi <span class="badge bg-secondary ms-2"><?php echo $total_data; ?> Data</span></h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Kategori</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_dibuat'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td><a href="detail_aspirasi.php?id=<?php echo $row['id_aspirasi']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Detail</a></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <nav><ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page-1])); ?>">« Sebelumnya</a></li><?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                        <?php if ($page < $total_pages): ?><li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page+1])); ?>">Selanjutnya »</a></li><?php endif; ?>
                    </ul></nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5"><i class="bi bi-inbox display-1 text-muted"></i><h4>Tidak ada data</h4><p class="text-muted">Tidak ada aspirasi yang sesuai dengan filter.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>