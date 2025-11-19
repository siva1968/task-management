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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['backlog', 'in_progress', 'review', 'completed', 'cancelled'])->default('backlog');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'project_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
            $table->index(['tenant_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
