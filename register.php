<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?php echo SITE_NAME; ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <section class="hero is-primary is-fullheight">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column is-6-tablet is-5-desktop is-4-widescreen">
                        <div class="box">
                            <div class="has-text-centered mb-5">
                                <img src="assets/images/logo.png" alt="Logo" style="max-width: 150px;">
                                <h1 class="title is-4 mt-3">Daftar Akaun Baru</h1>
                            </div>
                            
                            <form id="registerForm" method="post">
                                <input type="hidden" name="action" value="register">
                                
                                <div class="field">
                                    <label class="label">Nama Penuh</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="full_name" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Username</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="username" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-at"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Kata Laluan</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="password" name="password" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Jawatan</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="position" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-briefcase"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Jabatan</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="department" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-building"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">No. Telefon</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="tel" name="phone" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="control">
                                        <button type="submit" class="button is-primary is-fullwidth">
                                            Daftar
                                        </button>
                                    </div>
                                </div>

                                <div class="has-text-centered mt-4">
                                    <a href="index.php">Sudah ada akaun? Log masuk</a>
                                </div>

                                <div id="errorMessage" class="notification is-danger is-light" style="display: none;">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/register.js"></script>
</body>
</html> 