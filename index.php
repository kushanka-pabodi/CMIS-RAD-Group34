<?php
// index.php
session_start();

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Autoload PHPMailer classes (ensure Composer's autoload is used if available)
// If you're not using Composer, ensure the paths below are correct
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --------------------------------------------------------------------
// Function to Display Messages
// --------------------------------------------------------------------
function displayMessages() {
    // Success message
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8');
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success_message']);
    }

    // Error message from URL
    if (isset($_GET['error']) && !empty($_GET['error'])) {
        echo '<div class="alert alert-danger" role="alert">'
           . htmlspecialchars($_GET['error']) .
           '</div>';
    }

    // General error message
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
           . htmlspecialchars($_SESSION['error_message']) .
           '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
           . '</div>';
        unset($_SESSION['error_message']);
    }
}

// --------------------------------------------------------------------
// Handle Contact Form Submission
// --------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: index.php');
        exit;
    }

    // Retrieve and sanitize form inputs
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    // Initialize an array to hold errors
    $errors = [];

    // Validate Name
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    // Validate Email
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate Subject
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }

    // Validate Message
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    // If there are validation errors, store them in the session and redirect
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode(' ', $errors);
        header('Location: index.php');
        exit;
    }

    // Prepare PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kushanka55@gmail.com';      // Your full Gmail address
        $mail->Password   = 'wwwt deef zisv wpyv';             // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient settings
        $mail->setFrom('kushanka55@gmail.com', 'Website Contact Form');
        // This is the address you want to receive the messages at:
        $mail->addAddress('kushanka55@gmail.com', 'Admin/You');

        // Optional: let the "Reply-To" be the visitor's email so you can reply directly
        $mail->addReplyTo($email, $name);

        // Email content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = "Name: $name\nEmail: $email\n\nMessage:\n$message\n";

        // Send mail
        $mail->send();

        // Store success message and redirect to reload the page
        $_SESSION['success_message'] = 'Your message has been sent successfully!';
        header('Location: index.php');
        exit; // Stop further execution

    } catch (Exception $e) {
        // Store error message and redirect
        $_SESSION['error_message'] = 'Failed to send email. Error: ' . $mail->ErrorInfo;
        header('Location: index.php');
        exit;
    }
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="Course Management System" />
  <meta name="author" content="TemplateMo" />
  <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet" />
  <title>Course Management System</title>
  <!-- Bootstrap core CSS -->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Additional CSS Files -->
  <link rel="stylesheet" href="assets/css/fontawesome.css" />
  <link rel="stylesheet" href="assets/css/templatemo-edu-meeting.css" />
  <link rel="stylesheet" href="assets/css/owl.css" />
  <link rel="stylesheet" href="assets/css/lightbox.css" />
  <link rel="stylesheet" href="assets/css/flex-slider.css" />
  <style>
      /* Disable form submission on Enter key press to prevent accidental submissions */
      form {
          user-select: none;
      }
      form input, form textarea, form select {
          user-select: text;
      }
  </style>
</head>

<body>
  <!-- Sub Header -->
  <div class="sub-header">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 col-sm-4">
          <div class="right-icons">
            <ul>
              <li><a href="#"><i class="fa fa-facebook"></i></a></li>
              <li><a href="#"><i class="fa fa-twitter"></i></a></li>
              <li><a href="#"><i class="fa fa-behance"></i></a></li>
              <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
            </ul>
          </div>
        </div>
        <!-- Add Logout Button Here if Needed -->
      </div>
    </div>
  </div>

  <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <nav class="main-nav">
            <!-- ***** Logo Start ***** -->
            <a href="index1.html" class="logo">MENU</a>
            <!-- ***** Logo End ***** -->

            <!-- ***** Menu Start ***** -->
            <ul class="nav">
              <li class="scroll-to-section"><a href="#top" class="active">Home</a></li>
              <li><a href="admin.php?redirect=academic">Administration</a></li>
              <li class="scroll-to-section"><a href="#apply">Apply Now</a></li>
              <li><a href="admin.php?redirect=payment">Payment</a></li>
              <li class="scroll-to-section"><a href="#courses">Courses</a></li>
              <li class="scroll-to-section"><a href="#contact">Contact Us</a></li>
              <!-- Add Logout Button Here if Needed -->
            </ul>
            <a class='menu-trigger'><span>Menu</span></a>
            <!-- ***** Menu End ***** -->
          </nav>
        </div>
      </div>
    </div>
  </header>
  <!-- ***** Header Area End ***** -->

  <!-- ***** Main Banner Area Start ***** -->
  <section class="section main-banner" id="top" data-section="section1">
    <video autoplay muted loop id="bg-video">
      <source src="assets/images/uniVideo.mp4" type="video/mp4" />
    </video>
    <div class="video-overlay header-text">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="caption">
              <h2>Welcome to Our Website</h2>
              <p>
                We are committed to fostering academic excellence, innovation, and personal growth. 
                With a vibrant campus community, world-class faculty, and cutting-edge resources, 
                we empower students to achieve their goals and make a meaningful impact on the world.
                <br />
                Explore our programs, discover your passion, and join a legacy of leaders and changemakers. 
                Your journey to success starts here!
              </p>
              <div class="main-button-red">
                <div class="scroll-to-section"><a href="#contact">Join Us Now!</a></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ***** Main Banner Area End ***** -->

  <!-- ***** Services Section ***** -->
  <section class="services">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="owl-service-item owl-carousel">
            <div class="item">
              <div class="icon">
                <img src="assets/images/service-icon-01.png" alt="" />
              </div>
              <div class="down-content">
                <h4>Best Educational Environment</h4>
                <p>
                  Discover a dynamic and supportive educational environment where cutting-edge 
                  resources, world-class facilities, and vibrant student communities empower 
                  you to achieve your academic and personal aspirations.
                </p>
              </div>
            </div>
            <div class="item">
              <div class="icon">
                <img src="assets/images/service-icon-02.png" alt="" />
              </div>
              <div class="down-content">
                <h4>Best Staff</h4>
                <p>
                  Our university is home to exceptional staff who are dedicated to your success, 
                  combining expertise, mentorship, and a passion for innovation to create an 
                  inspiring and supportive learning experience.
                </p>
              </div>
            </div>
            <div class="item">
              <div class="icon">
                <img src="assets/images/service-icon-03.png" alt="" />
              </div>
              <div class="down-content">
                <h4>Best Graduates</h4>
                <p>
                  Our graduates stand out as leaders and innovators, excelling in their fields 
                  worldwide and embodying the success and excellence cultivated at our university.
                </p>
              </div>
            </div>
            <div class="item">
              <div class="icon">
                <img src="assets/images/service-icon-02.png" alt="" />
              </div>
              <div class="down-content">
                <h4>Best Courses</h4>
                <p>
                  Explore our top-ranked courses, designed with industry experts to combine 
                  cutting-edge knowledge, practical skills, and real-world opportunities, 
                  preparing you for a successful and rewarding career.
                </p>
              </div>
            </div>
            <div class="item">
              <div class="icon">
                <img src="assets/images/service-icon-03.png" alt="" />
              </div>
              <div class="down-content">
                <h4>Best Networking</h4>
                <p>
                  Join a university that connects you to a powerful global network of peers, 
                  alumni, and industry leaders, opening doors to endless opportunities 
                  and lifelong relationships.
                </p>
              </div>
            </div>
            <!-- Add more items as needed -->
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ***** Apply Now Section ***** -->
  <section class="apply-now" id="apply">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 align-self-center">
          <div class="row">
            <div class="col-lg-12">
              <div class="item">
                <h3>APPLY FOR BACHELOR DEGREE</h3>
                <p>Apply now for your Bachelor’s Degree and start your journey toward a brighter future!</p>
                <div class="main-button-red">
                  <div class="scroll-to-section"><a href="#contact">Join Us Now!</a></div>
                </div>
              </div>
            </div>
            <div class="col-lg-12">
              <div class="item">
                <h3>APPLY FOR DIPLOMA PROGRAM</h3>
                <p>Enroll now: Apply for our Diploma Program today and take the first step toward your future!</p>
                <div class="main-button-yellow">
                  <div class="scroll-to-section"><a href="#contact">Join Us Now!</a></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="accordions is-first-expanded">
            <article class="accordion">
              <div class="accordion-head">
                <span>About Our University</span>
                <span class="icon"><i class="icon fa fa-chevron-right"></i></span>
              </div>
              <div class="accordion-body white-section">
                <div class="content">
                  <p>
                    Discover Your Future at Our University!
                    Step into a world of possibilities where dreams turn into achievements. 
                    We are more than a university – we are a thriving community of thinkers, 
                    innovators, and leaders ready to make their mark on the world.
                    <br />
                    <h6>🌟 Why Choose Our University?</h6>
                    <br />
                    <ul>
                      <li><b>Top-Notch Academics:</b> Learn from world-class faculty with cutting-edge facilities.</li>
                      <li><b>Vibrant Campus Life:</b> From student clubs to cultural festivals, there’s always 
                          something exciting happening!</li>
                      <li><b>Global Opportunities:</b> Connect with international programs and networks 
                          that open doors worldwide.</li>
                      <li><b>Career-Ready Skills:</b> Our hands-on learning and industry partnerships 
                          ensure you’re ready for what’s next.</li>
                      <li><b>Inclusive Environment:</b> Join a diverse community where your voice matters 
                          and your ideas shine.</li>
                    </ul>
                  </p>
                </div>
              </div>
            </article>
            <article class="accordion">
              <div class="accordion-head">
                <span>VISION</span>
                <span class="icon"><i class="icon fa fa-chevron-right"></i></span>
              </div>
              <div class="accordion-body">
                <div class="content">
                  <p>
                    To be a leading higher education institute in Sri Lanka recognized for its outstanding 
                    academic programmes, innovative research, scholarship and outreach with the ultimate 
                    target of serving the mankind.
                  </p>
                </div>
              </div>
            </article>
            <article class="accordion">
              <div class="accordion-head">
                <span>MISSION</span>
                <span class="icon"><i class="icon fa fa-chevron-right"></i></span>
              </div>
              <div class="accordion-body">
                <div class="content">
                  <p>
                    To develop highly qualified and responsible citizens
                    who contribute to the improvement of society and 
                    sustainable development of the country.
                  </p>
                </div>
              </div>
            </article>
            <article class="accordion last-accordion">
              <div class="accordion-head">
                <span>OBJECTIVES</span>
                <span class="icon"><i class="icon fa fa-chevron-right"></i></span>
              </div>
              <div class="accordion-body">
                <div class="content">
                  <p>
                    <b>Provide Quality Education:</b><br />
                    Deliver exceptional academic programs to prepare students for success.
                    <br /><br />
                    <b>Foster Research and Innovation:</b><br />
                    Advance knowledge and solve global challenges through groundbreaking research.
                    <br /><br />
                    <b>Promote Personal Growth:</b><br />
                    Support students' intellectual, ethical, and social development.
                    <br /><br />
                    <b>Encourage Community Engagement:</b><br />
                    Build strong connections with communities to drive social and economic progress.
                    <br /><br />
                    <b>Uphold Diversity and Inclusion:</b><br />
                    Create an inclusive environment that values and respects diversity.
                    <br /><br />
                  </p>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- ***** Our Courses Section ***** -->
    <section class="our-courses" id="courses">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="section-heading">
              <h2>Our Popular Courses</h2>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="owl-courses-item owl-carousel">
              <div class="item">
                <img src="assets/images/course-01.jpg" alt="Course One" />
                <div class="down-content">
                  <h4>Diploma In English</h4>
                  <div class="info">
                    <div class="row">
                      <div class="col-8">
                        <ul>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="item">
                <img src="assets/images/course-02.jpg" alt="Course Two" />
                <div class="down-content">
                  <h4>Diploma In Information Systems</h4>
                  <div class="info">
                    <div class="row">
                      <div class="col-8">
                        <ul>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="item">
                <img src="assets/images/course-03.jpg" alt="Course Three" />
                <div class="down-content">
                  <h4>Diploma In Software Engineering</h4>
                  <div class="info">
                    <div class="row">
                      <div class="col-8">
                        <ul>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="item">
                <img src="assets/images/course-04.jpg" alt="Course Four" />
                <div class="down-content">
                  <h4>Diploma In Accounting Information Systems</h4>
                  <div class="info">
                    <div class="row">
                      <div class="col-8">
                        <ul>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                          <li><i class="fa fa-star"></i></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Add more items as needed -->
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ***** Our Facts Section ***** -->
    <section class="our-facts">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div class="row">
              <div class="col-lg-12">
                <h2>A Few Facts About Our University</h2>
              </div>
              <div class="col-lg-6">
                <div class="row">
                  <div class="col-12">
                    <div class="count-area-content percentage">
                      <div class="count-digit">100</div>
                      <div class="count-title">Successful Students</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="count-area-content">
                      <div class="count-digit">155</div>
                      <div class="count-title">Current Instructors</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="row">
                  <div class="col-12">
                    <div class="count-area-content new-students">
                      <div class="count-digit">2000</div>
                      <div class="count-title">New Students</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="count-area-content">
                      <div class="count-digit">50</div>
                      <div class="count-title">Awards</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 align-self-center">
            <div class="video">
              <a href="https://www.youtube.com/watch?v=XcYsV5tsnuo" target="_blank">
                <img src="assets/images/play-icon.png" alt="Play Video" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ***** Contact Us Section ***** -->
    <section class="contact-us" id="contact">
      <div class="container">
        <div class="row">
          <!-- Contact Form -->
          <div class="col-lg-9 align-self-center">
            <div class="row">
              <div class="col-lg-12">
                <?php displayMessages(); ?> <!-- Display messages here -->
                <form id="contact" action="" method="post" onsubmit="return disableSubmit();">
                  <!-- CSRF Token -->
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                  
                  <div class="row">
                    <div class="col-lg-12">
                      <h2>Let's get in touch</h2>
                    </div>

                    <!-- Name Field -->
                    <div class="col-lg-4">
                      <fieldset>
                        <input
                          name="name"
                          type="text"
                          id="name"
                          placeholder="YOUR NAME...*"
                          required
                        />
                      </fieldset>
                    </div>

                    <!-- Email Field -->
                    <div class="col-lg-4">
                      <fieldset>
                        <input
                          name="email"
                          type="email"
                          id="email"
                          placeholder="YOUR EMAIL...*"
                          required
                        />
                      </fieldset>
                    </div>

                    <!-- Subject Field -->
                    <div class="col-lg-4">
                      <fieldset>
                        <input
                          name="subject"
                          type="text"
                          id="subject"
                          placeholder="SUBJECT...*"
                          required
                        />
                      </fieldset>
                    </div>

                    <!-- Message Field -->
                    <div class="col-lg-12">
                      <fieldset>
                        <textarea
                          name="message"
                          class="form-control"
                          id="message"
                          placeholder="YOUR MESSAGE...*"
                          required
                        ></textarea>
                      </fieldset>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-lg-12">
                      <fieldset>
                        <button type="submit" id="form-submit" class="button">
                          SEND MESSAGE NOW
                        </button>
                      </fieldset>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="col-lg-3">
            <div class="right-info">
              <ul>
                <li>
                  <h6>Phone Number</h6>
                  <span>011-111222333</span>
                </li>
                <li>
                  <h6>Email Address</h6>
                  <span>ouruniversity@gmail.com</span>
                </li>
                <li>
                  <h6>Street Address</h6>
                  <span>Our university, Colombo 07, Sri Lanka</span>
                </li>
                <li>
                  <h6>Website URL</h6>
                  <span>www.university.lk</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>
          Copyright © 2024 University.lk
          All Rights Reserved.
          <br />
          Design By: Group 34
        </p>
      </div>
    </section>

    <!-- Scripts -->
    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/isotope.min.js"></script>
    <script src="assets/js/owl-carousel.js"></script>
    <script src="assets/js/lightbox.js"></script>
    <script src="assets/js/tabs.js"></script>
    <script src="assets/js/video.js"></script>
    <script src="assets/js/slick-slider.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
      // According to loftblog tutorial
      $('.nav li:first').addClass('active');

      var showSection = function(section, isAnimate) {
        var direction = section.replace(/#/, ''),
            reqSection = $('.section').filter('[data-section="' + direction + '"]'),
            reqSectionPos = reqSection.offset().top - 0;
        if (isAnimate) {
          $('body, html').animate({ scrollTop: reqSectionPos }, 800);
        } else {
          $('body, html').scrollTop(reqSectionPos);
        }
      };

      var checkSection = function() {
        $('.section').each(function() {
          var $this = $(this),
              topEdge = $this.offset().top - 80,
              bottomEdge = topEdge + $this.height(),
              wScroll = $(window).scrollTop();
          if (topEdge < wScroll && bottomEdge > wScroll) {
            var currentId = $this.data('section'),
                reqLink = $('a').filter('[href*=\\#' + currentId + ']');
            reqLink.closest('li').addClass('active').siblings().removeClass('active');
          }
        });
      };

      $('.main-menu, .responsive-menu, .scroll-to-section').on('click', 'a', function(e) {
        e.preventDefault();
        showSection($(this).attr('href'), true);
      });

      $(window).scroll(function() {
        checkSection();
      });

      // Disable submit button after form submission to prevent duplicate submissions
      function disableSubmit() {
          var submitButton = document.getElementById('form-submit');
          submitButton.disabled = true;
          submitButton.innerText = 'Sending...';
          return true;
      }
    </script>
</body>
</html>
