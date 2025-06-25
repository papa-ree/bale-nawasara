<?php

namespace Paparee\BaleNawasara\App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasEncryptedAttributes
{
    public static function bootHasEncryptedAttributes()
    {
        static::saving(function ($model) {
            foreach ($model->getEncryptedAttributes() as $field) {
                if (isset($model->attributes[$field])) {
                    try {
                        // Skip jika sudah terenkripsi
                        Crypt::decryptString($model->attributes[$field]);
                    } catch (\Exception $e) {
                        // Lanjutkan encrypt jika belum terenkripsi
                        $plain = $model->{$field};
                        $model->attributes[$field] = Crypt::encryptString($plain);
                        $model->attributes["{$field}_hash"] = hash('sha256', $plain);

                        logger("Encrypted [$field] => $plain");
                    }
                }
            }
        });
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->getEncryptedAttributes()) && isset($this->attributes[$key])) {
            try {
                return Crypt::decryptString($this->attributes[$key]);
            } catch (\Exception $e) {
                return null;
            }
        }

        return parent::getAttribute($key);
    }

    abstract public function getEncryptedAttributes(): array;
}
