<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\DB;

// Get all courses
Route::get('/user', function () {
    $user = DB::select('SELECT * FROM user');
    return response()->json($user);
});



// Get a specific course by ID
Route::get('/user/{id}', function ($id) {
    $course = DB::select('SELECT * FROM user WHERE id = ?', [$id]);
    if (empty($course)) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json($course[0]);
});

// Create a new course
Route::post('/user', function (\Illuminate\Http\Request $request) {
    $title = $request->input('title');
    $description = $request->input('description');

    DB::insert('INSERT INTO user (title, description) VALUES (?, ?)', [$title, $description]);

    return response()->json(['message' => 'user created successfully'], 201);
});

// Update a course by ID
Route::put('/user/{id}', function (\Illuminate\Http\Request $request, $id) {
    $title = $request->input('title');
    $description = $request->input('description');

    $affected = DB::update('UPDATE user SET title = ?, description = ? WHERE id = ?', [$title, $description, $id]);

    if ($affected === 0) {
        return response()->json(['message' => 'user not found or no changes made'], 404);
    }
    return response()->json(['message' => 'user updated successfully']);
});

// Delete a course by ID
Route::delete('/user/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM user WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json(['message' => 'user deleted successfully']);
});