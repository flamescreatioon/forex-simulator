<?php

session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $balance = floatval($_POST['balance']);
    $profit = floatval($_POST['profit']);
    $leverage = floatval($_POST['leverage']);
    $margin = floatval($_POST['margin']);
    $goal = floatval($_POST['goal']);
    $freemargin = floatval($_POST['freemargin']);
    $marginlevel = floatval($_POST['marginlevel']);
    $equity = floatval($_POST['equity']);

    if($balance <= 0 || $goal<=0){
        $error = "Please enter valid numbers for balance and profit.";
    }else{
        $_SESSION["balance"] = $balance;
        $_SESSION["profit"] = $profit;
        $_SESSION["leverage"] = $leverage;
        $_SESSION["margin"] = $margin;
        $_SESSION["goal"] = $goal;
        $_SESSION["freemargin"] = $freemargin;
        $_SESSION["marginlevel"] = $marginlevel;
        $_SESSION["equity"] = $equity;

        header("Location: index.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Parameters | Demo Forex</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="max-w-lg mx-auto bg-white mt-20 p-8 rounded-2xl shadow-md">
        <h1 class="text-2xl font-semibold text-center mb-6 text-blue-600">Set Demo Parameters</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block mb-2 font-medium">Starting Balance ($)</label>
                <input type="number" name="balance" step="0.01" value="<?= $_SESSION['balance'] ?? 10000 ?>" class="w-full p-2 border rounded-lg" required>
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Profit Goal ($)</label>
                <input type="number" name="goal" step="0.01" value="<?= $_SESSION['goal'] ?? 20000 ?>" class="w-full p-2 border rounded-lg" required>
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Leverage (e.g. 100)</label>
                <input type="number" name="leverage" value="<?= $_SESSION['leverage'] ?? 100 ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-medium">Margin ($)</label>
                <input type="number" name="margin" step="0.01" value="<?= $_SESSION['margin'] ?? 1000 ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-medium">Free Margin ($)</label>
                <input type="number" name="freemargin" step="0.01" value="<?= $_SESSION['freemargin'] ?? 8000 ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-medium">Margin Level (%)</label>
                <input type="number" name="marginlevel" step="0.01" value="<?= $_SESSION['marginlevel'] ?? 800 ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-medium">Equity ($)</label>
                <input type="number" name="equity" step="0.01" value="<?= $_SESSION['equity'] ?? 9000 ?>" class="w-full p-2 border rounded-lg">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                Save & Start Demo
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
        </div>
    </div>

</body>
</html>