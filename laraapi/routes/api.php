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
    $user = DB::select('SELECT * FROM user WHERE id = ?', [$id]);
    if (empty($user)) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json($user[0]);
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

// ---------
// this is where the requests for location
// ---------

Route::get('/location', function () {
    $location = DB::select('SELECT * FROM location');
    return response()->json($location);
});

// Get a location by ID
Route::get('/location/{id}', function ($id) {
    $location = DB::select('SELECT * FROM location WHERE id = ?', [$id]);
    if (empty($location)) {
        return response()->json(['message' => 'location not found'], 404);
    }
    return response()->json($location[0]);
});


// Create a new location
Route::post('/location', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $city_id = $request->input('city_id');

    DB::insert('INSERT INTO location (name, city_id) VALUES (?, ?)', [$name, $city_id]);

    return response()->json(['message' => 'location created successfully'], 201);
});

// Update a location by ID
Route::put('/location/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');
    $city_id = $request->input('city_id');

    $affected = DB::update('UPDATE location SET name = ?, city_id = ? WHERE id = ?', [$name, $city_id]);

    if ($affected === 0) {
        return response()->json(['message' => 'location not found or no changes made'], 404);
    }
    return response()->json(['message' => 'location updated successfully']);
});

Route::patch('/location/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['name', 'city_id']); // Get only provided fields
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

    $query = 'UPDATE location SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'location not found or no changes made'], 404);
    }
    return response()->json(['message' => 'location updated successfully']);
});

// Delete a location by ID
Route::delete('/location/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM location WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'location not found'], 404);
    }
    return response()->json(['message' => 'location deleted successfully']);
});