<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductComment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProductCommentController extends Controller
{
    public function index(): View
    {
        $comments = ProductComment::where('user_id', Auth::user()->id)->get();
        return view('frontend.pages.comments.index', compact('comments'));
    }
    public function show(ProductComment $comment): View
    {
        $comment = ProductComment::findOrFail($comment->id);
        $product = Product::findOrFail($comment->product_id);
        return view('frontend.pages.comments.show', compact('comment', 'product'));
    }
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
        ]);
        $comment = ProductComment::create([
            'comment' => $request->comment,
            'rating' => $request->rating,
            'user_id' => Auth::user()->id,
            'product_id' => $product->id,
            'status' => 0,
        ]);
        return redirect()->route('products.show', [$product->id, $product->slug])->with('success', 'Değerlendirmeniz incelemeye gönderildi.');
    }
}
