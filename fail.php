<?php
session_start();
include 'db.php';

// Record end time for this failed attempt
if (!empty($_SESSION['attempt_id'])) {
    $id = (int)$_SESSION['attempt_id'];
    $conn->query("UPDATE attempts SET end_time = NOW() WHERE id = $id");
}

$reason = $_SESSION['fail_reason'] ?? 'unknown';
$name   = $_SESSION['name'] ?? 'Learner';

// Friendly message based on reason
if ($reason === 'timeout') {
    $headline = "⏰ Time's Up!";
    $message  = "You didn't respond within 10 seconds. In a real emergency, hesitation can be fatal — speed and accuracy both matter.";
} else {
    $headline = "❌ Incorrect Action";
    $message  = $_SESSION['fail_feedback'] ?? "You selected an incorrect procedure.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Simulation Failed</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container fail-container">

    <h1 class="fail-title">Simulation Failed</h1>

    <p class="fail-name">Hello, <strong><?php echo htmlspecialchars($name); ?></strong>.</p>

    <div class="fail-box">
        <h2><?php echo $headline; ?></h2>
        <p><?php echo $message; ?></p>
    </div>

    <div class="fail-tip">
        <h2>💡 What to do next</h2>
        <p>Review the correct engine fire emergency checklist and re-attempt the simulation. Consistent correct responses under time pressure are the goal.</p>
    </div>

    <div style="text-align:center; margin-top: 30px;">
        <form method="GET" action="index.php"><button type="submit">Try Again</button></form>
    </div>

</div>

</body>
</html>
