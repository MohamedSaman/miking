<?php


namespace App\Models;

use App\Models\Attendance;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contact',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // Relationship: User has many attendances
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    // Relationship: User has one UserDetail
    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'id');
    }

    // Relationship: User has many staff permissions
    public function staffPermissions()
    {
        return $this->hasMany(StaffPermission::class, 'user_id', 'id');
    }

    // Relationship: User has many staff products
    public function staffProducts()
    {
        return $this->hasMany(StaffProduct::class, 'staff_id', 'id');
    }

    // Relationship: User has many sales
    public function sales()
    {
        return $this->hasMany(Sale::class, 'user_id', 'id');
    }

    /**
     * Check if user has a specific permission
     * Some pages are always accessible to staff without permission
     */
    public function hasPermission($permissionKey)
    {
        if ($this->role === 'admin') {
            return true; // Admin has all permissions
        }

        // These permissions are always available to staff without admin approval
        $alwaysAllowedPermissions = [
            'menu_dashboard',      // Overview
        ];

        if (in_array($permissionKey, $alwaysAllowedPermissions)) {
            return true;
        }

        // If staff has no permissions assigned, grant full access by default
        $hasAnyPermissions = StaffPermission::where('user_id', $this->id)->exists();
        if (!$hasAnyPermissions) {
            return true;
        }

        // For other permissions, check if admin has granted them
        return $this->staffPermissions()
            ->where('permission_key', $permissionKey)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Get the attendance status for a specific date based on activity (sessions and sales)
     */
    public function getAttendanceStatus($date = null)
    {
        $date = $date ?: now()->toDateString();
        $isToday = $date === now()->toDateString();
        
        // 1. Check if there is an official attendance record
        $attendance = $this->attendances()->whereDate('date', $date)->first();
        
        if ($attendance) {
            // Check if there was any system activity on this date even if record is official
            $startOfDay = \Carbon\Carbon::parse($date)->startOfDay()->timestamp;
            $endOfDay = \Carbon\Carbon::parse($date)->endOfDay()->timestamp;
            $hasSession = \DB::table('sessions')->where('user_id', $this->id)->whereBetween('last_activity', [$startOfDay, $endOfDay])->exists();
            $hasSales = $this->sales()->whereDate('created_at', $date)->exists();

            return [
                'status' => strtolower($attendance->status),
                'present_status' => strtolower($attendance->present_status),
                'type' => 'official',
                'record' => $attendance,
                'has_activity' => $hasSession || $hasSales
            ];
        }
        
        // 2. If no official record, check for system activity on that day
        // For sessions: check if any session was active during that day
        $startOfDay = \Carbon\Carbon::parse($date)->startOfDay()->timestamp;
        $endOfDay = \Carbon\Carbon::parse($date)->endOfDay()->timestamp;
        
        $hasActivityOnDate = \DB::table('sessions')
            ->where('user_id', $this->id)
            ->whereBetween('last_activity', [$startOfDay, $endOfDay])
            ->exists();
        
        // Check sales on that day
        $salesCount = $this->sales()
            ->whereDate('created_at', $date)
            ->count();
        
        if ($hasActivityOnDate || $salesCount > 0) {
            // New Rule: If sales count >= 2 and no official record exists, automatically mark as present
            if ($salesCount >= 2) {
                $this->markAttendance('present', $date);
                // Return official status now that we just created it
                return [
                    'status' => 'present',
                    'present_status' => 'ontime',
                    'type' => 'official',
                    'is_auto_marked' => true,
                    'has_activity' => true
                ];
            }

            return [
                'status' => 'pending',
                'present_status' => 'pending',
                'type' => 'detected',
                'sales_count' => $salesCount,
                'has_activity' => true,
                'is_today' => $isToday,
                'reason' => $salesCount >= 2 ? 'Auto-Attended (Sales)' : ($salesCount > 0 ? 'Sales Detected' : 'Login Detected')
            ];
        }
        
        return [
            'status' => 'absent',
            'present_status' => 'absent',
            'type' => 'none',
            'has_activity' => false,
            'is_today' => $isToday
        ];
    }

    /**
     * Manually mark attendance for a user for a specific date
     */
    public function markAttendance($status = 'present', $date = null)
    {
        $date = $date ?: now()->toDateString();
        
        return Attendance::updateOrCreate(
            ['user_id' => $this->id, 'date' => $date],
            [
                'status' => $status,
                'present_status' => $status === 'present' ? 'ontime' : null,
                'check_in' => ($status === 'present' && $date === now()->toDateString()) ? now()->format('H:i:s') : null
            ]
        );
    }
}
