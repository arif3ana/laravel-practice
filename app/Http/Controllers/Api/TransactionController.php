<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    use SendResponse;

    public function GetTransaction(Request $request, int $limit = 10, int $offset = 10, string $type = 'income') // get data transaction
    {
        $user = $request->user();
        $sort_by = $request->input('sort_by', 't.created_at');
        $order = $request->input('order', 'desc');
        $search_keyword = $request->input('search');
        $per_page = $limit;
        try {
            $row_data = Transaction::query()
                ->from('transactions AS t')
                ->join('categories as c', 't.category_id', '=', 'c.id')
                ->selectRaw('t.amount, t.type, t.description, t.transaction_date, c.name as category_name')
                ->search($search_keyword)
                ->where('t.user_id', $user->id)
                ->where('t.type', $type)
                ->orderBy($sort_by, $order)
                ->offset($offset)
                ->limit($limit)
                ->paginate($per_page);

            $response_data = [
                'per_page' => $row_data->perPage(),
                'total_data' => $row_data->total(),
                'total_page' => $row_data->lastPage(),
                'current_page' => $row_data->currentPage(),
                'from' => $row_data->firstItem(),
                'to' => $row_data->lastItem(),
                'sortBy' => $sort_by,
                'orderBy' => $order,
                'data' => $row_data->items(),
            ];

            $response = $this->response_success(message: 'Data query was successful!', data: $response_data);
            Log::info('SUCCESS: GetTransaction ', ['message' => 'Data query was successful!']);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: GetTransaction ', ['error' => $e]);
            $response = $this->response_error(message: 'Data query was fail!', data: null);
            return response()->json($response, 500);
        }
    }

    public function ShowCreateTransaction(Request $request)
    {
        $user = $request->user();

        try {
            $categories = Category::query()
                ->selectRaw('id, name')
                ->where('user_id', $user->id)
                ->get();

            $response = $this->response_success(message: 'Show data was successful!', data: $categories);
            Log::info('SUCCESS: ShowCreateTransaction ', ['message' => 'Show data was successful!']);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: ShowCreateTransaction ', ['error' => $e]);
            $response = $this->response_error(message: 'Data query was fail!', data: null);
            return response()->json($response, 500);
        }
    }

    public function CreateTransaction(Request $request)
    {
        $user = $request->user();
        $validate = Validator::make($request->all(), [
            'transaction_name' => ['required', 'string'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'digits_between:1,10'],
            'type' => ['required', 'string', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'category_name' => ['required', 'string'],
        ], [
            'required' => 'The :attribute field is required',
            'in' => 'The :attribute field is invalid',
            'numeric' => 'The :attribute field must be numeric',
            'max' => 'The :attribute field must be less than :max characters',
            'string' => 'The :attribute field must be text',
        ], [
            'transaction_name' => 'Transaction Name',
            'transaction_date' => 'Transaction Date',
            'amount' => 'Amount',
            'type' => 'Type',
            'description' => 'Description',
            'category_name' => 'Category Name',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = $this->response_validate($errors); // membuat array yang berisi mesage
            return response()->json($response, 400);
        }

        try {
            DB::beginTransaction();
            $category = Category::where('name', $request->category_name)->first();

            $new_transaction = new Transaction([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'transaction_name' => $request->transaction_name,
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => $request->type,
                'description' => $request->description,
            ]);

            $new_transaction->save();

            $response = $this->response_success(message: 'Create Transaction Success!', data: $new_transaction);
            DB::commit();
            Log::info('SUCCESS: CreateTransaction ', ['message' => 'Create Transaction Success!']);
            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('ERROR: CreateTransaction ', ['error' => $e]);
            $response = $this->response_error(message: 'Create Transaction Faild!', data: null);
            return response()->json($response, 500);
        }
    }

    public function ShowUpdateTransaction($id)
    {
        try {
            $transaction = Transaction::query()
                ->join('categories', 'categories.id', '=', 'transactions.category_id')
                ->selectRaw('transactions.id, transaction_name, transaction_date, amount, type, transactions.description, categories.name as category_name')
                ->where('transactions.id', $id)
                ->get();

            $response = $this->response_success(message: 'Show data was successful!', data: $transaction);
            Log::info('SUCCESS: ShowUpdateTransaction ', ['message' => 'Show data was successful!']);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: ShowUpdateTransaction ', ['error' => $e]);
            $response = $this->response_error(message: 'Data query was fail!', data: null);
            return response()->json($response, 500);
        }
    }

    public function UpdateTransaction(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'transaction_name' => ['nullable', 'string'],
            'transaction_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'digits_between:1,10'],
            'type' => ['nullable', 'string', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'category_name' => ['nullable', 'string'],
        ], [
            'in' => 'The :attribute field is invalid',
            'numeric' => 'The :attribute field must be numeric',
            'max' => 'The :attribute field must be less than :max characters',
            'string' => 'The :attribute field must be text',
        ], [
            'transaction_name' => 'Transaction Name',
            'transaction_date' => 'Transaction Date',
            'amount' => 'Amount',
            'type' => 'Type',
            'description' => 'Description',
            'category_name' => 'Category',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = $this->response_validate($errors); // membuat array yang berisi mesage
            return response()->json($response, 400);
        }

        try {
            DB::beginTransaction();
            $old_transaction = Transaction::find($id);
            $category = Category::where('name', $request->category_name)->first();
            $old_transaction->update([
                'transaction_name' => $request->transaction_name,
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => $request->type,
                'description' => $request->description,
                'category_id' => $category->id,
            ]);

            $response = $this->response_success(message: 'Update Transaction Success!', data: $old_transaction);
            DB::commit();
            Log::info('SUCCESS: UpdateTransaction ', ['message' => 'Update Transaction Success!']);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('ERROR: UpdateTransaction ', ['error' => $e]);
            $response = $this->response_error(message: 'Update Transaction Faild!', data: null);
            return response()->json($response, 500);
        }
    }

    public function DeleteTransaction($id)
    {
        try {
            $transaction = Transaction::find($id);
            $transaction->delete();
            // $transaction->softDeletes(); // fungsi untuk hapus sementara data dengan syarat table harus memiliki field deleted_at
            $response = $this->response_success(message: 'Delete Transaction Success!', data: $transaction);
            Log::info('SUCCESS: DeleteTransaction ', ['message' => 'Delete Transaction Success!']);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: DeleteTransaction ', ['error' => $e]);
            $response = $this->response_error(message: 'Delete Transaction Faild!', data: null);
            return response()->json($response, 500);
        }
    }
}
