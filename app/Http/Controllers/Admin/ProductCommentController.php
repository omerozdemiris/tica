<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ProductComment;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Admin\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;
use App\Services\Logs\AdminLogService;

class ProductCommentController extends Controller
{
    public function index(): View
    {
        $comments = ProductComment::with(['user', 'product'])->latest()->paginate(20);
        return view('admin.pages.products.comments.all', compact('comments'));
    }

    public function pending(): View
    {
        $comments = ProductComment::with(['user', 'product'])->where('status', 0)->latest()->paginate(20);
        return view('admin.pages.products.comments.pending', compact('comments'));
    }

    public function approved(): View
    {
        $comments = ProductComment::with(['user', 'product'])->where('status', 1)->latest()->paginate(20);
        return view('admin.pages.products.comments.approved', compact('comments'));
    }

    public function rejected(): View
    {
        $comments = ProductComment::with(['user', 'product'])->where('status', 2)->latest()->paginate(20);
        return view('admin.pages.products.comments.rejected', compact('comments'));
    }

    public function show($id): View
    {
        $comment = ProductComment::with(['user', 'product'])->findOrFail($id);
        $other_comments_product = ProductComment::where('product_id', $comment->product_id)->where('id', '!=', $comment->id)->get();
        return view('admin.pages.products.comments.show', compact('comment', 'other_comments_product'));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $comment = ProductComment::findOrFail($id);
        $before = $comment->toArray();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 2,
                'msg' => 'Geçersiz durum seçimi.',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update([
            'status' => $request->status
        ]);

        app(AdminLogService::class)->log('Ürün Yorum Durumu Güncellendi', $before, $comment->fresh()->toArray());

        // Send notification to user if approved or rejected (only if status changed)
        if ($comment->user_id && $request->status != 0) {
            $statusText = $request->status == 1 ? 'onaylandı' : 'reddedildi';
            $title = "Değerlendirmeniz " . ($request->status == 1 ? 'Onaylandı' : 'Reddedildi');
            $text = "{$comment->product->title} ürünü için yaptığınız değerlendirme admin tarafından {$statusText}.";

            NotificationService::createDirectNotification(
                $comment->user_id,
                $title,
                $text,
                'event'
            );
        }

        return response()->json([
            'code' => 1,
            'msg' => 'Değerlendirme durumu başarıyla güncellendi.'
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $comment = ProductComment::findOrFail($id);
        $before = $comment->toArray();
        $comment->delete();

        app(AdminLogService::class)->log('Ürün Yorumu Silindi', $before, null);

        return response()->json([
            'code' => 1,
            'msg' => 'Değerlendirme başarıyla silindi.'
        ]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:product_comments,id',
            'action' => 'required|in:delete,status_0,status_1,status_2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 2,
                'msg' => 'Geçersiz istek.',
                'errors' => $validator->errors()
            ], 422);
        }

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            ProductComment::whereIn('id', $ids)->delete();
            $msg = 'Seçilen değerlendirmeler başarıyla silindi.';
            app(AdminLogService::class)->log('Ürün Yorumları Toplu Silindi', null, ['ids' => $ids]);
        } else {
            $status = (int) str_replace('status_', '', $action);
            ProductComment::whereIn('id', $ids)->update(['status' => $status]);
            $msg = 'Seçilen değerlendirmelerin durumu başarıyla güncellendi.';
            app(AdminLogService::class)->log('Ürün Yorumları Toplu Durum Güncellendi', null, ['ids' => $ids, 'status' => $status]);
        }

        return response()->json([
            'code' => 1,
            'msg' => $msg
        ]);
    }
}
