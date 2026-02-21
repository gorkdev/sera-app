<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number', 50)->unique()->nullable()->after('id');
            }
            if (! Schema::hasColumn('orders', 'dealer_id')) {
                $table->foreignId('dealer_id')->nullable()->after('order_number')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'party_id')) {
                $table->foreignId('party_id')->nullable()->after('dealer_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'cart_id')) {
                $table->foreignId('cart_id')->nullable()->after('party_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'order_status_id')) {
                $table->foreignId('order_status_id')->nullable()->after('cart_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'delivery_type')) {
                $table->string('delivery_type', 20)->default('pickup')->after('order_status_id');
            }
            if (! Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('delivery_type');
            }
            if (! Schema::hasColumn('orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(20)->after('subtotal');
            }
            if (! Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_rate');
            }
            if (! Schema::hasColumn('orders', 'total')) {
                $table->decimal('total', 12, 2)->default(0)->after('tax_amount');
            }
            if (! Schema::hasColumn('orders', 'dealer_note')) {
                $table->text('dealer_note')->nullable()->after('total');
            }
            if (! Schema::hasColumn('orders', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('dealer_note');
            }
            if (! Schema::hasColumn('orders', 'created_by_admin_id')) {
                $table->foreignId('created_by_admin_id')->nullable()->after('admin_note')->constrained('admin_users')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['dealer_id']);
            $table->dropForeign(['party_id']);
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['order_status_id']);
            $table->dropForeign(['created_by_admin_id']);
            $table->dropColumn([
                'order_number', 'dealer_id', 'party_id', 'cart_id', 'order_status_id',
                'delivery_type', 'subtotal', 'tax_rate', 'tax_amount', 'total',
                'dealer_note', 'admin_note', 'created_by_admin_id', 'deleted_at',
            ]);
        });
    }
};
