<?php

namespace App\Validator;

use Illuminate\Support\Facades\Validator;

class ValidationExtend
{
    public static function extend(){
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });

        Validator::extend('custom_name', function ($attribute, $value) {
            return preg_match("/(^([a-zA-Z'Ññ\s]+)(\d+)?$)/u", $value);
        });

        Validator::extend('custom_address', function ($attribute, $value) {
            return preg_match('/^(?=.*[a-zA-Z]).+$/', $value);
        });

        Validator::extend('custom_phone', function ($attribute, $value) {
            return preg_match('/^([0-9\s\-\+\(\)]*)$/', $value);
        });

        Validator::extend('custom_text', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9\,\.\s_-]+$/', $value);
        });

        Validator::extend('custom_username', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9\,\.\@\s_-]+$/', $value);
        });

        Validator::extend('custom_first_name', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z\s]+$/', $value);
        });

        Validator::extend('custom_zipcode',function ($attribute, $value){
            return preg_match('/^[a-zA-Z\d\-\s]+$/', $value);
        });

        Validator::extend('policy_number',function ($attribute, $value){
            return preg_match('/^([A-Z]?)(?=.*\d).+$/', $value);
        });

        Validator::extend('alpha_hyphen', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z\-\,\.\s]+$/', $value);
        });

        Validator::extend('numeric_hyphen', function ($attribute, $value) {
            return preg_match('/^[0-9\-]+$/', $value);
        });

        Validator::extend('alpha_hyphen_space', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9\-\s]+$/', $value);
        });

        Validator::extend('alpha', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z]+$/', $value);
        });

        Validator::extend('alpha_numeric', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
        });

        Validator::extend('alpha_numeric_with_equal_operator', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9\=]+$/', $value);
        });

        Validator::extend('numeric_hyphen_with_commas', function ($attribute, $value) {
            return preg_match('/^[0-9\-\,]+$/', $value);
        });

        Validator::extend('float', function ($attribute, $value) {
            return preg_match('/^[0-9\.]+$/', $value);
        });

        Validator::extend('alpha_numeric_with_tilde_operator', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9\~\-]+$/', $value);
        });
    }
}
