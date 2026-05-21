<?php
/**
 * KEBANA Digital Management System - Mobile OCR Camera Upload
 * File: modules/members/mobile-ocr.php
 */

use App\Core\Database;

$token = trim($_GET['token'] ?? '');
$session_valid = false;
$error_message = '';

if (!empty($token)) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT status FROM mobile_ocr_sessions WHERE token = ? AND created_at >= NOW() - INTERVAL 1 HOUR");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] === 'pending') {
                $session_valid = true;
            } else {
                $error_message = 'Sesi pengimbasan ini telah digunakan atau tamat tempoh.';
            }
        } else {
            $error_message = 'Sesi tidak dijumpai atau telah tamat tempoh (aktif selama 1 jam).';
        }
        $stmt->close();
    } catch (Exception $e) {
        $error_message = 'Ralat sistem: ' . $e->getMessage();
    }
} else {
    $error_message = 'Token pengimbasan tidak dibekalkan.';
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Imbas Dokumen - KEBANA Digital</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (if available or standard CSS fallback, we use standard CSS with modern features) -->
    <style>
        :root {
            --kebana-blue: #0f172a;
            --kebana-blue-light: #1e293b;
            --kebana-accent: #3b82f6;
            --kebana-yellow: #eab308;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            overflow-x: hidden;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 480px;
            perspective: 1000px;
        }
        
        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }
        
        .logo-area {
            margin-bottom: 24px;
        }
        
        .logo-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--kebana-accent) 0%, #4f46e5 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 32px;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
            margin-bottom: 12px;
            animation: pulse-ring 2s infinite;
        }
        
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        .title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
            text-transform: uppercase;
            background: linear-gradient(to right, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 32px;
        }
        
        .btn-action {
            background: linear-gradient(135deg, var(--kebana-accent) 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 20px 24px;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            border-radius: 16px;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-action:active {
            transform: scale(0.97);
            box-shadow: 0 5px 10px -3px rgba(37, 99, 235, 0.4);
        }
        
        .status-container {
            margin-top: 24px;
            display: none;
            animation: fadeIn 0.4s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .progress-bar-bg {
            background: rgba(255, 255, 255, 0.05);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 16px 0;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--kebana-accent) 0%, var(--kebana-yellow) 100%);
            width: 0%;
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .status-text {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #3b82f6;
        }
        
        .preview-container {
            margin: 24px 0;
            border-radius: 16px;
            overflow: hidden;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
            position: relative;
            aspect-ratio: 4 / 3;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .preview-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .error-card {
            border-top: 8px solid #ef4444;
        }
        
        .error-icon {
            font-size: 48px;
            color: #ef4444;
            margin-bottom: 16px;
        }
        
        .error-desc {
            font-size: 13px;
            color: #94a3b8;
            font-weight: 600;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .btn-retry {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
        }
        
        .success-card {
            border-top: 8px solid #22c55e;
        }
        
        .success-icon {
            font-size: 56px;
            color: #22c55e;
            margin-bottom: 20px;
            animation: bounce 1s infinite alternate;
        }
        
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        
        .brand-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.2em;
            color: rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($session_valid): ?>
        <!-- ACTIVE UPLOAD SCREEN -->
        <div class="card" id="upload_card">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fa-solid fa-camera-retro"></i>
                </div>
                <h1 class="title">Imbas Borang</h1>
                <p class="subtitle">KEBANA DIGITAL OCR</p>
            </div>
            
            <p style="font-size: 12px; color: #cbd5e1; font-weight: 500; margin-bottom: 24px; line-height: 1.6;">
                Sila ambil gambar borang pendaftaran ahli dengan jelas dan dalam pencahayaan yang mencukupi untuk ketepatan OCR.
            </p>
            
            <div class="preview-container" id="preview_container">
                <img id="preview_img" class="preview-img" alt="Pratonton Borang">
            </div>
            
            <input type="file" id="mobile_camera_input" accept="image/*" capture="environment" style="display: none;">
            
            <button class="btn-action" id="btn_capture">
                <i class="fa-solid fa-camera"></i>
                Ambil Gambar Borang
            </button>
            
            <div class="status-container" id="status_container">
                <div class="progress-bar-bg">
                    <div class="progress-bar" id="progress_bar"></div>
                </div>
                <span class="status-text" id="status_text">Menyediakan fail...</span>
            </div>
        </div>
        
        <!-- SUCCESS SCREEN -->
        <div class="card success-card" id="success_card" style="display: none;">
            <div class="success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h1 class="title" style="color: #22c55e; -webkit-text-fill-color: initial;">IMBASAN BERJAYA!</h1>
            <p class="subtitle">HANTARAN SELESAI</p>
            <p class="error-desc">
                Borang anda telah berjaya dimuat naik ke pelayan. Sila lihat skrin komputer/laptop desktop anda untuk meneruskan pendaftaran. Skrin desktop akan memproses OCR secara automatik.
            </p>
            <p style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                Sesi ini kini ditutup. Anda boleh menutup tab browser ini.
            </p>
        </div>
    <?php else: ?>
        <!-- ERROR SCREEN -->
        <div class="card error-card">
            <div class="error-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h1 class="title" style="color: #ef4444; -webkit-text-fill-color: initial;">RALAT SESI</h1>
            <p class="subtitle">SAMBUNGAN GAGAL</p>
            <p class="error-desc">
                <?php echo htmlspecialchars($error_message); ?>
            </p>
            <button class="btn-action btn-retry" onclick="window.location.reload();">
                <i class="fa-solid fa-rotate-right"></i>
                Cuba Semula
            </button>
        </div>
    <?php endif; ?>
    
    <div class="brand-footer">
        KEBANA DIGITAL SYSTEM &copy; 2026
    </div>
</div>

<?php if ($session_valid): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCapture = document.getElementById('btn_capture');
    const cameraInput = document.getElementById('mobile_camera_input');
    const previewContainer = document.getElementById('preview_container');
    const previewImg = document.getElementById('preview_img');
    const statusContainer = document.getElementById('status_container');
    const progressBar = document.getElementById('progress_bar');
    const statusText = document.getElementById('status_text');
    const uploadCard = document.getElementById('upload_card');
    const successCard = document.getElementById('success_card');
    
    const sessionToken = "<?php echo htmlspecialchars($token); ?>";
    
    btnCapture.addEventListener('click', () => {
        cameraInput.click();
    });
    
    cameraInput.addEventListener('change', function(e) {
        if (e.target.files.length === 0) return;
        
        const file = e.target.files[0];
        
        // Show local preview
        const reader = new FileReader();
        reader.onload = function(event) {
            previewImg.src = event.target.result;
            previewContainer.style.display = 'flex';
            
            // Start processing & upload
            processAndUpload(file);
        };
        reader.readAsDataURL(file);
    });
    
    function processAndUpload(file) {
        // Change button to disabled
        btnCapture.disabled = true;
        btnCapture.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses gambar...';
        
        statusContainer.style.display = 'block';
        progressBar.style.width = '15%';
        statusText.innerText = 'Mengoptimumkan kualiti gambar...';
        
        // Use Canvas API to resize and compress image to reduce payload size
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                // Set max dimension
                const maxDim = 1600;
                let width = img.width;
                let height = img.height;
                
                if (width > height) {
                    if (width > maxDim) {
                        height *= maxDim / width;
                        width = maxDim;
                    }
                } else {
                    if (height > maxDim) {
                        width *= maxDim / height;
                        height = maxDim;
                    }
                }
                
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convert to compressed jpeg base64
                progressBar.style.width = '40%';
                statusText.innerText = 'Menukar fail ke Base64...';
                
                const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85); // 85% quality
                
                // Perform AJAX upload
                uploadToServer(compressedBase64);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
    
    function uploadToServer(base64Data) {
        progressBar.style.width = '60%';
        statusText.innerText = 'Menghantar ke pelayan...';
        
        const formData = new FormData();
        formData.append('token', sessionToken);
        formData.append('image_data', base64Data);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= URL_ROOT ?>/api/ocr/upload_image', true);
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 40) + 60; // scale from 60% to 100%
                progressBar.style.width = percent + '%';
                statusText.innerText = `Memuat naik... ${percent}%`;
            }
        });
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        progressBar.style.width = '100%';
                        statusText.innerText = 'Selesai!';
                        
                        setTimeout(() => {
                            // Show success screen with custom transitions
                            uploadCard.style.display = 'none';
                            successCard.style.display = 'block';
                        }, 500);
                    } else {
                        handleUploadError(response.error || 'Ralat tidak diketahui.');
                    }
                } catch(e) {
                    handleUploadError('Format maklumbalas ralat.');
                }
            } else {
                let errorMsg = 'Ralat sambungan pelayan.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) errorMsg = response.error;
                } catch (e) {}
                handleUploadError(errorMsg);
            }
        };
        
        xhr.onerror = function() {
            handleUploadError('Talian internet terputus atau ralat pelayan.');
        };
        
        xhr.send(formData);
    }
    
    function handleUploadError(msg) {
        progressBar.style.backgroundColor = '#ef4444';
        statusText.innerText = 'Ralat: ' + msg;
        statusText.style.color = '#ef4444';
        
        btnCapture.disabled = false;
        btnCapture.innerHTML = '<i class="fa-solid fa-camera"></i> Ambil Gambar Semula';
    }
});
</script>
<?php endif; ?>

</body>
</html>
