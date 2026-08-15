<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - Login</title>
    <!-- Use system fonts like ERP -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --erp-primary: #0C3527;
            --erp-primary-hover: #124836;
            --erp-primary-active: #08261C;
            --erp-secondary: #D9EFE9;
            --erp-danger: #DC2626;
            --erp-warning: #F59E0B;
            --erp-info: #0C3527;
            --erp-bg: #F8FAFC;
        }

        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            background: var(--erp-bg); 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
            color: #1e293b;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        .login-card { 
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px; 
            box-shadow: 0 20px 40px rgba(12, 53, 39, 0.08), 0 1px 3px rgba(0,0,0,0.05); 
            overflow: hidden; 
        }

        .login-header { 
            background: var(--erp-primary); 
            color: white; 
            padding: 40px 32px 32px; 
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            z-index: 1;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            z-index: 1;
        }

        .login-header > * {
            position: relative;
            z-index: 2;
        }

        .login-header .icon-wrapper {
            width: 64px;
            height: 64px;
            background: var(--erp-secondary);
            color: var(--erp-primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .login-header h4 {
            margin: 0 0 8px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-header p {
            margin: 0;
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            font-weight: 500;
        }

        .login-body { 
            padding: 32px; 
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 20px;
        }

        .form-control { 
            width: 100%;
            box-sizing: border-box;
            border-radius: 12px; 
            padding: 14px 16px 14px 48px; 
            border: 1px solid rgba(0,0,0,0.1); 
            background: white; 
            font-family: inherit;
            font-size: 15px;
            color: #1e293b;
            transition: all 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus { 
            outline: none;
            border-color: var(--erp-primary); 
            box-shadow: 0 0 0 4px var(--erp-secondary); 
        }

        .btn-login { 
            width: 100%;
            border-radius: 12px; 
            padding: 14px 24px; 
            font-weight: 600; 
            background: var(--erp-primary); 
            color: white;
            border: none; 
            font-size: 16px; 
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-login:hover { 
            background: var(--erp-primary-hover); 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(12, 53, 39, 0.2);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            background: rgba(220, 38, 38, 0.1);
            color: var(--erp-danger);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .footer-text {
            text-align: center;
            margin-top: 32px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="icon-wrapper">
                <i class="ph-fill ph-crown"></i>
            </div>
            <h4>Customer Portal</h4>
            <p>Akses member & poin loyalitas</p>
        </div>
        <div class="login-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="alert">
                    <i class="ph-fill ph-warning-circle"></i>
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form action="<?php echo e(route('portal.login.attempt')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <i class="ph ph-identification-card"></i>
                    <input type="text" name="customer_code" class="form-control" placeholder="Kode Customer (CUST-XXXX)" required value="<?php echo e(old('customer_code')); ?>">
                </div>
                <div class="form-group">
                    <i class="ph ph-phone"></i>
                    <input type="text" name="phone" class="form-control" placeholder="Nomor Handphone" required value="<?php echo e(old('phone')); ?>">
                </div>
                <button type="submit" class="btn-login">
                    Masuk Portal <i class="ph ph-arrow-right"></i>
                </button>
            </form>
            <div class="footer-text">
                Belum memiliki akun member?<br>Silakan daftarkan diri Anda di kasir.
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\portal\login.blade.php ENDPATH**/ ?>