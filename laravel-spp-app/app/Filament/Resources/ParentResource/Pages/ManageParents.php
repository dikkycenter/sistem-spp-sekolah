<?php

namespace App\Filament\Resources\ParentResource\Pages;

use App\Filament\Resources\ParentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageParents extends ManageRecords
{
    protected static string $resource = ParentResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
