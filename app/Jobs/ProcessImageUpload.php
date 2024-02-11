<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class ProcessImageUpload implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    protected $imagePath;

    public function __construct(Post $post, $imagePath)
    {
        $this->post = $post;
        $this->imagePath = $imagePath;
    }

    public function handle()
    {
        // Process the image upload (e.g., resize, optimize, save to final location, etc.)
        // For demonstration purposes, let's simply move the image from the temporary location to the final location
        
        $finalImagePath = 'images/' . uniqid() . '_' . basename($this->imagePath);
        Storage::move($this->imagePath, $finalImagePath);

        // Update the post's image field with the path to the stored image
        $this->post->image = $finalImagePath;
        $this->post->save();
    }
}
