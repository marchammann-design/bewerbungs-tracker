<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Bewerbungs-Tracker v0.7') ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="site-header">
        <div class="header-content">
            <h1 class="logo"><a href="dashboard.php">Bewerbungs-Tracker v0.5</a></h1>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Burger Button (nur auf Mobile sichtbar) -->
                <button class="burger-menu" id="burger-btn" aria-label="Menü öffnen">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Navigation -->
                <nav class="main-nav" id="main-nav">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="create.php">Neue Bewerbung</a>
                    <a href="stats.php">Statistik</a>
                    <a href="logout.php">Logout</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main class="site-main">