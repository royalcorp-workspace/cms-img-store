<?php

namespace App\Http\Controllers\Content;

use App\Models\Content\BlogPost;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $posts = $query->orderBy('sort_order')->orderByDesc('published_at')->paginate(15)->appends($request->query());
        return view('pages.content.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('pages.content.blog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:255',
            'author_name' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['creator'] = auth()->id();
        $validated['editor'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured'] = $request->boolean('is_featured');

        BlogPost::create($validated);

        return redirect()->route('content.blog.index')->with('success', 'Blog post created successfully');
    }

    public function edit(string $id)
    {
        $post = BlogPost::findOrFail($id);
        return view('pages.content.blog.edit', compact('post'));
    }

    public function update(Request $request, string $id)
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blog_posts,slug,' . $id,
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:255',
            'author_name' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['editor'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured'] = $request->boolean('is_featured');

        $post->update($validated);

        return redirect()->route('content.blog.index')->with('success', 'Blog post updated successfully');
    }

    public function destroy(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return redirect()->route('content.blog.index')->with('success', 'Blog post deleted successfully');
    }
}
