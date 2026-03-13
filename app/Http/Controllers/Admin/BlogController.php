<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogPost;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::latest()->paginate(20);
        return view('admin.pages.blog.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255', 'special_characters'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['title']);
        $data['is_published'] = (bool)($data['is_published'] ?? false);

        if ($request->hasFile('photo')) {
            $dir = public_path('upload/blog');
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file = $request->file('photo');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $name = uniqid('blog_') . '.' . $ext;
            $file->move($dir, $name);
            $data['photo'] = '/upload/blog/' . $name;
        }

        $post = BlogPost::create($data);

        app(AdminLogService::class)->log('Blog Yazısı Oluşturuldu', null, $post->toArray());

        return $this->jsonSuccess('Yazı oluşturuldu', ['id' => $post->id]);
    }

    public function update(Request $request, int $id)
    {
        $post = BlogPost::findOrFail($id);
        $before = $post->toArray();
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255', 'special_characters'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string', 'special_characters'],
            'is_published' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $data['is_published'] = (bool)($data['is_published'] ?? false);

        if ($request->hasFile('photo')) {
            $dir = public_path('upload/blog');
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            if ($post->photo && is_file(public_path(ltrim($post->photo, '/')))) {
                @unlink(public_path(ltrim($post->photo, '/')));
            }
            $file = $request->file('photo');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $name = uniqid('blog_') . '.' . $ext;
            $file->move($dir, $name);
            $data['photo'] = '/upload/blog/' . $name;
        }

        $post->update($data);

        app(AdminLogService::class)->log('Blog Yazısı Güncellendi', $before, $post->fresh()->toArray());

        return $this->jsonSuccess('Yazı güncellendi');
    }

    public function destroy(int $id)
    {
        $post = BlogPost::findOrFail($id);
        $before = $post->toArray();
        if ($post->photo && is_file(public_path(ltrim($post->photo, '/')))) {
            @unlink(public_path(ltrim($post->photo, '/')));
        }
        $post->delete();

        app(AdminLogService::class)->log('Blog Yazısı Silindi', $before, null);

        return $this->jsonSuccess('Yazı silindi');
    }
}
