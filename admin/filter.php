<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

// Update the path below if Database.php is located elsewhere, e.g., in the same directory or another foldertikan file ini ada dan berisi class Database
$db = Database::getInstance();
$conn = $db->getConnection();

// Filter parameters
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$filter_kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$filter_tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';
$filter_bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$filter_nis = isset($_GET['nis']) ? trim($_GET['nis']) : '';

// Build query with prepared statement
$sql = "SELECT a.*, k.nama_kategori, s.nama, s.kelas 
        FROM aspirasi a 
        JOIN kategori k ON a.id_kategori = k.id_kategori
        JOIN siswa s ON a.nis = s.nis 
        WHERE 1=1";

$types = '';
$params = [];

if (!empty($filter_status)) {
    $sql .= " AND a.status = ?";
    $types .= 's';
    $params[] = $filter_status;
}

if (!empty($filter_kategori)) {
    $sql .= " AND a.id_kategori = ?";
    $types .= 's';
    $params[] = $filter_kategori;
}

if (!empty($filter_tanggal)) {
    $sql .= " AND DATE(a.tanggal_dibuat) = ?";
    $types .= 's';
    $params[] = $filter_tanggal;
}

if (!empty($filter_bulan)) {
    $sql .= " AND DATE_FORMAT(a.tanggal_dibuat, '%Y-%m') = ?";
    $types .= 's';
    $params[] = $filter_bulan;
}

if (!empty($filter_nis)) {
    $sql .= " AND a.nis LIKE ?";
    $types .= 's';
    $filter_nis_search = '%' . $filter_nis . '%';
    $params[] = $filter_nis_search;
}

$sql .= " ORDER BY a.tanggal_dibuat DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Query preparation failed: ' . $conn->error);
}
if ($types) {
    // bind_param requires variables to be passed by reference
    $bind_params = [];
    $bind_params[] = $types;
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
}
if (!$stmt->execute()) {
    die('Query execution failed: ' . $stmt->error);
}
$result = $stmt->get_result();
if (!$result) {
    $result = new class {
        public $num_rows = 0;
        public function data_seek($_n) {}
        public function fetch_assoc() { return false; }
    };
}

// Fetch kategori list
$kategori_query = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
$kategori_result = $conn->query($kategori_query);
$kategori_list = [];
if ($kategori_result->num_rows > 0) {
    while ($row = $kategori_result->fetch_assoc()) {
        $kategori_list[] = $row;
    }
}

// Get statistics
$stats = [
    'total' => 0,
    'menunggu' => 0,
    'proses' => 0,
    'selesai' => 0
];

if ($result->num_rows > 0) {
    $stats['total'] = $result->num_rows;
    $result->data_seek(0); // Reset pointer
    
    while ($row = $result->fetch_assoc()) {
        switch ($row['status']) {
            case 'Menunggu': $stats['menunggu']++; break;
            case 'Proses': $stats['proses']++; break;
            case 'Selesai': $stats['selesai']++; break;
        }
    }
    $result->data_seek(0); // Reset pointer again
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Lanjutan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid py-4">
        <h2 class="mb-4 text-warning">
            <i class="bi bi-funnel"></i> Filter Lanjutan & Laporan
        </h2>
        
        <!-- Advanced Filter Form -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="bi bi-search"></i> Filter Data
                </h5>
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
                            <option value="<?php echo htmlspecialchars($kat['id_kategori']); ?>" 
                                <?php echo ($filter_kategori == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Bulan</label>
                        <input type="month" name="bulan" class="form-control" value="<?php echo htmlspecialchars($filter_bulan); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" class="form-control" value="<?php echo htmlspecialchars($filter_nis); ?>" 
                                   placeholder="NIS Siswa">
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-filter"></i> Terapkan Filter
                            </button>
                            <a href="filter.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                            <button type="button" onclick="printReport()" class="btn btn-outline-success">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card total">
                    <i class="bi bi-inbox"></i>
                    <div class="number"><?php echo $stats['total']; ?></div>
                    <div>Total Data</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card menunggu">
                    <i class="bi bi-clock"></i>
                    <div class="number"><?php echo $stats['menunggu']; ?></div>
                    <div>Menunggu</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card proses">
                    <i class="bi bi-gear"></i>
                    <div class="number"><?php echo $stats['proses']; ?></div>
                    <div>Proses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card selesai">
                    <i class="bi bi-check-circle"></i>
                    <div class="number"><?php echo $stats['selesai']; ?></div>
                    <div>Selesai</div>
                </div>
            </div>
        </div>
        
        <!-- Results -->
        <div class="card">
            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i> Hasil Filter
                    <span class="badge bg-light text-dark ms-2">
                        <?php echo $stats['total']; ?> Data
                    </span>
                </h5>
                <div>
                    <button onclick="exportToCSV('dataTable', 'laporan_<?php echo date('Y-m-d'); ?>')" 
                            class="btn btn-light btn-sm me-2">
                        <i class="bi bi-file-earmark-excel"></i> CSV
                    </button>
                    <button onclick="exportToExcel()" class="btn btn-light btn-sm">
                        <i class="bi bi-download"></i> Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($stats['total'] > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo str_pad($row['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_dibuat'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['feedback']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-content="<?php echo htmlspecialchars($row['feedback']); ?>">
                                                Lihat
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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
                        <p class="text-muted">Tidak ada aspirasi yang sesuai dengan filter yang dipilih.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Charts (Placeholder) -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart"></i> Statistik Visual
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="statusChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6>Interpretasi Data:</h6>
                            <ul class="mb-0">
                                <li>Total data ditemukan: <strong><?php echo $stats['total']; ?></strong></li>
                                <li>Masih menunggu: <strong><?php echo $stats['menunggu']; ?></strong> (<strong><?php echo $stats['total'] > 0 ? round(($stats['menunggu']/$stats['total'])*100, 1) : 0; ?>%</strong>)</li>
                                <li>Dalam proses: <strong><?php echo $stats['proses']; ?></strong> (<strong><?php echo $stats['total'] > 0 ? round(($stats['proses']/$stats['total'])*100, 1) : 0; ?>%</strong>)</li>
                                <li>Sudah selesai: <strong><?php echo $stats['selesai']; ?></strong> (<strong><?php echo $stats['total'] > 0 ? round(($stats['selesai']/$stats['total'])*100, 1) : 0; ?>%</strong>)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Initialize popovers
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Status Chart
            const ctx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Menunggu', 'Proses', 'Selesai'],
                    datasets: [{
                        data: [<?php echo $stats['menunggu']; ?>, <?php echo $stats['proses']; ?>, <?php echo $stats['selesai']; ?>],
                        backgroundColor: [
                            '#ff9800',
                            '#2196f3',
                            '#4caf50'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Status Aspirasi'
                        }
                    }
                }
            });
        });
        
        function exportToExcel() {
            // Simple Excel export
            let table = document.getElementById('dataTable');
            let html = table.outerHTML;
            let url = 'data:application/vnd.ms-excel,' + escape(html);
            let link = document.createElement('a');
            link.href = url;
            link.download = 'laporan_aspirasi.xls';
            link.click();
        }
        
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>