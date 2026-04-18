<?php
session_start();
include 'db.php';

$total_steps = 8;
$current_step = $_SESSION['step_id'];

// ── Handle timeout (auto-submitted by JS when timer hits 0) ──────────────────
if (isset($_POST['timeout'])) {
    $id = (int)$_SESSION['attempt_id'];
    $step_id = (int)$_SESSION['step_id'];

    $time_used = 10;
    if (isset($_SESSION['step_started_at'])) {
        $time_used = time() - $_SESSION['step_started_at'];
        if ($time_used < 0) {
            $time_used = 0;
        }
        if ($time_used > 10) {
            $time_used = 10;
        }
    }

    // Record timeout as a response row with no selected choice
    $conn->query("INSERT INTO responses (attempt_id, step_id, choice_id, time_used)
                  VALUES ({$_SESSION['attempt_id']}, {$step_id}, NULL, {$time_used})");

    $conn->query("UPDATE attempts SET score = 0, end_time = NOW() WHERE id = $id");

    $_SESSION['fail_reason'] = 'timeout';
    unset($_SESSION['step_started_at'], $_SESSION['step_started_for']);

    header('Location: fail.php');
    exit();
}

// ── Handle "Continue" after feedback ────────────────────────────────────────
if (isset($_POST['continue'])) {
    $_SESSION['step_id'] = $_SESSION['next_step'];
    unset($_SESSION['feedback']);
    unset($_SESSION['action_label']);
    unset($_SESSION['step_started_at'], $_SESSION['step_started_for']);

    if ($_SESSION['step_id'] == NULL) {
        header("Location: result.php");
        exit();
    }

    header("Location: simulation.php");
    exit();
}

// ── Handle choice selection ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choice_id'])) {

    $choice_id = (int)$_POST['choice_id'];

    $choice = $conn->query("SELECT * FROM choices WHERE id = $choice_id")->fetch_assoc();

    // Save response with time used
    $time_used = 0;
    if (isset($_SESSION['step_started_at'])) {
        $time_used = time() - $_SESSION['step_started_at'];
        if ($time_used < 0) {
            $time_used = 0;
        }
        if ($time_used > 10) {
            $time_used = 10;
        }
    }

    $conn->query("INSERT INTO responses (attempt_id, step_id, choice_id, time_used)
                VALUES ({$_SESSION['attempt_id']}, {$_SESSION['step_id']}, $choice_id, $time_used)");

    unset($_SESSION['step_started_at'], $_SESSION['step_started_for']);

    // Wrong answer → fail immediately
    if ((int)$choice['score'] < 0) {
        $_SESSION['fail_reason'] = "wrong";
        header("Location: fail.php");
        exit();
    }

    // Correct answer → update score and show feedback
    $conn->query("UPDATE attempts
              SET score = score + {$choice['score']}
              WHERE id = {$_SESSION['attempt_id']}");

    if ((int)$choice['score'] >= 10) {
        $_SESSION['action_label'] = "Best action";
    } elseif ((int)$choice['score'] >= 0) {
        $_SESSION['action_label'] = "Good action";
    } else {
        $_SESSION['action_label'] = "Unsafe action";
    }

    $_SESSION['feedback']  = $choice['feedback'];
    $_SESSION['next_step'] = $choice['next_step_id'];

    header("Location: simulation.php");
    exit();
}

// ── Load current step ────────────────────────────────────────────────────────
$step_id = $_SESSION['step_id'];
$result  = $conn->query("SELECT * FROM steps WHERE id = $step_id");
$step    = $result->fetch_assoc();

// Track when the current step started
if (!isset($_SESSION['feedback'])) {
    $_SESSION['step_started_at'] = time();
}

// Freeze timer after a correct answer (feedback screen)
$show_timer = !isset($_SESSION['feedback']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Simulation – Step <?php echo $current_step; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <!-- Step title -->
    <h1 style="margin:0 0 16px 0; font-weight:normal;">Step <?php echo $current_step; ?> of 8</h1>

    <!-- Content row: scenario text + timer side by side -->
    <div style="display:flex; align-items:flex-start; gap:16px;">

        <!-- Step content -->
        <h2 style="margin:0; flex:1;"><?php echo htmlspecialchars($step['content']); ?></h2>

        <!-- Countdown timer (only while awaiting a choice) -->
        <?php if ($show_timer): ?>
        <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0;">
            <div id="timer-ring" style="position:relative; width:60px; height:60px;">
                <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" width="60" height="60" style="transform:rotate(-90deg); display:block;">
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e9ecef" stroke-width="6"/>
                    <circle id="timer-arc" cx="32" cy="32" r="28" fill="none" stroke="#28a745" stroke-width="6" stroke-linecap="round"/>
                </svg>
                <span id="timer-count" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-size:1rem; font-weight:bold; color:#28a745;">10</span>
            </div>
            <span style="font-size:1rem; color:black; margin-top:3px;">seconds</span>
        </div>
        <?php endif; ?>

    </div>

    <!-- Choices -->
    <?php if (!isset($_SESSION['feedback'])): ?>
        <?php
        $choices = $conn->query("SELECT * FROM choices WHERE step_id = $step_id ORDER BY RAND()");
        while ($row = $choices->fetch_assoc()):
        ?>
            <form method="POST" class="choice-form">
                <input type="hidden" name="choice_id" value="<?php echo $row['id']; ?>">
                <button type="submit"><?php echo htmlspecialchars($row['choice_text']); ?></button>
            </form>
        <?php endwhile; ?>

        <!-- Hidden timeout form (submitted automatically by JS) -->
        <form method="POST" id="timeout-form">
            <input type="hidden" name="timeout" value="1">
        </form>
    <?php endif; ?>

    <!-- Feedback + Continue (correct answer) -->
    <?php if (isset($_SESSION['feedback'])): ?>
        <p class="feedback">
            <strong>You chose a <?php echo htmlspecialchars($_SESSION['action_label']); ?>.</strong><br>
            <?php echo htmlspecialchars($_SESSION['feedback']); ?>
        </p>
        <form method="POST">
            <button type="submit" name="continue" class="continue-btn">Continue →</button>
        </form>
    <?php endif; ?>

</div>

<?php if ($show_timer): ?>
<script>
(function () {
    const SECONDS   = 10;
    const arcEl     = document.getElementById('timer-arc');
    const countEl   = document.getElementById('timer-count');
    const timeoutFm = document.getElementById('timeout-form');

    // SVG circle circumference: 2π × r = 2π × 28 ≈ 175.93
    const CIRC = 2 * Math.PI * 28;
    arcEl.style.strokeDasharray  = CIRC;
    arcEl.style.strokeDashoffset = 0;

    let remaining = SECONDS;

    const interval = setInterval(() => {
        remaining -= 1;

        // Update number
        countEl.textContent = remaining;

        // Shrink arc
        const offset = CIRC * (1 - remaining / SECONDS);
        arcEl.style.strokeDashoffset = offset;

        // Colour shift: green → amber → red
        if (remaining <= 3) {
            arcEl.style.stroke   = '#dc3545';
            countEl.style.color  = '#dc3545';
        } else if (remaining <= 6) {
            arcEl.style.stroke   = '#fd7e14';
            countEl.style.color  = '#fd7e14';
        }

        if (remaining <= 0) {
            clearInterval(interval);
            // Disable all choice buttons to prevent race condition
            document.querySelectorAll('.choice-form button').forEach(b => b.disabled = true);
            timeoutFm.submit();
        }
    }, 1000);

    // If the user submits a choice, cancel the timer
    document.querySelectorAll('.choice-form').forEach(form => {
        form.addEventListener('submit', () => clearInterval(interval));
    });
})();
</script>
<?php endif; ?>

</body>
</html>