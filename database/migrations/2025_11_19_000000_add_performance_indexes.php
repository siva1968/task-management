<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add composite index for "my created tasks" queries
            $table->index(['tenant_id', 'created_by'], 'tasks_tenant_creator_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            // Add composite index for "my projects" queries
            $table->index(['tenant_id', 'owner_id'], 'projects_tenant_owner_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_tenant_creator_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_tenant_owner_index');
        });
    }
};
