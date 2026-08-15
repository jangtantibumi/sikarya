<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom ke crm_customers
        Schema::table('crm_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_customers', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('crm_customers', 'blacklist_reason')) {
                $table->text('blacklist_reason')->nullable()->after('is_blacklisted');
            }
            if (!Schema::hasColumn('crm_customers', 'referral_code')) {
                $table->string('referral_code')->nullable()->unique()->after('customer_code');
            }
            if (!Schema::hasColumn('crm_customers', 'referred_by_id')) {
                $table->foreignId('referred_by_id')->nullable()->constrained('crm_customers')->nullOnDelete()->after('referral_code');
            }
            if (!Schema::hasColumn('crm_customers', 'segment_id')) {
                $table->unsignedBigInteger('segment_id')->nullable()->after('membership_level');
            }
        });

        // 2. crm_tags & crm_customer_tag
        if (!Schema::hasTable('crm_tags')) {
            Schema::create('crm_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->default('#3B82F6'); // Tailwind hex
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_customer_tag')) {
            Schema::create('crm_customer_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('crm_customers')->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained('crm_tags')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 3. crm_segments
        if (!Schema::hasTable('crm_segments')) {
            Schema::create('crm_segments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('rules')->nullable(); // e.g. min_spending, min_points, membership_level
                $table->timestamps();
            });
        }

        // 4. crm_campaigns
        if (!Schema::hasTable('crm_campaigns')) {
            Schema::create('crm_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->enum('channel', ['whatsapp', 'email', 'broadcast'])->default('whatsapp');
                $table->enum('target_type', ['all', 'segment', 'tag'])->default('all');
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('subject')->nullable(); // for email
                $table->text('message_body');
                $table->enum('status', ['draft', 'scheduled', 'sent'])->default('draft');
                $table->dateTime('scheduled_at')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. crm_broadcast_logs
        if (!Schema::hasTable('crm_broadcast_logs')) {
            Schema::create('crm_broadcast_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('crm_campaigns')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('crm_customers')->cascadeOnDelete();
                $table->string('channel');
                $table->string('recipient');
                $table->text('message');
                $table->enum('status', ['pending', 'sent', 'failed'])->default('sent');
                $table->text('error_message')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. crm_promotions
        if (!Schema::hasTable('crm_promotions')) {
            Schema::create('crm_promotions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('promo_code')->unique();
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->decimal('min_spend', 15, 2)->default(0);
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 7. crm_coupons
        if (!Schema::hasTable('crm_coupons')) {
            Schema::create('crm_coupons', function (Blueprint $table) {
                $table->id();
                $table->string('coupon_code')->unique();
                $table->foreignId('voucher_id')->nullable()->constrained('crm_vouchers')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('crm_customers')->cascadeOnDelete();
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->boolean('is_used')->default(false);
                $table->dateTime('used_at')->nullable();
                $table->date('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // 8. crm_referrals
        if (!Schema::hasTable('crm_referrals')) {
            Schema::create('crm_referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained('crm_customers')->cascadeOnDelete();
                $table->foreignId('referee_id')->constrained('crm_customers')->cascadeOnDelete();
                $table->integer('reward_points')->default(50);
                $table->enum('status', ['pending', 'rewarded', 'expired'])->default('rewarded');
                $table->timestamps();
            });
        }

        // 9. crm_customer_vouchers (Redeemed vouchers by customer)
        if (!Schema::hasTable('crm_customer_vouchers')) {
            Schema::create('crm_customer_vouchers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('crm_customers')->cascadeOnDelete();
                $table->foreignId('voucher_id')->constrained('crm_vouchers')->cascadeOnDelete();
                $table->boolean('is_used')->default(false);
                $table->dateTime('redeemed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_customer_vouchers');
        Schema::dropIfExists('crm_referrals');
        Schema::dropIfExists('crm_coupons');
        Schema::dropIfExists('crm_promotions');
        Schema::dropIfExists('crm_broadcast_logs');
        Schema::dropIfExists('crm_campaigns');
        Schema::dropIfExists('crm_segments');
        Schema::dropIfExists('crm_customer_tag');
        Schema::dropIfExists('crm_tags');

        Schema::table('crm_customers', function (Blueprint $table) {
            $table->dropColumn(['is_blacklisted', 'blacklist_reason', 'referral_code', 'referred_by_id', 'segment_id']);
        });
    }
};
