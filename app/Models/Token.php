<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cryptommer\Smsir\Objects\Parameters;
use Cryptommer\Smsir\Smsir;

class Token extends Model
{
    use HasFactory;
    const EXPIRATION_TIME = 15; // minutes
    protected $fillable = [
        'code',
        'user_id',
        'used'
    ];
    public function __construct(array $attributes = [])
    {
        if (! isset($attributes['code'])) {
            $attributes['code'] = $this->generateCode();
        }
        parent::__construct($attributes);
    }
    /**
     * Generate a six digits code
     *
     * @param int $codeLength
     * @return string
     */
    public function generateCode($codeLength = 4)
    {
        $max = pow(10, $codeLength);
        $min = $max / 10 - 1;
        $code = mt_rand($min, $max);
        return $code;
    }
    /**
     * User tokens relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * True if the token is not used nor expired
     *
     * @return bool
     */
    public function isValid()
    {
        return ! $this->isUsed() && ! $this->isExpired();
    }
    /**
     * Is the current token used
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }
    /**
     * Is the current token expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->created_at->diffInMinutes(Carbon::now()) > static::EXPIRATION_TIME;
    }

    public function sendCode()
    {
        if (! $this->user) {
            throw new \Exception("No user attached to this token.");
        }
        if (! $this->code) {
            $this->code = $this->generateCode();
        }
        try {
            $mobile = auth()->user()->phone;
            $templateId = 100000;
            $send = Smsir::Send();
            // تولید کد
            $code = $this->generateCode();
            // ایجاد شیء Parameters با نام و مقدار کد تولید شده
            $codeParameter = new Parameters('code', $code);
            // ایجاد آرایه از پارامترها
            $parameters = [$codeParameter];

            $send->Verify($mobile, $templateId, $parameters);

        } catch (\Exception $ex) {
            return false; //enable to send SMS
        }
        return true;
    }
}
