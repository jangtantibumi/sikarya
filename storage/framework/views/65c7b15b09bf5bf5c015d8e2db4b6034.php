<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Customer Portal - <?php echo e($customer->name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
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
            color: #1e293b; 
            padding-bottom: 80px; 
            -webkit-font-smoothing: antialiased;
        }

        .portal-header {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .member-card { 
            background: var(--erp-primary); 
            color: white; 
            border-radius: 24px; 
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(12, 53, 39, 0.15);
            margin: 24px 20px;
        }

        .tier-badge { 
            background: var(--erp-secondary); 
            color: var(--erp-primary);
            border-radius: 100px; 
            padding: 6px 16px; 
            font-size: 12px; 
            font-weight: 700; 
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .points-display { 
            font-size: 40px; 
            font-weight: 800; 
            line-height: 1; 
            margin: 16px 0 8px; 
            letter-spacing: -1px;
        }

        .qr-placeholder { 
            background: white; 
            width: 72px; 
            height: 72px; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0 auto; 
            padding: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .qr-placeholder img { 
            max-width: 100%; 
            max-height: 100%; 
            border-radius: 4px;
        }
        
        .action-card { 
            background: white; 
            border-radius: 20px; 
            padding: 20px 12px; 
            text-align: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            border: 1px solid rgba(0,0,0,0.03); 
            cursor: pointer; 
            transition: all 0.2s; 
            text-decoration: none !important;
            color: inherit;
            display: block;
        }

        .action-card:active { transform: scale(0.96); background: #f8fafc; }

        .action-icon { 
            width: 48px; 
            height: 48px; 
            border-radius: 14px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0 auto 12px auto; 
            font-size: 24px; 
        }

        .icon-reserve { background: var(--erp-secondary); color: var(--erp-primary); }
        .icon-feedback { background: rgba(245, 158, 11, 0.15); color: var(--erp-warning); }
        .icon-voucher { background: rgba(22, 163, 74, 0.15); color: var(--text-accent); }
        .icon-card { background: rgba(59, 130, 246, 0.15); color: var(--text-accent); }
        
        .history-item { 
            background: white; 
            border-radius: 16px; 
            padding: 16px; 
            margin-bottom: 12px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); 
            border: 1px solid rgba(0,0,0,0.03); 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 600px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 200;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #64748b;
            text-decoration: none !important;
            font-size: 11px;
            font-weight: 600;
        }

        .bottom-nav-item i { font-size: 20px; margin-bottom: 2px; }
        .bottom-nav-item.active { color: var(--erp-primary); }

        .modal-content { border-radius: 24px; border: none; overflow: hidden; }
        .modal-header { padding: 24px 24px 16px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px 24px; border-top: 1px solid rgba(0,0,0,0.05); }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1px solid rgba(0,0,0,0.1); background: #f8fafc; }
        .form-control:focus { box-shadow: none; border-color: var(--erp-primary); background: white; }
        .btn-primary { background: var(--erp-primary); border: none; border-radius: 12px; padding: 12px 24px; font-weight: 600; }
        .btn-light { border-radius: 12px; padding: 12px 24px; font-weight: 600; background: #f1f5f9; border: none; }
        .status-badge { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 100px; }
        .status-badge.pending { background: rgba(245, 158, 11, 0.1); color: var(--erp-warning); }
        .status-badge.confirmed { background: rgba(59, 130, 246, 0.1); color: var(--erp-info); }
        .status-badge.completed { background: rgba(12, 53, 39, 0.1); color: var(--text-accent); }
    </style>
</head>
<body>

<div style="max-width: 600px; margin: 0 auto; background: var(--erp-bg); min-height: 100vh;">
    
    <!-- Header -->
    <div class="portal-header">
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 500;">Selamat datang,</div>
            <h5 style="font-weight: 800; margin: 0; color: #1e293b; font-size: 20px;"><?php echo e(explode(' ', $customer->name)[0]); ?></h5>
        </div>
        <form action="<?php echo e(route('portal.logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" style="background: white; border: 1px solid rgba(0,0,0,0.05); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;">
                <i class="ph ph-sign-out" style="font-size: 20px;"></i>
            </button>
        </form>
    </div>

    <!-- Alerts -->
    <div style="padding: 0 20px; margin-top: 16px;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert" style="background: rgba(12, 53, 39, 0.1); color: var(--text-accent); border: none; border-radius: 12px; font-weight: 500; font-size: 14px; padding: 12px 16px;">
                <i class="ph-fill ph-check-circle" style="margin-right: 6px;"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert" style="background: rgba(220, 38, 38, 0.1); color: var(--erp-danger); border: none; border-radius: 12px; font-weight: 500; font-size: 14px; padding: 12px 16px;">
                <i class="ph-fill ph-warning-circle" style="margin-right: 6px;"></i> <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Member Card -->
    <a href="<?php echo e(route('portal.card')); ?>" style="text-decoration: none; color: inherit; display: block;">
        <div class="member-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 12px; font-weight: 600; opacity: 0.8; letter-spacing: 0.5px; text-transform: uppercase;">Level Membership</div>
                    <div class="tier-badge mt-2 mb-2">
                        <i class="ph-fill ph-crown"></i> <?php echo e($customer->membership_level); ?>

                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="qr-placeholder">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo e($customer->customer_code); ?>" alt="QR Code">
                    </div>
                    <div style="font-size: 11px; font-weight: 700; margin-top: 8px; letter-spacing: 1px; color: rgba(255,255,255,0.8);"><?php echo e($customer->customer_code); ?></div>
                </div>
            </div>
            <div style="margin-top: 16px;">
                <div style="font-size: 12px; font-weight: 600; opacity: 0.8; letter-spacing: 0.5px; text-transform: uppercase;">Loyalty Points</div>
                <div class="points-display"><?php echo e(number_format($customer->total_points, 0, ',', '.')); ?></div>
                <div style="font-size: 13px; opacity: 0.8; font-weight: 500;">Klik untuk buka Kartu Member Digital</div>
            </div>
        </div>
    </a>

    <!-- Quick Actions -->
    <h6 style="font-weight: 800; font-size: 16px; padding: 0 20px; margin-bottom: 16px;">Menu Utama</h6>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 0 20px; margin-bottom: 32px;">
        <div class="action-card" data-toggle="modal" data-target="#modalReserve">
            <div class="action-icon icon-reserve"><i class="ph-fill ph-calendar-plus"></i></div>
            <div style="font-weight: 700; font-size: 12px;">Reservasi</div>
        </div>
        <a href="<?php echo e(route('portal.vouchers')); ?>" class="action-card">
            <div class="action-icon icon-voucher"><i class="ph-fill ph-ticket"></i></div>
            <div style="font-weight: 700; font-size: 12px;">Voucher</div>
        </a>
        <a href="<?php echo e(route('portal.card')); ?>" class="action-card">
            <div class="action-icon icon-card"><i class="ph-fill ph-identification-card"></i></div>
            <div style="font-weight: 700; font-size: 12px;">Kartu Digital</div>
        </a>
        <div class="action-card" data-toggle="modal" data-target="#modalFeedback">
            <div class="action-icon icon-feedback"><i class="ph-fill ph-star"></i></div>
            <div style="font-weight: 700; font-size: 12px;">Ulasan</div>
        </div>
    </div>

    <!-- History Tabs -->
    <div style="padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h6 style="font-weight: 800; font-size: 16px; margin: 0;">Riwayat Reservasi</h6>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customer->reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="history-item">
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: #1e293b; margin-bottom: 4px;"><?php echo e($res->reservation_date->format('d/m/Y')); ?></div>
                    <div style="font-size: 13px; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-clock"></i> <?php echo e(date('H:i', strtotime($res->reservation_time))); ?> &bull; <i class="ph ph-users"></i> <?php echo e($res->pax); ?> Orang
                    </div>
                </div>
                <div><span class="status-badge <?php echo e(strtolower($res->status)); ?>"><?php echo e($res->status); ?></span></div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div style="text-align: center; padding: 30px 20px; background: white; border-radius: 16px;">
                <div style="font-weight: 600; color: #94a3b8; font-size: 13px;">Belum ada riwayat reservasi.</div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="<?php echo e(route('portal.dashboard')); ?>" class="bottom-nav-item active">
            <i class="ph-fill ph-house"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo e(route('portal.vouchers')); ?>" class="bottom-nav-item">
            <i class="ph-fill ph-ticket"></i>
            <span>Voucher</span>
        </a>
        <a href="<?php echo e(route('portal.loyalty')); ?>" class="bottom-nav-item">
            <i class="ph-fill ph-coins"></i>
            <span>Poin</span>
        </a>
        <a href="<?php echo e(route('portal.invoices')); ?>" class="bottom-nav-item">
            <i class="ph-fill ph-receipt"></i>
            <span>Transaksi</span>
        </a>
        <a href="<?php echo e(route('portal.profile')); ?>" class="bottom-nav-item">
            <i class="ph-fill ph-user"></i>
            <span>Profil</span>
        </a>
    </div>

</div>

<!-- Modal Reservasi -->
<div class="modal fade" id="modalReserve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight: 700; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--erp-secondary); color: var(--erp-primary); display: flex; align-items: center; justify-content: center;"><i class="ph-fill ph-calendar-plus"></i></div>
                    Buat Reservasi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('portal.reserve')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Tanggal Kedatangan</label>
                        <input type="date" name="reservation_date" class="form-control" required min="<?php echo e(date('Y-m-d')); ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Waktu</label>
                            <input type="time" name="reservation_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Jumlah Orang</label>
                            <input type="number" name="pax" class="form-control" min="1" value="2" required>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Catatan Tambahan</label>
                        <textarea name="special_requests" class="form-control" rows="2" placeholder="Meja dekat jendela, ulang tahun, dll"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc;">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Reservasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Feedback -->
<div class="modal fade" id="modalFeedback" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight: 700; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: var(--erp-warning); display: flex; align-items: center; justify-content: center;"><i class="ph-fill ph-star"></i></div>
                    Beri Ulasan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('portal.feedback')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group text-center mb-4">
                        <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 12px;">Rating Kepuasan</label>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%;">
                            <label class="btn btn-light" style="font-weight: 700;"><input type="radio" name="rating" value="1" required> 1</label>
                            <label class="btn btn-light" style="font-weight: 700;"><input type="radio" name="rating" value="2"> 2</label>
                            <label class="btn btn-light" style="font-weight: 700;"><input type="radio" name="rating" value="3"> 3</label>
                            <label class="btn btn-light" style="font-weight: 700;"><input type="radio" name="rating" value="4"> 4</label>
                            <label class="btn btn-primary active" style="font-weight: 700;"><input type="radio" name="rating" value="5" checked> 5</label>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Kategori</label>
                        <select name="category" class="form-control" required>
                            <option value="Food">Kualitas Makanan (Food)</option>
                            <option value="Service">Pelayanan (Service)</option>
                            <option value="Ambience">Suasana (Ambience)</option>
                            <option value="Other">Lainnya (Other)</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pesan / Komentar</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="Ceritakan pengalaman Anda..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc;">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\portal\dashboard.blade.php ENDPATH**/ ?>