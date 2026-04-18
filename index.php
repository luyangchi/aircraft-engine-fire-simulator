<!DOCTYPE html>
<html lang="en">
<head>
    <title>Engine Fire Simulator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Aircraft Engine Fire Simulator</h1>

    <form class="form-box" method="POST" action="start.php">
        <div>
            <label for="name">Name</label><br>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>
        </div>

        <div>
            <label for="email">Email</label><br>
            <input type="email" id="email" name="email" placeholder="Enter your email" required pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
            title="Please enter a valid email address">
        </div>

    <button type="submit">Submit</button>
    </form>
</div>

</body>
</html>