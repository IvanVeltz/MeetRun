<?php

namespace App\Data;

class SearchDataEvent{

    public ?string $q = null;
    public array $departements = [];
    public ?int $distanceMin = null;
    public ?int $distanceMax = null;     
    public int $page = 1;
}