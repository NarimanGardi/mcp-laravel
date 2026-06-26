<?php

namespace Gardi\McpLaravel\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $table = 'posts';

    protected $guarded = [];

    public $timestamps = false;

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
