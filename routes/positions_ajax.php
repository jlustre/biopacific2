<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Position;

Route::post('/admin/positions/add', function(Request $request) {
    $title = $request->input('title');
    if (!$title) return response()->json(['error' => 'No title'], 400);
    $exists = Position::where('title', $title)->first();
    if ($exists) return response()->json(['exists' => true]);
    $pos = Position::create(['title' => $title]);
    return response()->json(['id' => $pos->id, 'title' => $pos->title]);
})->middleware('auth');
