<?php

namespace App\Http\Controllers\Api;

use App\Models\Product\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->query('id');
        
        if (!$parentId || $parentId === '#') {
            $categories = Category::whereNull('parent_id')->with('children.children')->get();
            $tree = $categories->map(fn($c) => $this->buildJSTreeNode($c, 0));
        } else {
            $categories = Category::where('parent_id', $parentId)->with('children')->get();
            $tree = $categories->map(fn($c) => $this->buildJSTreeNode($c, 1));
        }
        
        return $this->successResponse($tree);
    }

    private function buildJSTreeNode(Category $category, int $depth): array
    {
        $node = [
            'id' => $category->id,
            'text' => $category->name,
            'icon' => $depth >= 2 ? 'jstree-file' : 'jstree-folder',
            'state' => ['opened' => $depth < 1, 'selected' => false],
            'data' => [
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'status' => $category->status,
            ],
        ];

        if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
            $node['children'] = $category->children->map(fn($child) => $this->buildJSTreeNode($child, $depth + 1));
        }

        return $node;
    }

    public function flat(): JsonResponse
    {
        $categories = Category::all()->map(fn($c) => [
            'id' => $c->id,
            'text' => $c->name,
            'parent' => $c->parent_id ?: '#',
        ])->values();
        return $this->successResponse($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::create($request->all());
        return $this->successResponse($category, 'Category created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        return $this->successResponse($category);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        $category->update($request->all());
        return $this->successResponse($category, 'Category updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        $category->delete();
        return $this->successResponse(null, 'Category deleted', 204);
    }
}