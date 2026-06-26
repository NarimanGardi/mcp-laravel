<?php

namespace Gardi\McpLaravel\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $guarded = [];

    public $timestamps = false;
}
