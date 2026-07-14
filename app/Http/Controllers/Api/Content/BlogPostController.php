<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Api\ApiController;
use App\Models\Content\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()->where('is_published', true)->orderBy('sort_order')->orderByDesc('published_at');

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        $posts = $query->paginate(15);
        return $this->successResponse($posts);
    }

    public function show(string $id): JsonResponse
    {
        $post = BlogPost::where('is_published', true)->find($id);
        if (!$post) {
            return $this->errorResponse('Blog post not found', 404);
        }
        return $this->successResponse($post);
    }
}
