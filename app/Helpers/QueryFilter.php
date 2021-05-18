<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-04-30
 * Time: 22:35
 */

namespace App\Helpers;

use App\Models\Attribute;
use App\Models\AttributeToCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected $builder = null;
    private $request = null;
    protected $attributes = [];
    protected $exclude_params = [];

    public function __construct(Request $request)
    {
        $this->request = $request;

        foreach (Attribute::whereHas(
            'to_categories', function ($query) use ($request) {
                $query->select('category_id');
                $query->where('category_id', $request->input('category_id'));
            })
            ->select('slug', 'attributes.id')->get() as $attr_info) {
            $this->attributes[$attr_info->slug] = $attr_info->id;
        }
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        $filters = $this->filters();

        foreach ($filters as $filter => $value) {
            if ((!in_array($filter, $this->getExcludeParams())) && method_exists($this, $filter)) {
                if (!is_null($value)) $this->$filter($value);
            }
        }

        return $this->builder;
    }

    public function filters()
    {
        return $this->request->all();
    }

    public function setExcludeParams(array $params)
    {
        $this->exclude_params = $params;

        return $this;
    }

    public function getExcludeParams(): array
    {
        return $this->exclude_params;
    }

    public function getGeneratedAttributes()
    {
        return $this->attributes;
    }
}