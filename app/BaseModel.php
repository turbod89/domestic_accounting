<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    // Allow for camelCased attribute access

    public function getAttribute($key)
    {
        return parent::getAttribute(snake_case($key));
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(snake_case($key), $value);
    }
}
