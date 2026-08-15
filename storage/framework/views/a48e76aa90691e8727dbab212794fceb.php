<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['company' => $company,'features' => $features,'divisions' => $divisions,'user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['company' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($company),'features' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($features),'divisions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($divisions),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <section id="view-overview" style="animation: fadeIn 0.3s ease;">
            <?php
                $latestAnnouncement = \App\Models\Announcement::where('is_active', true)->latest()->first();
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($latestAnnouncement): ?>
                <div style="background: linear-gradient(135deg, #4f46e5, #0C3527); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);">
                    <i class="fa-solid fa-bullhorn" style="font-size: 24px; margin-top: 4px;"></i>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: white; border: none; padding: 0;"><?php echo e($latestAnnouncement->title); ?></h3>
                        <div style="font-size: 14px; line-height: 1.5; opacity: 0.9;"><?php echo e($latestAnnouncement->content); ?></div>
                        <div style="font-size: 11px; margin-top: 12px; opacity: 0.7;">Disiarkan oleh Management &bull; <?php echo e($latestAnnouncement->created_at->diffForHumans()); ?></div>
                    </div>
                </div>

                <!-- Announcement Popup Modal -->
                <div id="modal-announcement-popup" class="modal-overlay" style="display:none; z-index: 10000; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
                    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); box-shadow: 0 20px 40px rgba(0,0,0,0.2); padding: 32px 24px;">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">Pengumuman Baru</h2>
                        </div>
                        <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: #1e293b; text-align: center;"><?php echo e($latestAnnouncement->title); ?></h3>
                        <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 24px; text-align: center; background: #f8fafc; padding: 16px; border-radius: 12px;">
                            <?php echo e($latestAnnouncement->content); ?>

                        </div>
                        <button class="ios-btn ios-btn-primary" style="width: 100%;" onclick="dismissAnnouncementPopup('<?php echo e($latestAnnouncement->id); ?>')">Saya Mengerti</button>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const announcementId = '<?php echo e($latestAnnouncement->id); ?>';
                        const dismissed = localStorage.getItem('announcement_dismissed_' + announcementId);
                        if (!dismissed) {
                            setTimeout(() => {
                                document.getElementById('modal-announcement-popup').style.display = 'flex';
                            }, 500);
                        }
                    });
                    function dismissAnnouncementPopup(id) {
                        localStorage.setItem('announcement_dismissed_' + id, 'true');
                        document.getElementById('modal-announcement-popup').style.display = 'none';
                    }
                </script>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="grid-4">
                <div class="card" style="opacity: 0.5; cursor: not-allowed;" title="Module coming soon">
                    <h3>CRM Pipeline</h3>
                    <div class="value" id="metrics-crm-value"><div class="loader"></div></div>
                    <div class="trend" style="color: var(--text-muted);"><i class="fa-solid fa-tools"></i> Module in Development</div>
                </div>
                <div class="card" style="opacity: 0.5; cursor: not-allowed;" title="Module coming soon">
                    <h3>Pending Payables</h3>
                    <div class="value" id="metrics-po-value"><div class="loader"></div></div>
                    <div class="trend" style="color: var(--text-muted);"><i class="fa-solid fa-tools"></i> Module in Development</div>
                </div>
                <div class="card" style="opacity: 0.5; cursor: not-allowed;" title="Module coming soon">
                    <h3>Production Quality</h3>
                    <div class="value" id="metrics-qa-value"><div class="loader"></div></div>
                    <div class="trend" style="color: var(--text-muted);"><i class="fa-solid fa-tools"></i> Module in Development</div>
                </div>
                <div class="card interactive" onclick="switchView('inventory_umkm')" style="cursor:pointer;">
                    <h3>Asset Valuation</h3>
                    <div class="value" id="metrics-inv-value"><div class="loader"></div></div>
                    <div class="trend"><i class="fa-solid fa-boxes-stacked"></i> Go to Inventory & Warehouse</div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3>Executive Alerts</h3>
                    <div id="alerts-container">
                        <div class="loader"></div> Fetching system alerts...
                    </div>
                </div>
                <div class="card">
                    <h3>System Modules</h3>
                    <div style="margin-top: 10px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = collect($features)->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="list-item">
                                <div>
                                    <div class="title"><?php echo e($mod['label']); ?></div>
                                    <div class="desc"><?php echo e($mod['group']); ?></div>
                                </div>
                                <div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mod['state'] === 'active'): ?>
                                        <span style="color: var(--success); font-weight: bold; font-size: 12px;">ACTIVE</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 12px;"><?php echo e(strtoupper($mod['state'])); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div style="margin-top: 16px; text-align: center;">
                            <a href="#" onclick="switchView('modules')" style="color: var(--accent); text-decoration: none; font-size: 13px; font-weight: bold;">View All Modules &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0;"><i class="fa-solid fa-chart-line" style="color: var(--accent); margin-right: 8px;"></i> Tren Kehadiran Mingguan (Agregat)</h3>
                    <button class="ios-btn ios-btn-secondary" style="font-size: 11px; padding: 6px 12px;" onclick="switchView('people')">Manajemen HR &rarr;</button>
                </div>
                <div style="height: 180px; display: flex; align-items: flex-end; gap: 16px; padding: 16px 24px 32px 24px; border-bottom: 1px solid var(--panel-border); background: var(--bg); border-radius: 8px;">
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 75%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Sen</span>
                        <span style="position: absolute; top: 15%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">75%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 85%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Sel</span>
                        <span style="position: absolute; top: 5%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">85%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 92%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Rab</span>
                        <span style="position: absolute; top: -5%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">92%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: #ef4444; height: 60%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(239, 68, 68, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Kam</span>
                        <span style="position: absolute; top: 30%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">60%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 95%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Jum</span>
                        <span style="position: absolute; top: -8%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">95%</span>
                    </div>
                </div>
            </div>

        </section>

        <script>
            async function deleteAnnouncement(id) {
                if(!confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) return;
                try {
                    const res = await fetch('/master-demo/announcements/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    if(res.ok) {
                        alert('Pengumuman berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('Gagal menghapus pengumuman.');
                    }
                } catch(e) { console.error(e); }
            }

            async function bulkDeleteAnnouncements(period) {
                let pStr = period === 'daily' ? '1 Hari' : (period === 'weekly' ? '1 Minggu' : '1 Bulan');
                if(!confirm('Anda akan menghapus SEMUA pengumuman yang lebih lama dari ' + pStr + '. Tindakan ini tidak bisa dibatalkan. Lanjutkan?')) return;
                try {
                    const res = await fetch('/master-demo/announcements/bulk-delete', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify({ period: period })
                    });
                    if(res.ok) {
                        const data = await res.json();
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Gagal menghapus pengumuman.');
                    }
                } catch(e) { console.error(e); }
            }
        </script>

        <!-- DIVISION SETTINGS VIEW -->
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>


<?php /**PATH D:\suba-erp-master-local-latest\resources\views/pages/command-center.blade.php ENDPATH**/ ?>