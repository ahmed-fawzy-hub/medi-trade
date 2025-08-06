<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class BlogController extends Controller
{
    public function index()
{
    try {
        $blogs = Blog::all()->map(function ($blog) {
            return [
                ...$blog->toArray(),
                'external_image_url' => $blog->external_image ? asset('uploads/blogs/' . $blog->external_image) : null,
                'internal_image_url' => $blog->internal_image ? asset('uploads/blogs/' . $blog->internal_image) : null,
                'header_image_url' => $blog->header_image ? asset('uploads/blogs/' . $blog->header_image) : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $blogs,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error retrieving blogs',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function availableBlogs()
{
    try {
        $blogs = Blog::where('is_active', true)->latest()->get();

        $banner = Banner::where('page', 'blog')->first(); // جلب بانر صفحة blog

        return response()->json([
            'status' => true,
            'blogs' => $blogs,
            'banner' => $banner
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function showActiveBySlug($slug)
    {
        try {
            $blog = Blog::where('is_active', true)
                ->where(function ($query) use ($slug) {
                    $query->where('slug_en', $slug)
                        ->orWhere('slug_ar', $slug);
                })
                ->firstOrFail();

            return response()->json($blog);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                    ? 'Blog not found or inactive'
                    : 'Something went wrong',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    public function showById($id)
    {
        try {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                ...$blog->toArray(),
                'external_image_url' => $blog->external_image ? asset('uploads/blogs/' . $blog->external_image) : null,
                'internal_image_url' => $blog->internal_image ? asset('uploads/blogs/' . $blog->internal_image) : null,
                'header_image_url' => $blog->header_image ? asset('uploads/blogs/' . $blog->header_image) : null,
            ]
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error retrieving blog',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title_en' => 'required|string|max:255',
                'title_ar' => 'required|string|max:255',
                'short_description_en' => 'required|string',
                'short_description_ar' => 'required|string',
                'full_description_ar' => 'required|string',
                'full_description_en' => 'required|string',
                'en_meta_title' => 'nullable|string|max:255',
                'en_meta_description' => 'nullable|string',
                'ar_meta_title' => 'nullable|string|max:255',
                'ar_meta_description' => 'nullable|string',
                'external_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'external_image_alt_en' => 'required|string|max:255',
                'external_image_alt_ar' => 'required|string|max:255',
                'internal_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'internal_image_alt_en' => 'required|string|max:255',
                'internal_image_alt_ar' => 'required|string|max:255',
                'header_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'header_image_alt_en' => 'required|string|max:255',
                'header_image_alt_ar' => 'required|string|max:255',

                'is_active' => 'boolean'
            ]);

$path = public_path('uploads/blogs');
            if (!File::exists($path)) File::makeDirectory($path, 0755, true);

            if ($request->hasFile('external_image')) {
                $file = $request->file('external_image');
                $fileName = 'external_' . Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save($path . '/' . $fileName);
                $validated['external_image'] = $fileName;
            }

            if ($request->hasFile('internal_image')) {
                $file = $request->file('internal_image');
                $fileName = 'internal_' . Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save($path . '/' . $fileName);
                $validated['internal_image'] = $fileName;
            }
            if ($request->hasFile('header_image')) {
                $file = $request->file('header_image');
                $fileName = 'header_' . Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save($path . '/' . $fileName);
                $validated['header_image'] = $fileName;
            }


            $slug = Str::slug($validated['title_en']);
            $originalSlug = $slug;
            $i = 1;
            while (Blog::where('slug_en', $slug)->exists()) {
                $slug = $originalSlug . '-' . $i++;
            }
            $validated['slug_en'] = $slug;
            $validated['slug_ar'] = $validated['title_ar'];
            $validated['is_active'] = $request->boolean('is_active') ? 1 : 0;

            $blog = Blog::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Blog created successfully',
            'data' => $blog
        ], 201);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => str_contains($e->getMessage(), 'slug_en') ? 'The English slug already exists.'
                : (str_contains($e->getMessage(), 'slug_ar') ? 'The Arabic slug already exists.'
                    : 'Something went wrong'),
            'error' => $e->getMessage()
        ], 500);
    }
    }

    public function update(Request $request, $id)
    {
        try {
            $blog = Blog::findOrFail($id);

            $validated = $request->validate([
                'title_en' => 'required|string|max:255',
                'title_ar' => 'required|string|max:255',
                'short_description_en' => 'required|string',
                'short_description_ar' => 'required|string',
                'full_description_en' => 'required|string',
                'full_description_ar' => 'required|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'external_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'external_image_alt_en' => 'nullable|string|max:255',
                'external_image_alt_ar' => 'nullable|string|max:255',
                'internal_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'internal_image_alt_en' => 'nullable|string|max:255',
                'internal_image_alt_ar' => 'nullable|string|max:255',
                'header_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'header_image_alt_en' => 'nullable|string|max:255',
                'header_image_alt_ar' => 'nullable|string|max:255',

                'is_active' => 'boolean'
            ]);

$path = public_path('uploads/blogs');
            if ($request->hasFile('external_image')) {
                if ($blog->external_image && File::exists($path . '/' . $blog->external_image)) {
                    File::delete($path . '/' . $blog->external_image);
                }
                $file = $request->file('external_image');
                $fileName = 'external_' . Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save($path . '/' . $fileName);
                $validated['external_image'] = $fileName;
            }

            if ($request->hasFile('internal_image')) {
                if ($blog->internal_image && File::exists($path . '/' . $blog->internal_image)) {
                    File::delete($path . '/' . $blog->internal_image);
                }
                $file = $request->file('internal_image');
                $fileName = 'internal_' . Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save($path . '/' . $fileName);
                $validated['internal_image'] = $fileName;
            }
            if ($request->hasFile('header_image')) {
                if ($blog->header_image && File::exists($path . '/' . $blog->header_image)) {
                    File::delete($path . '/' . $blog->header_image);
                }
                $file = $request->file('header_image');
                $fileName = 'header_' . Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save($path . '/' . $fileName);
                $validated['header_image'] = $fileName;
            }

$validated['is_active'] = $request->has('is_active') ? ($request->boolean('is_active') ? 1 : 0) : $blog->is_active;

        $blog->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                ? 'Blog not found'
                : 'Something went wrong',
            'error' => $e->getMessage()
        ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
    }
    }

    public function toggleStatus($id)
{
    try {
        $blog = Blog::findOrFail($id);
        $blog->is_active = !$blog->is_active;
        $blog->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated',
            'is_active' => (int) $blog->is_active
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                ? 'Blog not found'
                : 'Something went wrong',
            'error' => $e->getMessage()
        ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
    }
}

}
