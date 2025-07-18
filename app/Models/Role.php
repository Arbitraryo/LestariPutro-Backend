<?php
// app/Models/Role.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model
 */
class Role extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}