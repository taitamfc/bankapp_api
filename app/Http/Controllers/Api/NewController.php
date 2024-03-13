<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\NewResource;
use App\Models\News;

class NewController extends Controller
{
    public function index()
    {
        $data = NewResource::collection(News::orderBy('id', 'desc')->paginate(5));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function getAllNews(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là
        $query = News::query(true);
        if ($request->search) {
            $query = $query->where('title', 'LIKE',"%" .$request->search . "%");
        }
        $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $news = NewResource::collection($items);
        $res = [
            'success' => true,
            'data' => $news,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ];
        return $res;
    }

    public function show(Request $request)
    {
        $data = News::find($request->id);
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function store(Request $request)
    {
        $news = new News;
        $news->title = $request->title;
        $news->content = $request->content;
        $news->save();
        $res = [
            'success' => true,
            'data' => $news,
        ];
        return $res;
    }

    public function update(Request $request)
    {
        $news = News::find($request->id);
        $news->title = $request->title;
        $news->content = $request->content;
        $news->save();
        $res = [
            'success' => true,
            'data' => $news,
        ];
        return $res;
    }

    public function delete($id){
        $news = News::find($id);
        if ($news) {
            $news->delete();
            return response()->json([
                'message' => 'Đã xóa thành công',
            ]);
        } else {
            return response()->json([
                'message' => 'Không tìm thấy người dùng',
            ], 404);
        }
    }
}
