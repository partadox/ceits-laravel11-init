<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CEITS Prisma Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
                <i class="bi bi-mortarboard-fill me-2"></i>
                CEITS Prisma Laravel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo e($user['nama'] ?? 'User'); ?>

                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header"><?php echo e($user['email'] ?? ''); ?></h6></li>
                            <li><h6 class="dropdown-header text-muted"><?php echo e($user['role'] ?? ''); ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('logout')); ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </h1>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-0">Selamat Datang!</h6>
                                <h4 class="mt-2 mb-0"><?php echo e($user['nama'] ?? 'User'); ?></h4>
                            </div>
                            <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-0">Role</h6>
                                <h4 class="mt-2 mb-0"><?php echo e($user['role'] ?? 'N/A'); ?></h4>
                            </div>
                            <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-0">NRP</h6>
                                <h4 class="mt-2 mb-0"><?php echo e($user['nrp'] ?? 'N/A'); ?></h4>
                            </div>
                            <i class="bi bi-card-text" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Informasi Akun
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="150">Nama</th>
                                <td><?php echo e($user['nama'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>NRP</th>
                                <td><?php echo e($user['nrp'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo e($user['email'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td>
                                    <span class="badge bg-primary"><?php echo e($user['role'] ?? '-'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Departemen</th>
                                <td><?php echo e($user['departemen'] ?? '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-text me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('logout')); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb-fill me-2"></i>
                    <strong>Info:</strong> Aplikasi ini menggunakan CEITS SSO (Single Sign-On) untuk autentikasi.
                    Framework: Laravel 11 dengan PHP 8.2.
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-3 mt-5">
        <div class="container text-center text-muted">
            <p class="mb-0">&copy; <?php echo e(date('Y')); ?> CEITS - Computer Engineering ITS</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/dashboard.blade.php ENDPATH**/ ?>