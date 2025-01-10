<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class GravatarProvider implements Contracts\AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        return self::generateGravatarUrl($record->email);
    }

    public static function generateGravatarUrl($email, $size = null): string
    {
        $hash = md5(strtolower(trim($email)));

        return "https://gravatar.com/avatar/{$hash}?".http_build_query(array_filter([
            'd' => 'mp',
            's' => $size,
        ]));
    }
}
