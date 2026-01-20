<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $table = 'service_requests';
    private string $column = 'fixer_id';
    // Use an explicit FK name to avoid duplicates/conflicts
    private string $fkName = 'fk_service_requests_fixer_id';

    /** Find existing FK name on service_requests.fixer_id (if any) */
    private function currentFkName(): ?string
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $this->table)
            ->where('COLUMN_NAME', $this->column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');
    }

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        if (!Schema::hasTable($this->table)) return;

        // 1) Drop existing FK if present (whatever its name is)
        if ($fk = $this->currentFkName()) {
            DB::statement("ALTER TABLE `{$this->table}` DROP FOREIGN KEY `{$fk}`");
        }

        // 2) Make column NULLable (raw SQL avoids doctrine/dbal)
        DB::statement("ALTER TABLE `{$this->table}` MODIFY `{$this->column}` BIGINT UNSIGNED NULL");

        // 3) Re-add FK only if it does NOT already exist; give it an explicit name
        if (!$this->currentFkName()) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->foreign($this->column, $this->fkName)
                      ->references('id')->on('fixers')
                      ->nullOnDelete(); // or ->cascadeOnDelete() as desired
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        if (!Schema::hasTable($this->table)) return;

        // Drop FK if present
        if ($fk = $this->currentFkName()) {
            DB::statement("ALTER TABLE `{$this->table}` DROP FOREIGN KEY `{$fk}`");
        }

        // Make NOT NULL again
        DB::statement("ALTER TABLE `{$this->table}` MODIFY `{$this->column}` BIGINT UNSIGNED NOT NULL");

        // Re-add FK (explicit name, desired delete behavior)
        Schema::table($this->table, function (Blueprint $table) {
            $table->foreign($this->column, $this->fkName)
                  ->references('id')->on('fixers')
                  ->restrictOnDelete(); // mirror your original behavior
        });
    }
};
