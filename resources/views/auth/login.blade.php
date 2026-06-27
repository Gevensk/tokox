<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TokoX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e4ecf7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            overflow: hidden;
        }
        .login-header {
            background: #1e2229;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .login-header h3 {
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .login-body {
            padding: 40px 35px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(59, 125, 221, 0.15);
            border-color: #3b7ddd;
        }
        .btn-primary {
            background-color: #3b7ddd;
            border-color: #3b7ddd;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #2b6cb0;
            border-color: #2b6cb0;
        }
        .input-group-text {
            background: none;
            border-left: none;
            cursor: pointer;
        }
        .password-input {
            border-right: none;
        }
        /* Hide Microsoft Edge / IE default eye icon */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h3>TOKO X</h3>
        <p class="text-muted small mb-0" style="color: #98a6ad !important;">Sistem Informasi Produksi</p>
    </div>
    
    <div class="login-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                    <input type="text" name="username" id="username" class="form-control bg-light border-start-0" placeholder="admin" value="{{ old('username') }}" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control bg-light border-start-0 password-input" placeholder="••••••••" required>
                    <span class="input-group-text bg-light border-start-0" id="togglePassword"><i class="fa-regular fa-eye text-muted"></i></span>
                </div>
            </div>



            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                Masuk ke Sistem <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
