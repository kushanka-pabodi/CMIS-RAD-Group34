<?php
// academic.php
require_once 'db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  $_SESSION['login_error'] = "Please log in to access the dashboard.";
  header("Location: admin.php?loginError=1");
  exit();
}

$admin_username = $_SESSION['admin_username'];

// Initialize variables for messages
$success_message = "";
$error_message = "";

// Retrieve and clear messages from session
if (isset($_SESSION['success_message'])) {
  $success_message = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
  $error_message = $_SESSION['error_message'];
  unset($_SESSION['error_message']);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Determine the action
  $action = $_POST['action'] ?? '';

  // Students CRUD
  if ($action == 'create_student') {
    // Retrieve and sanitize POST data
    $stuid = trim($_POST['stuid'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $cid = trim($_POST['course'] ?? '');

    // Validate inputs
    if (empty($stuid) || empty($name) || empty($cid)) {
      $error_message = "Please fill in all required fields for student.";
    } else {
      // Check if student ID already exists
      $stmt = $conn->prepare("SELECT stuid FROM student WHERE stuid = ?");
      if ($stmt === false) {
        $error_message = "Error preparing the statement: " . $conn->error;
      } else {
        $stmt->bind_param("s", $stuid);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
          $error_message = "Student ID already exists.";
        } else {
          // Insert into DB
          $stmt_insert = $conn->prepare("INSERT INTO student (stuid, name, cid) VALUES (?, ?, ?)");
          if ($stmt_insert === false) {
            $error_message = "Error preparing the insert statement: " . $conn->error;
          } else {
            $stmt_insert->bind_param("ssi", $stuid, $name, $cid);
            if ($stmt_insert->execute()) {
              $_SESSION['success_message'] = "Student created successfully.";
              header("Location: academic.php");
              exit();
            } else {
              $error_message = "Error creating student: " . $stmt_insert->error;
            }
            $stmt_insert->close();
          }
        }
        $stmt->close();
      }
    }
  } elseif ($action == 'edit_student') {
    $stuid = trim($_POST['edit_stuid'] ?? '');
    $name = trim($_POST['edit_name'] ?? '');
    $cid = trim($_POST['edit_course'] ?? '');

    // Validate inputs
    if (empty($stuid) || empty($name) || empty($cid)) {
      $error_message = "Please fill in all required fields for student.";
    } else {
      // Update in DB
      $stmt_update = $conn->prepare("UPDATE student SET name = ?, cid = ? WHERE stuid = ?");
      if ($stmt_update === false) {
        $error_message = "Error preparing the update statement: " . $conn->error;
      } else {
        $stmt_update->bind_param("sis", $name, $cid, $stuid);
        if ($stmt_update->execute()) {
          $success_message = "Student updated successfully.";
        } else {
          $error_message = "Error updating student: " . $stmt_update->error;
        }
        $stmt_update->close();
      }
    }
  } elseif ($action == 'delete_student') {
    $stuid = trim($_POST['delete_stuid'] ?? '');

    if (empty($stuid)) {
      $error_message = "Invalid Student ID.";
    } else {
      // Delete from DB
      $stmt_delete = $conn->prepare("DELETE FROM student WHERE stuid = ?");
      if ($stmt_delete === false) {
        $error_message = "Error preparing the delete statement: " . $conn->error;
      } else {
        $stmt_delete->bind_param("s", $stuid);
        if ($stmt_delete->execute()) {
          $success_message = "Student deleted successfully.";
        } else {
          $error_message = "Error deleting student: " . $stmt_delete->error;
        }
        $stmt_delete->close();
      }
    }
  }

  // Courses CRUD
  elseif ($action == 'create_course') {
    $name = trim($_POST['course_name'] ?? '');
    $year_input = trim($_POST['year'] ?? '');

    // Validate inputs
    if (empty($name) || empty($year_input)) {
      $error_message = "Please fill in all required fields for the course.";
    } else {
      // Assume the user selects only the year; set month and day to 01-01
      $year = $year_input . '-01-01';

      // Insert into DB (cid is auto-incremented)
      $stmt_insert = $conn->prepare("INSERT INTO course (name, year) VALUES (?, ?)");
      if ($stmt_insert === false) {
        $error_message = "Error preparing the statement: " . $conn->error;
      } else {
        $stmt_insert->bind_param("ss", $name, $year);
        if ($stmt_insert->execute()) {
          $_SESSION['success_message'] = "Course created successfully.";
        } else {
          $error_message = "Error creating course: " . $stmt_insert->error;
        }
        $stmt_insert->close();
      }
    }
  } elseif ($action == 'edit_course') {
    $cid = trim($_POST['edit_cid'] ?? '');
    $name = trim($_POST['edit_course_name'] ?? '');
    $year_input = trim($_POST['edit_year'] ?? '');

    // Validate inputs
    if (empty($cid) || empty($name) || empty($year_input)) {
      $error_message = "Please fill in all required fields for the course.";
    } else {
      // Assume the user selects only the year; set month and day to 01-01
      $year = $year_input . '-01-01';

      // Update in DB
      $stmt_update = $conn->prepare("UPDATE course SET name = ?, year = ? WHERE cid = ?");
      if ($stmt_update === false) {
        $error_message = "Error preparing the update statement: " . $conn->error;
      } else {
        $stmt_update->bind_param("ssi", $name, $year, $cid);
        if ($stmt_update->execute()) {
          $success_message = "Course updated successfully.";
          header("Location: academic.php");
          exit();
        } else {
          $error_message = "Error updating course: " . $stmt_update->error;
        }
        $stmt_update->close();
      }
    }
  } elseif ($action == 'delete_course') {
    $cid = trim($_POST['delete_cid'] ?? '');

    if (empty($cid)) {
      $error_message = "Invalid Course ID.";
    } else {
      // Delete from DB
      $stmt_delete = $conn->prepare("DELETE FROM course WHERE cid = ?");
      if ($stmt_delete === false) {
        $error_message = "Error preparing the delete statement: " . $conn->error;
      } else {
        $stmt_delete->bind_param("i", $cid);
        if ($stmt_delete->execute()) {
          $success_message = "Course deleted successfully.";
        } else {
          $error_message = "Error deleting course: " . $stmt_delete->error;
        }
        $stmt_delete->close();
      }
    }
  }

  // Coordinators CRUD
  elseif ($action == 'create_coordinator') {
    // Retrieve and sanitize POST data
    $name = trim($_POST['c_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $cid = trim($_POST['c_course'] ?? '');

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($cid)) {
      $error_message = "Please fill in all required fields for coordinator.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error_message = "Invalid email format.";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
      $error_message = "Phone number must be exactly 10 digits.";
    } else {
      // Check if email or phone already exists
      $stmt = $conn->prepare("SELECT coid FROM coordinator WHERE email = ? OR phone = ?");
      if ($stmt === false) {
        $error_message = "Error preparing the statement: " . $conn->error;
      } else {
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
          $error_message = "Email or Phone number already in use.";
        } else {
          // Insert into DB (coid is auto-incremented)
          $stmt_insert = $conn->prepare("INSERT INTO coordinator (name, email, phone, cid) VALUES (?, ?, ?, ?)");
          if ($stmt_insert === false) {
            $error_message = "Error preparing the insert statement: " . $conn->error;
          } else {
            // Assuming cid is an integer
            $stmt_insert->bind_param("sssi", $name, $email, $phone, $cid);
            if ($stmt_insert->execute()) {
              $_SESSION['success_message'] = "Coordinator created successfully.";
              header("Location: academic.php");
              exit();
            } else {
              $error_message = "Error creating coordinator: " . $stmt_insert->error;
            }
            $stmt_insert->close();
          }
        }
        $stmt->close();
      }
    }
  } elseif ($action == 'edit_coordinator') {
    $coid = trim($_POST['edit_coid'] ?? '');
    $name = trim($_POST['edit_c_name'] ?? '');
    $email = trim($_POST['edit_email'] ?? '');
    $phone = trim($_POST['edit_phone'] ?? '');
    $cid = trim($_POST['edit_c_course'] ?? '');

    // Validate inputs
    if (empty($coid) || empty($name) || empty($email) || empty($phone) || empty($cid)) {
      $error_message = "Please fill in all required fields for coordinator.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error_message = "Invalid email format.";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
      $error_message = "Phone number must be exactly 10 digits.";
    } else {
      // Check if email or phone already exists for another coordinator
      $stmt = $conn->prepare("SELECT coid FROM coordinator WHERE (email = ? OR phone = ?) AND coid != ?");
      if ($stmt === false) {
        $error_message = "Error preparing the statement: " . $conn->error;
      } else {
        $stmt->bind_param("ssi", $email, $phone, $coid);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
          $error_message = "Email or Phone number already in use.";
        } else {
          // Update in DB
          $stmt_update = $conn->prepare("UPDATE coordinator SET name = ?, email = ?, phone = ?, cid = ? WHERE coid = ?");
          if ($stmt_update === false) {
            $error_message = "Error preparing the update statement: " . $conn->error;
          } else {
            $stmt_update->bind_param("sssii", $name, $email, $phone, $cid, $coid);
            if ($stmt_update->execute()) {
              $success_message = "Coordinator updated successfully.";
            } else {
              $error_message = "Error updating coordinator: " . $stmt_update->error;
            }
            $stmt_update->close();
          }
        }
        $stmt->close();
      }
    }
  } elseif ($action == 'delete_coordinator') {
    $coid = trim($_POST['delete_coid'] ?? '');

    if (empty($coid)) {
      $error_message = "Invalid Coordinator ID.";
    } else {
      // Delete from DB
      $stmt_delete = $conn->prepare("DELETE FROM coordinator WHERE coid = ?");
      if ($stmt_delete === false) {
        $error_message = "Error preparing the delete statement: " . $conn->error;
      } else {
        $stmt_delete->bind_param("i", $coid);
        if ($stmt_delete->execute()) {
          $success_message = "Coordinator deleted successfully.";
        } else {
          $error_message = "Error deleting coordinator: " . $stmt_delete->error;
        }
        $stmt_delete->close();
      }
    }
  }

  // Marks CRUD
  elseif ($action == 'create_marks') {
    // Retrieve and sanitize POST data
    $stuid = trim($_POST['stuid'] ?? '');
    $mark = trim($_POST['mark'] ?? '');
    $cid = trim($_POST['mark_course'] ?? '');
    $status = trim($_POST['mark_status'] ?? ''); // Pass or Fail
    $year = trim($_POST['year'] ?? '');

    // Validate inputs
    if (empty($stuid) || empty($mark) || empty($cid) || empty($status) || empty($year)) {
      $error_message = "Please fill in all required fields for marks.";
    } else {
      // Validate status
      $valid_status = ['Pass', 'Fail'];
      if (!in_array($status, $valid_status)) {
        $error_message = "Invalid status selected for marks.";
      } else {
        // Optional: Validate if stuid exists in the student table
        $stmt_check = $conn->prepare("SELECT stuid FROM student WHERE stuid = ?");
        if ($stmt_check === false) {
          $error_message = "Error preparing the statement: " . $conn->error;
        } else {
          $stmt_check->bind_param("s", $stuid);
          $stmt_check->execute();
          $stmt_check->store_result();
          if ($stmt_check->num_rows == 0) {
            $error_message = "Student ID does not exist.";
          } else {
            // Insert into DB (mid is auto-incremented)
            $stmt_insert = $conn->prepare("INSERT INTO mark (stuid, mark, cid, mark_status, year) VALUES (?, ?, ?, ?, ?)");
            if ($stmt_insert === false) {
              $error_message = "Error preparing the insert statement: " . $conn->error;
            } else {
              // Assuming cid is integer and year is a four-digit number
              $stmt_insert->bind_param("siiss", $stuid, $mark, $cid, $status, $year);
              if ($stmt_insert->execute()) {
                $_SESSION['success_message'] = "Mark record created successfully.";
                header("Location: academic.php");
                exit();
              } else {
                $error_message = "Error creating mark record: " . $stmt_insert->error;
              }
              $stmt_insert->close();
            }
          }
          $stmt_check->close();
        }
      }
    }
  } elseif ($action == 'edit_marks') {
    $mid = trim($_POST['edit_mid'] ?? '');
    $mark = trim($_POST['edit_mark'] ?? '');
    $cid = trim($_POST['edit_mark_course'] ?? '');
    $stuid = trim($_POST['edit_stuid'] ?? '');
    $status = trim($_POST['edit_mark_status'] ?? ''); // Pass or Fail
    $year = trim($_POST['edit_year'] ?? '');

    // Validate inputs
    if (empty($mid) || empty($mark) || empty($cid) || empty($stuid) || empty($status) || empty($year)) {
      $error_message = "Please fill in all required fields for marks.";
    } else {
      // Validate status
      $valid_status = ['Pass', 'Fail'];
      if (!in_array($status, $valid_status)) {
        $error_message = "Invalid status selected for marks.";
      } else {
        // Optional: Validate if stuid exists in the student table
        $stmt_check = $conn->prepare("SELECT stuid FROM student WHERE stuid = ?");
        if ($stmt_check === false) {
          $error_message = "Error preparing the statement: " . $conn->error;
        } else {
          $stmt_check->bind_param("s", $stuid);
          $stmt_check->execute();
          $stmt_check->store_result();
          if ($stmt_check->num_rows == 0) {
            $error_message = "Student ID does not exist.";
          } else {
            // Update in DB
            $stmt_update = $conn->prepare("UPDATE mark SET mark = ?, cid = ?, stuid = ?, mark_status = ?, year = ? WHERE mid = ?");
            if ($stmt_update === false) {
              $error_message = "Error preparing the update statement: " . $conn->error;
            } else {
              $stmt_update->bind_param("iissii", $mark, $cid, $stuid, $status, $year, $mid);
              if ($stmt_update->execute()) {
                $success_message = "Mark record updated successfully.";
              } else {
                $error_message = "Error updating mark record: " . $stmt_update->error;
              }
              $stmt_update->close();
            }
          }
          $stmt_check->close();
        }
      }
    }
  } elseif ($action == 'delete_marks') {
    $mid = trim($_POST['delete_mid'] ?? '');

    if (empty($mid)) {
      $error_message = "Invalid Mark ID.";
    } else {
      // Delete from DB
      $stmt_delete = $conn->prepare("DELETE FROM mark WHERE mid = ?");
      if ($stmt_delete === false) {
        $error_message = "Error preparing the delete statement: " . $conn->error;
      } else {
        $stmt_delete->bind_param("i", $mid);
        if ($stmt_delete->execute()) {
          $success_message = "Mark record deleted successfully.";
        } else {
          $error_message = "Error deleting mark record: " . $stmt_delete->error;
        }
        $stmt_delete->close();
      }
    }
  }

  // Additional actions can be added here
}

// Fetch data for display
// Fetch students
$students = [];
$sql_students = "SELECT student.stuid, student.name, course.name AS course_name
                FROM student 
                JOIN course ON student.cid = course.cid";
$result_students = $conn->query($sql_students);
if ($result_students) {
  while ($row = $result_students->fetch_assoc()) {
    $students[] = $row;
  }
}

// Fetch courses
$courses = [];
$sql_courses = "SELECT * FROM course";
$result_courses = $conn->query($sql_courses);
if ($result_courses) {
  while ($row = $result_courses->fetch_assoc()) {
    $courses[] = $row;
  }
}

// Fetch coordinators
$coordinators = [];
$sql_coordinators = "SELECT coordinator.coid, coordinator.name, coordinator.email, coordinator.phone, course.name AS course_name 
                    FROM coordinator 
                    JOIN course ON coordinator.cid = course.cid";
$result_coordinators = $conn->query($sql_coordinators);
if ($result_coordinators) {
  while ($row = $result_coordinators->fetch_assoc()) {
    $coordinators[] = $row;
  }
}

// Fetch marks
$marks = [];
$sql_marks = "SELECT mark.mid, mark.mark, mark.mark_status, mark.year, course.name AS course_name, mark.stuid 
              FROM mark 
              JOIN course ON mark.cid = course.cid";
$result_marks = $conn->query($sql_marks);
if ($result_marks) {
  while ($row = $result_marks->fetch_assoc()) {
    $marks[] = $row;
  }
}

// Close the DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Course Management Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <!-- Bootstrap 5 -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet" />

  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

  <!-- Date Picker CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

  <style>
    /* Layout and Basic Styles */
    body {
      background-image: url('image/valueedu.jpeg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      margin: 0;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* Top Bar */
    .top-bar {
      width: 100%;
      height: 60px;
      background-color: #34495e;
      color: #ecf0f1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 9999;
    }

    .top-bar .time-date {
      font-size: 0.9rem;
    }

    .top-bar button.logout-btn {
      background: none;
      border: 1px solid #ecf0f1;
      color: #ecf0f1;
      padding: 5px 10px;
      border-radius: 5px;
    }

    .top-bar button.logout-btn:hover {
      background-color: #ecf0f1;
      color: #34495e;
      cursor: pointer;
    }

    /* Sidebar */
    .sidebar {
      min-height: 100vh;
      width: 250px;
      background-color: #2c3e50;
      color: white;
      padding-top: 20px;
      position: fixed;
      top: 60px;
      /* placed below the top bar */
      bottom: 0;
      overflow-y: auto;
    }

    .sidebar h3 {
      text-align: center;
      margin-bottom: 20px;
    }

    .sidebar button {
      width: 100%;
      margin: 5px 0;
      text-align: left;
      border: none;
      background: none;
      color: #ecf0f1;
      padding: 10px 20px;
      font-size: 15px;
      cursor: pointer;
    }

    .sidebar button:hover {
      background-color: #34495e;
    }

    /* Main Content */
    .main-content {
      margin-left: 250px;
      /* same as sidebar width */
      margin-top: 60px;
      /* below top bar */
      padding: 20px;
      background-color: rgba(255, 255, 255, 0.9);
      min-height: calc(100vh - 60px);
    }

    .hidden {
      display: none !important;
    }

    /* Table styling */
    table {
      width: 100%;
      background-color: #fff;
      border-radius: 5px;
      overflow: hidden;
    }

    th,
    td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f7f7f7;
    }

    .btn-sm {
      font-size: 0.8rem;
      margin: 2px;
    }
  </style>
</head>

<body>
  <!-- Top Bar -->
  <div class="top-bar">
    <span>Welcome <?php echo htmlspecialchars($admin_username); ?></span>
    <div class="d-flex align-items-center">
      <div class="time-date me-3" id="timeDateDisplay">
        <!-- will be populated by JS -->
      </div>
      <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <h3>CMS Dashboard</h3>
    <button onclick="showSection('studentsSection')">
      <i class="fa fa-user-graduate"></i> Students
    </button>
    <button onclick="showSection('coursesSection')">
      <i class="fa fa-book"></i> Courses
    </button>
    <button onclick="showSection('coordinatorsSection')">
      <i class="fa fa-chalkboard-teacher"></i> Coordinators
    </button>
    <button onclick="showSection('marksSection')">
      <i class="fa fa-file-signature"></i> Marks
    </button>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Display success or error messages -->
    <?php if (!empty($success_message)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- STUDENTS SECTION -->
    <div id="studentsSection" class="">
      <h2>Students</h2>
      <!-- Search + Add button row -->
      <div class="d-flex mb-3">
        <input
          type="text"
          id="searchStudent"
          class="form-control me-2"
          placeholder="Search by Student ID or Name..."
          style="max-width: 300px;" />
        <button class="btn btn-primary" onclick="toggleForm('studentForm')">
          <i class="fa fa-plus"></i> Create Student
        </button>
      </div>
      <!-- Create Student Form -->
      <div
        id="studentForm"
        class="border p-3 mb-3"
        style="background-color: #fff; display: none;">
        <h5>Create Student</h5>
        <form method="POST" action="academic.php">
          <input type="hidden" name="action" value="create_student">
          <div class="mb-2">
            <label class="form-label">Student ID:</label>
            <input type="text" name="stuid" class="form-control" placeholder="e.g. ST001" required />
          </div>
          <div class="mb-2">
            <label class="form-label">Name:</label>
            <input type="text" name="name" class="form-control" placeholder="Student Name" required />
          </div>
          <div class="mb-2">
            <label class="form-label">Course Name:</label>
            <select name="course" class="form-select" required>
              <option value="">Select Course</option>
              <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['cid']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success btn-sm">
            Save
          </button>
          <button
            type="button"
            class="btn btn-secondary btn-sm"
            onclick="toggleForm('studentForm')">
            Cancel
          </button>
        </form>
      </div>
      <!-- Student Table -->
      <div class="table-responsive">
        <table class="table align-middle" id="studentTable">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th>Course Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $student): ?>
              <tr>
                <td><?php echo htmlspecialchars($student['stuid']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                <td>
                  <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editStudentModal"
                    onclick="loadStudentEdit('<?php echo htmlspecialchars($student['stuid']); ?>','<?php echo htmlspecialchars($student['name']); ?>','<?php echo htmlspecialchars($student['course_name']); ?>')">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button
                    class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteStudentModal"
                    onclick="confirmDelete('student', '<?php echo htmlspecialchars($student['stuid']); ?>')">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
            <!-- more rows from DB -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- COURSES SECTION -->
    <div id="coursesSection" class="hidden">
      <h2>Courses</h2>
      <!-- Search + Add button row -->
      <div class="d-flex mb-3">
        <input
          type="text"
          id="searchCourse"
          class="form-control me-2"
          placeholder="Search Course ID or Name..."
          style="max-width: 300px;" />
        <button class="btn btn-primary" onclick="toggleForm('courseForm')">
          <i class="fa fa-plus"></i> Create Course
        </button>
      </div>
      <!-- Create Course Form -->
      <div
        id="courseForm"
        class="border p-3 mb-3"
        style="background-color: #fff; display: none;">
        <h5>Create Course</h5>
        <form method="POST" action="academic.php">
          <input type="hidden" name="action" value="create_course">
          <div class="mb-2">
            <label class="form-label">Course Name:</label>
            <input type="text" name="course_name" class="form-control" placeholder="Course Name" required />
          </div>
          <div class="mb-2">
            <label class="form-label">Year:</label>
            <select name="year" class="form-select" required>
              <option value="">Select Year</option>
              <?php
              $current_year = date("Y");
              for ($i = $current_year; $i <= $current_year + 10; $i++) {
                echo "<option value=\"$i\">$i</option>";
              }
              ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success btn-sm">
            Save
          </button>
          <button
            type="button"
            class="btn btn-secondary btn-sm"
            onclick="toggleForm('courseForm')">
            Cancel
          </button>
        </form>
      </div>
      <!-- Course Table -->
      <div class="table-responsive">
        <table class="table align-middle" id="courseTable">
          <thead>
            <tr>
              <th>Course ID</th>
              <th>Course Name</th>
              <th>Year</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($courses as $course): ?>
              <tr>
                <td><?php echo htmlspecialchars($course['cid']); ?></td>
                <td><?php echo htmlspecialchars($course['name']); ?></td>
                <td>
                  <?php
                  // If 'year' is DATE, display only the year part
                  echo htmlspecialchars(date("Y", strtotime($course['year'])));
                  ?>
                </td>
                <td>
                  <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editCourseModal"
                    onclick="loadCourseEdit('<?php echo htmlspecialchars($course['cid']); ?>','<?php echo htmlspecialchars($course['name']); ?>','<?php echo date("Y", strtotime($course['year'])); ?>')">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button
                    class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteCourseModal"
                    onclick="confirmDelete('course', '<?php echo htmlspecialchars($course['cid']); ?>')">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
            <!-- more rows from DB -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- COORDINATORS SECTION -->
    <div id="coordinatorsSection" class="hidden">
      <h2>Coordinators</h2>
      <!-- Search + Add button row -->
      <div class="d-flex mb-3">
        <input
          type="text"
          id="searchCoordinator"
          class="form-control me-2"
          placeholder="Search by Name or Email..."
          style="max-width: 300px;" />
        <button class="btn btn-primary" onclick="toggleForm('coordinatorForm')">
          <i class="fa fa-plus"></i> Create Coordinator
        </button>
      </div>
      <!-- Create Coordinator Form -->
      <div
        id="coordinatorForm"
        class="border p-3 mb-3"
        style="background-color: #fff; display: none;">
        <h5>Create Coordinator</h5>
        <form method="POST" action="academic.php">
          <input type="hidden" name="action" value="create_coordinator">
          <div class="mb-2">
            <label class="form-label">Name:</label>
            <input type="text" name="c_name" class="form-control" placeholder="Coordinator Name" required />
          </div>
          <div class="mb-2">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required />
          </div>
          <div class="mb-2">
            <label class="form-label">Phone:</label>
            <input type="text" name="phone" class="form-control" placeholder="Phone Number" pattern="\d{10}" title="Phone number must be exactly 10 digits." required />
          </div>
          <div class="mb-2">
            <label class="form-label">Course Name:</label>
            <select name="c_course" class="form-select" required>
              <option value="">Select Course</option>
              <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['cid']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success btn-sm">
            Save
          </button>
          <button
            type="button"
            class="btn btn-secondary btn-sm"
            onclick="toggleForm('coordinatorForm')">
            Cancel
          </button>
        </form>
      </div>
      <!-- Coordinator Table -->
      <div class="table-responsive">
        <table class="table align-middle" id="coordinatorTable">
          <thead>
            <tr>
              <th>Coordinator ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Course</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($coordinators as $coordinator): ?>
              <tr>
                <td><?php echo htmlspecialchars($coordinator['coid']); ?></td>
                <td><?php echo htmlspecialchars($coordinator['name']); ?></td>
                <td><?php echo htmlspecialchars($coordinator['email']); ?></td>
                <td><?php echo htmlspecialchars($coordinator['phone']); ?></td>
                <td><?php echo htmlspecialchars($coordinator['course_name']); ?></td>
                <td>
                  <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editCoordinatorModal"
                    onclick="loadCoordinatorEdit('<?php echo htmlspecialchars($coordinator['coid']); ?>','<?php echo htmlspecialchars($coordinator['name']); ?>','<?php echo htmlspecialchars($coordinator['email']); ?>','<?php echo htmlspecialchars($coordinator['phone']); ?>','<?php echo htmlspecialchars($coordinator['course_name']); ?>')">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button
                    class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteCoordinatorModal"
                    onclick="confirmDelete('coordinator', '<?php echo htmlspecialchars($coordinator['coid']); ?>')">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
            <!-- more rows from DB -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- MARKS SECTION -->
    <div id="marksSection" class="hidden">
      <h2>Student Marks</h2>
      <!-- Add Report Button -->
      <div class="d-flex justify-content-end mb-3">
        <a href="generate_report.php" class="btn btn-secondary">
          <i class="fa fa-file-pdf"></i> Get PDF Report
        </a>
      </div>
      <!-- Search + Add button row -->
      <div class="d-flex mb-3">
        <input
          type="text"
          id="searchMarks"
          class="form-control me-2"
          placeholder="Search by Mark ID..."
          style="max-width: 300px;" />
        <button class="btn btn-primary" onclick="toggleForm('marksForm')">
          <i class="fa fa-plus"></i> Create Marks
        </button>
      </div>
      <!-- Create Marks Form -->
      <div
        id="marksForm"
        class="border p-3 mb-3"
        style="background-color: #fff; display: none;">
        <h5>Add Student Mark</h5>
        <form method="POST" action="academic.php">
          <input type="hidden" name="action" value="create_marks">
          <div class="mb-2">
            <label class="form-label">Select Course:</label>
            <select name="mark_course" class="form-select" required>
              <option value="">Select Course</option>
              <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['cid']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Select Student ID:</label>
            <select name="stuid" class="form-select" required>
              <option value="">Select Student</option>
              <?php foreach ($students as $student): ?>
                <option value="<?php echo htmlspecialchars($student['stuid']); ?>"><?php echo htmlspecialchars($student['stuid']) . ' - ' . htmlspecialchars($student['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Mark:</label>
            <input type="number" name="mark" class="form-control" placeholder="e.g. 85" required />
          </div>
          <div class="mb-2">
            <label class="form-label">Status:</label>
            <select name="mark_status" class="form-select" required>
              <option value="">Select Status</option>
              <option value="Pass">Pass</option>
              <option value="Fail">Fail</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Year:</label>
            <input type="number" name="year" class="form-control" placeholder="e.g. 2024" required />
          </div>
          <button type="submit" class="btn btn-success btn-sm">
            Save
          </button>
          <button
            type="button"
            class="btn btn-secondary btn-sm"
            onclick="toggleForm('marksForm')">
            Cancel
          </button>
        </form>
      </div>
      <!-- Marks Table -->
      <div class="table-responsive">
        <table class="table align-middle" id="marksTable">
          <thead>
            <tr>
              <th>Mark ID</th>
              <th>Mark</th>
              <th>Status</th>
              <th>Year</th>
              <th>Course</th>
              <th>Student ID</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($marks as $mark): ?>
              <tr>
                <td><?php echo htmlspecialchars($mark['mid']); ?></td>
                <td><?php echo htmlspecialchars($mark['mark']); ?></td>
                <td><?php echo htmlspecialchars($mark['mark_status']); ?></td>
                <td><?php echo htmlspecialchars($mark['year']); ?></td>
                <td><?php echo htmlspecialchars($mark['course_name']); ?></td>
                <td><?php echo htmlspecialchars($mark['stuid']); ?></td>
                <td>
                  <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editMarksModal"
                    onclick="loadMarksEdit('<?php echo htmlspecialchars($mark['mid']); ?>','<?php echo htmlspecialchars($mark['mark']); ?>','<?php echo htmlspecialchars($mark['course_name']); ?>','<?php echo htmlspecialchars($mark['stuid']); ?>','<?php echo htmlspecialchars($mark['mark_status']); ?>','<?php echo htmlspecialchars($mark['year']); ?>')">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button
                    class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteMarksModal"
                    onclick="confirmDelete('mark', '<?php echo htmlspecialchars($mark['mid']); ?>')">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                  <a href="#" class="btn btn-info btn-sm" onclick="confirmReport('<?php echo htmlspecialchars($mark['stuid']); ?>')">
                    <i class="fa fa-file-pdf"></i> Get Report
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            <!-- more rows from DB -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ======================= MODALS FOR EDITING ======================= -->

  <!-- STUDENT EDIT MODAL -->
  <div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="edit_student">
            <div class="mb-2">
              <label class="form-label">Student ID:</label>
              <input type="text" name="edit_stuid" id="editStudentId" class="form-control" readonly />
            </div>
            <div class="mb-2">
              <label class="form-label">Name:</label>
              <input type="text" name="edit_name" id="editStudentName" class="form-control" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Course Name:</label>
              <select name="edit_course" id="editStudentCourse" class="form-select" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?php echo htmlspecialchars($course['cid']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-success btn-sm">
              Save Changes
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- DELETE STUDENT MODAL -->
  <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="delete_student">
            <input type="hidden" name="delete_stuid" id="deleteStudentId">
            <p>Are you sure you want to delete Student ID: <strong id="deleteStudentName"></strong>?</p>
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- COURSE EDIT MODAL -->
  <div class="modal fade" id="editCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="edit_course">
            <div class="mb-2">
              <label class="form-label">Course ID:</label>
              <input type="number" name="edit_cid" id="editCourseId" class="form-control" readonly />
            </div>
            <div class="mb-2">
              <label class="form-label">Course Name:</label>
              <input type="text" name="edit_course_name" id="editCourseName" class="form-control" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Year:</label>
              <select name="edit_year" id="editCourseYear" class="form-select" required>
                <option value="">Select Year</option>
                <?php
                $current_year = date("Y");
                for ($i = $current_year; $i <= $current_year + 10; $i++) {
                  echo "<option value=\"$i\">$i</option>";
                }
                ?>
              </select>
            </div>
            <button type="submit" class="btn btn-success btn-sm">
              Save Changes
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- DELETE COURSE MODAL -->
  <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="delete_course">
            <input type="hidden" name="delete_cid" id="deleteCourseId">
            <p>Are you sure you want to delete Course ID: <strong id="deleteCourseName"></strong>?</p>
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- COORDINATOR EDIT MODAL -->
  <div class="modal fade" id="editCoordinatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Coordinator</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="edit_coordinator">
            <div class="mb-2">
              <label class="form-label">Coordinator ID:</label>
              <input type="number" name="edit_coid" id="editCoordinatorId" class="form-control" readonly />
            </div>
            <div class="mb-2">
              <label class="form-label">Name:</label>
              <input type="text" name="edit_c_name" id="editCoordinatorName" class="form-control" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Email:</label>
              <input type="email" name="edit_email" id="editCoordinatorEmail" class="form-control" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Phone:</label>
              <input type="text" name="edit_phone" id="editCoordinatorPhone" class="form-control" pattern="\d{10}" title="Phone number must be exactly 10 digits." required />
            </div>
            <div class="mb-2">
              <label class="form-label">Select Course:</label>
              <select name="edit_c_course" id="editCoordinatorCourse" class="form-select" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?php echo htmlspecialchars($course['cid']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-success btn-sm">
              Save Changes
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- DELETE COORDINATOR MODAL -->
  <div class="modal fade" id="deleteCoordinatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Coordinator</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="delete_coordinator">
            <input type="hidden" name="delete_coid" id="deleteCoordinatorId">
            <p>Are you sure you want to delete Coordinator ID: <strong id="deleteCoordinatorName"></strong>?</p>
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MARKS EDIT MODAL -->
  <div class="modal fade" id="editMarksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Marks</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="edit_marks">
            <input type="hidden" name="edit_mid" id="editMarksId">
            <div class="mb-2">
              <label class="form-label">Mark:</label>
              <input type="number" name="edit_mark" id="editMark" class="form-control" required />
            </div>
            <div class="mb-2">
              <label class="form-label">Course:</label>
              <select name="edit_mark_course" id="editMarkCourse" class="form-select" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?php echo htmlspecialchars($course['cid']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Student ID:</label>
              <select name="edit_stuid" id="editMarksStuid" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $student): ?>
                  <option value="<?php echo htmlspecialchars($student['stuid']); ?>"><?php echo htmlspecialchars($student['stuid']) . ' - ' . htmlspecialchars($student['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Status:</label>
              <select name="edit_mark_status" id="editMarkStatus" class="form-select" required>
                <option value="">Select Status</option>
                <option value="Pass">Pass</option>
                <option value="Fail">Fail</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Year:</label>
              <input type="number" name="edit_year" id="editMarkYear" class="form-control" placeholder="e.g. 2024" required />
            </div>
            <button type="submit" class="btn btn-success btn-sm">
              Save Changes
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- DELETE MARKS MODAL -->
  <div class="modal fade" id="deleteMarksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Marks</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="academic.php">
            <input type="hidden" name="action" value="delete_marks">
            <input type="hidden" name="delete_mid" id="deleteMarksId">
            <p>Are you sure you want to delete Mark ID: <strong id="deleteMarksName"></strong>?</p>
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Date Picker JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <script>
    // Show/Hide main sections
    function showSection(sectionId) {
      // Hide all
      const sections = ['studentsSection', 'coursesSection', 'coordinatorsSection', 'marksSection'];
      sections.forEach(sec => {
        document.getElementById(sec).classList.add('hidden');
      });
      // Show the one we want
      document.getElementById(sectionId).classList.remove('hidden');
    }

    // Toggle create form
    function toggleForm(formId) {
      const formEl = document.getElementById(formId);
      if (formEl.style.display === 'none' || formEl.style.display === '') {
        formEl.style.display = 'block';
      } else {
        formEl.style.display = 'none';
      }
    }

    // Load data into edit modal for Students
    function loadStudentEdit(id, name, course) {
      document.getElementById('editStudentId').value = id;
      document.getElementById('editStudentName').value = name;
      // Set course select
      const courseSelect = document.getElementById('editStudentCourse');
      courseSelect.value = getCourseIdByName(course);
    }

    // Load data into edit modal for Courses
    function loadCourseEdit(cid, name, year) {
      document.getElementById('editCourseId').value = cid;
      document.getElementById('editCourseName').value = name;
      document.getElementById('editCourseYear').value = year;
    }

    // Load data into edit modal for Coordinators
    function loadCoordinatorEdit(coid, name, email, phone, course) {
      document.getElementById('editCoordinatorId').value = coid;
      document.getElementById('editCoordinatorName').value = name;
      document.getElementById('editCoordinatorEmail').value = email;
      document.getElementById('editCoordinatorPhone').value = phone;
      // Set course select
      const courseSelect = document.getElementById('editCoordinatorCourse');
      courseSelect.value = getCourseIdByName(course);
    }

    // Load data into edit modal for Marks
    function loadMarksEdit(mid, mark, course, stuid, status, year) {
      document.getElementById('editMarksId').value = mid;
      document.getElementById('editMark').value = mark;

      // Set course select
      const courseSelect = document.getElementById('editMarkCourse');
      courseSelect.value = getCourseIdByName(course);

      // Set student ID select
      const stuidSelect = document.getElementById('editMarksStuid');
      stuidSelect.value = stuid;

      // Set status select
      document.getElementById('editMarkStatus').value = status;

      // Set year input
      document.getElementById('editMarkYear').value = year;
    }

    // Helper function to get course ID by name
    function getCourseIdByName(name) {
      const courses = <?php echo json_encode(array_column($courses, 'cid', 'name')); ?>;
      return courses[name] || '';
    }

    // Confirm Delete
    function confirmDelete(type, id) {
      if (type === 'student') {
        document.getElementById('deleteStudentId').value = id;
        document.getElementById('deleteStudentName').textContent = id;
      } else if (type === 'course') {
        document.getElementById('deleteCourseId').value = id;
        document.getElementById('deleteCourseName').textContent = id;
      } else if (type === 'coordinator') {
        document.getElementById('deleteCoordinatorId').value = id;
        document.getElementById('deleteCoordinatorName').textContent = id;
      } else if (type === 'mark') {
        document.getElementById('deleteMarksId').value = id;
        document.getElementById('deleteMarksName').textContent = id;
      }
    }

    // Update Time/Date in top bar
    function updateTimeDate() {
      const now = new Date();
      const dateStr = now.toLocaleDateString();
      const timeStr = now.toLocaleTimeString();
      document.getElementById('timeDateDisplay').textContent = dateStr + ' ' + timeStr;
    }
    setInterval(updateTimeDate, 1000); // update every second
    updateTimeDate(); // initial call

    // Implement search functionality
    document.getElementById('searchStudent').addEventListener('input', function() {
      const query = this.value.toLowerCase();
      const table = document.getElementById('studentTable');
      const rows = table.getElementsByTagName('tr');
      for (let i = 1; i < rows.length; i++) { // start from 1 to skip header
        const stuid = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
        const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        if (stuid.includes(query) || name.includes(query)) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    });

    document.getElementById('searchCourse').addEventListener('input', function() {
      const query = this.value.toLowerCase();
      const table = document.getElementById('courseTable');
      const rows = table.getElementsByTagName('tr');
      for (let i = 1; i < rows.length; i++) { // start from 1 to skip header
        const cid = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
        const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        if (cid.includes(query) || name.includes(query)) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    });

    document.getElementById('searchCoordinator').addEventListener('input', function() {
      const query = this.value.toLowerCase();
      const table = document.getElementById('coordinatorTable');
      const rows = table.getElementsByTagName('tr');
      for (let i = 1; i < rows.length; i++) { // start from 1 to skip header
        const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        const email = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
        if (name.includes(query) || email.includes(query)) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    });

    document.getElementById('searchMarks').addEventListener('input', function() {
      const query = this.value.toLowerCase();
      const table = document.getElementById('marksTable');
      const rows = table.getElementsByTagName('tr');
      for (let i = 1; i < rows.length; i++) { // start from 1 to skip header
        const mid = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
        if (mid.includes(query)) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    });

    // Initialize Datepickers if needed
    document.addEventListener('DOMContentLoaded', function() {
      // Example: initialize datepicker on year fields
      $('input[type="number"]').attr('min', '1900').attr('max', '2100');
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      $('.datepicker-year').datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true
      });
    });

    function confirmReport(stuid) {
      if (confirm("Are you sure you want to generate a report for Student ID: " + stuid + "?")) {
        window.location.href = "generate_student_report.php?stuid=" + encodeURIComponent(stuid);
      }
    }
  </script>

</body>

</html>