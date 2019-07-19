<?php

namespace CloudCreativity\LaravelStripe\Http\Requests;

use CloudCreativity\LaravelStripe\Connect\AuthorizeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorizeConnect extends FormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'code' => [
                'required_without:error',
                'string',
            ],
            'state' => [
                'required',
                'string',
            ],
            'scope' => [
                'required_with:code',
                Rule::in(AuthorizeUrl::scopes()),
            ],
            'error' => [
                'required_without:code',
                'string',
            ],
            'error_description' => [
                'required_with:error',
                'string',
            ],
        ];
    }

    /**
     * @return array|null
     */
    public function error()
    {
        return $this->only('error', 'error_description') ?: null;
    }

    /**
     * @return array
     */
    protected function validationData()
    {
        return $this->query();
    }
}
