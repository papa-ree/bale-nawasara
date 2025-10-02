<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Paparee\BaleNawasara\App\Traits\GeneratesTicketNumber;

class HelpdeskForm extends Model
{
    use GeneratesTicketNumber;
    use HasUuids;

    // Atur prefix khusus untuk model ini
    protected string $ticketPrefix = 'ADU';

    protected $guarded = ['id'];
}
