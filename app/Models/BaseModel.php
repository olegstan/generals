<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Sofa\Eloquence\Subquery;
use App\Helpers\Picture;

/**
 * Class BaseModel
 * @package App\Models
 */
class BaseModel extends Model
{
    /**
     * @var bool
     */
    public static $uniquieHash = true;
	
	
	public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * @param $field
     * @param int $count
     * @return string
     */
    public static function getUniqueHash($field, $count = 32)
    {
        if (self::$uniquieHash) {
            $hash = str_random($count);
            $item = static::where($field, $hash)->first();
            if ($item) {
                self::getUniqueHash($field, $count);
            } else {
                return $hash;
            }
        } else {
            $item = static::orderBy('id', 'desc')->limit(1)->first();
            if ($item) {
                return $field . '_' . $item->id;
            } else {
                return $field . '_' . 1;
            }
        }
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active ? true : false;
    }

    /**
     * @param $attr
     * @return bool
     */
    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }

    /**
     * @param $field
     * @param array $values
     */
    public function fieldSwitch($field, $values = [0, 1])
    {
        if ($this->{$field} === $values[0]) {
            $this->update([$field => $values[1]]);
        } else {
            $this->update([$field => $values[0]]);
        }
    }

    public function newQuery()
    {
        $query = parent::newQuery();


        return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where($this->table . '.active', 1);
    }

    /**
     * @param $query
     * @param Carbon $data
     * @param string $field
     */
    public function scopeWhereDay($query, Carbon $data, $field = 'created_at')
    {
        $data2 = clone $data;
        $query->where($field, '>=', $data)->where($field, '<', $data2->addDay());
    }

    /**
     * @param $query
     * @param string $field
     */
    public function scopeWhereToday($query, $field = 'created_at')
    {
        $query->whereDay(Carbon::today(), $field);
    }

    /**
     * @param $field
     * @return int
     */
    public function getDiffSecond($field)
    {
        return Carbon::now()->diffInSeconds($this->{$field});
    }

    /**
     * @param $value
     * @param int $round
     * @return float
     */
    public function round($value, $round = 10)
    {
        return round($value, $round);
    }

    /**
     * @param string $related
     * @param null $foreignKey
     * @param null $ownerKey
     * @param null $relation
     * @return $this|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation)->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @param $query
     * @param $build
     * @param $alias
     */
    public function scopeAddSubQuery($query, $build, $alias)
    {
        $sub = new Subquery(
            $build,
            $alias
        );
        $query->addSelect($sub)->addBinding($sub->getBindings(), 'select');
    }

    /**
     * @param $query
     * @param $date
     * @param string $field
     * @param string $format
     */
	public function scopeDateStart($query, $date, $field = 'created_at', $format = 'd.m.Y')
    {
		$query->whereDate($field, '>=', $this->convertToDate($date, $format)->endOfDay());
	}

    /**
     * @param $query
     * @param $date
     * @param string $field
     * @param string $format
     */
	public function scopeDateEnd($query, $date, $field = 'created_at', $format = 'd.m.Y')
    {
		$query->whereDate($field, '<=', $this->convertToDate($date, $format)->endOfDay());
	}

    /**
     * @param $query
     * @param array $filters
     * @param string $field
     * @param string $format
     * @return mixed
     */
	public function scopePeriod($query, array $filters, $field = 'created_at', $format = 'd.m.Y')
    {
        if (! (count($filters) >= 2)) {
            return $query;
        }

        if (key_exists('startDate', $filters) && key_exists('endDate', $filters)) {
            [$startDate, $endDate] = [$filters['startDate'], $filters['endDate']];
        } else {
            [$startDate, $endDate] = $filters;
        }

        if ($startDate && $endDate) {
            $query->whereDate($field, '<=', $this->convertToDate($endDate, $format)->endOfDay())
                ->whereDate($field, '>=', $this->convertToDate($startDate, $format)->startOfDay());
        }

        return $query;
    }

    /**
     * @param string $time
     * @param string $format
     *
     * @return \Carbon\Carbon
     */
	public static function convertToDate(string $time, string $format = 'Y-m-d'): Carbon
    {
        return Carbon::createFromFormat($format, $time);
    }

    /**
     * @param $field
     * @param int $length
     * @return string
     */
	public static function getUniqueHashImg($field, $length = 15)
    {
		return self::getUniqueHash($field, $length);
	}
}