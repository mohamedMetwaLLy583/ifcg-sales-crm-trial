<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteOrdersTableForNewStatuses();

            return;
        }

        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        DB::table('orders')->where('status', 'processing')->update(['status' => 'confirmed']);
        DB::table('orders')->where('status', 'completed')->update(['status' => 'delivered']);

        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteOrdersTableForOldStatuses();

            return;
        }

        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        DB::table('orders')->where('status', 'confirmed')->update(['status' => 'processing']);
        DB::table('orders')->where('status', 'shipped')->update(['status' => 'processing']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'completed']);

        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    private function rebuildSqliteOrdersTableForNewStatuses(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::beginTransaction();

        try {
            DB::statement('DROP TABLE IF EXISTS orders_status_migration');
            DB::statement($this->sqliteCreateOrdersTableSql(
                'orders_status_migration',
                "'pending', 'confirmed', 'shipped', 'delivered', 'cancelled'"
            ));

            DB::statement("
                INSERT INTO orders_status_migration (
                    id, user_id, order_number, total, shipping_total, tax_total, status,
                    payment_status, payment_method, created_at, updated_at, customer_name,
                    customer_phone, customer_email, shipping_address, shipping_city,
                    customer_notes, admin_notes
                )
                SELECT
                    id, user_id, order_number, total, shipping_total, tax_total,
                    CASE status
                        WHEN 'processing' THEN 'confirmed'
                        WHEN 'completed' THEN 'delivered'
                        ELSE status
                    END,
                    payment_status, payment_method, created_at, updated_at, customer_name,
                    customer_phone, customer_email, shipping_address, shipping_city,
                    customer_notes, admin_notes
                FROM orders
            ");

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_status_migration RENAME TO orders');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS orders_order_number_unique ON orders (order_number)');

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        } finally {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    private function rebuildSqliteOrdersTableForOldStatuses(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::beginTransaction();

        try {
            DB::statement('DROP TABLE IF EXISTS orders_status_migration');
            DB::statement($this->sqliteCreateOrdersTableSql(
                'orders_status_migration',
                "'pending', 'processing', 'completed', 'cancelled'"
            ));

            DB::statement("
                INSERT INTO orders_status_migration (
                    id, user_id, order_number, total, shipping_total, tax_total, status,
                    payment_status, payment_method, created_at, updated_at, customer_name,
                    customer_phone, customer_email, shipping_address, shipping_city,
                    customer_notes, admin_notes
                )
                SELECT
                    id, user_id, order_number, total, shipping_total, tax_total,
                    CASE status
                        WHEN 'confirmed' THEN 'processing'
                        WHEN 'shipped' THEN 'processing'
                        WHEN 'delivered' THEN 'completed'
                        ELSE status
                    END,
                    payment_status, payment_method, created_at, updated_at, customer_name,
                    customer_phone, customer_email, shipping_address, shipping_city,
                    customer_notes, admin_notes
                FROM orders
            ");

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_status_migration RENAME TO orders');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS orders_order_number_unique ON orders (order_number)');

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        } finally {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    private function sqliteCreateOrdersTableSql(string $table, string $statuses): string
    {
        return <<<SQL
CREATE TABLE "{$table}" (
    "id" integer not null primary key autoincrement,
    "user_id" integer not null,
    "order_number" varchar not null,
    "total" numeric not null,
    "shipping_total" numeric not null default '0',
    "tax_total" numeric not null default '0',
    "status" varchar check ("status" in ({$statuses})) not null default 'pending',
    "payment_status" varchar check ("payment_status" in ('unpaid', 'paid', 'refunded')) not null default 'unpaid',
    "payment_method" varchar,
    "created_at" datetime,
    "updated_at" datetime,
    "customer_name" varchar,
    "customer_phone" varchar,
    "customer_email" varchar,
    "shipping_address" text,
    "shipping_city" varchar,
    "customer_notes" text,
    "admin_notes" text,
    foreign key("user_id") references "users"("id") on delete cascade
)
SQL;
    }
};
