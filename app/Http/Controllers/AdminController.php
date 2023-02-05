<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;

class AdminController extends Controller
{
    public function adminHome()
    {
        return view('admin.dashboard');
    }

    public function showItems()
    {
        //This will get the category_name from categories table
        $items = Item::with('category')->get();

        return view('admin.items')->with(compact('items'));
    }

    public function showCategory()
    {
        $categories = Category::all();

        return view('admin.modals.new-item')->with('category', $categories);
    }

    public function stocks()
    {
        //This will get the category_name from categories table
        $items = Item::with('category')->get();

        return view('admin.stocks')->with(compact('items'));
    }

    public function deployment()
    {
        return view('admin.deployment');
    }

    public function userRequest()
    {
        return view('admin.userRequest');
    }

    public function users()
    {
        return view('admin.users');
    }

    public function log()
    {
        return view('admin.log');
    }
}
