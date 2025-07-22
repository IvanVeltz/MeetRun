<?php

namespace App\Data;

class SearchDataRunner{

    public ?string $q = null;     
    public array $departements = [];     
    public ?int $ageMin = null;
    public ?int $ageMax = null;
    public array $sexe = [];
    public array $levels = [];
    public int $page = 1;
}