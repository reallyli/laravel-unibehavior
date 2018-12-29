<?php
/**
 * Created by PhpStorm.
 * User: uniqueway
 * Date: 2018/12/28
 * Time: 上午11:19
 */

namespace Reallyli\LaravelUnibehavior;

use Illuminate\Database\Eloquent\Model;

class BehaviorLog extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var array
     */
    protected $hidden = ['deleted_at'];

    /**
     * BehaviorLog constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (! isset($this->table)) {
            $this->setTable(config('unibehavior.table_name'));
        }

        parent::__construct($attributes);
    }
}