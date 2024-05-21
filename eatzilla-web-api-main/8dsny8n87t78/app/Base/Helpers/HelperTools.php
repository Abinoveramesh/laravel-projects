<?php

namespace App\Base\Helpers;

use Illuminate\Support\Facades\Log;

trait HelperTools
{
    public static function refundStatus($value)
    {
        if ($value === 'TS-REFC') {
              $status = '1';
            } else if ($value === 'TS-REFA') {
              $status = '0';
            } else if ($value === 'TS-REFF') {
                $status = '2';
            } else if ($value === 'TS-REFD') {
               $status = '3';
            }
        return $status;
    }
}
?>