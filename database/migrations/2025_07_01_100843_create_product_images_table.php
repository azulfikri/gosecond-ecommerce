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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('image_path');
            $table->string('alt_text')->nullable(); // Optional alt text for the image
            // You can add more fields if needed, like 'is_primary' to indicate the main image
            $table->integer('sort_order')->default(0)->comment('order for displaying image'); // Example for sorting images
            $table->boolean('is_primary')->default(false)->comment('mark as primary image'); // Example for primary image
            $table->timestamps();
            // If you want to store additional metadata, you can add more columns here
            // $table->json('metadata')->nullable(); // Example for storing additional metadata
            // $table->string('caption')->nullable(); // Example for image caption
            // $table->string('image_type')->nullable(); // Example for image type (e.g., thumbnail, gallery)
            // $table->string('image_size')->nullable(); // Example for image size (e.g., small, medium, large)
            // $table->string('image_format')->nullable(); // Example for image format (e.g., jpg, png, webp)
            // $table->string('image_resolution')->nullable(); // Example for image resolution (e.g., 1920x1080)
            // $table->string('image_color_profile')->nullable(); // Example for image color profile (e.g., sRGB, Adobe RGB)
            // If you want to store the original image name
            // $table->string('original_image_name')->nullable(); // Example for original image name
            // If you want to store the image size
            // $table->integer('image_size_bytes')->nullable(); // Example for image size in bytes
            // If you want to store the image dimensions
            // $table->string('image_dimensions')->nullable(); // Example for image dimensions (e.g., 1920x1080)
            // If you want to store the image upload date
            // $table->timestamp('uploaded_at')->nullable(); // Example for image upload date
            // If you want to store the image uploader's user ID
            // $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null'); // Example for image uploader's user ID
            // If you want to store the image's visibility status
            // $table->boolean('is_visible')->default(true);
            // If you want to store the image's SEO title
            // $table->string('seo_title')->nullable(); // Example for SEO title
            // If you want to store the image's SEO description
            // $table->text('seo_description')->nullable(); // Example for SEO description
            // If you want to store the image's SEO keywords
            // $table->string('seo_keywords')->nullable(); // Example for SEO keywords
            // If you want to store the image's SEO slug
            // $table->string('seo_slug')->nullable(); // Example for SEO slug
            // If you want to store the image's SEO canonical URL
            // $table->string('seo_canonical_url')->nullable(); // Example for SEO canonical URL
            // If you want to store the image's SEO robots meta tag
            // $table->string('seo_robots')->nullable(); // Example for SEO robots meta tag
            // If you want to store the image's SEO Open Graph title
            // $table->string('seo_og_title')->nullable(); // Example for SEO Open Graph title
            // If you want to store the image's SEO Open Graph description
            // $table->text('seo_og_description')->nullable(); // Example for SEO Open Graph description
            // If you want to store the image's SEO Open Graph image URL
            // $table->string('seo_og_image_url')->nullable(); // Example for SEO Open Graph image URL
            // If you want to store the image's SEO Twitter Card title
            // $table->string('seo_twitter_card_title')->nullable(); // Example for SEO Twitter Card title
            // If you want to store the image's SEO Twitter Card description
            // $table->text('seo_twitter_card_description')->nullable(); // Example for SEO Twitter Card description
            // If you want to store the image's SEO Twitter Card image URL
            // $table->string('seo_twitter_card_image_url')->nullable(); // Example for SEO Twitter Card image URL
            // If you want to store the image's SEO Twitter Card type
            // $table->string('seo_twitter_card_type')->nullable(); // Example for SEO Twitter Card type
            // If you want to store the image's SEO Twitter Card site
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
