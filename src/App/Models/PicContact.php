<?php

namespace Paparee\BaleNawasara\App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PicContact extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected function contactName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Str::ucfirst($value) : null,
            set: fn (?string $value) => $value ? Str::lower($value) : null,
        );
    }

    protected function contactJob(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Str::ucfirst($value) : null,
            set: fn (?string $value) => $value ? Str::lower($value) : null,
        );
    }

    protected function contactOffice(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Str::ucfirst($value) : null,
            set: fn (?string $value) => $value ? Str::lower($value) : null,
        );
    }

    protected function contactPhone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function contactPhoneHash(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? hash('sha256', $value) : null,
            set: fn (?string $value) => $value ? hash('sha256', $value) : null,
        );
    }

    protected function contactNip(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function contactNipHash(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? hash('sha256', $value) : null,
            set: fn (?string $value) => $value ? hash('sha256', $value) : null,
        );
    }

    protected function recoveryEmailAddress(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : "-",
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : "-",
        );
    }

    protected function recoveryEmailAddressHash(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? hash('sha256', $value) : null,
            set: fn (?string $value) => $value ? hash('sha256', $value) : null,
        );
    }

    public function subdomains(): HasMany
    {
        return $this->hasMany(DnsRecord::class);
    }
}
