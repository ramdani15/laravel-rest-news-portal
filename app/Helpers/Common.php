<?php

if (! function_exists('checkPerm')) {
    /**
     * Check Permision Role using Entrust
     *
     * @param  string  $name
     */
    function checkPerm($name = '', $show_abort = false)
    {
        if ($show_abort) {
            if (! auth()->user()->can($name)) {
                if (request()->expectsJson()) {
                    throw new \App\Exceptions\JsonException('You don\'t have permission', 403);
                }
                abort(401, 'You don\'t have permission');
            }
        } else {
            return auth()->user()->can($name) ? true : false;
        }
    }
}

if (! function_exists('getMaxImageSize')) {
    /**
     * Get Max Image Size
     *
     * @return int
     */
    function getMaxImageSize()
    {
        return 5 * 1024;
    }
}

if (! function_exists('getImageTypesValidation')) {
    /**
     * Get Image Types Validation
     *
     * @return string
     */
    function getImageTypesValidation()
    {
        return 'jpg,jpeg,bmp,png,gif,svg';
    }
}
