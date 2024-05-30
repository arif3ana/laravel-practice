<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}
