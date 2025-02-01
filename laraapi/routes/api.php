<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\UserController;
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
    $hashedPassword = bcrypt($request->input('password'));
    $streetnumber = $request->input('streetnumber') ?? null;
    $location_id = $request->input('location_id') ?? null;
    $max_water = $request->input('max_water') ?? null;
    $max_food = $request->input('max_food') ?? null;
    DB::insert('INSERT INTO user (name, email,password, streetnumber,location_id,max_water,max_food) VALUES (?, ?, ?, ?, ?,?,?)', [$name, $email, $hashedPassword, $streetnumber, $location_id,$max_water, $max_food]);

    return response()->json(['message' => 'user created successfully'], 201);
});



Route::patch('/user/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['name', 'email', 'password', 'streetnumber', 'location_id','max_water', 'max_food']); // Get only provided fields
    if (empty($fields)) {
        return response()->json(['message' => 'No data provided for update'], 400); // No fields to update
    }

    $setClause = [];
    $bindings = [];
    foreach ($fields as $key => $value) {
        if ($key === 'password') {
            $value = bcrypt($value); 
        }
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
// this is where the requests for login begin
// ---------

Route::post('/login', function (Request $request){ 
    $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            return response()->json([
                'token' => Str::random(80),
                'user' => $user
                
            ]);
        }

        return response()->json([ 
            'message' => 'Invalid credentials'
        ], 401);
} ); 

//get family members for logged in user
Route::get('/user/{user_id}/family_member', function ($user_id) {
    try {
        $userFamilyMembers = DB::select('SELECT * FROM family_member WHERE user_id = ?', [$user_id]);
        return response()->json($userFamilyMembers);

    } catch (Exception $e) {
        return response()->json(['error' => 'User not found or another error occurred'], 404);
    }
});

// ---------
// Password verification
// ---------


Route::post('/verifyPassword', function (\Illuminate\Http\Request $request) {
    $userId = $request->input('userId');
    $password = $request->input('password');

    // Fetch user by ID
    $user = \App\Models\User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Verify the password using Laravel's Hash facade
    if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        return response()->json(['valid' => true]);
    }

    return response()->json(['valid' => false], 400);
});


// ---------
// this is where the requests for location begin
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

// ---------
// this is where the requests for city begin
// ---------

Route::get('/city', function () {
    $city = DB::select('SELECT * FROM city');
    return response()->json($city);
});

// Get a city by ID
Route::get('/city/{id}', function ($id) {
    $city = DB::select('SELECT * FROM city WHERE id = ?', [$id]);
    if (empty($city)) {
        return response()->json(['message' => 'city not found'], 404);
    }
    return response()->json($city[0]);
});


// Create a new city
Route::post('/city', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $street = $request->input('street');

    DB::insert('INSERT INTO city (name, street) VALUES (?, ?)', [$name, $street]);

    return response()->json(['message' => 'city created successfully'], 201);
});

// Update a city by ID
Route::put('/city/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');
    $street = $request->input('street');

    $affected = DB::update('UPDATE city SET name = ?, street = ? WHERE id = ?', [$name, $street]);

    if ($affected === 0) {
        return response()->json(['message' => 'city not found or no changes made'], 404);
    }
    return response()->json(['message' => 'city updated successfully']);
});

Route::patch('/city/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['name', 'street']); // Get only provided fields
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

    $query = 'UPDATE city SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'city not found or no changes made'], 404);
    }
    return response()->json(['message' => 'city updated successfully']);
});

// Delete a city by ID
Route::delete('/city/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM city WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'city not found'], 404);
    }
    return response()->json(['message' => 'city deleted successfully']);
});

// ---------
// this is where the requests for food begin
// ---------

Route::get('/food', function () {
    $food = DB::select('SELECT * FROM food');
    return response()->json($food);
});

// Get a food by ID
Route::get('/food/{id}', function ($id) {
    $food = DB::select('SELECT * FROM food WHERE id = ?', [$id]);
    if (empty($food)) {
        return response()->json(['message' => 'food not found'], 404);
    }
    return response()->json($food[0]);
});


// Create a new food
Route::post('/food', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $calories_per_kilo = $request->input('calories_per_kilo');

    DB::insert('INSERT INTO food (name, calories_per_kilo) VALUES (?, ?)', [$name, $calories_per_kilo]);

    return response()->json(['message' => 'food created successfully'], 201);
});

// Update a food by ID
Route::put('/food/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');
    $calories_per_kilo = $request->input('calories_per_kilo');

    $affected = DB::update('UPDATE food SET name = ?, calories_per_kilo = ? WHERE id = ?', [$name, $calories_per_kilo]);

    if ($affected === 0) {
        return response()->json(['message' => 'food not found or no changes made'], 404);
    }
    return response()->json(['message' => 'food updated successfully']);
});

Route::patch('/food/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['name', 'calories_per_kilo']); // Get only provided fields
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

    $query = 'UPDATE food SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'food not found or no changes made'], 404);
    }
    return response()->json(['message' => 'food updated successfully']);
});

// Delete a food by ID
Route::delete('/food/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM food WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'food not found'], 404);
    }
    return response()->json(['message' => 'food deleted successfully']);
});

// ---------
// this is where the requests for supplies begin
// ---------

Route::get('/supplies', function () {
    $supplies = DB::select('SELECT * FROM supplies');
    return response()->json($supplies);
});

// Get a supplies by ID
Route::get('/supplies/{id}', function ($id) {
    $supplies = DB::select('SELECT * FROM supplies WHERE id = ?', [$id]);
    if (empty($supplies)) {
        return response()->json(['message' => 'supplies not found'], 404);
    }
    return response()->json($supplies[0]);
});


// Create a new supplies
Route::post('/supplies', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');

    DB::insert('INSERT INTO supplies (name) VALUES (?)', [$name]);

    return response()->json(['message' => 'supplies created successfully'], 201);
});


Route::patch('/supplies/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['name' ]); // Get only provided fields
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

    $query = 'UPDATE supplies SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'supplies not found or no changes made'], 404);
    }
    return response()->json(['message' => 'supplies updated successfully']);
});

// Delete a supplies by ID
Route::delete('/supplies/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM supplies WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'supplies not found'], 404);
    }
    return response()->json(['message' => 'supplies deleted successfully']);
});


// ---------
// this is where the requests for family_member begin
// ---------

Route::get('/family_member', function () {
    $family_member = DB::select('SELECT * FROM family_member');
    return response()->json($family_member);
});

// Get a family_member by ID
Route::get('/family_member/{id}', function ($id) {
    $family_member = DB::select('SELECT * FROM family_member WHERE id = ?', [$id]);
    if (empty($family_member)) {
        return response()->json(['message' => 'family_member not found'], 404);
    }
    return response()->json($family_member[0]);
});


// Create a new family_member
Route::post('/family_member', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $last_name = $request->input('last_name');
    $gender = $request->input('gender');
    $height = $request->input('height');
    $weight = $request->input('weight');
    $date_of_birth = $request->input('date_of_birth');
    $diet = $request->input('diet');
    $user_id = $request ->input('user_id');

    // Check if user_id is provided
    if (empty($user_id)) {
        return response()->json(['message' => 'user_id is required'], 400);
    }

    DB::insert('INSERT INTO family_member (name,last_name,gender,height,weight,date_of_birth,diet,user_id) VALUES (?,?,?,?,?,?,?,?)', [$name, $last_name,$gender,$height,$weight,$date_of_birth,$diet,$user_id]);

    return response()->json(['message' => 'family_member created successfully'], 201);
});


Route::patch('/family_member/{id}', function (\Illuminate\Http\Request $request, $id) {
    \Log::info("PATCH request received for family member with ID: {$id}");
    //AI was used for this request
    $fields = $request->only(['name','last_name','gender','height', 'weight','date_of_birth','diet','user_id' ]); // Get only provided fields
    \Log::info('Fields to update: ', $fields);
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

    $query = 'UPDATE family_member SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        \Log::info('No rows affected, family_member not found or no changes made.');
        return response()->json(['message' => 'family_member not found or no changes made'], 404);
    }
    \Log::info('Successfully updated family member with ID: ' . $id);
    return response()->json(['message' => 'family_member updated successfully']);
});

// Delete a family_member by ID
Route::delete('/family_member/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM family_member WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'family_member not found'], 404);
    }
    return response()->json(['message' => 'family_member deleted successfully']);
});


// ---------
// this is where the requests for family_member connection to user begin
// ---------

Route::get('/user_family_member', function () {
    $user_family_member = DB::select('SELECT * FROM user_family_member');
    return response()->json($user_family_member);
});

// Get a family_member by ID
Route::get('/user_family_member/{id}', function ($id) {
    $user_family_member = DB::select('SELECT * FROM user_family_member WHERE id = ?', [$id]);
    if (empty($user_family_member)) {
        return response()->json(['message' => 'user_family_member not found'], 404);
    }
    return response()->json($user_family_member[0]);
});


// Create a new user_family_member
Route::post('/user_family_member', function (\Illuminate\Http\Request $request) {
    $user_id = $request->input('User_id');
    $family_member_id = $request->input('family_member_id');

    DB::insert('INSERT INTO user_family_member (user_id, family_member_id) VALUES (?,?)', [$user_id,$family_member_id]);
    return response()->json(['message' => 'user_family_member created successfully'], 201);
});


Route::patch('/user_family_member/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['user_id','family_member_id']); // Get only provided fields
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

    $query = 'UPDATE user_family_member SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'user_family_member not found or no changes made'], 404);
    }
    return response()->json(['message' => 'user_family_member updated successfully']);
});

// Delete a user_family_member by ID
Route::delete('/user_family_member/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM user_family_member WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'user_family_member not found'], 404);
    }
    return response()->json(['message' => 'user_family_member deleted successfully']);
});

// ---------
// this is where the requests for food connection to user begin
// ---------

Route::get('/user_food', function () {
    $user_food = DB::select('SELECT * FROM user_food');
    return response()->json($user_food);
});

// Get a user - food connection by ID
Route::get('/user_food/{id}', function ($id) {
    $user_food = DB::select('SELECT * FROM user_food WHERE id = ?', [$id]);
    if (empty($user_food)) {
        return response()->json(['message' => 'user_food not found'], 404);
    }
    return response()->json($user_food[0]);
});


// Create a new user_food
Route::post('/user_food', function (\Illuminate\Http\Request $request) {
    $user_id = $request->input('User_id');
    $food_id = $request->input('food_id');
    $expiration_date = $request->input('expiration_date');
    $amount = $request->input('amount');

    DB::insert('INSERT INTO user_food (user_id, food_id,expiration_date,amount) VALUES (?,?,?,?)', [$user_id,$food_id,$expiration_date,$amount]);
    return response()->json(['message' => 'user_food created successfully'], 201);
});


Route::patch('/user_food/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['user_id','family_member_id','expiration_date','amount']); // Get only provided fields
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

    $query = 'UPDATE user_food SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'user_food not found or no changes made'], 404);
    }
    return response()->json(['message' => 'user_food updated successfully']);
});

// Delete a user_food by ID
Route::delete('/user_food/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM user_food WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'user_food not found'], 404);
    }
    return response()->json(['message' => 'user_food deleted successfully']);
});

// ---------
// this is where the requests for supplies connection to user begin
// ---------

Route::get('/user_supplies', function () {
    $user_supplies = DB::select('SELECT * FROM user_supplies');
    return response()->json($user_supplies);
});

// Get a user - food connection by ID
Route::get('/user_supplies/{id}', function ($id) {
    $user_supplies = DB::select('SELECT * FROM user_supplies WHERE id = ?', [$id]);
    if (empty($user_supplies)) {
        return response()->json(['message' => 'user_supplies not found'], 404);
    }
    return response()->json($user_supplies[0]);
});


// Create a new user_supplies
Route::post('/user_supplies', function (\Illuminate\Http\Request $request) {
    $user_id = $request->input('User_id');
    $food_id = $request->input('food_id');
    $quantity = $request->input('quantity');

    DB::insert('INSERT INTO user_supplies (user_id, food_id,quantity) VALUES (?,?,?,?)', [$user_id,$food_id,$quantity]);
    return response()->json(['message' => 'user_supplies created successfully'], 201);
});


Route::patch('/user_supplies/{id}', function (\Illuminate\Http\Request $request, $id) {
    //AI was used for this request
    $fields = $request->only(['user_id','family_member_id','quantity']); // Get only provided fields
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

    $query = 'UPDATE user_supplies SET ' . implode(', ', $setClause) . ' WHERE id = ?';
    $affected = DB::update($query, $bindings);
    if ($affected === 0) {
        return response()->json(['message' => 'user_supplies not found or no changes made'], 404);
    }
    return response()->json(['message' => 'user_supplies updated successfully']);
});

// Delete a user_supplies by ID
Route::delete('/user_supplies/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM user_supplies WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'user_supplies not found'], 404);
    }
    return response()->json(['message' => 'user_supplies deleted successfully']);
});

//-----
// WIP - this is where the requests related to a specific user's supplies begin 
//----
Route::get('/user/{user_id}/supplies', function ($user_id) {
    $userSupplies = DB::table('user_supplies')
    ->join('user', 'user.id', '=', 'user_supplies.user_id')
    ->join('supplies', 'supplies.id', '=', 'user_supplies.supply_id')
    ->select(
        'user_supplies.quantity',
        'user.id as user_id',
        'supplies.id as supply_id',
        'supplies.name as supply_name'
    )
    ->where('user.id', $user_id)
    ->get();

    return response()->json($userSupplies);
});

Route::put('/user/{user_id}/supplies', function ($user_id) {
    $supply_id = request('supply_id');
    $quantity = request('quantity');

    DB::table('user_supplies')->updateOrInsert(
        ['user_id' => $user_id, 'supply_id' => $supply_id],
        ['quantity' => $quantity]
    );

    return response()->json(['message' => 'Supply updated successfully']);
});

Route::delete('/user/{user_id}/supplies/{supply_id}', function ($user_id, $supply_id) {
    // Delete the supply from the user's supplies
    DB::table('user_supplies')
        ->where('user_id', $user_id)
        ->where('supply_id', $supply_id)
        ->delete();

    return response()->json(['message' => 'Supply deleted successfully']);
});
