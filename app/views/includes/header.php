<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= SITE_NAME ?></title>
    
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Flatpickr for date/time -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar is-primary" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="<?= SITE_URL ?>/<?= isKetua() ? 'ketua_dashboard.php' : (isPengarah() ? 'pengarah_dashboard.php' : 'dashboard.php') ?>">
                <strong><?= SITE_NAME ?></strong>
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarMain">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarMain" class="navbar-menu">
            <div class="navbar-start">
                <?php if (isLoggedIn()): ?>
                    <?php if (isKetua()): ?>
                    <a class="navbar-item" href="<?= SITE_URL ?>/ketua_dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard Ketua
                    </a>
                    <?php elseif (isPengarah()): ?>
                    <a class="navbar-item" href="<?= SITE_URL ?>/pengarah_dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard Pengarah
                    </a>
                    <?php else: ?>
                    <a class="navbar-item" href="<?= SITE_URL ?>/dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <?php endif; ?>
                    
                    <a class="navbar-item" href="<?= SITE_URL ?>/applications.php">
                        <i class="fas fa-list mr-2"></i> Senarai Permohonan
                    </a>
                    
                    <a class="navbar-item" href="<?= SITE_URL ?>/create_application.php">
                        <i class="fas fa-plus-circle mr-2"></i> Permohonan Baru
                    </a>
                    
                    <?php if (isAdmin()): ?>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                <i class="fas fa-cogs mr-2"></i> Admin
                            </a>
                            
                            <div class="navbar-dropdown">
                                <a class="navbar-item" href="<?= SITE_URL ?>/admin/applications.php">
                                    <i class="fas fa-clipboard-list mr-2"></i> Pengurusan Permohonan
                                </a>
                                <a class="navbar-item" href="<?= SITE_URL ?>/admin/users.php">
                                    <i class="fas fa-users mr-2"></i> Pengurusan Pengguna
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <?php if (isLoggedIn()): ?>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                <i class="fas fa-user mr-2"></i> <?= $_SESSION['user_name'] ?? 'Pengguna' ?>
                            </a>
                            
                            <div class="navbar-dropdown is-right">
                                <a class="navbar-item" href="<?= SITE_URL ?>/profile.php">
                                    <i class="fas fa-id-card mr-2"></i> Profil
                                </a>
                                <hr class="navbar-divider">
                                <a class="navbar-item" href="<?= SITE_URL ?>/logout.php">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Log Keluar
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="buttons">
                            <a class="button is-primary" href="<?= SITE_URL ?>/register.php">
                                <strong>Daftar</strong>
                            </a>
                            <a class="button is-light" href="<?= SITE_URL ?>/login.php">
                                Log Masuk
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main content -->
    <section class="section">
        <div class="container">
            <?php if ($message = getFlashMessage()): ?>
                <div class="notification is-<?= $message['type'] ?> is-light">
                    <button class="delete"></button>
                    <?= $message['message'] ?>
                </div>
            <?php endif; ?> 