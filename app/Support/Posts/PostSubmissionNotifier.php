<?php

namespace App\Support\Posts;

use App\Models\Post;
use App\Models\User;
use App\Notifications\PostSubmittedByCollaboratorNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class PostSubmissionNotifier
{
    /**
     * @return array{
     *     recipient_count: int,
     *     recipient_ids: array<int, int|string>,
     *     failed_recipient_ids: array<int, int|string>,
     *     channels: array<int, string>
     * }
     */
    public function notify(Post $post, User $submittedBy): array
    {
        $recipients = User::query()
            ->whereKeyNot($submittedBy->getKey())
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', ['editor', 'administrador']);
            })
            ->get();

        if ($recipients->isEmpty()) {
            Log::warning('post_submission_no_recipients', [
                'post_id' => $post->getKey(),
                'post_title' => $post->title,
                'submitted_by_id' => $submittedBy->getKey(),
                'submitted_by_email' => $submittedBy->email,
                'target_roles' => ['editor', 'administrador'],
                'channels' => ['database', 'mail'],
            ]);

            return [
                'recipient_count' => 0,
                'recipient_ids' => [],
                'failed_recipient_ids' => [],
                'channels' => ['database', 'mail'],
            ];
        }

        $notifiedRecipientIds = [];
        $failedRecipientIds = [];

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new PostSubmittedByCollaboratorNotification($post, $submittedBy));
                $notifiedRecipientIds[] = $recipient->getKey();
            } catch (Throwable $exception) {
                $failedRecipientIds[] = $recipient->getKey();

                Log::error('post_submission_notify_failed', [
                    'post_id' => $post->getKey(),
                    'post_title' => $post->title,
                    'submitted_by_id' => $submittedBy->getKey(),
                    'recipient_id' => $recipient->getKey(),
                    'recipient_email' => $recipient->email,
                    'channels' => ['database', 'mail'],
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('post_submission_notified', [
            'post_id' => $post->getKey(),
            'post_title' => $post->title,
            'submitted_by_id' => $submittedBy->getKey(),
            'recipient_count' => count($notifiedRecipientIds),
            'recipient_ids' => $notifiedRecipientIds,
            'failed_recipient_ids' => $failedRecipientIds,
            'channels' => ['database', 'mail'],
        ]);

        return [
            'recipient_count' => count($notifiedRecipientIds),
            'recipient_ids' => $notifiedRecipientIds,
            'failed_recipient_ids' => $failedRecipientIds,
            'channels' => ['database', 'mail'],
        ];
    }
}
