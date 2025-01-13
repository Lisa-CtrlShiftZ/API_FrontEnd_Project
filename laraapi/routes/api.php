<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\DB;

// Get all users
Route::get('/user', function () {
    $user = DB::select('SELECT * FROM user');
    return response()->json($user);
});



// Get a user by ID
Route::get('/user/{id}', function ($id) {
    $course = DB::select('SELECT * FROM user WHERE id = ?', [$id]);
    if (empty($course)) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json($course[0]);
});

// Create a new user
Route::post('/user', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');
    $streetnumber = $request->input('streetnumber');
    $location_id = $request->input('location_id');

    DB::insert('INSERT INTO user (name, email,password, streetnumber,location_id) VALUES (?, ?, ?, ?, ?)', [$name, $email,$password,$streetnumber,$location_id]);

    return response()->json(['message' => 'user created successfully'], 201);
});

// Update a user by ID
Route::put('/user/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');
    $streetnumber = $request->input('streetnumber');
    $location_id = $request->input('location_id');

    $affected = DB::update('UPDATE user SET name = ?, email = ?,password = ?,streetnumber = ?,location_id = ? WHERE id = ?', [$name, $email, $password, $streetnumber, $location_id, $id]);

    if ($affected === 0) {
        return response()->json(['message' => 'user not found or no changes made'], 404);
    }
    return response()->json(['message' => 'user updated successfully']);
});

Route::patch('/user/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['name', 'email', 'password', 'streetnumber', 'location_id']); // Get only provided fields
    if (empty($fields)) {
        return response()->json(['message' => 'No data provided for update'], 400); // No fields to update
    }

    $setClause = [];
    $bindings = [];
    foreach ($fields as $key => $value) {
        $setClause[] = "$key = ?";
        $bindings[] = $value;
    }
    $bindings[] = $id; // Add the ID to the bindings

    $query = 'UPDATE user SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'user not found or no changes made'], 404);
    }
    return response()->json(['message' => 'user updated successfully']);
});

// Delete a user by ID
Route::delete('/user/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM user WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json(['message' => 'user deleted successfully']);
});