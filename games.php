<?php

$games = [];
$csvPath = __DIR__ . '/games/games.csv';

if (is_file($csvPath) && ($handle = fopen($csvPath, 'r')) !== false) {
    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        if (count($row) < 5) {
            continue;
        }
        $games[] = $row;
    }
    fclose($handle);
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
    <title>List of Games</title>
    <?php include 'includes/bootstrap.php' ?>
</head>
<body class="bg-info">
    <?php include 'includes/navigation.php' ?>
    <h1 class="display-6 text-center"><strong>List of Games</strong></h1>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <?php if (empty($games)): ?>
                <div class="col-md-6">
                    <div class="alert alert-warning text-center">
                        <p class="mb-2">No games have been added yet.</p>
                        <a href="index.php" class="btn btn-primary">Add your first game</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-lg-10">
                    <div class="alert alert-success">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle text-center">
                                <thead>
                                    <tr>
                                        <th>Cover</th>
                                        <th>Inventory Code</th>
                                        <th>Title</th>
                                        <th>Console</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($games as $game): ?>
                                        <tr>
                                            <td>
                                                <img src="uploads/<?= e(rawurlencode($game[4])) ?>" alt="<?= e($game[1]) ?>" class="rounded">
                                            </td>
                                            <td><?= e($game[0]) ?></td>
                                            <td><?= e($game[1]) ?></td>
                                            <td><?= e($game[2]) ?></td>
                                            <td>$<?= number_format((float)$game[3], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary">Add Another Game</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>