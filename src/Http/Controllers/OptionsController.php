<?php

namespace Hubertnnn\LaravelNova\Fields\DynamicSelect\Http\Controllers;

use Hubertnnn\LaravelNova\Fields\DynamicSelect\DynamicSelect;
use Illuminate\Routing\Controller;
use Laravel\Nova\Actions\ActionMethod;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

class OptionsController extends Controller
{
    public function index(NovaRequest $request)
    {
        $attribute = $request->input('attribute');
        $dependValues = $request->input('depends');

        $resource = $request->newResource();

        $fields = $resource->updateFields($request);
        $field = $fields->findFieldByAttribute($attribute);

        // Flexible content compatibility:
        // https://github.com/whitecube/nova-flexible-content
        if (!$field) {
            foreach ($fields as $updateField) {
                if ($updateField->component == 'nova-flexible-content') {
                    foreach ($updateField->meta['layouts'] as $layout) {
                        foreach ($layout->fields() as $layoutField) {
                            if ($layoutField->attribute == $attribute) {
                                $field = $layoutField;
                            }
                        }
                    }
                }
            }
        }

        /** @var DynamicSelect $field */
        $options = $field->getOptions($dependValues);

        return [
            'options' => $options,
        ];
    }

    public function action(ActionRequest $request)
    {
        $attribute = $request->input('attribute');
        $dependValues = $request->input('depends');

        $fields = $this->resolveFields($request);

        $field = $fields->get($attribute);
        $options = $field->getOptions($dependValues);

        return [
            'options' => $options,
        ];
    }

    public function resolveFields($request)
    {
        return collect($request->action()->fields())->mapWithKeys(function ($field) {
            return [$field->attribute => $field];
        });
    }
}
