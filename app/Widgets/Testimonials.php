<?php

namespace App\Widgets;

use App\Helpers\HelperFunction;
use Arrilot\Widgets\AbstractWidget;

class Testimonials extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        return [
            'view' => 'widgets.testimonials',
            'data' => [
                'config' => $this->config
            ]
        ];
    }
}
