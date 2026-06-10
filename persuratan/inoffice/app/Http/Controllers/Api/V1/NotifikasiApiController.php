<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiResource;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotifikasiApiController extends Controller
{
    /**
     * GET /api/v1/notifikasi
     * List semua notifikasi user yang sedang login (terbaru dulu).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 25;

        $query = Notifikasi::where('user_id', Auth::id())
            ->orderByDesc('created_at');

        // Opsional: filter hanya yang belum dibaca
        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $list = $query->paginate($perPage);

        return response()->json([
            'data' => NotifikasiResource::collection($list->items()),
            'meta' => [
                'current_page' => $list->currentPage(),
                'last_page'    => $list->lastPage(),
                'per_page'     => $list->perPage(),
                'total'        => $list->total(),
                'unread_count' => Notifikasi::where('user_id', Auth::id())->where('is_read', false)->count(),
            ],
            'links' => [
                'prev' => $list->previousPageUrl(),
                'next' => $list->nextPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/v1/notifikasi/unread-count
     * Hitung notifikasi belum dibaca untuk badge di bottom tab.
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notifikasi::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'data' => ['count' => $count],
        ]);
    }

    /**
     * PATCH /api/v1/notifikasi/{id}/read
     * Tandai notifikasi sebagai sudah dibaca.
     */
    public function markRead(int $id): JsonResponse
    {
        $notifikasi = Notifikasi::where('user_id', Auth::id())
            ->findOrFail($id);

        $notifikasi->markAsRead();

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data'    => new NotifikasiResource($notifikasi->fresh()),
        ]);
    }
}
