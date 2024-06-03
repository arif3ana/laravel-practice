<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use SendResponse;
    public function GetCategory(Request $request) // get data category
    {
        $user = $request->user();
        try {
            $row_data = Category::query()
                ->from('categories AS c')
                ->selectRaw('c.name, c.description')
                ->where('c.user_id', $user->id)
                ->get();

            $response = $this->response_success(message: 'Data categories was successful!', data: $row_data);
            Log::info('SUCCESS: GetCategory ', ['message' => $response['message']]);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: GetCategory ', ['error' => $e->getMessage()]);
            $response = $this->response_error(message: 'Data categories was failed!', data: null);
            return response()->json($response, 500);
        }
    }

    public function CreateCategory(Request $request)
    {
        $user = $request->user();
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
        ], [
            'required' => ':attribute cannot be empty!',
            'string' => ':attribute must be a text!',
            'max' => ':attribute must be less than :max characters!',
        ], [
            'name' => 'category name',
            'description' => 'Description',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = $this->response_validate($errors);
            return response()->json($response, 400);
        }

        try {
            DB::beginTransaction();
            $new_category = new Category([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $new_category->save();
            $response = $this->response_success(message: 'New Category has been created!', data: $new_category);
            Log::info('SUCCESS: CreateCategory ', ['message' => $response['message']]);
            DB::commit();
            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('ERROR: CreateCategory ', ['error' => $e->getMessage()]);
            $response = $this->response_error(message: 'New categories was failed to created!', data: null);
            return response()->json($response, 500);
        }
    }

    public function ShowUpdateCategory(Request $request, $id)
    {
        try {
            $category = Category::query()
                ->from('categories AS c')
                ->selectRaw('c.name, c.description')
                ->where('id', $id)
                ->first();

            $response = $this->response_success(message: 'Show data update category was saccessfull!', data: $category);
            Log::info('SUCCESS: ShowUpdateCategory ', ['message' => $response['message']]);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: ShowUpdateCategory ', ['error' => $e->getMessage()]);
            $response = $this->response_error(message: 'Show data update category was failed!', data: null);
            return response()->json($response, 500);
        }
    }

    public function UpdateCategory(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => ['nullable', 'string'],
            'description' => ['nullable','string','max:255'],
        ], [
            'required' => ':attribute cannot be empty!',
           'string' => ':attribute must be a text!',
           'max' => ':attribute must be less than :max characters!',
        ], [
            'name' => 'category name',
            'description' => 'Description',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = $this->response_validate($errors);
            return response()->json($response, 400);
        }

        try {
            DB::beginTransaction();

            $category = Category::find($id);
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $response = $this->response_success(message: 'Update category was saccessfull!', data: $category);
            Log::info('SUCCESS: UpdateCategory ', ['message' => $response['message']]);

            DB::commit();
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('ERROR: UpdateCategory ', ['error' => $e->getMessage()]);
            $response = $this->response_error(message: 'Update category was failed!', data: null);
            return response()->json($response, 500);
        }
    }
}
