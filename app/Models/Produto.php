<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Attributes\{Fillable, Table};

#[Table('produtos', key:'id')]
#[Fillable(['nome', 'valor', 'quantidade', 'fora_validade'])]
class Produto extends Model
{
    use HasFactory, SoftDeletes;
}
