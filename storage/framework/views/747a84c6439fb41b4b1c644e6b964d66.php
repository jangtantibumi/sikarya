<div class="payroll-module ios-design" style="animation: iosFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px; color: var(--text-heading);">
                Payroll & Benefits
            </h2>
            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 15px;">Manage employee salaries, allowances, and automatic deductions.</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;" id="payroll-actions-container">
            <!-- Buttons injected via JS -->
        </div>
    </div>

    <div style="display: flex; gap: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--panel-border); padding-bottom: 16px;">
        <button id="btn-tab-dashboard" class="ios-btn ios-btn-primary" onclick="switchPayrollTab('dashboard')" style="border-radius: 12px; font-size: 14px; padding: 8px 16px;">Payroll Run</button>
        <button id="btn-tab-components" class="ios-btn ios-btn-secondary" onclick="switchPayrollTab('components')" style="border-radius: 12px; font-size: 14px; padding: 8px 16px; background: transparent; color: var(--text-muted); border: 1px solid var(--panel-border);">Master Komponen</button>
    </div>

    <!-- TAB: DASHBOARD -->
    <div id="payroll-tab-dashboard" class="payroll-tab-content">
        <!-- Top Metrics Board -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;" id="payroll-metrics-board">
            <div style="background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0,0,0,0.05); border-radius: 16px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; color: #8E8E93; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Total Beban Gaji (Paid)</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-heading);" id="metric-total-paid">Rp 0</div>
            </div>
            <div style="background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0,0,0,0.05); border-radius: 16px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; color: #8E8E93; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Rata-rata Take Home Pay</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-heading);" id="metric-avg-thp">Rp 0</div>
            </div>
            <div style="background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0,0,0,0.05); border-radius: 16px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; color: #8E8E93; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Total Potongan / Pajak</div>
                <div style="font-size: 24px; font-weight: 700; color: #ef4444;" id="metric-total-deductions">Rp 0</div>
            </div>
        </div>

        <!-- Toolbar: Search & Filters -->
    <div style="display: flex; gap: 16px; margin-bottom: 20px; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex: 1;">
            <div style="position: relative; width: 320px;">
                <i class="fa-solid fa-search" style="position: absolute; left: 14px; top: 11px; color: #8E8E93; font-size: 14px;"></i>
                <input type="text" id="payroll-search" placeholder="Search employee..." style="width: 100%; padding: 10px 14px 10px 38px; border: none; border-radius: 12px; background: var(--panel-secondary); color: var(--text-main); font-family: inherit; font-size: 15px; outline: none; transition: box-shadow 0.2s;" onfocus="this.style.boxShadow='0 0 0 3px rgba(0, 122, 255, 0.3)'" onblur="this.style.boxShadow='none'" onkeyup="filterPayrolls()">
            </div>
            <select id="payroll-status-filter" style="padding: 10px 16px; border: none; border-radius: 12px; background: var(--panel-secondary); color: var(--text-main); font-family: inherit; font-size: 15px; outline: none; cursor: pointer;" onchange="filterPayrolls()">
                <option value="all">All Status</option>
                <option value="draft">Draft</option>
                <option value="approved">Approved</option>
                <option value="paid">Paid</option>
            </select>
        </div>
        <div style="color: var(--text-muted); font-size: 14px; font-weight: 500;" id="payroll-count-text">
            0 records
        </div>
    </div>

    <!-- Payroll Table -->
    <div style="padding: 0; overflow: hidden; box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04); border-radius: 20px; background: var(--panel); border: 1px solid rgba(0,0,0,0.05);">
        <div style="overflow-x: auto; max-height: calc(100vh - 280px); overflow-y: auto;">
            <table class="data-table ios-table" style="width: 100%; border-collapse: collapse; min-width: 900px;" id="payroll-table">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr style="background: rgba(250, 250, 250, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--panel-border);">
                        <th style="text-align: left; padding: 14px 20px; font-weight: 600; color: #8E8E93; font-size: 13px; text-transform: uppercase; cursor: pointer;" onclick="sortPayrolls('period_start')">Period <i class="fa-solid fa-sort" style="font-size: 10px; margin-left: 4px;"></i></th>
                        <th style="text-align: left; padding: 14px 20px; font-weight: 600; color: #8E8E93; font-size: 13px; text-transform: uppercase; cursor: pointer;" onclick="sortPayrolls('name')">Employee <i class="fa-solid fa-sort" style="font-size: 10px; margin-left: 4px;"></i></th>
                        <th style="text-align: right; padding: 14px 20px; font-weight: 600; color: #8E8E93; font-size: 13px; text-transform: uppercase; cursor: pointer;" onclick="sortPayrolls('base_amount')">Base Salary <i class="fa-solid fa-sort" style="font-size: 10px; margin-left: 4px;"></i></th>
                        <th style="text-align: right; padding: 14px 20px; font-weight: 600; color: #8E8E93; font-size: 13px; text-transform: uppercase; cursor: pointer;" onclick="sortPayrolls('net_amount')">Net Salary <i class="fa-solid fa-sort" style="font-size: 10px; margin-left: 4px;"></i></th>
                        <th style="text-align: center; padding: 14px 20px; font-weight: 600; color: #8E8E93; font-size: 13px; text-transform: uppercase; cursor: pointer;" onclick="sortPayrolls('status')">Status <i class="fa-solid fa-sort" style="font-size: 10px; margin-left: 4px;"></i></th>
                        <th style="text-align: right; padding: 14px 20px; font-weight: 600; color: #8E8E93; font-size: 13px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody id="payroll-table-body">
                    <tr><td colspan="6" style="text-align: center; padding: 80px; color: var(--text-muted);">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i>
                        Loading payroll data...
                    </td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-top: 1px solid var(--panel-border); background: var(--panel);">
            <div style="font-size: 14px; color: #8E8E93; font-weight: 500;" id="payroll-pagination-info">Page 1 of 1</div>
            <div style="display: flex; gap: 8px;" id="payroll-pagination-controls">
                <button class="ios-btn-icon" disabled><i class="fa-solid fa-chevron-left"></i></button>
                <button class="ios-btn-icon" disabled><i class="fa-solid fa-chevron-right"></i></button>
            </div>
    </div>
    </div> <!-- END TAB: DASHBOARD -->

    <!-- TAB: COMPONENTS -->
    <div id="payroll-tab-components" class="payroll-tab-content" style="display: none;">
        <div style="display: flex; gap: 24px; align-items: flex-start;">
            
            <!-- List Components -->
            <div style="flex: 2; padding: 24px; background: var(--panel); border: 1px solid var(--panel-border); border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 16px; font-size: 18px; color: var(--text-heading);">Daftar Komponen Gaji</h3>
                <table class="data-table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px; font-size: 14px;">
                    <thead>
                        <tr>
                            <th style="padding: 10px 16px; text-align: left; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Kode & Nama</th>
                            <th style="padding: 10px 16px; text-align: left; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Tipe</th>
                            <th style="padding: 10px 16px; text-align: left; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Default Nilai</th>
                            <th style="padding: 10px 16px; text-align: right; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $salaryComponents ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr style="background: var(--panel-secondary);">
                            <td style="padding: 14px 16px; border-radius: 12px 0 0 12px; font-weight: 600; color: var(--text-heading);">
                                <?php echo e($comp->code); ?><br>
                                <span style="font-size: 12px; font-weight: 500; color: var(--text-muted);"><?php echo e($comp->name); ?></span>
                            </td>
                            <td style="padding: 14px 16px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comp->type === 'allowance'): ?>
                                    <span class="ios-badge ios-badge-approved">Tunjangan</span>
                                <?php else: ?>
                                    <span class="ios-badge ios-badge-draft">Potongan</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comp->is_default): ?>
                                    <span class="ios-badge" style="background: var(--accent); color: white; margin-left: 4px;">Default</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; font-weight: 600;">Rp <?php echo e(number_format($comp->default_amount, 0, ',', '.')); ?></td>
                            <td style="padding: 14px 16px; border-radius: 0 12px 12px 0; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="ios-btn-icon" onclick="editSalaryComponent('<?php echo e($comp->id); ?>', '<?php echo e($comp->code); ?>', '<?php echo e($comp->name); ?>', '<?php echo e($comp->type); ?>', '<?php echo e($comp->is_default); ?>', '<?php echo e($comp->default_amount); ?>')"><i class="fa-solid fa-pen"></i></button>
                                    <form method="POST" action="/master-demo/salary-components/<?php echo e($comp->id); ?>" onsubmit="return confirm('Hapus komponen ini?');" style="margin: 0;">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="ios-btn-icon" style="color: #ef4444;"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: var(--text-muted);">Belum ada komponen gaji.</td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Form Components -->
            <div style="flex: 1; padding: 24px; background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.08);">
                <h3 style="margin: 0 0 16px; font-size: 16px; color: var(--text-heading);" id="salcomp-title">Tambah Komponen Baru</h3>
                <form id="salcomp-form" method="POST" action="<?php echo e(route('master-demo.salary-components.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="salcomp-method" value="POST">
                    
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px;">Kode Komponen</label>
                        <input type="text" id="salcomp-code" name="code" class="ios-input" placeholder="Ex: T01, PPH21" required>
                    </div>
                    
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px;">Nama Komponen</label>
                        <input type="text" id="salcomp-name" name="name" class="ios-input" placeholder="Ex: Tunjangan Makan" required>
                    </div>
                    
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px;">Tipe</label>
                        <select id="salcomp-type" name="type" class="ios-input" required>
                            <option value="allowance">Tunjangan (+)</option>
                            <option value="deduction">Potongan (-)</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px;">Nilai Default / Flat (Rp)</label>
                        <input type="number" id="salcomp-amount" name="default_amount" class="ios-input" value="0" required>
                    </div>
                    
                    <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="salcomp-default" name="is_default" style="width: 18px; height: 18px;">
                        <label for="salcomp-default" style="font-size: 14px; font-weight: 500; color: var(--text-heading);">Berlaku otomatis ke semua?</label>
                    </div>
                    
                    <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%; justify-content: center;">Simpan Komponen</button>
                    <button type="button" id="salcomp-reset" class="ios-btn ios-btn-secondary" style="width: 100%; justify-content: center; margin-top: 8px; display: none;" onclick="resetSalaryComponent()">Batal Edit</button>
                </form>
            </div>
            
        </div>
    </div> <!-- END TAB: COMPONENTS -->
</div>

<!-- Generate Payroll Modal -->
<div id="payroll-generate-modal" class="modal ios-modal-overlay" style="display:none;">
    <div class="modal-content ios-modal">
        <div class="ios-modal-header">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: var(--text-heading);">
                Generate Monthly Payroll
            </h3>
            <button type="button" class="ios-btn-close" onclick="document.getElementById('payroll-generate-modal').style.display='none'"><i class="fa-solid fa-times"></i></button>
        </div>
        
        <form id="payroll-generate-form" onsubmit="submitPayrollGenerate(event)">
            <div class="ios-modal-body">
                <p style="font-size: 15px; color: var(--text-muted); margin-top: 0; margin-bottom: 24px; line-height: 1.5;">Select the period to automatically calculate overtime, unpaid leaves, and generate draft payrolls for all active employees.</p>
                
                <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                    <div style="flex:1;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px; text-transform: uppercase;">Start Date</label>
                        <input type="date" name="period_start" required class="ios-input">
                    </div>
                    <div style="flex:1;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px; text-transform: uppercase;">End Date</label>
                        <input type="date" name="period_end" required class="ios-input">
                    </div>
                </div>
            </div>
            <div class="ios-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="document.getElementById('payroll-generate-modal').style.display='none'">Cancel</button>
                <button type="submit" class="ios-btn ios-btn-primary"><i class="fa-solid fa-bolt" style="margin-right: 6px;"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Payroll Modal -->
<div id="payroll-edit-modal" class="modal ios-modal-overlay" style="display:none;">
    <div class="modal-content ios-modal" style="width: 550px;">
        <div class="ios-modal-header">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: var(--text-heading);">
                Edit Draft Payroll
            </h3>
            <button type="button" class="ios-btn-close" onclick="document.getElementById('payroll-edit-modal').style.display='none'"><i class="fa-solid fa-times"></i></button>
        </div>
        
        <form id="payroll-edit-form" onsubmit="submitPayrollEdit(event)">
            <input type="hidden" id="edit-payroll-id" name="id">
            <div class="ios-modal-body" style="max-height: 60vh; overflow-y: auto;">
                <p style="font-size: 15px; color: var(--text-muted); margin-top: 0; margin-bottom: 24px;">Adjust the base salary or add/edit components. Net salary is recalculated automatically.</p>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 6px; text-transform: uppercase;">Base Amount (IDR)</label>
                    <input type="number" id="edit-base-amount" name="base_amount" required class="ios-input" min="0" oninput="calculateLiveEdit()">
                </div>

                <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #8E8E93; margin-bottom: 0; text-transform: uppercase;">Komponen Gaji</label>
                    <button type="button" class="ios-btn-icon" style="width: 28px; height: 28px; font-size: 12px; background: var(--accent); color: white;" onclick="addEditItemRow()"><i class="fa-solid fa-plus"></i></button>
                </div>
                
                <div id="edit-items-container" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                    <!-- Items injected via JS -->
                </div>

                <div class="ios-data-card" style="background: rgba(0,122,255,0.05); border: 1px solid rgba(0,122,255,0.1); margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #007AFF; font-weight: 700; font-size: 16px;">Live Net Take Home Pay</span>
                        <span style="color: #007AFF; font-weight: 800; font-size: 20px;" id="edit-live-net">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="ios-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="document.getElementById('payroll-edit-modal').style.display='none'">Cancel</button>
                <button type="submit" class="ios-btn ios-btn-primary"><i class="fa-solid fa-save" style="margin-right: 6px;"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Payroll Detail Drawer -->
<div id="payroll-detail-modal" class="modal ios-modal-overlay" style="display:none; justify-content: flex-end; padding: 0;">
    <div class="modal-content ios-modal" style="width: 500px; height: 100vh; border-radius: 24px 0 0 24px; animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        <div class="ios-modal-header" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(20px);">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: var(--text-heading); letter-spacing: -0.5px;">
                Payroll Statement
            </h3>
            <button type="button" class="ios-btn-close" onclick="document.getElementById('payroll-detail-modal').style.display='none'"><i class="fa-solid fa-times"></i></button>
        </div>
        
        <div id="payroll-detail-body" class="ios-modal-body" style="overflow-y: auto; flex: 1; padding: 24px; background: #f8f9fa;">
            <!-- Content injected via JS -->
        </div>
        
        <div id="payroll-detail-footer" class="ios-modal-footer" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(20px); border-top: 1px solid var(--panel-border);">
            <!-- Actions injected via JS -->
            <button type="button" class="ios-btn ios-btn-secondary" onclick="document.getElementById('payroll-detail-modal').style.display='none'">Close</button>
        </div>
    </div>
</div>
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>

<!-- iOS Alert Dialog -->
<div id="ios-alert-dialog" class="ios-dialog-overlay" style="display:none;">
    <div class="ios-dialog">
        <div class="ios-dialog-body">
            <h3 id="ios-alert-title">Title</h3>
            <p id="ios-alert-message">Message</p>
        </div>
        <div class="ios-dialog-footer">
            <button id="ios-alert-ok" class="ios-dialog-btn ios-dialog-btn-bold">OK</button>
        </div>
    </div>
</div>

<!-- iOS Confirm Dialog -->
<div id="ios-confirm-dialog" class="ios-dialog-overlay" style="display:none;">
    <div class="ios-dialog">
        <div class="ios-dialog-body">
            <h3 id="ios-confirm-title">Title</h3>
            <p id="ios-confirm-message">Message</p>
        </div>
        <div class="ios-dialog-footer">
            <button id="ios-confirm-cancel" class="ios-dialog-btn" style="border-right: 1px solid rgba(0,0,0,0.1);">Cancel</button>
            <button id="ios-confirm-ok" class="ios-dialog-btn ios-dialog-btn-bold">OK</button>
        </div>
    </div>
</div>

<style>
    /* iOS Design Language Styles */
    .ios-design { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, sans-serif; }
    
    @keyframes iosFadeIn {
        from { opacity: 0; transform: translateY(10px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    @keyframes iosModalShow {
        from { opacity: 0; transform: scale(1.1); }
        to { opacity: 1; transform: scale(1); }
    }
    
    @keyframes iosBackdropFade {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .ios-table tbody tr { transition: background-color 0.15s ease; border-bottom: 1px solid var(--panel-border); }
    .ios-table tbody tr:hover { background-color: var(--panel-secondary); }
    
    .ios-btn {
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: -0.2px;
    }
    .ios-btn:active { transform: scale(0.96); }
    
    .ios-btn-primary { background: var(--accent); color: var(--text-accent); box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.3); }
    .ios-btn-primary:hover { background: var(--accent-hover); }
    
    .ios-btn-secondary { background: var(--secondary-surface); color: var(--accent); }
    .ios-btn-secondary:hover { background: #c2ded7; }
    
    .ios-btn-success { background: #34C759; color: white; box-shadow: 0 4px 12px rgba(52, 199, 89, 0.3); }
    .ios-btn-success:hover { background: #2DAE4E; }
    
    .ios-btn-danger { background: #FF3B30; color: var(--text-accent); box-shadow: 0 4px 12px rgba(255, 59, 48, 0.3); }
    .ios-btn-danger:hover { background: #D93229; }
    
    .ios-btn-ghost { background: transparent; color: var(--accent); padding: 8px 16px; }
    .ios-btn-ghost:hover { background: rgba(var(--accent-rgb), 0.1); }
    
    .ios-btn-ghost-danger { background: transparent; color: #FF3B30; padding: 8px 16px; }
    .ios-btn-ghost-danger:hover { background: rgba(255, 59, 48, 0.1); }
    
    .ios-btn-ghost-warning { background: transparent; color: #FF9500; padding: 8px 16px; }
    .ios-btn-ghost-warning:hover { background: rgba(255, 149, 0, 0.1); }

    .ios-btn-icon {
        width: 36px; height: 36px; border-radius: 50%; border: none; background: var(--panel-secondary);
        color: var(--text-heading); cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .ios-btn-icon:hover:not(:disabled) { background: #E5E5EA; }
    .ios-btn-icon:disabled { opacity: 0.5; cursor: not-allowed; }

    .ios-modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
        background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        z-index: 1000; display: flex; align-items: center; justify-content: center;
        animation: iosBackdropFade 0.3s ease-out;
    }
    
    .ios-modal {
        background: var(--panel); border-radius: 24px; box-shadow: 0 24px 48px rgba(0,0,0,0.15);
        width: 450px; overflow: hidden; animation: iosModalShow 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column;
    }
    
    .ios-modal-header {
        padding: 24px; text-align: center; position: relative; border-bottom: 1px solid var(--panel-border);
    }
    
    .ios-btn-close {
        position: absolute; right: 20px; top: 20px; width: 30px; height: 30px;
        border-radius: 50%; border: none; background: var(--panel-secondary); color: #8E8E93;
        font-size: 14px; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center;
    }
    .ios-btn-close:hover { background: #D1D1D6; color: var(--text-heading); }
    
    .ios-modal-body { padding: 24px; flex: 1; }
    
    .ios-modal-footer {
        padding: 16px 24px 24px; display: flex; gap: 12px; justify-content: flex-end;
    }
    
    .ios-input {
        width: 100%; padding: 12px 16px; border: 1px solid var(--panel-border); border-radius: 12px;
        background: var(--panel-secondary); color: var(--text-heading); font-family: inherit; font-size: 15px;
        transition: all 0.2s; outline: none; box-sizing: border-box;
    }
    .ios-input:focus { background: var(--panel); border-color: var(--accent); box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.15); }
    
    .ios-badge {
        display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px;
        font-size: 12px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;
    }
    .ios-badge-draft { background: rgba(255, 149, 0, 0.15); color: #FF9500; }
    .ios-badge-approved { background: rgba(52, 199, 89, 0.15); color: #34C759; }
    .ios-badge-paid { background: rgba(var(--accent-rgb), 0.15); color: var(--accent); }
    
    .ios-data-card {
        background: var(--panel-secondary); border-radius: 16px; padding: 16px; margin-bottom: 16px;
    }

    /* System Dialogs (Alert & Confirm) */
    .ios-dialog-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
        background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);
        z-index: 10000; display: flex; align-items: center; justify-content: center;
        animation: iosBackdropFade 0.2s ease-out; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, sans-serif;
    }
    .ios-dialog {
        background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        border-radius: 14px; width: 270px; text-align: center;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        animation: iosModalShow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .ios-dialog-body { padding: 20px 16px 16px; }
    .ios-dialog-body h3 { margin: 0 0 4px; font-size: 17px; font-weight: 600; color: #000; letter-spacing: -0.4px; }
    .ios-dialog-body p { margin: 0; font-size: 13px; font-weight: 400; color: #000; line-height: 1.3; }
    .ios-dialog-footer { border-top: 1px solid rgba(0,0,0,0.1); display: flex; width: 100%; }
    .ios-dialog-btn { 
        flex: 1; padding: 12px; background: transparent; border: none; 
        font-size: 17px; color: var(--accent); cursor: pointer; letter-spacing: -0.4px;
        transition: background 0.1s;
    }
    .ios-dialog-btn:active { background: rgba(0,0,0,0.05); }
    .ios-dialog-btn-bold { font-weight: 600; }
    .ios-dialog-btn-danger { color: #FF3B30; }
</style>

<script>
    // iOS System Dialog Promisified Methods
    function iosAlert(title, message) {
        return new Promise(resolve => {
            const overlay = document.getElementById('ios-alert-dialog');
            document.getElementById('ios-alert-title').innerText = title;
            document.getElementById('ios-alert-message').innerText = message;
            overlay.style.display = 'flex';
            
            const btn = document.getElementById('ios-alert-ok');
            btn.onclick = () => {
                overlay.style.display = 'none';
                resolve();
            };
        });
    }

    function iosConfirm(title, message, isDestructive = false) {
        return new Promise(resolve => {
            const overlay = document.getElementById('ios-confirm-dialog');
            document.getElementById('ios-confirm-title').innerText = title;
            document.getElementById('ios-confirm-message').innerText = message;
            
            const btnOk = document.getElementById('ios-confirm-ok');
            if (isDestructive) {
                btnOk.classList.add('ios-dialog-btn-danger');
            } else {
                btnOk.classList.remove('ios-dialog-btn-danger');
            }
            
            overlay.style.display = 'flex';
            
            document.getElementById('ios-confirm-cancel').onclick = () => {
                overlay.style.display = 'none';
                resolve(false);
            };
            btnOk.onclick = () => {
                overlay.style.display = 'none';
                resolve(true);
            };
        });
    }


    function switchPayrollTab(tab) {
        document.querySelectorAll('.payroll-tab-content').forEach(el => el.style.display = 'none');
        document.getElementById('payroll-tab-' + tab).style.display = 'block';
        
        document.getElementById('btn-tab-dashboard').className = tab === 'dashboard' ? 'ios-btn ios-btn-primary' : 'ios-btn ios-btn-secondary';
        document.getElementById('btn-tab-components').className = tab === 'components' ? 'ios-btn ios-btn-primary' : 'ios-btn ios-btn-secondary';
        
        if(tab === 'dashboard') {
            document.getElementById('btn-tab-dashboard').style.background = 'var(--accent)';
            document.getElementById('btn-tab-dashboard').style.color = 'white';
            document.getElementById('btn-tab-components').style.background = 'transparent';
            document.getElementById('btn-tab-components').style.color = 'var(--text-muted)';
        } else {
            document.getElementById('btn-tab-components').style.background = 'var(--accent)';
            document.getElementById('btn-tab-components').style.color = 'white';
            document.getElementById('btn-tab-dashboard').style.background = 'transparent';
            document.getElementById('btn-tab-dashboard').style.color = 'var(--text-muted)';
        }
    }

    function editSalaryComponent(id, code, name, type, isDefault, amount) {
        document.getElementById('salcomp-title').innerText = 'Edit Komponen Gaji';
        const form = document.getElementById('salcomp-form');
        form.action = `/master-demo/salary-components/${id}`;
        document.getElementById('salcomp-method').value = 'PUT';
        
        document.getElementById('salcomp-code').value = code;
        document.getElementById('salcomp-name').value = name;
        document.getElementById('salcomp-type').value = type;
        document.getElementById('salcomp-amount').value = amount;
        document.getElementById('salcomp-default').checked = (isDefault == 1 || isDefault === '1');
        
        document.getElementById('salcomp-reset').style.display = 'flex';
    }
    
    function resetSalaryComponent() {
        document.getElementById('salcomp-title').innerText = 'Tambah Komponen Baru';
        const form = document.getElementById('salcomp-form');
        form.action = `/master-demo/salary-components`;
        document.getElementById('salcomp-method').value = 'POST';
        form.reset();
        document.getElementById('salcomp-reset').style.display = 'none';
    }


    let payrollState = {
        can_manage: false,
        payrolls: [],
        filtered: [],
        currentPage: 1,
        perPage: 15,
        sortCol: 'period_start',
        sortDesc: true
    };

    function loadPayrolls() {
        const tbody = document.getElementById('payroll-table-body');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 80px; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i><div style="font-size:15px; font-weight:500;">Loading payroll data...</div></td></tr>';
        
        const apiPrefix = window.location.pathname.includes('/master-demo') ? '/master-demo' : '';
        fetch(`${apiPrefix}/payroll`)
            .then(res => res.json())
            .then(data => {
                payrollState.can_manage = data.can_manage;
                payrollState.payrolls = data.payrolls;
                filterPayrolls();
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 80px; color: #FF3B30;"><i class="fa-solid fa-exclamation-circle" style="font-size:32px; margin-bottom:16px;"></i><br><div style="font-size:15px; font-weight:500;">Failed to load data.</div></td></tr>';
            });
    }

    function sortPayrolls(col) {
        if (payrollState.sortCol === col) {
            payrollState.sortDesc = !payrollState.sortDesc;
        } else {
            payrollState.sortCol = col;
            payrollState.sortDesc = false;
        }
        filterPayrolls();
    }

    function filterPayrolls() {
        const search = document.getElementById('payroll-search').value.toLowerCase();
        const status = document.getElementById('payroll-status-filter').value;
        
        let filtered = payrollState.payrolls.filter(p => {
            const matchSearch = p.user && p.user.name.toLowerCase().includes(search);
            const matchStatus = status === 'all' || p.status === status;
            return matchSearch && matchStatus;
        });
        
        // Sorting
        filtered.sort((a, b) => {
            let valA, valB;
            if (payrollState.sortCol === 'name') {
                valA = a.user ? a.user.name : '';
                valB = b.user ? b.user.name : '';
            } else {
                valA = a[payrollState.sortCol];
                valB = b[payrollState.sortCol];
            }
            
            if (valA < valB) return payrollState.sortDesc ? 1 : -1;
            if (valA > valB) return payrollState.sortDesc ? -1 : 1;
            return 0;
        });
        
        payrollState.filtered = filtered;
        payrollState.currentPage = 1;
        renderPayrollUI();
    }

    function changePage(delta) {
        const maxPage = Math.ceil(payrollState.filtered.length / payrollState.perPage);
        let newPage = payrollState.currentPage + delta;
        if (newPage >= 1 && newPage <= maxPage) {
            payrollState.currentPage = newPage;
            renderPayrollUI();
        }
    }

    function renderPayrollUI() {
        // Calculate Metrics
        let totalPaid = 0;
        let totalThp = 0;
        let thpCount = 0;
        let totalDed = 0;
        
        payrollState.payrolls.forEach(p => {
            if (p.status === 'paid') totalPaid += parseFloat(p.net_amount);
            totalThp += parseFloat(p.net_amount);
            thpCount++;
            if (p.total_deductions) totalDed += parseFloat(p.total_deductions);
            else if (p.items) {
                p.items.forEach(item => {
                    if (item.type === 'deduction') totalDed += parseFloat(item.amount);
                });
            }
        });
        
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
        document.getElementById('metric-total-paid').innerText = formatter.format(totalPaid);
        document.getElementById('metric-avg-thp').innerText = thpCount > 0 ? formatter.format(totalThp / thpCount) : 'Rp 0';
        document.getElementById('metric-total-deductions').innerText = formatter.format(totalDed);

        // Render Actions Toolbar
        const actionsContainer = document.getElementById('payroll-actions-container');
        if (payrollState.can_manage) {
            actionsContainer.innerHTML = `<button class="ios-btn ios-btn-primary" onclick="document.getElementById('payroll-generate-modal').style.display='flex'"><i class="fa-solid fa-plus" style="margin-right:6px;"></i> Generate Payroll</button>`;
        } else {
            actionsContainer.innerHTML = '';
        }

        const tbody = document.getElementById('payroll-table-body');
        const countText = document.getElementById('payroll-count-text');
        
        countText.innerHTML = `${payrollState.filtered.length} records`;

        if (payrollState.filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 100px 20px;">
                <div style="font-size: 64px; color: #E5E5EA; margin-bottom: 24px;"><i class="fa-solid fa-folder-open"></i></div>
                <div style="font-size: 20px; font-weight: 600; color: var(--text-heading); margin-bottom: 8px;">No Payrolls Found</div>
                <div style="font-size: 15px; color: #8E8E93; margin-bottom: 24px;">There are no records matching your current criteria.</div>
                ${payrollState.can_manage ? `<button class="ios-btn ios-btn-primary" onclick="document.getElementById('payroll-generate-modal').style.display='flex'"><i class="fa-solid fa-plus" style="margin-right:6px;"></i> Create New</button>` : ''}
            </td></tr>`;
            document.getElementById('payroll-pagination-info').innerText = `Page 1 of 1`;
            document.getElementById('payroll-pagination-controls').innerHTML = `
                <button class="ios-btn-icon" disabled><i class="fa-solid fa-chevron-left"></i></button>
                <button class="ios-btn-icon" disabled><i class="fa-solid fa-chevron-right"></i></button>`;
        } else {
            const startIdx = (payrollState.currentPage - 1) * payrollState.perPage;
            const pageData = payrollState.filtered.slice(startIdx, startIdx + payrollState.perPage);
            
            let html = '';
            pageData.forEach(p => {
                let statusBadge = '';
                if (p.status === 'draft') statusBadge = '<span class="ios-badge ios-badge-draft">Draft</span>';
                if (p.status === 'verified') statusBadge = '<span class="ios-badge" style="background: rgba(0,122,255,0.15); color: #007AFF;">Verified</span>';
                if (p.status === 'approved') statusBadge = '<span class="ios-badge ios-badge-approved">Approved</span>';
                if (p.status === 'paid') statusBadge = '<span class="ios-badge ios-badge-paid">Paid</span>';

                let actions = '';
                if (payrollState.can_manage) {
                    if (p.status === 'draft') {
                        actions += `<button class="ios-btn ios-btn-ghost" onclick="openEditModal('${p.id}', ${p.base_amount})"><i class="fa-solid fa-pen"></i></button>`;
                        actions += `<button class="ios-btn ios-btn-ghost" style="color: #007AFF;" onclick="actionPayroll('${p.id}', 'verify', 'Verify')"><i class="fa-solid fa-user-check"></i></button>`;
                        actions += `<button class="ios-btn ios-btn-ghost-danger" onclick="actionPayroll('${p.id}', 'delete', 'Delete', 'DELETE')"><i class="fa-solid fa-trash"></i></button>`;
                    }
                    if (p.status === 'verified') {
                        actions += `<button class="ios-btn ios-btn-ghost" onclick="viewPayrollDetail('${p.id}')"><i class="fa-solid fa-eye"></i></button>`;
                        actions += `<button class="ios-btn ios-btn-ghost" style="color: #34C759;" onclick="actionPayroll('${p.id}', 'approve', 'Approve')"><i class="fa-solid fa-check-double"></i></button>`;
                    }
                    if (p.status === 'approved') {
                        actions += `<button class="ios-btn ios-btn-ghost" onclick="viewPayrollDetail('${p.id}')"><i class="fa-solid fa-eye"></i></button>`;
                        actions += `<button class="ios-btn ios-btn-ghost-warning" onclick="iosAlert('Unavailable', 'Reject feature under construction')"><i class="fa-solid fa-times"></i></button>`;
                        actions += `<button class="ios-btn ios-btn-success" style="padding: 6px 14px; margin-left: 8px;" onclick="actionPayroll('${p.id}', 'pay', 'Disburse')"><i class="fa-solid fa-paper-plane" style="margin-right:6px;"></i> Pay</button>`;
                    }
                }
                
                if (p.status === 'paid') {
                    actions += `<button class="ios-btn ios-btn-ghost" onclick="viewPayrollDetail('${p.id}')"><i class="fa-solid fa-eye"></i></button>`;
                    actions += `<button class="ios-btn ios-btn-secondary" style="padding: 6px 14px; margin-left: 8px; font-size: 13px;" onclick="iosAlert('Unavailable', 'PDF Generation under construction')"><i class="fa-solid fa-file-pdf" style="margin-right:6px; color:#FF3B30;"></i> PDF</button>`;
                }

                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
                const dStart = new Date(p.period_start).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                const dEnd = new Date(p.period_end).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                
                html += `
                <tr>
                    <td style="padding: 16px 20px; font-size: 14px; color: var(--text-heading); font-weight: 500;">${dStart} - ${dEnd}</td>
                    <td style="padding: 16px 20px;">
                        <div style="font-weight: 600; color: var(--text-heading); font-size: 15px;">${p.user ? p.user.name : '-'}</div>
                        <div style="font-size: 13px; color: #8E8E93; margin-top: 4px;">${p.user ? p.user.job_title : ''}</div>
                    </td>
                    <td style="padding: 16px 20px; text-align: right; font-size: 15px; color: #8E8E93;">${formatter.format(p.base_amount)}</td>
                    <td style="padding: 16px 20px; text-align: right; font-weight: 700; font-size: 16px; color: var(--text-heading);">${formatter.format(p.net_amount)}</td>
                    <td style="padding: 16px 20px; text-align: center;">${statusBadge}</td>
                    <td style="padding: 16px 20px; text-align: right; white-space: nowrap;">${actions}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
            
            const maxPage = Math.ceil(payrollState.filtered.length / payrollState.perPage);
            document.getElementById('payroll-pagination-info').innerText = `Page ${payrollState.currentPage} of ${maxPage}`;
            document.getElementById('payroll-pagination-controls').innerHTML = `
                <button class="ios-btn-icon" ${payrollState.currentPage === 1 ? 'disabled' : ''} onclick="changePage(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="ios-btn-icon" ${payrollState.currentPage === maxPage ? 'disabled' : ''} onclick="changePage(1)"><i class="fa-solid fa-chevron-right"></i></button>`;
        }
    }

    function viewPayrollDetail(id) {
        const p = payrollState.payrolls.find(x => x.id == id);
        if(!p) return;
        
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
        const dStart = new Date(p.period_start).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
        const dEnd = new Date(p.period_end).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
        
        let itemsHtml = '';
        let allowances = 0;
        let deductions = 0;
        
        if (p.items && p.items.length > 0) {
            itemsHtml = `<div class="ios-data-card" style="margin-top: 16px;">
            <h4 style="margin: 0 0 12px; font-size: 13px; color: #8E8E93; text-transform: uppercase;">Earnings & Deductions</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">`;
            
            p.items.forEach((item, idx) => {
                const isDed = item.type === 'deduction';
                if(isDed) deductions += parseFloat(item.amount);
                else allowances += parseFloat(item.amount);
                const borderTop = idx > 0 ? 'border-top: 1px solid rgba(0,0,0,0.05);' : '';
                itemsHtml += `<tr>
                    <td style="padding: 10px 0; color: var(--text-heading); ${borderTop}">${item.description}</td>
                    <td style="padding: 10px 0; text-align: right; color: ${isDed ? '#FF3B30' : '#34C759'}; font-weight: 600; ${borderTop}">
                        ${isDed ? '-' : '+'}${formatter.format(item.amount)}
                    </td>
                </tr>`;
            });
            itemsHtml += `</table></div>`;
        }

        let timelineHtml = `
        <div class="ios-data-card" style="margin-top: 24px;">
            <h4 style="margin: 0 0 16px; font-size: 13px; color: #8E8E93; text-transform: uppercase; font-weight: 700;">Approval Timeline</h4>
            
            <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px; position: relative;">
                <div style="position: absolute; left: 15px; top: 32px; bottom: -20px; width: 2px; background: #E5E5EA; z-index: 0;"></div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; z-index: 1;"><i class="fa-solid fa-file-invoice"></i></div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-heading);">Draft Generated</div>
                    <div style="font-size: 13px; color: #8E8E93; margin-top: 4px;">${new Date(p.created_at).toLocaleString()}</div>
                </div>
            </div>
            
            ${['verified', 'approved', 'paid'].includes(p.status) ? `
            <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px; position: relative;">
                <div style="position: absolute; left: 15px; top: 32px; bottom: -20px; width: 2px; background: #E5E5EA; z-index: 0;"></div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #007AFF; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; z-index: 1;"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-heading);">Verified by HR</div>
                    <div style="font-size: 13px; color: #8E8E93; margin-top: 4px;">Data has been cross-checked.</div>
                </div>
            </div>` : ''}

            ${p.approved_by ? `
            <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px; position: relative;">
                ${p.status === 'paid' ? '<div style="position: absolute; left: 15px; top: 32px; bottom: -20px; width: 2px; background: #E5E5EA; z-index: 0;"></div>' : ''}
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #34C759; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; z-index: 1;"><i class="fa-solid fa-check-double"></i></div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-heading);">Approved by ${p.approver ? p.approver.name : 'CEO'}</div>
                    <div style="font-size: 13px; color: #8E8E93; margin-top: 4px;">Approval completed successfully.</div>
                </div>
            </div>` : ''}
            
            ${p.status === 'paid' ? `
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #FF9500; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; z-index: 1;"><i class="fa-solid fa-money-bill-transfer"></i></div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-heading);">Disbursed / Paid</div>
                    <div style="font-size: 13px; color: #8E8E93; margin-top: 4px;">${new Date(p.paid_at).toLocaleString()}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; padding: 4px 8px; background: rgba(0,0,0,0.05); border-radius: 4px; display: inline-block;"><i class="fa-solid fa-book-journal-whills" style="margin-right: 4px;"></i> Auto-Journaled to Accounting</div>
                </div>
            </div>` : ''}
        </div>`;

        const bodyHtml = `
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--panel-secondary); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--accent); margin: 0 auto 12px;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div style="font-size: 22px; font-weight: 700; color: var(--text-heading); letter-spacing: -0.5px;">${p.user.name}</div>
                <div style="font-size: 15px; color: #8E8E93; margin-top: 4px;">${p.user.job_title}</div>
            </div>
            
            <div class="ios-data-card" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 13px; color: #8E8E93; text-transform: uppercase; font-weight: 600;">Payroll Period</div>
                    <div style="font-size: 16px; font-weight: 600; color: var(--text-heading); margin-top: 4px;">${dStart} - ${dEnd}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 13px; color: #8E8E93; text-transform: uppercase; font-weight: 600;">Status</div>
                    <div style="font-size: 16px; font-weight: 600; color: var(--text-heading); margin-top: 4px; text-transform: capitalize;">${p.status}</div>
                </div>
            </div>
            
            <div class="ios-data-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-heading); font-weight: 600; font-size: 16px;">Base Salary</span>
                    <span style="color: var(--text-heading); font-weight: 700; font-size: 16px;">${formatter.format(p.base_amount)}</span>
                </div>
            </div>
            ${itemsHtml}
            <div class="ios-data-card" style="background: rgba(0,122,255,0.05); border: 1px solid rgba(0,122,255,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #007AFF; font-weight: 700; font-size: 18px;">Net Take Home Pay</span>
                    <span style="color: #007AFF; font-weight: 800; font-size: 22px;">${formatter.format(p.net_amount)}</span>
                </div>
            </div>
            ${timelineHtml}
        `;
        
        document.getElementById('payroll-detail-body').innerHTML = bodyHtml;
        
        let footerHtml = `<button type="button" class="ios-btn ios-btn-secondary" onclick="document.getElementById('payroll-detail-modal').style.display='none'">Close</button>`;
        if(p.status === 'paid') {
            footerHtml = `<button class="ios-btn ios-btn-primary" onclick="iosAlert('PDF Generator', 'Downloading iOS style PDF...')"><i class="fa-solid fa-arrow-down" style="margin-right:6px;"></i> Download PDF</button>` + footerHtml;
        }
        document.getElementById('payroll-detail-footer').innerHTML = footerHtml;

        document.getElementById('payroll-detail-modal').style.display = 'flex';
    }

    function openEditModal(id, baseAmount) {
        document.getElementById('edit-payroll-id').value = id;
        document.getElementById('edit-base-amount').value = baseAmount;
        
        const p = payrollState.payrolls.find(x => x.id == id);
        const container = document.getElementById('edit-items-container');
        container.innerHTML = '';
        
        if (p && p.items) {
            p.items.forEach(item => {
                addEditItemRow(item.description, item.type, item.amount);
            });
        }
        
        calculateLiveEdit();
        document.getElementById('payroll-edit-modal').style.display = 'flex';
    }

    function addEditItemRow(description = '', type = 'allowance', amount = 0) {
        const container = document.getElementById('edit-items-container');
        const div = document.createElement('div');
        div.style.cssText = "display: flex; gap: 8px; align-items: center; background: var(--panel-secondary); padding: 8px; border-radius: 12px;";
        div.className = 'edit-item-row';
        
        div.innerHTML = `
            <input type="text" class="ios-input item-desc" placeholder="Nama Komponen" value="${description}" style="flex: 2; padding: 8px 12px;" required>
            <select class="ios-input item-type" style="flex: 1; padding: 8px 12px;" onchange="calculateLiveEdit()">
                <option value="allowance" ${type === 'allowance' ? 'selected' : ''}>+ Tunjangan</option>
                <option value="deduction" ${type === 'deduction' ? 'selected' : ''}>- Potongan</option>
            </select>
            <input type="number" class="ios-input item-amount" value="${amount}" min="0" style="flex: 1.5; padding: 8px 12px;" oninput="calculateLiveEdit()" required>
            <button type="button" class="ios-btn-icon" style="color: #ef4444;" onclick="this.parentElement.remove(); calculateLiveEdit();"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(div);
    }

    function calculateLiveEdit() {
        let base = parseFloat(document.getElementById('edit-base-amount').value) || 0;
        const rows = document.querySelectorAll('.edit-item-row');
        let totalAllowances = 0;
        let totalDeductions = 0;
        
        rows.forEach(row => {
            const type = row.querySelector('.item-type').value;
            const amount = parseFloat(row.querySelector('.item-amount').value) || 0;
            if (type === 'allowance') totalAllowances += amount;
            else totalDeductions += amount;
        });
        
        const net = base + totalAllowances - totalDeductions;
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
        document.getElementById('edit-live-net').innerText = formatter.format(net);
    }

    async function submitPayrollEdit(e) {
        e.preventDefault();
        const id = document.getElementById('edit-payroll-id').value;
        const baseAmount = document.getElementById('edit-base-amount').value;
        
        const items = [];
        document.querySelectorAll('.edit-item-row').forEach(row => {
            items.push({
                description: row.querySelector('.item-desc').value,
                type: row.querySelector('.item-type').value,
                amount: row.querySelector('.item-amount').value
            });
        });
        
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        
        try {
            const apiPrefix = window.location.pathname.includes('/master-demo') ? '/master-demo' : '';
            const res = await fetch(`${apiPrefix}/payroll/${id}`, { 
                method: 'PUT', 
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ base_amount: baseAmount, items: items })
            });
            if (!res.ok) throw new Error(await res.text());
            await iosAlert('Success', 'Payroll updated successfully!');
            document.getElementById('payroll-edit-modal').style.display='none';
            loadPayrolls();
        } catch (err) { 
            await iosAlert('Error', err.message); 
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    async function submitPayrollGenerate(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;
        
        try {
            const apiPrefix = window.location.pathname.includes('/master-demo') ? '/master-demo' : '';
            const res = await fetch(`${apiPrefix}/payroll/generate`, { method: 'POST', body: formData, headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            await iosAlert('Success', 'Payroll Drafts Generated Successfully!');
            document.getElementById('payroll-generate-modal').style.display='none';
            e.target.reset();
            loadPayrolls();
        } catch (err) { 
            await iosAlert('Error', err.message); 
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    async function actionPayroll(id, action, label, method = 'POST') {
        const isDestructive = method === 'DELETE';
        const confirmed = await iosConfirm(label, `Are you sure you want to ${label.toLowerCase()} this payroll?`, isDestructive);
        if (!confirmed) return;
        
        try {
            const apiPrefix = window.location.pathname.includes('/master-demo') ? '/master-demo' : '';
            const res = await fetch(`${apiPrefix}/payroll/${id}${method === 'POST' ? '/' + action : ''}`, { method: method, headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            await iosAlert('Success', `Payroll ${label.toLowerCase()}d successfully!`);
            loadPayrolls();
        } catch (err) { 
            await iosAlert('Error', err.message); 
        }
    }

</script>

<?php /**PATH D:\suba-erp-master-local-latest\resources\views\payroll\index.blade.php ENDPATH**/ ?>