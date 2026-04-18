<?php
include 'db.php';

// Attempt summary
$attempts = $conn->query("
    SELECT * 
    FROM attempts 
    ORDER BY id DESC
");

// Step performance summary with drop-off rate
$step_summary = $conn->query("
    SELECT
        s.id AS step_number,
        s.content AS scenario,
        COUNT(r.id) AS total_reached,
        SUM(CASE WHEN c.score > 0 THEN 1 ELSE 0 END) AS accurate_count,
        ROUND(
            (SUM(CASE WHEN c.score > 0 THEN 1 ELSE 0 END) / NULLIF(COUNT(r.id), 0)) * 100,
            1
        ) AS accuracy_rate,
        ROUND(AVG(r.time_used), 1) AS avg_time_used,
        (
            SELECT COUNT(r2.id)
            FROM responses r2
            WHERE r2.step_id = s.id + 1
        ) AS next_step_reached
    FROM steps s
    LEFT JOIN responses r ON s.id = r.step_id
    LEFT JOIN choices c ON r.choice_id = c.id
    GROUP BY s.id, s.content
    ORDER BY s.id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>

<div class="container">
    <h1>Admin Dashboard</h1>

    <h2>Attempts</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Score</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>

        <?php while ($row = $attempts->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo $row['score']; ?></td>
            <td><?php echo $row['start_time']; ?></td>
            <td><?php echo $row['end_time']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2 style="margin-top:40px;">Step Accuracy Summary</h2>

<div class="analytics-dashboard">
    <?php while ($row = $step_summary->fetch_assoc()): ?>
        <?php
            $total_reached = (int)$row['total_reached'];
            $next_step_reached = (int)$row['next_step_reached'];

            if ($row['step_number'] == 8) {
                $dropoff_rate = 0.0;
            } elseif ($total_reached > 0 && $next_step_reached >= 0) {
                $dropoff_rate = round((($total_reached - $next_step_reached) / $total_reached) * 100, 1);
            } else {
                $dropoff_rate = null;
            }

            $accuracy = $row['accuracy_rate'];

            if ($accuracy === null) {
                $heatmap_class = 'heatmap-na';
                $difficulty_label = 'N/A';
            } elseif ($accuracy >= 80) {
                $heatmap_class = 'heatmap-easy';
                $difficulty_label = 'Low';
            } elseif ($accuracy >= 60) {
                $heatmap_class = 'heatmap-medium';
                $difficulty_label = 'Medium';
            } else {
                $heatmap_class = 'heatmap-hard';
                $difficulty_label = 'High';
            }
        ?>

        <details class="step-card">
            <summary class="step-card-summary">
                <div class="step-summary-left">
                    <span class="step-number-badge">Step <?php echo $row['step_number']; ?></span>
                    <span class="difficulty-badge <?php echo $heatmap_class; ?>">
                        <?php echo $difficulty_label; ?>
                    </span>
                </div>

                <div class="step-summary-metrics">
                    <div class="metric-pill">
                        <span class="metric-label">Reached</span>
                        <span class="metric-value"><?php echo $total_reached; ?></span>
                    </div>
                    <div class="metric-pill">
                        <span class="metric-label">Accuracy</span>
                        <span class="metric-value"><?php echo $accuracy !== null ? $accuracy . '%' : 'N/A'; ?></span>
                    </div>
                    <div class="metric-pill">
                        <span class="metric-label">Avg Time</span>
                        <span class="metric-value"><?php echo $row['avg_time_used'] !== null ? $row['avg_time_used'] . 's' : 'N/A'; ?></span>
                    </div>
                    <div class="metric-pill">
                        <span class="metric-label">Drop-off</span>
                        <span class="metric-value"><?php echo $dropoff_rate !== null ? $dropoff_rate . '%' : 'N/A'; ?></span>
                    </div>
                </div>
            </summary>

            <div class="step-card-body">
                <div class="scenario-block">
                    <span class="scenario-label">Scenario</span>
                    <p><?php echo htmlspecialchars($row['scenario']); ?></p>
                </div>

                <div class="step-detail-grid">
                    <div class="detail-box">
                        <span class="detail-label">Total Reached</span>
                        <span class="detail-value"><?php echo $total_reached; ?></span>
                    </div>

                    <div class="detail-box">
                        <span class="detail-label">Accurate Responses</span>
                        <span class="detail-value"><?php echo $row['accurate_count'] !== null ? $row['accurate_count'] : 0; ?></span>
                    </div>

                    <div class="detail-box">
                        <span class="detail-label">Accuracy Rate</span>
                        <span class="detail-value"><?php echo $accuracy !== null ? $accuracy . '%' : 'N/A'; ?></span>
                    </div>

                    <div class="detail-box">
                        <span class="detail-label">Avg Time Used</span>
                        <span class="detail-value"><?php echo $row['avg_time_used'] !== null ? $row['avg_time_used'] . ' sec' : 'N/A'; ?></span>
                    </div>

                    <div class="detail-box">
                        <span class="detail-label">Drop-off Rate</span>
                        <span class="detail-value"><?php echo $dropoff_rate !== null ? $dropoff_rate . '%' : 'N/A'; ?></span>
                    </div>

                    <div class="detail-box">
                        <span class="detail-label">Difficulty</span>
                        <span class="detail-value"><?php echo $difficulty_label; ?></span>
                    </div>
                </div>
            </div>
        </details>
    <?php endwhile; ?>
</div>

</body>
</html>