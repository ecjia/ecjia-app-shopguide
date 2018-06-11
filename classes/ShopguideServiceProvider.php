<?php

namespace Ecjia\App\Shopguide;

use Royalcms\Component\App\AppServiceProvider;

class ShopguideServiceProvider extends  AppServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-shopguide');
    }
    
    public function register()
    {
        
    }
    
    
    
}