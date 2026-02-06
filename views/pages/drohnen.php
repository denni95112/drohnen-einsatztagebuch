<?php
/**
 * Drohnen page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Services\AuthService;
use App\Services\DashboardApiService;
use App\Models\Drohne;

AuthService::requireAdminAuth();

$drohnenModel = new Drohne();
$dashboardApiManaged = DashboardApiService::isApiEnabled();

if ($dashboardApiManaged) {
    $drohnen = DashboardApiService::getDrones();
} else {
    if (isset($_POST['add'])) {
        $drohnenModel->create(['name' => trim($_POST['name'])]);
        header("Location: /public/index.php?page=drohnen");
        exit;
    }
    if (isset($_GET['delete'])) {
        $drohnenModel->delete($_GET['delete']);
        header("Location: /public/index.php?page=drohnen");
        exit;
    }
    $drohnen = $drohnenModel->getAllOrdered();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drohnen-Verwaltung</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= getVersionedAsset('css/drohnen.css') ?>">
</head>
<body>

<?php include dirname(__DIR__) . '/layouts/header.php'; ?>

<div class="container">
    <h2>Drohnen-Verwaltung</h2>

    <?php if ($dashboardApiManaged): ?>
    <p class="dashboard-api-notice">Diese Daten werden vom <strong>Flug-Dienstbuch</strong> per API bereitgestellt. Bearbeitung nur im Flug-Dienstbuch möglich.</p>
    <?php endif; ?>

    <?php if (!$dashboardApiManaged): ?>
    <form method="post" action="/public/index.php?page=drohnen" style="background: transparent; padding: 0; box-shadow: none; margin-bottom: 0;">
        <div class="form-group">
            <input type="text" name="name" placeholder="Name der Drohne" required>
            <button type="submit" name="add" class="btn-add">Hinzufügen</button>
        </div>
    </form>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <?php if (!$dashboardApiManaged): ?>
            <th>Aktion</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($drohnen as $drohne): ?>
            <tr>
                <td><?= $drohne['id'] ?></td>
                <td><?= htmlspecialchars($drohne['name']) ?></td>
                <?php if (!$dashboardApiManaged): ?>
                <td>
                    <a href="/public/index.php?page=drohnen&delete=<?= $drohne['id'] ?>"
                       onclick="return confirm('Drohne wirklich löschen?')"
                       class="btn-delete">
                        Löschen
                    </a>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="btn-container">
        <a href="/public/index.php?page=index" class="back-btn">Zurück zur Übersicht</a>
    </div>
</div>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

</body>
</html>
