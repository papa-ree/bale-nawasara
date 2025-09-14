<?php

namespace Paparee\BaleNawasara\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Paparee\BaleNawasara\App\Services\WagoService;

class SendWagoMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $phone;
    protected string $message;
    protected ?string $replyMessageId;

    public function __construct(string $phone, string $message, ?string $replyMessageId = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->replyMessageId = $replyMessageId;
    }

    public function handle(): void
    {
        (new WagoService())->sendMessage($this->phone, $this->message, $this->replyMessageId);
    }
}
