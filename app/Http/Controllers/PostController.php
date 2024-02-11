<?php

namespace App\Http\Controllers;

use App\Helpers\API\ResponseBuilder;
use App\Jobs\ProcessImageUpload;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2048',
            'contact_phone' => 'required|string|max:20',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::response(null, null, $validator->errors()->all(), ResponseBuilder::Validation_Error);
        }

        $post = new Post();
        $post->fill($request->only(['title', 'description', 'contact_phone']));
        $post->user_id = Auth::id();
        $post->save();

        if ($request->hasFile('image')) {
            $this->uploadImage($request->file('image'), $post);
        }

        return ResponseBuilder::response($post, "New Post Added Successfully", null, ResponseBuilder::Success);
    }

    private function uploadImage($image, Post $post)
    {
        $imagePath = $image->store('temp');
        dispatch(new ProcessImageUpload($post, $imagePath));
    }

    public function index()
    {
        $posts = Post::with('user')
            ->select('id', 'title', 'description', 'created_at','user_id')
            ->orderByDesc('created_at')
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $post->title = Str::limit($post->title, 512);
            $post->description = Str::limit($post->description, 512);
            return $post;
        });

        if ($posts->isNotEmpty()) {
            return ResponseBuilder::response($posts, "Posts data", null, ResponseBuilder::Success);
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

        $post = Post::with('user')->findOrFail($request->post_id);
        return ResponseBuilder::response($post, "Post data", null, ResponseBuilder::Success);
    }
}
