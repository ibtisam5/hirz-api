<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\IslamicSource;
use App\Services\HirzAiService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    // =========================
    // Create conversation
    // =========================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'emotion' => 'required|string|max:100',
            'context' => 'nullable|string',
        ]);

        $conversation = Conversation::create([
            'user_id' => $request->user()->id,
            'emotion' => $validated['emotion'],
            'context' => $validated['context'] ?? null,
            'title' => $validated['emotion'],
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'تم إنشاء المحادثة بنجاح',
            'conversation' => $conversation,
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Active conversations
    // =========================
    public function index(Request $request)
    {
        $conversations = Conversation::where(
            'user_id',
            $request->user()->id
        )
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conversation) {

                $lastMessage = Message::where(
                    'conversation_id',
                    $conversation->id
                )
                    ->latest('id')
                    ->first();

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'emotion' => $conversation->emotion,
                    'status' => $conversation->status,
                    'updated_at' => $conversation->updated_at,

                    'last_message' => $lastMessage ? [
                        'role' => $lastMessage->role,
                        'content' => $lastMessage->content,
                        'created_at' => $lastMessage->created_at,
                    ] : null,
                ];
            });

        return response()->json([
            'conversations' => $conversations,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Archived conversations
    // =========================
    public function archived(Request $request)
    {
        $conversations = Conversation::where(
            'user_id',
            $request->user()->id
        )
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conversation) {

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'emotion' => $conversation->emotion,
                    'status' => $conversation->status,
                    'updated_at' => $conversation->updated_at,
                ];
            });

        return response()->json([
            'conversations' => $conversations,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Show one conversation
    // =========================
    public function show(
        Request $request,
        Conversation $conversation
    ) {
        $this->authorizeConversation($request, $conversation);

        $messages = Message::where(
            'conversation_id',
            $conversation->id
        )
            ->orderBy('id')
            ->get();

        $formattedMessages = $messages->map(function ($message) {

            $source = null;

            if ($message->islamic_source_id) {

                $islamicSource = IslamicSource::find(
                    $message->islamic_source_id
                );

                if ($islamicSource) {
                    $source = [
                        'id' => $islamicSource->id,
                        'type' => $islamicSource->type,
                        'reference' => $islamicSource->reference,
                    ];
                }
            }

            return [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'source' => $source,
                'created_at' => $message->created_at,
            ];
        });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'emotion' => $conversation->emotion,
                'context' => $conversation->context,
                'title' => $conversation->title,
                'status' => $conversation->status,
            ],

            'messages' => $formattedMessages,

        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Send message
    // =========================
    public function sendMessage(
        Request $request,
        Conversation $conversation,
        HirzAiService $hirzAi
    ) {
        $this->authorizeConversation($request, $conversation);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['content'],
        ]);

        $aiReply = $hirzAi->reply(
            $validated['content'],
            $conversation->emotion
        );

        $assistantMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $aiReply['text'],
            'islamic_source_id' => $aiReply['source_id'],
        ]);

        $conversation->touch();

        $islamicSource = null;

        if ($assistantMessage->islamic_source_id) {

            $islamicSource = IslamicSource::find(
                $assistantMessage->islamic_source_id
            );
        }

        return response()->json([
            'message' => 'تمت معالجة الرسالة بنجاح',

            'user_message' => [
                'id' => $message->id,
                'content' => $message->content,
                'created_at' => $message->created_at,
            ],

            'reply' => [
                'id' => $assistantMessage->id,
                'text' => $assistantMessage->content,
                'created_at' => $assistantMessage->created_at,

                'source' => $islamicSource ? [
                    'id' => $islamicSource->id,
                    'type' => $islamicSource->type,
                    'reference' => $islamicSource->reference,
                ] : null,
            ],

        ], 201, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Archive conversation
    // =========================
    public function archive(
        Request $request,
        Conversation $conversation
    ) {
        $this->authorizeConversation($request, $conversation);

        $conversation->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'message' => 'تمت أرشفة المحادثة بنجاح',

            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'status' => $conversation->status,
                'updated_at' => $conversation->updated_at,
            ],

        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Restore conversation
    // =========================
    public function restore(
        Request $request,
        Conversation $conversation
    ) {
        $this->authorizeConversation($request, $conversation);

        $conversation->update([
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'تمت استعادة المحادثة بنجاح',

            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'status' => $conversation->status,
                'updated_at' => $conversation->updated_at,
            ],

        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Delete conversation
    // =========================
    public function destroy(
        Request $request,
        Conversation $conversation
    ) {
        $this->authorizeConversation($request, $conversation);

        if ($conversation->status !== 'archived') {
            return response()->json([
                'message' => 'يجب أرشفة المحادثة أولًا قبل حذفها نهائيًا',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        Message::where(
            'conversation_id',
            $conversation->id
        )->delete();

        $conversation->delete();

        return response()->json([
            'message' => 'تم حذف المحادثة نهائيًا',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    // =========================
    // Check conversation owner
    // =========================
    private function authorizeConversation(
        Request $request,
        Conversation $conversation
    ): void {
        if ($conversation->user_id !== $request->user()->id) {
            abort(
                403,
                'غير مصرح لك بالوصول إلى هذه المحادثة'
            );
        }
    }
}
