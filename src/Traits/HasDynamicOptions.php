<?php

namespace Hubertnnn\LaravelNova\Fields\DynamicSelect\Traits;

use Closure;

trait HasDynamicOptions
{
    protected $options = [];

    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions($parameters = [])
    {
        if (is_string($parameters)) {
            $parameters = json_decode($parameters, true);
        }

        $options = $this->options instanceof Closure
            ? call_user_func($this->options, $parameters)
            : $this->options;

        $result = [];
        foreach ($options as $key => $option) {
            $result[] = [
                'value' => $key,
                'label' => $option,
            ];
        }

        return $result;
    }
}
