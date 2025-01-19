<?php
// Start session
session_start();

// Database connection
require_once 'db_connect.php';

// Fetch students
$students = [];
$sql = "SELECT stuid, name FROM student";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch courses
$courses = [];
$sql = "SELECT cid, name FROM course";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch purposes
$purposes = [];
$sql = "SELECT puid, amount, purpose FROM purpose";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purposes[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stuid = $_POST['student_id'];
    $puid = $_POST['purpose'];
    $card = $_POST['card_number'];
    $expire_date = $_POST['expiry_date'] . '-01'; // Convert YYYY-MM to YYYY-MM-01

    // Get the amount based on purpose
    $amount = 0;
    foreach ($purposes as $purpose) {
        if ($purpose['puid'] == $puid) {
            $amount = $purpose['amount'];
            break;
        }
    }

    // Insert into payment table
    $sql = "INSERT INTO payment (card, expire_date, stuid, puid) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $card, $expire_date, $stuid, $puid);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Payment recorded successfully.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to record payment.";
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch payments
$payments = [];
$sql = "SELECT p.pid, p.card, DATE_FORMAT(p.expire_date, '%Y-%m') AS expire_date, 
               pr.amount, pr.purpose, s.name AS student_name, c.name AS course_name
        FROM payment p
        JOIN student s ON p.stuid = s.stuid
        JOIN course c ON s.cid = c.cid
        JOIN purpose pr ON p.puid = pr.puid
        ORDER BY p.pid DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Payment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url("image/valueedu.jpeg") no-repeat center center;
            background-size: cover;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: #ffffffcc;
            padding: 30px;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="container">
        <!-- Header with Logout Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>University Payment System</h2>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Display Success and Error Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
                 . htmlspecialchars($_SESSION['success_message']) .
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                 . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
                 . htmlspecialchars($_SESSION['error_message']) .
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                 . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form method="POST">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student</label>
                <select class="form-select" id="student_id" name="student_id" required>
                    <option value="">Select Student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo htmlspecialchars($student['stuid']); ?>">
                            <?php echo htmlspecialchars($student['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <select class="form-select" id="purpose" name="purpose" required>
                    <option value="">Select Purpose</option>
                    <?php foreach ($purposes as $purpose): ?>
                        <option value="<?php echo htmlspecialchars($purpose['puid']); ?>" data-amount="<?php echo htmlspecialchars($purpose['amount']); ?>">
                            <?php echo htmlspecialchars($purpose['purpose']); ?> (RS. <?php echo htmlspecialchars($purpose['amount']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="card_number" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="card_number" name="card_number" maxlength="16" pattern="\d{16}" required placeholder="Enter 16-digit card number">
            </div>

            <div class="mb-3">
                <label for="expiry_date" class="form-label">Expiry Date</label>
                <input type="month" class="form-control" id="expiry_date" name="expiry_date" required>
            </div>

            <button type="submit" class="btn btn-primary">Submit Payment</button>
        </form>

        <h3 class="mt-5">Payment Records</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Purpose</th>
                    <th>Card</th>
                    <th>Expiry</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments): ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['pid']); ?></td>
                            <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($payment['card']); ?></td>
                            <td><?php echo htmlspecialchars($payment['expire_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
