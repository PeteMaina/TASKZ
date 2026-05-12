<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('plan', 20)->default('free');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('workspace_members', function (Blueprint $table) {
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->primary(['workspace_id', 'user_id']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('slug', 200);
            $table->text('description')->nullable();
            $table->string('client_name', 200)->nullable();
            $table->string('color', 7)->default('#2563EB');
            $table->string('icon', 10)->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'slug']);
            $table->index(['workspace_id', 'status']);
        });

        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('goal')->nullable();
            $table->string('status', 20)->default('planned');
            $table->date('start_date');
            $table->date('end_date');
            $table->smallInteger('velocity_plan')->nullable();
            $table->smallInteger('velocity_actual')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->unsignedInteger('task_number');
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->string('type', 20)->default('task');
            $table->string('status', 20)->default('open');
            $table->unsignedTinyInteger('priority')->default(2);
            $table->unsignedTinyInteger('story_points')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedSmallInteger('estimated_mins')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_client_facing')->default(false);
            $table->text('client_note')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'task_number']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'status', 'position']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('tasks', function (Blueprint $table) {
                $table->fullText(['title', 'description']);
            });
        }

        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 7);
            $table->timestamps();
            $table->unique(['project_id', 'name']);
        });

        Schema::create('task_assignments', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at')->useCurrent();
            $table->primary(['task_id', 'user_id']);
        });

        Schema::create('task_labels', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->primary(['task_id', 'label_id']);
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blocked_by_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['task_id', 'blocked_by_id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('body');
            $table->boolean('is_edited')->default(false);
            $table->timestamps();
            $table->index(['task_id', 'created_at']);
        });

        Schema::create('task_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activity');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_labels');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('sprints');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('workspace_members');
        Schema::dropIfExists('workspaces');
    }
};
