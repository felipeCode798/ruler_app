<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'dni'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }


    public function paymentProcesses()
    {
        return $this->hasMany(PaymentProcess::class, 'responsible_id');
    }

    public function paymentControversies()
    {
        return $this->hasMany(PaymentControversy::class, 'responsible_id');
    }

    public function licensesPayments()
    {
        return $this->hasMany(LicensesPayment::class, 'responsible_id');
    }

    public function paymentCourses()
    {
        return $this->hasMany(PaymentCourse::class, 'responsible_id');
    }

    public function paymentRenewalls()
    {
        return $this->hasMany(PaymentRenewall::class, 'responsible_id');
    }

    public function expense()
    {
        return $this->hasMany(Expense::class, 'responsible_id');
    }
}
