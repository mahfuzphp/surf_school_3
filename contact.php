<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="mb-4">Contact Us</h1>
            <p class="lead">Have questions? We'd love to hear from you!</p>

            <form action="process-contact.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>

        <div class="col-lg-6">
            <div class="card mt-4 mt-lg-0">
                <div class="card-body">
                    <h5 class="card-title">Our Location</h5>
                    <p class="card-text">
                        <strong>Address:</strong><br>
                        123 Beach Road<br>
                        Surf City, SC 12345
                    </p>

                    <p class="card-text">
                        <strong>Phone:</strong><br>
                        (123) 456-7890
                    </p>

                    <p class="card-text">
                        <strong>Email:</strong><br>
                        info@surfschool.com
                    </p>

                    <h5 class="card-title mt-4">Business Hours</h5>
                    <p class="card-text">
                        Monday - Friday: 8:00 AM - 6:00 PM<br>
                        Saturday: 9:00 AM - 5:00 PM<br>
                        Sunday: 10:00 AM - 4:00 PM
                    </p>
                </div>
            </div>

            <!-- Add a map here if desired -->
            <div class="mt-4" style="width: 100%; height: 300px; background-color: #eee;">
                <!-- Replace with actual map embed code -->
                <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                    <p class="text-muted">Map Goes Here</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>