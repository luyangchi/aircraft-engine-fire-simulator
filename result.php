<?php
session_start();
include 'db.php';

$id = $_SESSION['attempt_id'];

// Get score
$result = $conn->query("SELECT * FROM attempts WHERE id = $id");
$data = $result->fetch_assoc();

// Update end time
$conn->query("UPDATE attempts SET end_time = NOW() WHERE id = $id");

// Performance level
$score = $data['score'];

if ($score >= 55) {
    $level = "Excellent";
} elseif ($score >= 40) {
    $level = "Competent";
} elseif ($score >= 30) {
    $level = "Developing";
} else {
    $level = "Needs Improvement";
}

// Get learner responses for each step
$responses = $conn->query("
    SELECT 
        s.id AS step_number,
        s.content AS step_content,
        c.choice_text,
        c.score AS points
    FROM responses r
    INNER JOIN steps s ON r.step_id = s.id
    INNER JOIN choices c ON r.choice_id = c.id
    WHERE r.attempt_id = $id
    ORDER BY s.id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Results</title>
    <link rel="stylesheet" href="style.css?v=3">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">
    <h1>Simulation Complete</h1>

    <p><strong>Name:</strong> <?php echo htmlspecialchars($data['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email']); ?></p>
    <p><strong>Score:</strong> <?php echo $score; ?></p>
    <canvas id="scoreChart" width="400" height="200"></canvas>
    <p><strong>Performance:</strong> <?php echo $level; ?></p>

    <h2>Debrief Dashboard</h2>

    <div class="debrief-dashboard">
        <?php while ($row = $responses->fetch_assoc()): ?>
            <?php
                if ($row['points'] >= 10) {
                    $tag = "Best";
                    $tag_class = "tag-best";
                } elseif ($row['points'] >= 0) {
                    $tag = "Good";
                    $tag_class = "tag-good";
                } else {
                    $tag = "Unsafe";
                    $tag_class = "tag-unsafe";
                }
            ?>
            <div class="debrief-card">
                <div class="debrief-card-header">
                    <h3>Step <?php echo $row['step_number']; ?></h3>
                    <span class="status-tag <?php echo $tag_class; ?>"><?php echo $tag; ?></span>
                </div>

                <p><strong>Scenario:</strong> <?php echo htmlspecialchars($row['step_content']); ?></p>
                <p><strong>Your Response:</strong> <?php echo htmlspecialchars($row['choice_text']); ?></p>
                <p><strong>Points Earned:</strong> <?php echo $row['points']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>

    <form method="GET" action="index.php"><button type="submit">Try Again</button></form>
</div>

<script>
const score = <?php echo $score; ?>;
const maxScore = 80;

const ctx = document.getElementById('scoreChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Your Score', 'Max Score'],
        datasets: [{
            label: 'Performance',
            data: [score, maxScore]
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: maxScore
            }
        }
    }
});
</script>
</body>
</html>
