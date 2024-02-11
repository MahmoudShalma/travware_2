<?php

namespace App\Http\Controllers;

use App\Helpers\API\ResponseBuilder;
use App\Jobs\ProcessImageUpload;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2048', // Limit description to 2 KB
            'contact_phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::response(null, null, $validator->errors()->all(), ResponseBuilder::Validation_Error);
        }

        $post = new Post();
        $post->title = $request->input('title');
        $post->description = $request->input('description');
        $post->contact_phone = $request->input('contact_phone');
        $post->user_id = Auth::id();
        $post->save();

        return ResponseBuilder::response($post, "New Post Added Successfully", null, ResponseBuilder::Success);
    }

    public function uploadImage(Request $request, Post $post)
    {
        // Validate image upload
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust validation rules as needed
        ]);

        // Store the uploaded image in a temporary location
        $imagePath = $request->file('image')->store('temp');

        // Dispatch a job to process the image upload in the background
        ProcessImageUpload::dispatch($post, $imagePath);

        return response()->json(['message' => 'Image upload request submitted for processing']);
    }

    public function index()
    {
        $posts = Post::with('user')
            ->select('id', 'title', 'description', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(10); // You can adjust the pagination limit as needed

        // Truncate title and description to 512 characters
        $posts->getCollection()->transform(function ($post) {
            $post->title = \Str::limit($post->title, 512);
            $post->description = \Str::limit($post->description, 512);
            return $post;
        });

        if (count($posts) > 0) {
            return ResponseBuilder::response($posts, "posts data", null, ResponseBuilder::Success);
        }

        return ResponseBuilder::response(null, "No post added yet", ["No post added yet"], ResponseBuilder::Success);
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::response(null, null, $validator->errors()->all(), ResponseBuilder::Validation_Error);
        }
        $post = Post::with('user')->where("id", $request->post_id)->first();
        return ResponseBuilder::response($post, "post data", null, ResponseBuilder::Success);
    }
}
