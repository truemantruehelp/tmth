<?php
// application.php - Simple Donation Application Form
require_once 'includes/config.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$successMessage = '';
$errorMessage = '';
$uploadErrors = [];

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed. Please try again later.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $age = isset($_POST['age']) ? trim($_POST['age']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $ref_name = isset($_POST['ref_name']) ? trim($_POST['ref_name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $fund_purpose = isset($_POST['fund_purpose']) ? trim($_POST['fund_purpose']) : '';
    $story = isset($_POST['story']) ? trim($_POST['story']) : '';
    $urgent = isset($_POST['urgent']) ? 1 : 0;
    
    // Validate mobile number (only required field)
    if (empty($mobile)) {
        $errorMessage = 'Mobile number is required.';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $mobile)) {
        $errorMessage = 'Please enter a valid 11-digit mobile number.';
    } else {
        // Handle file uploads (optional)
        $uploads = [];
        $uploadDir = 'assets/images/application/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // File upload handling
        $fileFields = [
            'id_card' => 'National ID Card',
            'photo' => 'Recent Photo',
            'proof' => 'Medical/Other Proof'
        ];
        
        foreach ($fileFields as $field => $fieldName) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $fileName = $_FILES[$field]['name'];
                $fileTmp = $_FILES[$field]['tmp_name'];
                $fileSize = $_FILES[$field]['size'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Allowed file types
                $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
                
                // Check file type
                if (!in_array($fileExt, $allowedTypes)) {
                    $uploadErrors[] = "$fieldName must be JPG, PNG, PDF, or DOC format.";
                    continue;
                }
                
                // Check file size (max 5MB)
                if ($fileSize > 5 * 1024 * 1024) {
                    $uploadErrors[] = "$fieldName is too large. Maximum 5MB.";
                    continue;
                }
                
                // Generate unique filename
                $newFileName = time() . '_' . preg_replace('/[^0-9]/', '', $mobile) . '_' . $field . '.' . $fileExt;
                $uploadPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    $uploads[$field] = $newFileName;
                } else {
                    $uploadErrors[] = "Failed to upload $fieldName.";
                }
            }
        }
        
        // If no upload errors, save to database
        if (empty($uploadErrors)) {
            try {
                // Prepare SQL statement
               $sql = "INSERT INTO applications (name, age, address, mobile, fund_purpose, story, urgent, id_card, photo, proof, ref_name, status, application_date) 
        VALUES (:name, :age, :address, :mobile, :fund_purpose, :story, :urgent, :id_card, :photo, :proof, :ref_name, 'pending', NOW())";
                
                $stmt = $pdo->prepare($sql);
                
                // Bind values using bindValue() instead of bindParam()
                $stmt->bindValue(':name', $name ?: null);
                $stmt->bindValue(':age', $age ? (int)$age : null, PDO::PARAM_INT);
                $stmt->bindValue(':address', $address ?: null);
                $stmt->bindValue(':mobile', $mobile);
                $stmt->bindValue(':ref_name', $ref_name ?: null);
                $stmt->bindValue(':fund_purpose', $fund_purpose ?: null);
                $stmt->bindValue(':story', $story ?: null);
                $stmt->bindValue(':urgent', $urgent, PDO::PARAM_INT);
                $stmt->bindValue(':id_card', $uploads['id_card'] ?? null);
                $stmt->bindValue(':photo', $uploads['photo'] ?? null);
                $stmt->bindValue(':proof', $uploads['proof'] ?? null);
                
                // Execute
                if ($stmt->execute()) {
                    $successMessage = '✅ Application submitted successfully! We will call you on ' . htmlspecialchars($mobile) . ' within 5 working days.';
                    // Clear form
                    $_POST = [];
                } else {
                    $errorMessage = 'Error submitting application. Please try again.';
                }
                
            } catch(PDOException $e) {
                $errorMessage = 'Database error: ' . $e->getMessage();
                error_log("Application Error: " . $e->getMessage());
            }
        }
    }
}

$page_title = 'Apply for Financial Help | TMTH';
include 'includes/header.php';
?>

<!-- Mobile-Friendly Header -->
<div class="bg-primary text-white py-3">
    <div class="container">
        <h1 class="h4 mb-0 fw-bold">Apply for Financial Help</h1>
        <p class="small mb-0 opacity-90">Simple & Quick Application</p>
    </div>
</div>

<div class="container my-4">
    <!-- Progress Steps -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-1" 
                     style="width: 36px; height: 36px; font-size: 14px;">
                    1
                </div>
                <div class="small">Fill Form</div>
            </div>
            <div class="flex-grow-1 mx-2">
                <div class="progress" style="height: 2px;">
                    <div class="progress-bar" style="width: 33%"></div>
                </div>
            </div>
            <div class="text-center">
                <div class="bg-light text-muted rounded-circle d-inline-flex align-items-center justify-content-center mb-1" 
                     style="width: 36px; height: 36px; font-size: 14px;">
                    2
                </div>
                <div class="small">We Call</div>
            </div>
            <div class="flex-grow-1 mx-2">
                <div class="progress bg-light" style="height: 2px;"></div>
            </div>
            <div class="text-center">
                <div class="bg-light text-muted rounded-circle d-inline-flex align-items-center justify-content-center mb-1" 
                     style="width: 36px; height: 36px; font-size: 14px;">
                    3
                </div>
                <div class="small">Get Help</div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-lg me-3 text-success"></i>
                <div><?php echo $successMessage; ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-lg me-3 text-danger"></i>
                <div><?php echo $errorMessage; ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($uploadErrors)): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-lg me-3 text-warning"></i>
                <div>
                    <strong>Upload Errors:</strong>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($uploadErrors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Application Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="POST" action="" enctype="multipart/form-data" id="applicationForm">
                
                <!-- Basic Information -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-primary">
                        <i class="fas fa-user me-2"></i>Basic Information
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Your Name</label>
                        <input type="text" class="form-control" name="name" 
                               placeholder="Enter your full name"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Age</label>
                            <input type="number" class="form-control" name="age" 
                                   placeholder="Your age"
                                   value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control" name="mobile" 
                                   placeholder="017XXXXXXXX"
                                   value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>"
                                   required>
                            <div class="form-text small">We'll call this number</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" rows="2" 
                                  placeholder="Village, District, Division"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12 mb-3">
    <label class="form-label fw-bold">Reference Person (if any)</label>
    <input type="text" class="form-control" name="ref_name" 
           placeholder="Name of TMTH member who referred you (if any)"
           value="<?php echo htmlspecialchars($_POST['ref_name'] ?? ''); ?>">
    <div class="form-text small">Optional - If someone referred you to TMTH</div>
</div>
                </div>
                
                <!-- Help Needed -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-primary">
                        <i class="fas fa-hands-helping me-2"></i>What Help Do You Need?
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Purpose of Help</label>
                        <select class="form-select" name="fund_purpose">
                            <option value="">Select reason...</option>
                            <option value="medical" <?php echo ($_POST['fund_purpose'] ?? '') == 'medical' ? 'selected' : ''; ?>>Medical Treatment</option>
                            <option value="education" <?php echo ($_POST['fund_purpose'] ?? '') == 'education' ? 'selected' : ''; ?>>Education Fees</option>
                            <option value="emergency" <?php echo ($_POST['fund_purpose'] ?? '') == 'emergency' ? 'selected' : ''; ?>>Family Emergency</option>
                            <option value="food" <?php echo ($_POST['fund_purpose'] ?? '') == 'food' ? 'selected' : ''; ?>>Food & Basic Needs</option>
                            <option value="other" <?php echo ($_POST['fund_purpose'] ?? '') == 'other' ? 'selected' : ''; ?>>Other Help Needed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Explain Your Situation</label>
                        <textarea class="form-control" name="story" rows="3" 
                                  placeholder="Briefly tell us why you need help..."><?php echo htmlspecialchars($_POST['story'] ?? ''); ?></textarea>
                        <div class="form-text small">Short description is enough</div>
                    </div>
                </div>
                
                <!-- Optional Documents -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-primary">
                        <i class="fas fa-file-upload me-2"></i>Optional Documents
                    </h6>
                    <p class="small text-muted mb-3">Upload if available (not required)</p>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-id-card text-primary me-2"></i>
                            National ID Card
                        </label>
                        <input type="file" class="form-control" name="id_card" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-camera text-primary me-2"></i>
                            Your Photo
                        </label>
                        <input type="file" class="form-control" name="photo" accept=".jpg,.jpeg,.png">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-file-medical text-primary me-2"></i>
                            Medical/Other Proof
                        </label>
                        <input type="file" class="form-control" name="proof" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text small">Medical reports, bills, or any proof</div>
                    </div>
                </div>
                
                <!-- Urgent Help -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="urgent" id="urgent" value="1"
                               <?php echo isset($_POST['urgent']) ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold text-danger" for="urgent">
                            <i class="fas fa-exclamation-triangle me-1"></i> I need urgent help
                        </label>
                    </div>
                </div>
                
                <!-- Terms -->
                <div class="alert alert-light border mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label small" for="terms">
                            I agree that the information I provided is correct. I understand this is a request for help and approval depends on available funds.
                        </label>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i>SUBMIT APPLICATION
                    </button>
                </div>
                
                <!-- Privacy Note -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-lock me-1"></i> Your information is safe with us
                    </small>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Help Section -->
    <div class="card border-0 bg-light mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-question-circle text-primary me-2"></i>Need Help?
            </h6>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-headset fa-2x text-primary"></i>
                </div>
                <div>
                    <p class="mb-1 small">Call our helpline for assistance:</p>
                    <a href="tel:+8801234567890" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-phone me-1"></i>
Call Us
+880 1859-135478
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('applicationForm');
    const mobileInput = form.querySelector('input[name="mobile"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Mobile number validation
    mobileInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        // Validate mobile number
        if (mobileInput.value.length !== 11) {
            e.preventDefault();
            alert('Please enter a valid 11-digit mobile number.');
            mobileInput.focus();
            return false;
        }
        
        // Validate terms agreement
        const termsCheckbox = form.querySelector('#terms');
        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert('Please agree to the terms.');
            termsCheckbox.focus();
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        
        return true;
    });
});
</script>

<!-- Simple CSS -->
<style>
body {
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin-top: 70px;
}

.form-label {
    font-weight: 600 !important;
    color: #333;
    font-size: 15px;
    margin-bottom: 5px;
}

.form-control, .form-select {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 15px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
}

.form-control::placeholder, .form-select option:first-child {
    color: #888;
    font-weight: 400;
}

.form-control:focus, .form-select:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    outline: none;
}

.btn-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    border: none;
    font-weight: 600;
    border-radius: 8px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #3a0ca3, #4361ee);
}

.card {
    border-radius: 10px;
    border: none;
}

.alert {
    border-radius: 8px;
    border: none;
}

/* Mobile optimizations */
@media (max-width: 576px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .btn-lg {
        font-size: 18px;
        padding: 15px;
    }
}
</style>

<?php 
// Check if database table exists
try {
    $checkTable = $pdo->query("SELECT 1 FROM applications LIMIT 1");
} catch(PDOException $e) {
    // Table doesn't exist
    echo '<div class="container text-center py-5">
        <div class="alert alert-danger">
            <h5>⚠️ Database Setup Required</h5>
            <p>The applications table does not exist. Please run this SQL in your database:</p>
            <pre class="text-start bg-dark text-white p-3 rounded">
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `fund_purpose` varchar(50) DEFAULT NULL,
  `story` text DEFAULT NULL,
  `urgent` tinyint(1) DEFAULT 0,
  `id_card` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `status` enum("pending","approved","rejected") DEFAULT "pending",
  `notes` text DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>
            <a href="admin/applications.php" class="btn btn-primary mt-3">Go to Admin Panel</a>
        </div>
    </div>';
}
?>

<?php include 'includes/footer.php'; ?>