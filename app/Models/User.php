<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'branch_id',
        'password',
        'phone',
        'address',
        'country',
        'city',
        'state',
        'zip',
        'profile_image',
        'email_verified_at',
        'otp',
        'otp_expires_at',
        'is_active'
        ,'crm_team_id'
        ,'session_version'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'otp',
        'otp_expires_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
            'is_active' => 'boolean'
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'email_verified' => !is_null($this->email_verified_at)
        ];
    }

    public function contactMessages()
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function favoriteProducts()
    {
        return $this->hasMany(FavoriteProduct::class);
    }

    /**
     * المنتجات المفضلة مع تفاصيل المنتج
     */
    public function favoriteProductDetails()
    {
        return $this->belongsToMany(Product::class, 'favorite_products', 'user_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * العلاقة مع التصنيفات المفضلة
     */
    public function favoriteCategories()
    {
        return $this->hasMany(FavoriteCategory::class);
    }

    /**
     * التصنيفات المفضلة مع تفاصيل التصنيف
     */
    public function favoriteCategoryDetails()
    {
        return $this->belongsToMany(Category::class, 'favorite_categories', 'user_id', 'category_id')
            ->withTimestamps();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlists', 'user_id', 'product_id')
            ->withTimestamps();
    }
}
