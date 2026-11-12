<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'email')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
            } catch (Throwable $e) {
                report($e);
            }

            return;
        }

        if ($driver === 'sqlite' && $this->sqliteEmailIsNotNull()) {
            $this->rebuildSqliteUsersTable();
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        try {
            DB::statement("UPDATE users SET email = CONCAT('user-', id, '@xstore.local') WHERE email IS NULL");
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function sqliteEmailIsNotNull(): bool
    {
        foreach (DB::select('PRAGMA table_info(users)') as $column) {
            if ($column->name === 'email') {
                return (bool) $column->notnull;
            }
        }

        return false;
    }

    private function rebuildSqliteUsersTable(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');

        DB::transaction(function () {
            DB::statement('DROP TABLE IF EXISTS users_new');
            DB::statement(<<<'SQL'
CREATE TABLE users_new (
    id integer primary key autoincrement not null,
    name varchar not null,
    email varchar null,
    email_verified_at datetime null,
    password varchar not null,
    phone varchar null,
    address varchar null,
    country varchar null,
    city varchar null,
    state varchar null,
    zip varchar null,
    profile_image varchar null,
    otp varchar(6) null,
    otp_expires_at datetime null,
    is_active tinyint(1) not null default '0',
    remember_token varchar(100) null,
    created_at datetime null,
    updated_at datetime null,
    deleted_at datetime null,
    branch_id integer null,
    foreign key(branch_id) references branches(id) on delete set null
)
SQL);

            DB::statement(<<<'SQL'
INSERT INTO users_new (
    id, name, email, email_verified_at, password, phone, address, country, city,
    state, zip, profile_image, otp, otp_expires_at, is_active, remember_token,
    created_at, updated_at, deleted_at, branch_id
)
SELECT
    id, name, email, email_verified_at, password, phone, address, country, city,
    state, zip, profile_image, otp, otp_expires_at, is_active, remember_token,
    created_at, updated_at, deleted_at, branch_id
FROM users
SQL);

            DB::statement('DROP TABLE users');
            DB::statement('ALTER TABLE users_new RENAME TO users');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email)');
            DB::statement('CREATE INDEX IF NOT EXISTS users_branch_id_index ON users(branch_id)');
        });

        DB::statement('PRAGMA foreign_keys=ON');
    }
};
