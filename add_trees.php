<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numTrees = intval($_POST['numTrees']);
    $_SESSION['numTrees'] = $numTrees; // Store number of trees in session
    header("Location: display_trees.php"); // Redirect to display page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Trees</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Add Trees to Your Drawing</h1>
    <form method="POST">
        <label for="numTrees">Number of Trees:</label>
        <input type="number" id="numTrees" name="numTrees" min="1" required>
        <button type="submit">Submit</button>
    </form>
    <br>
    <button onclick="window.location.href='draw.html'">Back to Drawing</button>
</body>
</html>