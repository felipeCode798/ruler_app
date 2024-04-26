<?php

namespace App\Filament\Personal\Resources\CourseResource\Pages;

use App\Filament\Personal\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}
