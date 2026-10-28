<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('category');

        $books = Book::query()
            ->with('category')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->when($selectedCategory, function ($query, $selectedCategory) {
                $query->where('categories_id', $selectedCategory);
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $title = 'Book Catalog';
        $view = view()->exists('member.books.index') ? 'member.books.index' : 'coming-soon';

        return view($view, compact('books', 'categories', 'search', 'selectedCategory', 'title'));
    }

    public function show(Book $book): View
    {
        $book->load('category');
        $availableStock = $book->availableStock();
        $title = $book->title;

        $view = view()->exists('member.books.show') ? 'member.books.show' : 'coming-soon';

        return view($view, compact('book', 'availableStock', 'title'));
    }
}
