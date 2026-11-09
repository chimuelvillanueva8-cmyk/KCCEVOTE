<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <title>Register - KEVS (KCC e-Voting System)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #0B2748;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #ecf0f1;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .register-card {
            background: rgba(31, 60, 90, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo {
            width: 70px;
            height: 70px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 0 10px rgba(252, 211, 77, 0.3));
        }
        .title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #FCD34D;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(252, 211, 77, 0.2);
        }
        .subtitle {
            color: #b8c5d6;
            font-size: 0.95rem;
        }
        .progress-section {
            margin-bottom: 2rem;
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }
        .step:hover:not(.active) {
            opacity: 0.8;
        }
        .step.completed:hover .step-circle {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(81, 207, 102, 0.5);
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: #b8c5d6;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .step.active .step-circle {
            background: #FCD34D;
            color: #0f1f35;
            border-color: #FCD34D;
            box-shadow: 0 0 15px rgba(252, 211, 77, 0.3);
        }
        .step.completed .step-circle {
            background: #51cf66;
            color: #fff;
            border-color: #51cf66;
        }
        .step-label {
            font-size: 0.8rem;
            text-align: center;
            color: #b8c5d6;
            transition: color 0.3s ease;
        }
        .step.active .step-label {
            color: #FCD34D;
            font-weight: 600;
        }
        .step.completed .step-label {
            color: #51cf66;
        }
        .step-content {
            display: none;
        }
        .step-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .form-label {
            color: #ecf0f1;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            background: rgba(15, 31, 53, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ecf0f1;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(15, 31, 53, 0.95);
            border-color: #FCD34D;
            color: #ecf0f1;
            box-shadow: 0 0 0 0.2rem rgba(252, 211, 77, 0.15);
        }
        .form-control::placeholder {
            color: #8b95a5;
        }
        .form-select option {
            background: #1f3c5a;
            color: #ecf0f1;
        }
        .btn {
            box-sizing: border-box;
            min-height: 44px;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            line-height: 1.25;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            user-select: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn > i,
        .btn > svg {
            flex: 0 0 auto;
            line-height: 1;
        }
        .btn-primary {
            background: #FCD34D;
            color: #0f1f35;
        }
        .btn-primary:hover:not(:disabled) {
            background: #f5c842;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(252, 211, 77, 0.2);
        }
        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 5px 10px rgba(252, 211, 77, 0.2);
        }
        .btn-secondary {
            background: #6b7280;
            color: #fff;
        }
        .btn-secondary:hover:not(:disabled) {
            background: #4b5563;
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .button-group button {
            flex: 1;
            min-width: 0;
        }
        @media (max-width: 480px) {
            .button-group {
                flex-direction: column;
                gap: 0.75rem;
            }
            .button-group .btn {
                width: 100%;
            }
        }
        .alert {
            border-radius: 8px;
            border: 1px solid;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border-color: rgba(40, 167, 69, 0.3);
            color: #51cf66;
        }
        .alert-info {
            background: rgba(79, 195, 247, 0.15);
            border-color: rgba(79, 195, 247, 0.3);
            color: #4fc3f7;
        }
        .invalid-feedback {
            color: #ff6b6b;
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .mb-3 {
            margin-bottom: 1.5rem;
        }
        .mt-2 {
            margin-top: 0.5rem;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #8b95a5;
        }
        .camera-container {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        #video {
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            background: #000;
            display: none;
            transform: scaleX(-1);
        }
        .camera-placeholder {
            width: 100%;
            height: 300px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8b95a5;
            background: rgba(15, 31, 53, 0.5);
        }
        .photo-preview {
            margin-top: 1rem;
            text-align: center;
        }
        .photo-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem;
            background: rgba(15, 31, 53, 0.8);
        }
        .status-message {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background: rgba(79, 195, 247, 0.15);
            color: #4fc3f7;
            font-size: 0.9rem;
        }
        .success-section {
            text-align: center;
        }
        .success-icon {
            font-size: 3rem;
            color: #51cf66;
            margin-bottom: 1rem;
        }
        .login-link {
            color: #2D4A73;
            text-decoration: none;
        }
        .login-link:hover {
            text-decoration: underline;
        }
        #startCameraBtn {
            background-color: #FCD34D !important;
            color: #0f1f35 !important;
        }
    </style>
    <!-- Load face-api.js from CDN for reliability -->
    <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.10/dist/face-api.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <!-- Header -->
            <div class="logo-section">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <img src="<?= base_url('images/kcclogo.png') ?>" alt="KEVS Logo" style="width: 70px; height: 70px; display: inline-block; filter: drop-shadow(0 4px 12px rgba(252, 211, 77, 0.2));">
                </div>
                <h1 class="title" style="font-size: 2.2rem; margin-bottom: 0.3rem;">KEVS</h1>
                <p class="subtitle" style="font-size: 0.85rem;">Student Registration</p>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-section">
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Info</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">ID Photo</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Face</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Verify</div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>

            <!-- STEP 1: Student Information -->
            <div class="step-content active" data-step="1">
                <h3 style="color: #FCD34D; margin-bottom: 1.5rem;">Step 1: Your Information</h3>
                
                <div class="form-row">
                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-control" id="student_id" placeholder="e.g., STU001" required>
                        <div class="invalid-feedback" id="err_student_id"></div>
                        <div id="studentIdStatus" style="font-size: 0.85rem; margin-top: 0.5rem; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" placeholder="Your full name" required>
                        <div class="invalid-feedback" id="err_full_name"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" placeholder="Login username" required>
                        <div class="invalid-feedback" id="err_username"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" placeholder="e.g., Computer Science" required>
                        <div class="invalid-feedback" id="err_department"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <input type="text" class="form-control" id="course" placeholder="e.g., BSCS" required>
                        <div class="invalid-feedback" id="err_course"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year Level</label>
                        <input type="number" class="form-control" id="year_level" min="1" max="4" placeholder="1, 2, 3, or 4" required>
                        <div class="invalid-feedback" id="err_year_level"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <input type="text" class="form-control" id="section" placeholder="e.g., A" required>
                        <div class="invalid-feedback" id="err_section"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" class="form-control" id="school_year" placeholder="e.g., 2024-2025" required>
                        <div class="invalid-feedback" id="err_school_year"></div>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="At least 6 characters" required>
                        <div class="invalid-feedback" id="err_password"></div>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" placeholder="Re-enter password" required>
                        <div class="invalid-feedback" id="err_confirm_password"></div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="goToLogin()">Back to Login</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next</button>
                </div>
            </div>

            <!-- STEP 2: ID Photo -->
            <div class="step-content" data-step="2">
                <h3 style="color: #FCD34D; margin-bottom: 1.5rem;">Step 2: Upload ID Photo</h3>
                
                <p class="text-muted" style="margin-bottom: 1.5rem;">
                    <i class="bi bi-info-circle"></i> Upload a clear photo of your ID card showing your face
                </p>

                <div class="mb-3">
                    <label class="form-label">ID Photo</label>
                    <input type="file" class="form-control" id="id_photo" accept="image/jpeg,image/png" required onchange="handleIdPhotoChange(event)">
                    <div class="invalid-feedback" id="err_id_photo"></div>
                </div>

                <div id="idPhotoPreview" class="photo-preview" style="display: none;">
                    <img id="idPhotoImg" alt="ID Photo Preview">
                </div>

                <div id="photoStatus" class="status-message" style="display: none;"></div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)">Back</button>
                    <button type="button" class="btn btn-primary" id="nextStep2Btn" disabled onclick="goToStep(3)">Next</button>
                </div>
            </div>

            <!-- STEP 3: Face Capture -->
            <div class="step-content" data-step="3">
                <h3 style="color: #FCD34D; margin-bottom: 1.5rem;">Step 3: Capture Your Face</h3>
                
                <p class="text-muted" style="margin-bottom: 1.5rem;">
                    <i class="bi bi-camera-video"></i> Use your webcam to capture your face
                </p>

                <div style="background: rgba(79, 195, 247, 0.15); border-left: 4px solid #4fc3f7; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9rem; color: #2D4A73;">
                    <strong>� Face Capture Instructions:</strong><br>
                    • Click "Start Camera" to begin<br>
                    • Position your face clearly in the camera<br>
                    • Click "Capture Face" when ready<br>
                    • Ensure good lighting on your face<br>
                    • Make sure your entire face is visible
                </div>

                <div class="camera-container">
                    <div class="camera-placeholder" id="cameraPlaceholder">
                        <div>
                            <p><i class="bi bi-camera" style="font-size: 2rem; color: #FCD34D;"></i></p>
                            <p>Camera will appear here</p>
                        </div>
                    </div>
                    <video id="video" autoplay playsinline muted></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                </div>

                <div id="cameraStatus" class="status-message" style="display: none;"></div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" id="startCameraBtn" onclick="startCamera()">Start Camera</button>
                    <button type="button" class="btn btn-primary" id="captureFaceBtn" disabled onclick="captureFace()" style="display: none;">Capture Face</button>
                </div>

                <div id="capturedFacePreview" class="photo-preview" style="display: none;">
                    <img id="capturedFaceImg" alt="Captured Face">
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">Back</button>
                    <button type="button" class="btn btn-primary" id="nextStep3Btn" disabled onclick="goToStep(4)">Next</button>
                </div>
            </div>

            <!-- STEP 4: Face Verification -->
            <div class="step-content" data-step="4">
                <h3 style="color: #FCD34D; margin-bottom: 1.5rem;">Step 4: Verify & Complete</h3>
                
                <div id="verificationResult" style="margin-bottom: 1.5rem;"></div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">Back</button>
                    <button type="button" class="btn btn-secondary" id="editPhotosBtn" onclick="editPhotos()" style="display: none; flex: 0.5;">Retake Photos</button>
                    <button type="button" class="btn btn-primary" id="submitBtn" disabled>Complete Registration</button>
                </div>
            </div>

            <!-- Success Message -->
            <div id="successMessage" style="display: none;">
                <div class="success-section">
                    <div class="success-icon">✓</div>
                    <h3 style="color: #51cf66; margin-bottom: 1rem;">Registration Successful!</h3>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">
                        Your account has been created. You can now login to start voting.
                    </p>
                    <a href="<?= base_url('login') ?>" class="btn btn-primary">Go to Login</a>
                </div>
            </div>

            <!-- Login Link -->
            <div style="text-align: center; margin-top: 2rem;">
                <p class="text-muted">
                    Already have an account? 
                    <a href="<?= base_url('login') ?>" class="login-link">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '<?= base_url() ?>';
        let currentStep = 1;
        let idPhotoImage = null;
        let capturedFaceImage = null;
        let videoStream = null;
        let modelsLoaded = false;
        let idPhotoDescriptor = null;
        let capturedFaceDescriptor = null;

        // Blink detection variables
        let blinkCount = 0;
        let eyeClosedFrames = 0;
        let eyeOpenThreshold = 0.30;  // More lenient
        let eyeClosedThreshold = 0.15;  // Lower threshold for closed eyes
        let consecutiveClosedFramesRequired = 2;  // Fewer frames for blink
        const requiredBlinks = 1;
        let isDetectingBlink = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', async function() {
            // Start loading face-api models immediately
            loadFaceApiModels();
            
            // Add Student ID validation listener
            document.getElementById('student_id').addEventListener('change', function() {
                const studentId = this.value.trim();
                if (studentId) {
                    checkStudentIdAvailability(studentId);
                }
            });

            // Add click handlers to step indicators for easy navigation
            document.querySelectorAll('.step').forEach(stepEl => {
                stepEl.addEventListener('click', function() {
                    const stepNum = parseInt(this.getAttribute('data-step'));
                    // Allow clicking on completed steps to go back
                    if (stepNum < currentStep) {
                        goToStep(stepNum);
                    } else if (stepNum === currentStep) {
                        // Already on this step, no need to do anything
                        return;
                    } else {
                        // Can't jump ahead, need to complete current step first
                        showError('Please complete current step first');
                    }
                });
            });
        });

        // Check if Student ID is already registered
        async function checkStudentIdAvailability(studentId) {
            const statusDiv = document.getElementById('studentIdStatus');
            const studentIdInput = document.getElementById('student_id');
            
            if (!studentId) {
                statusDiv.style.display = 'none';
                studentIdInput.classList.remove('is-invalid');
                return;
            }

            try {
                const response = await fetch(BASE_URL + 'api/check-student-id', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ student_id: studentId })
                });

                const result = await response.json();

                if (result.exists && result.has_face_registered) {
                    // Account already exists with face registered
                    statusDiv.innerHTML = '❌ This Student ID is already registered. <strong>One account per student is allowed.</strong> <a href="' + BASE_URL + 'login" style="color: #4fc3f7;">Go to Login</a>';
                    statusDiv.style.color = '#ff6b6b';
                    statusDiv.style.display = 'block';
                    studentIdInput.classList.add('is-invalid');
                } else if (result.exists) {
                    // Account exists but no face (shouldn't happen, but handle it)
                    statusDiv.innerHTML = '⚠️ This Student ID already has an account.';
                    statusDiv.style.color = '#ffa726';
                    statusDiv.style.display = 'block';
                    studentIdInput.classList.add('is-invalid');
                } else {
                    // Student ID is available
                    statusDiv.innerHTML = '✓ Student ID is available';
                    statusDiv.style.color = '#51cf66';
                    statusDiv.style.display = 'block';
                    studentIdInput.classList.remove('is-invalid');
                }
            } catch (err) {
                console.error('Error checking student ID:', err);
                // On error, allow user to continue (server will validate)
                statusDiv.style.display = 'none';
            }
        }

        // Load face-api models from CDN (more reliable than local files)
        async function loadFaceApiModels() {
            // Wait for face-api to be available
            let attempts = 0;
            while (typeof faceapi === 'undefined' && attempts < 50) { // Wait up to ~5 seconds
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }
            
            if (typeof faceapi === 'undefined') {
                console.error('face-api.js failed to load');
                modelsLoaded = false;
                return;
            }
            
            // Use CDN URL instead of local models
            const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.10/model/';
            console.log('Loading face-api models from CDN...');
            
            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                modelsLoaded = true;
                console.log('✓ Face API models loaded successfully from CDN');
            } catch (err) {
                console.error('Failed to load models from CDN:', err);
                modelsLoaded = false;
                showError('Failed to load face recognition. Please refresh the page.');
            }
        }

        // Direct face capture without blink detection

        // Navigation
        function goToStep(step) {
            // Only validate current step when moving forward
            if (step > currentStep) {
                if (!validateCurrentStep()) {
                    showError('Please complete current step correctly');
                    return;
                }
            }

            currentStep = step;
            updateUI();
        }

        function goToLogin() {
            // Confirm before leaving
            if (currentStep > 1 && confirm('Are you sure you want to go back to login? Your progress will be lost.')) {
                window.location.href = BASE_URL + 'login';
            } else if (currentStep === 1) {
                window.location.href = BASE_URL + 'login';
            }
        }

        function validateCurrentStep() {
            if (currentStep === 1) {
                const fields = ['student_id', 'full_name', 'username', 'department', 'course', 'year_level', 'section', 'school_year', 'password', 'confirm_password'];
                for (let field of fields) {
                    const value = document.getElementById(field).value.trim();
                    if (!value) {
                        showFieldError(field, 'This field is required');
                        return false;
                    }
                }

                // Validate password
                const pwd = document.getElementById('password').value;
                if (pwd.length < 6) {
                    showFieldError('password', 'Password must be at least 6 characters');
                    return false;
                }

                // Validate password match
                const confirmPwd = document.getElementById('confirm_password').value;
                if (pwd !== confirmPwd) {
                    showFieldError('confirm_password', 'Passwords do not match');
                    return false;
                }

                return true;
            }

            if (currentStep === 2) {
                if (!idPhotoImage) {
                    showError('Please upload an ID photo');
                    return false;
                }
                return true;
            }

            if (currentStep === 3) {
                if (!capturedFaceImage) {
                    showError('Please capture your face');
                    return false;
                }
                return true;
            }

            return true;
        }

        function updateUI() {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));

            // Show current step
            document.querySelector(`.step-content[data-step="${currentStep}"]`).classList.add('active');

            // Update progress indicators
            document.querySelectorAll('.step').forEach(el => {
                const step = parseInt(el.dataset.step);
                el.classList.remove('active', 'completed');
                if (step < currentStep) {
                    el.classList.add('completed');
                } else if (step === currentStep) {
                    el.classList.add('active');
                }
            });

            // Handle step-specific logic
            if (currentStep === 4) {
                verifyFaces();
            }

            window.scrollTo(0, 0);
        }

        // Step 2: ID Photo Handling
        function handleIdPhotoChange(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                showError('Please upload JPG or PNG image');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                idPhotoImage = e.target.result;
                document.getElementById('idPhotoImg').src = idPhotoImage;
                document.getElementById('idPhotoPreview').style.display = 'block';

                // Extract face descriptor from ID photo
                extractIdPhotoFace();
            };
            reader.readAsDataURL(file);
        }

        async function extractIdPhotoFace() {
            const status = document.getElementById('photoStatus');
            status.style.display = 'block';
            document.getElementById('nextStep2Btn').disabled = true;

            try {
                const img = new Image();
                img.src = idPhotoImage;
                await img.decode();

                // Wait for models (max 15 seconds)
                if (!modelsLoaded) {
                    status.textContent = '⏳ Loading face recognition...';
                    status.style.background = 'rgba(255, 152, 0, 0.15)';
                    status.style.color = '#ffa726';
                    
                    let waited = 0;
                    while (!modelsLoaded && waited < 15000) {
                        await new Promise(resolve => setTimeout(resolve, 500));
                        waited += 500;
                    }
                }

                if (!modelsLoaded) {
                    status.textContent = '✗ Face recognition service unavailable. Check console for details.';
                    status.style.background = 'rgba(255, 107, 107, 0.15)';
                    status.style.color = '#ff6b6b';
                    document.getElementById('nextStep2Btn').disabled = true;
                    console.error('Models failed to load after 15 seconds');
                    return;
                }

                status.textContent = '⏳ Analyzing ID photo...';
                
                // Try with lenient threshold first, then stricter if needed
                let detections = null;
                for (let threshold of [0.3, 0.4, 0.5]) {
                    detections = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({
                        inputSize: 416,
                        scoreThreshold: threshold
                    })).withFaceLandmarks().withFaceDescriptor();
                    
                    if (detections) {
                        console.log('Face detected with threshold:', threshold);
                        break;
                    }
                }

                if (detections) {
                    idPhotoDescriptor = detections.descriptor;
                    status.textContent = '✓ Face detected in ID photo!';
                    status.style.background = 'rgba(81, 207, 102, 0.15)';
                    status.style.color = '#51cf66';
                    document.getElementById('nextStep2Btn').disabled = false;
                } else {
                    status.textContent = '✗ No face detected. Try:\n• Better lighting\n• Clear ID photo\n• Face directly visible';
                    status.style.background = 'rgba(255, 107, 107, 0.15)';
                    status.style.color = '#ff6b6b';
                    document.getElementById('nextStep2Btn').disabled = true;
                }
            } catch (err) {
                console.error('Error analyzing photo:', err);
                status.textContent = 'Error analyzing photo. Try another image.';
                status.style.background = 'rgba(255, 107, 107, 0.15)';
                status.style.color = '#ff6b6b';
                document.getElementById('nextStep2Btn').disabled = true;
            }
        }

        // Step 3: Camera & Face Capture
        async function startCamera() {
            const btn = document.getElementById('startCameraBtn');
            const captureBtn = document.getElementById('captureFaceBtn');
            btn.disabled = true;
            btn.textContent = 'Starting...';

            try {
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' }
                });

                const video = document.getElementById('video');
                video.srcObject = videoStream;
                video.style.display = 'block';
                
                // Show and ENABLE capture button immediately
                captureBtn.style.display = 'inline-block';
                captureBtn.disabled = false;

                const status = document.getElementById('cameraStatus');
                status.textContent = '✓ Camera ready. Click "Capture Face" when ready.';
                status.style.display = 'block';
                status.style.background = 'rgba(81, 207, 102, 0.15)';
                status.style.color = '#51cf66';

                document.getElementById('cameraPlaceholder').style.display = 'none';
                btn.textContent = 'Camera On';
                btn.style.opacity = '0.7';

                console.log('✓ Camera started successfully');
            } catch (err) {
                console.error('Camera error:', err);
                showError('Camera access denied or not available. Please check permissions.');
                btn.disabled = false;
                btn.textContent = 'Start Camera';
                btn.style.opacity = '1';
                captureBtn.disabled = true;
                captureBtn.style.display = 'none';
            }
        }

        async function captureFace() {
            const btn = document.getElementById('captureFaceBtn');
            const status = document.getElementById('cameraStatus');
            btn.disabled = true;
            btn.textContent = 'Capturing...';
            status.textContent = '⏳ Capturing your face...';
            status.style.display = 'block';
            status.style.background = 'rgba(255, 152, 0, 0.15)';
            status.style.color = '#ffa726';

            try {
                const video = document.getElementById('video');
                
                // Verify video is streaming
                if (!video.srcObject || !videoStream) {
                    showError('Camera is not streaming. Click "Start Camera" again.');
                    btn.disabled = false;
                    btn.textContent = 'Capture Face';
                    return;
                }

                // Capture the frame directly
                const canvas = document.getElementById('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);

                capturedFaceImage = canvas.toDataURL('image/jpeg');
                document.getElementById('capturedFaceImg').src = capturedFaceImage;
                document.getElementById('capturedFacePreview').style.display = 'block';

                // Update button text to show success
                btn.textContent = 'Captured Face';
                
                // Extract face descriptor
                extractCapturedFace();

                // Stop camera
                videoStream.getTracks().forEach(track => track.stop());
                video.style.display = 'none';
                document.getElementById('cameraPlaceholder').style.display = 'flex';
                document.getElementById('startCameraBtn').textContent = 'Start Camera';
                document.getElementById('startCameraBtn').disabled = false;
                document.getElementById('captureFaceBtn').style.display = 'none';

            } catch (err) {
                console.error('Capture error:', err);
                showError('Failed to capture face. Try again.');
                btn.disabled = false;
                btn.textContent = 'Capture Face';
            }
        }

        async function extractCapturedFace() {
            const status = document.getElementById('cameraStatus');
            status.textContent = '⏳ Analyzing captured face...';
            status.style.display = 'block';

            try {
                const img = new Image();
                img.src = capturedFaceImage;
                await img.decode();

                // Make sure models are ready (max 15 seconds)
                if (!modelsLoaded) {
                    let waited = 0;
                    while (!modelsLoaded && waited < 15000) {
                        await new Promise(resolve => setTimeout(resolve, 500));
                        waited += 500;
                    }
                }

                if (!modelsLoaded) {
                    status.textContent = '✗ Face recognition service unavailable.';
                    status.style.background = 'rgba(255, 107, 107, 0.15)';
                    status.style.color = '#ff6b6b';
                    return;
                }

                // Try with lenient thresholds
                let detections = null;
                for (let threshold of [0.3, 0.4, 0.5]) {
                    detections = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({
                        inputSize: 416,
                        scoreThreshold: threshold
                    })).withFaceLandmarks().withFaceDescriptor();
                    
                    if (detections) {
                        console.log('Face detected in captured image with threshold:', threshold);
                        break;
                    }
                }

                if (detections) {
                    capturedFaceDescriptor = detections.descriptor;
                    status.textContent = '✓ Face captured successfully!';
                    status.style.background = 'rgba(81, 207, 102, 0.15)';
                    status.style.color = '#51cf66';
                    document.getElementById('nextStep3Btn').disabled = false;
                } else {
                    status.textContent = '✗ Face not clear.\n• Try better lighting\n• Ensure face fills camera\n• Capture again';
                    status.style.background = 'rgba(255, 107, 107, 0.15)';
                    status.style.color = '#ff6b6b';
                    capturedFaceImage = null;
                }
            } catch (err) {
                console.error('Error analyzing face:', err);
                status.textContent = 'Error analyzing face. Try again.';
                status.style.background = 'rgba(255, 107, 107, 0.15)';
                status.style.color = '#ff6b6b';
            }
        }

        // Step 4: Face Verification
        async function verifyFaces() {
            const resultDiv = document.getElementById('verificationResult');

            if (!idPhotoDescriptor || !capturedFaceDescriptor) {
                resultDiv.innerHTML = '<div class="alert alert-danger">Face data missing. Please go back and retry.</div>';
                return;
            }

            // Wait for models to load (max 15 seconds)
            if (!modelsLoaded) {
                resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Loading face recognition models...</div>';
                let waited = 0;
                while (!modelsLoaded && waited < 15000) {
                    await new Promise(resolve => setTimeout(resolve, 500));
                    waited += 500;
                }
                
                if (!modelsLoaded) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Failed to load face recognition. Please refresh the page.</div>';
                    return;
                }
            }

            try {
                // Calculate distance between faces
                const distance = faceapi.euclideanDistance(idPhotoDescriptor, capturedFaceDescriptor);
                const threshold = 0.45; // Adjusted for better accuracy

                resultDiv.innerHTML = `
                    <div style="margin-bottom: 1.5rem;">
                        <p style="margin-bottom: 1rem;"><strong>ID Photo:</strong></p>
                        <img src="${idPhotoImage}" style="max-width: 150px; border-radius: 8px; margin-right: 1rem; display: inline-block;">
                        <p style="margin-bottom: 1rem; margin-top: 1rem;"><strong>Captured Face:</strong></p>
                        <img src="${capturedFaceImage}" style="max-width: 150px; border-radius: 8px; display: inline-block;">
                    </div>
                `;

                if (distance < threshold) {
                    resultDiv.innerHTML += `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> ✓ Face verification successful! Your faces match.
                        </div>
                    `;

                    // Check if this face is already registered in the system
                    console.log('Checking if face is already registered...');
                    const faceCheckResult = await checkIfFaceAlreadyRegistered(capturedFaceDescriptor);
                    
                    if (faceCheckResult.registered) {
                        // Face already registered - show error
                        resultDiv.innerHTML += `
                            <div class="alert alert-danger" style="margin-top: 1rem;">
                                <strong style="display: block; margin-bottom: 0.5rem;">❌ Face Already Registered</strong>
                                <p style="margin: 0.5rem 0; color: #ff6b6b;">${faceCheckResult.message}</p>
                                <p style="margin: 0.5rem 0; font-size: 0.9rem; color: #ecf0f1;">
                                    <strong>${faceCheckResult.details}</strong><br>
                                    One account per student is enforced.
                                </p>
                                <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 0.75rem 0;">
                                <p style="margin: 0; font-size: 0.9rem;">
                                    <a href="${BASE_URL}login" style="color: #4fc3f7; text-decoration: underline;">Please login with your existing account</a>
                                </p>
                            </div>
                        `;
                        document.getElementById('submitBtn').disabled = true;
                        document.getElementById('editPhotosBtn').style.display = 'inline-block';
                        console.warn('Face already registered:', faceCheckResult);
                    } else {
                        // Face not registered - allow submission
                        document.getElementById('submitBtn').disabled = false;
                        document.getElementById('editPhotosBtn').style.display = 'none';
                        console.log('✓ Face is not registered - ready to submit');
                    }
                } else {
                    resultDiv.innerHTML += `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i> ✗ Faces do not match. Please retake photos.
                        </div>
                    `;
                    document.getElementById('submitBtn').disabled = true;
                    document.getElementById('editPhotosBtn').style.display = 'inline-block';
                }

                console.log('Face distance:', distance.toFixed(4), 'Threshold:', threshold);

            } catch (err) {
                console.error('Verification error:', err);
                resultDiv.innerHTML = `<div class="alert alert-danger">Verification error. Please try again.</div>`;
                document.getElementById('submitBtn').disabled = true;
            }
        }

        // Check if face is already registered in the system
        async function checkIfFaceAlreadyRegistered(faceDescriptor) {
            try {
                const studentId = document.getElementById('student_id').value;
                const response = await fetch(BASE_URL + 'auth/check-face-registered', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        face_descriptor: JSON.stringify(Array.from(faceDescriptor)),
                        student_id: studentId
                    })
                });

                if (!response.ok) {
                    console.error('Face check request failed:', response.status);
                    return {
                        registered: false,
                        message: 'Could not verify face (continuing anyway)'
                    };
                }

                const result = await response.json();
                console.log('Face check result:', result);
                return result;

            } catch (err) {
                console.error('Error checking face registration:', err);
                return {
                    registered: false,
                    message: 'Connection error (continuing anyway)'
                };
            }
        }

        function editPhotos() {
            currentStep = 2;
            idPhotoImage = null;
            capturedFaceImage = null;
            idPhotoDescriptor = null;
            capturedFaceDescriptor = null;
            document.getElementById('id_photo').value = '';
            document.getElementById('idPhotoPreview').style.display = 'none';
            document.getElementById('capturedFacePreview').style.display = 'none';
            updateUI();
        }

        // Submit Registration
        document.getElementById('submitBtn')?.addEventListener('click', async function() {
            this.disabled = true;
            this.textContent = 'Submitting...';

            try {
                // Get location if available
                let latitude = null;
                let longitude = null;

                if (navigator.geolocation) {
                    try {
                        const position = await new Promise((resolve, reject) => {
                            navigator.geolocation.getCurrentPosition(resolve, reject, {
                                enableHighAccuracy: true,
                                timeout: 5000,
                                maximumAge: 0
                            });
                        });
                        latitude = position.coords.latitude;
                        longitude = position.coords.longitude;
                        console.log('✓ Location acquired: ' + latitude + ', ' + longitude);
                    } catch (geoError) {
                        console.warn('Geolocation error:', geoError);
                        // Allow submission to continue without location
                    }
                }

                const formData = new FormData();
                
                // Form fields
                formData.append('student_id', document.getElementById('student_id').value);
                formData.append('full_name', document.getElementById('full_name').value);
                formData.append('username', document.getElementById('username').value);
                formData.append('department', document.getElementById('department').value);
                formData.append('course', document.getElementById('course').value);
                formData.append('year_level', document.getElementById('year_level').value);
                formData.append('section', document.getElementById('section').value);
                formData.append('school_year', document.getElementById('school_year').value);
                formData.append('password', document.getElementById('password').value);
                
                // Location data
                if (latitude !== null) formData.append('latitude', latitude);
                if (longitude !== null) formData.append('longitude', longitude);
                
                // Photos as base64
                formData.append('id_photo_base64', idPhotoImage);
                formData.append('captured_face_base64', capturedFaceImage);
                formData.append('face_descriptor', JSON.stringify(Array.from(capturedFaceDescriptor)));

                const response = await fetch(BASE_URL + 'register/submit', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    document.querySelector('.step-content.active').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                    setTimeout(() => {
                        window.location.href = BASE_URL + 'login';
                    }, 3000);
                } else {
                    // Check if it's a duplicate account error
                    if (result.message && result.message.includes('already registered')) {
                        const errorAlert = document.getElementById('errorAlert');
                        errorAlert.innerHTML = `
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: #ff6b6b;">⚠️ Account Already Registered</strong>
                                <p style="margin-top: 0.5rem; color: #ecf0f1;">${result.message}</p>
                                <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem 0;">
                                <p style="color: #b8c5d6; font-size: 0.9rem; margin: 0;">
                                    <strong>Next Steps:</strong><br>
                                    • Go to <a href="${BASE_URL}login" style="color: #4fc3f7; text-decoration: underline;">Login Page</a><br>
                                    • Enter your username and password<br>
                                    • Complete face verification to access voting
                                </p>
                            </div>
                        `;
                        errorAlert.style.display = 'block';
                        errorAlert.style.background = 'rgba(220, 53, 69, 0.15)';
                        errorAlert.style.borderColor = 'rgba(220, 53, 69, 0.3)';
                    } else {
                        showError(result.message || 'Registration failed. Please try again.');
                    }
                    this.disabled = false;
                    this.textContent = 'Complete Registration';
                }
            } catch (err) {
                console.error('Submit error:', err);
                showError('Error submitting registration. Please try again.');
                this.disabled = false;
                this.textContent = 'Complete Registration';
            }
        });

        // Utilities
        function showError(message) {
            const alert = document.getElementById('errorAlert');
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        function showFieldError(field, message) {
            document.getElementById(`err_${field}`).textContent = message;
        }
    </script>
</body>
</html>
