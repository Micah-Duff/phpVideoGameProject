<?php
$consoles = [
    'PC',
    'PlayStation 5',
    'Xbox',
    'Nintendo Switch',
];

$allowedImages = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$values = ['code' => '', 'name' => '', 'console' => '', 'price' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['code']    = trim($_POST['code'] ?? '');
    $values['name']    = trim($_POST['name'] ?? '');
    $values['console'] = $_POST['console'] ?? '';
    $values['price']   = trim($_POST['price'] ?? '');

    if ($values['code'] === '') {
        $errors['code'] = 'Inventory code is required.';
    } elseif (!preg_match('/^GAME-\d{4}$/D', $values['code'])) {
        $errors['code'] = 'Use the format GAME-1234 (uppercase GAME- followed by exactly four digits).';
    }

    if ($values['name'] === '') {
        $errors['name'] = 'Video game name is required.';
    }

    if ($values['console'] === '') {
        $errors['console'] = 'Please choose a console.';
    } elseif (!in_array($values['console'], $consoles, true)) {
        $errors['console'] = 'Please choose a console from the list.';
    }

    if ($values['price'] === '') {
        $errors['price'] = 'Price is required.';
    } elseif (!is_numeric($values['price'])) {
        $errors['price'] = 'Price must be a number.';
    } elseif ((float)$values['price'] <= 0) {
        $errors['price'] = 'Price must be greater than zero.';
    }

    $extension = '';
    $file = $_FILES['image'] ?? null;
    if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['image'] = 'Please select a game image.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = 'The image could not be uploaded. Please try again';
    } elseif (!is_uploaded_file($file['tmp_name'])) {
        $errors['image'] = 'Invalid upload.';
    } else {
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || !isset($allowedImages[$info['mime']])) {
            $errors['image'] = 'The file must be a JPG, PNG, GIF, or WEBP file format.';
        } else {
            $extension = $allowedImages[$info['mime']];
        }
    }

    if (empty($errors)) {
        $uploadDir = __DIR__ . '/uploads/';
        $csvPath   = __DIR__ . '/games/games.csv';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        if (!is_dir(dirname($csvPath))) {
            mkdir(dirname($csvPath), 0775, true);
        }

        $milliseconds = (int) round(microtime(true) * 1000);
        $filename = $milliseconds . '_' . bin2hex(random_bytes(2)) . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $errors['image'] = 'The image could not be saved. Please try again.';
        } else {
            $handle = fopen($csvPath, 'a');
            if ($handle === false) {
                unlink($uploadDir . $filename);
                $errors['image'] = 'The game could not be saved. Please try again.';
            } else {
                flock($handle, LOCK_EX);
                fputcsv($handle, [
                    $values['code'],
                    $values['name'],
                    $values['console'],
                    number_format((float)$values['price'], 2, '.', ''),
                    $filename,
                ], ',', '"', '\\');
                flock($handle, LOCK_UN);
                fclose($handle);

                header('Location: games.php');
                exit;
            }
        }
    }
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Game Store Entry</title>
    <?php include 'includes/bootstrap.php' ?>
</head>

<body class="bg-info">
    <?php include 'includes/navigation.php' ?>
    <h1 class="display-4 text-center"><strong>Video Game Entry</strong></h1>

    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <p class="mb-0 fw-bold">Please fix the errors below and submit again.</p>
                    </div>
    <?php endif; ?>

    <form method="POST" action="index.php" enctype="multipart/form-data" class="container text-center display-6" style="margin-top: 10;" novalidate>
        <hr> 
        <div class="row justify-content-center">
            <div class="col-md-6">
                <label for="code">Inventory Code:</label>
                <input type="text" id="code" name="code" class="form-control" placeholder="GAME-1234" value="<?= e($values['code']) ?>">
                <?php if (isset($errors['code'])): ?><div class="alert alert-danger"><?= e($errors['code']) ?></div><?php endif; ?>
                <br>
                <label for="name">Video Game Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= e($values['name']) ?>">
                <?php if (isset($errors['name'])): ?><div class="alert alert-danger"><?= e($errors['name']) ?></div><?php endif; ?>
                <br>
                <label for="console">Console:</label>
                <select id="console" name="console" class="form-select">
                    <option value="">-- Select a console --</option>
                    <?php foreach ($consoles as $console): ?>
                        <option value="<?= e($console) ?>" <?= $values['console'] === $console ? 'selected' : '' ?>><?= e($console) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['console'])): ?><div class="alert alert-danger"><?= e($errors['console']) ?></div><?php endif; ?>
                <br>
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" placeholder="59.99" value="<?= e($values['price']) ?>">
                <?php if (isset($errors['price'])): ?><div class="alert alert-danger"><?= e($errors['price']) ?></div><?php endif; ?>
                <br>
                <label for="image">Game Image:</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <?php if (isset($errors['image'])): ?><div class="alert alert-danger"><?= e($errors['image']) ?></div><?php endif; ?>
            </div>
        </div>
        <hr>
        <input type="submit" value="Add Game" class="btn btn-primary">
    </form>
</body>

</html>