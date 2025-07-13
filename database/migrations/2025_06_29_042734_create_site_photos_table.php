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
        // Create site_photos table
        Schema::create('site_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Site coordinator who uploaded');
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('photo_path', 500);
            $table->string('original_filename');
            $table->unsignedInteger('file_size')->comment('File size in bytes');
            $table->string('mime_type', 100);
            
            $table->date('photo_date');
            $table->string('location')->nullable();
            $table->enum('weather_conditions', ['sunny', 'cloudy', 'rainy', 'stormy', 'windy'])->nullable();
            $table->enum('photo_category', [
                'progress', 'quality', 'safety', 'equipment', 'materials', 
                'workers', 'documentation', 'issues', 'completion', 'other'
            ])->default('progress');
            
            // Submission and approval workflow
            $table->enum('submission_status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_comments')->nullable();
            $table->unsignedTinyInteger('admin_rating')->nullable()->comment('1-5 rating');
            $table->text('rejection_reason')->nullable();
            
            // Photo metadata
            $table->json('camera_info')->nullable()->comment('Camera settings, device info, GPS if available');
            $table->json('tags')->nullable()->comment('Array of tags for organization');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'photo_date']);
            $table->index(['user_id', 'photo_date']);
            $table->index(['submission_status', 'submitted_at']);
            $table->index('photo_category');
            $table->index(['reviewed_by', 'reviewed_at']);
            $table->index(['is_featured', 'is_public']);
        });

        // Create site_photo_comments table
        Schema::create('site_photo_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained('site_photos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->boolean('is_internal')->default(false)->comment('Internal admin comments vs external');
            $table->timestamps();
            
            // Indexes
            $table->index(['photo_id', 'created_at']);
            $table->index('user_id');
            $table->index('is_internal');
        });

        // Create site_photo_collections table (for future photo albums/collections)
        Schema::create('site_photo_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('collection_name');
            $table->text('description')->nullable();
            $table->date('collection_date');
            $table->foreignId('cover_photo_id')->nullable()->constrained('site_photos')->onDelete('set null');
            
            $table->enum('submission_status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_comments')->nullable();
            
            $table->integer('total_photos')->default(0);
            $table->boolean('is_public')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'collection_date']);
            $table->index('user_id');
            $table->index(['submission_status', 'submitted_at']);
        });

        // Create junction table for photos in collections
        Schema::create('site_photo_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('site_photo_collections')->onDelete('cascade');
            $table->foreignId('photo_id')->constrained('site_photos')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamp('added_at')->useCurrent();
            
            $table->unique(['collection_id', 'photo_id']);
            $table->index(['collection_id', 'sort_order']);
            $table->index('photo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_photo_collection_items');
        Schema::dropIfExists('site_photo_collections');
        Schema::dropIfExists('site_photo_comments');
        Schema::dropIfExists('site_photos');
    }
};