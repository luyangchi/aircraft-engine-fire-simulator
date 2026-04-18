<!DOCTYPE html>
<html lang="en">
<head>
    <title>Simulation Overview</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<div class="container">
    <h1>Aircraft Engine Fire Simulator</h1>

    <div class="overview-box">
        <div class="overview-content">
            <div class="overview-text">
                <h2>Welcome to the Simulation</h2>
                <p>
                    This simulation will test your ability to respond correctly to an aircraft engine fire emergency.
                    You will work through <strong>8 critical decision points</strong> drawn from real emergency procedures.
                </p>

                <h3>⚠️ Rules</h3>
                <ul class="overview-rules">
                    <li>Each step has a <strong>10-second timer</strong>. You must respond before time runs out.</li>
                    <li>Choosing an <strong>incorrect action</strong> will immediately fail the simulation.</li>
                    <li>Running out of time counts as a <strong>failure</strong>.</li>
                    <li>You must complete all steps correctly to receive a final score.</li>
                </ul>

                <h3>📋 Scoring</h3>
                <ul class="overview-rules">
                    <li>Best action (10 points): The strongest procedural response.</li>
                    <li>Good action (5 points): Acceptable, but not the optimal response.</li>
                    <li>Unsafe action: Ends the simulation immediately.</li>                    
                </ul>

                <h3>📊 Performance Levels</h3>
                <ul class="overview-rules">
                    <li>55–60 points: Excellent</li>
                    <li>40–54 points: Competent</li>
                    <li>30–39 points: Developing</li>
                    <li>Below 30: Needs Improvement</li>
                </ul>
            </div>

            <div class="overview-image-col">
                <img src="images/engine-fire.jpg" alt="Aircraft engine fire" class="overview-image">
            </div>
        </div>

        <div class="overview-start">
            <form method="GET" action="simulation.php">
                <button type="submit">Start Simulation</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>