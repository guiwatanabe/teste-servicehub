<?php

namespace App\Jobs;

use App\Models\TicketDetail;
use App\Models\TicketFileProcess;
use App\Notifications\TicketDetailsAvailableNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessTicketAttachmentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $ticketFileProcessId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $process = TicketFileProcess::find($this->ticketFileProcessId);

        if (! $process) {
            Log::error("TicketFileProcess not found: {$this->ticketFileProcessId}");

            return;
        }

        try {
            $filePath = $process->file_path;
            $fullPath = Storage::path($filePath);

            if (! Storage::exists($filePath)) {
                throw new \Exception("File not found: $filePath");
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $contents = Storage::get($filePath);
            $type = 'text';

            if ($extension === 'json') {
                $parsed = json_decode($contents, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON: '.json_last_error_msg());
                }
                $type = 'json';
                $detailContent = json_encode($parsed, JSON_PRETTY_PRINT);
            } else {
                $detailContent = $contents;
            }

            TicketDetail::create([
                'ticket_id' => $process->ticket_id,
                'details' => ['type' => $type, 'content' => $detailContent],
            ]);

            /** @var \App\Models\Ticket $ticket */
            $ticket = $process->ticket;

            /** @var \App\Models\User $recipient */
            $recipient = $ticket->recipient;
            $recipient->notify(new TicketDetailsAvailableNotification($ticket));

            $process->status = 'completed';
            $process->save();
        } catch (\Throwable $e) {
            $process->status = 'failed';
            $process->error_message = $e->getMessage();
            $process->save();
            Log::error('Failed to process ticket attachment: '.$e->getMessage());
        }
    }
}
