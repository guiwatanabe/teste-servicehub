<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, UserProfile::class, 'company_id', 'id', 'id', 'user_id');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(UserProfile::class)
            ->where('role', 'admin');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(UserProfile::class)
            ->where('role', 'manager');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(UserProfile::class)
            ->where('role', 'employee');
    }

    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(Ticket::class, Project::class);
    }
}
