<?php

namespace App;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Jedrzej\Sortable\SortableTrait;
use App\ModelFilters\ProfileFilter;
use EloquentFilter\Filterable;

class Profile extends Model
{
    use Sluggable, Filterable, SortableTrait;

    /**
     * Date Fields.
     *
     * @var array
     */
    protected $dates = ['founded_at' ,'created_at', 'updated_at'];

    /**
     * Guarded attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Parameter key to listen for sorting.
     *
     * @var string
     */
    protected $sortParameterName = 'sorteer';

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Return the ModelFilter for Profile
     *
     * @return \EloquentFilter\ModelFilter
     */
    public function modelFilter()
    {
        return $this->provideFilter(ProfileFilter::class);
    }

    /**
     * Return the list of sortable fields
     *
     * @return array
     */
    public function getSortableAttributes()
    {
        return ['name', 'city', 'country', 'highlight'];
    }

    /**
     * Format the hourly_rate attribute
     *
     * @param $value
     * @return string
     */
    public function getHourlyRateAttribute($value)
    {
        if(is_null($value) || $value == 0) {
            return '?';
        } else {
            return format_valuta($value);
        }
    }

    /**
     * User relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Owner (User) relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tag relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}