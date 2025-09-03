<?php

namespace Paparee\BaleNawasara\App\Services;

use Paparee\BaleNawasara\App\Models\IpPublic;

class IpPublicService
{
    public function updateComment(IpPublic $ip, string $comment, ?string $mikrotikId = null): IpPublic
    {

        $data = [
            'comment' => $comment,
            'dynamic' => 'false',
        ];

        if ($mikrotikId) {
            $data['id'] = $mikrotikId;
        }

        $ip->update($data);

        return $ip;
    }
}
