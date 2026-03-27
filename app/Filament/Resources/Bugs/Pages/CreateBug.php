<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Filament\Resources\Bugs\BugResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBug extends CreateRecord
{
    protected static string $resource = BugResource::class;

    protected function afterCreate(): void
    {
        $sessionKey = 'bug_ai_uses_'.session()->getId();
        $uses = session()->pull($sessionKey, 0);

        if ($uses > 0) {
            $this->record->update([
                'ai_uses' => $uses,
                'ai_last_used_at' => now(),
            ]);
        }
    }
}
