<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/admin.php';
require_once __DIR__ . '/lib/custom_films_store.php';

if (!site_admin_is()) {
    header('Location: /login.php', true, 302);
    exit;
}

$ok = isset($_GET['ok']) && $_GET['ok'] === '1';
$deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string) ($_POST['admin_action'] ?? 'add_film');
    if ($act === 'delete_film') {
        $delId = trim((string) ($_POST['film_id'] ?? ''));
        if ($delId !== '' && custom_films_remove($delId)) {
            header('Location: /admin.php?deleted=1', true, 302);
            exit;
        }
        header('Location: /admin.php?err=delete', true, 302);
        exit;
    }
    if ($act === 'update_film') {
        $filmId = trim((string) ($_POST['film_id'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        $price = (int) ($_POST['price'] ?? 0);
        $description = (string) ($_POST['description'] ?? '');
        $genre = (string) ($_POST['genre'] ?? 'action');
        if (custom_films_update($filmId, $title, $price, $description, $genre)) {
            header('Location: /admin.php?updated=1', true, 302);
            exit;
        }
        $err = 'Could not update. Check title (required, max 200 chars) and price.';
    }
    if ($act === 'add_film') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $price = (int) ($_POST['price'] ?? 0);
        $description = (string) ($_POST['description'] ?? '');
        $genre = (string) ($_POST['genre'] ?? 'action');
        $newId = custom_films_append($title, $price, $description, $genre);
        if ($newId !== null) {
            header('Location: /admin.php?ok=1', true, 302);
            exit;
        }
        $err = 'Could not save. Check title (required, max 200 chars) and price.';
    }
}

$customList = custom_films_load_raw();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay — Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.0.10">
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/blocks/header.php'; ?>
        <div class="feedback">
            <div class="container admin-panel-narrow">
                <h2 class="admin-page-title">Admin</h2>
                <p class="feedback-user-meta">Logged in as <b><?= htmlspecialchars(site_admin_login(), ENT_QUOTES, 'UTF-8') ?></b></p>
                <?php if ($ok): ?>
                    <p class="admin-msg-ok">Film added. It appears on <a href="/films.php">Films</a> immediately.</p>
                <?php endif; ?>
                <?php if ($deleted): ?>
                    <p class="admin-msg-ok">Custom film removed from the catalog.</p>
                <?php endif; ?>
                <?php if ($updated): ?>
                    <p class="admin-msg-ok">Film updated successfully.</p>
                <?php endif; ?>
                <?php if (isset($_GET['err']) && $_GET['err'] === 'delete'): ?>
                    <p class="admin-msg-err">Could not delete that film (invalid id or not found).</p>
                <?php endif; ?>
                <?php if ($err !== ''): ?>
                    <p class="admin-msg-err"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <h2 class="admin-manage-heading">Custom films (JSON)</h2>
                <?php if ($customList === []): ?>
                    <p class="admin-manage-empty">No custom films yet.</p>
                <?php else: ?>
                    <div class="analytics-table-wrap admin-custom-list">
                        <table class="analytics-table analytics-table--snapshot">
                            <thead>
                                <tr>
                                    <th scope="col">Title</th>
                                    <th scope="col">ID</th>
                                    <th scope="col" class="analytics-table-num">Price</th>
                                    <th scope="col" class="admin-col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customList as $row): ?>
                                    <?php
                                    if (!is_array($row)) {
                                        continue;
                                    }
                                    $cid = (string) ($row['filmId'] ?? '');
                                    if ($cid === '' || !custom_film_id_valid($cid)) {
                                        continue;
                                    }
                                    $ct = htmlspecialchars(trim((string) ($row['title'] ?? '')), ENT_QUOTES, 'UTF-8');
                                    $cp = (int) ($row['price'] ?? 0);
                                    $cgen = htmlspecialchars((string) ($row['genre'] ?? 'action'), ENT_QUOTES, 'UTF-8');
                                    $cdesc = htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td><?= $ct !== '' ? $ct : '—' ?></td>
                                        <td class="admin-film-id-cell"><code><?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?></code></td>
                                        <td class="analytics-table-num"><?= $cp ?> rub</td>
                                        <td class="admin-col-action">
                                            <button type="button" class="more-button admin-edit-btn" onclick="openEditModal('<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>', '<?= $ct ?>', <?= $cp ?>, '<?= $cgen ?>', '<?= $cdesc ?>')">Edit</button>
                                            <button type="button" class="more-button admin-delete-btn" onclick="deleteFilm('<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>')">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <?php
                $genrePost = (string) ($_POST['genre'] ?? '');
                $genreSel = in_array($genrePost, ['action', 'drama', 'comedy', 'fantasy'], true) ? $genrePost : 'action';
                ?>
                <h2 class="admin-manage-heading">Add film</h2>
                <form method="post" action="/admin.php" class="admin-film-form admin-film-form-wrap">
                    <input type="hidden" name="admin_action" value="add_film">
                    <label for="af-title">Title</label>
                    <input id="af-title" name="title" type="text" required maxlength="200" value="<?= htmlspecialchars((string) ($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <label for="af-price">Price (rub)</label>
                    <input id="af-price" name="price" type="number" min="0" max="9999999" required value="<?= htmlspecialchars((string) ($_POST['price'] ?? '299'), ENT_QUOTES, 'UTF-8') ?>">

                    <label for="af-genre">Genre</label>
                    <select id="af-genre" name="genre">
                        <option value="action"<?= $genreSel === 'action' ? ' selected' : '' ?>>Action</option>
                        <option value="drama"<?= $genreSel === 'drama' ? ' selected' : '' ?>>Drama</option>
                        <option value="comedy"<?= $genreSel === 'comedy' ? ' selected' : '' ?>>Comedy</option>
                        <option value="fantasy"<?= $genreSel === 'fantasy' ? ' selected' : '' ?>>Fantasy</option>
                    </select>

                    <label for="af-desc">Description (shown on “more” page)</label>
                    <textarea id="af-desc" name="description" rows="8" maxlength="12000"><?= htmlspecialchars((string) ($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

                    <button type="submit" class="more-button admin-film-submit">Add film</button>
                </form>

                <!-- Edit Modal -->
                <div id="editModal" class="admin-modal" style="display: none;">
                    <div class="admin-modal-content">
                        <div class="admin-modal-header">
                            <h3>Edit film</h3>
                            <span class="admin-modal-close" onclick="closeEditModal()">&times;</span>
                        </div>
                        <form method="post" action="/admin.php" class="admin-film-form">
                            <input type="hidden" name="admin_action" value="update_film">
                            <input type="hidden" name="film_id" id="edit-film-id">
                            <label for="edit-title">Title</label>
                            <input id="edit-title" name="title" type="text" required maxlength="200">

                            <label for="edit-price">Price (rub)</label>
                            <input id="edit-price" name="price" type="number" min="0" max="9999999" required>

                            <label for="edit-genre">Genre</label>
                            <select id="edit-genre" name="genre">
                                <option value="action">Action</option>
                                <option value="drama">Drama</option>
                                <option value="comedy">Comedy</option>
                                <option value="fantasy">Fantasy</option>
                            </select>

                            <label for="edit-desc">Description (shown on "more" page)</label>
                            <textarea id="edit-desc" name="description" rows="8" maxlength="12000"></textarea>

                            <button type="submit" class="more-button admin-film-submit">Update film</button>
                        </form>
                    </div>
                </div>

                <!-- Delete form -->
                <form id="deleteForm" method="post" action="/admin.php" style="display: none;">
                    <input type="hidden" name="admin_action" value="delete_film">
                    <input type="hidden" name="film_id" id="delete-film-id">
                </form>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/blocks/footer.php'; ?>
    <script src="perehod.js"></script>
    <script>
        function openEditModal(filmId, title, price, genre, description) {
            document.getElementById('edit-film-id').value = filmId;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-genre').value = genre;
            document.getElementById('edit-desc').value = description;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteFilm(filmId) {
            if (confirm('Remove this film from the site catalog?')) {
                document.getElementById('delete-film-id').value = filmId;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
