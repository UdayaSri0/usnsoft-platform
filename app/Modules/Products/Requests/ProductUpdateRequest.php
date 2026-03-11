<?php

namespace App\Modules\Products\Requests;

use App\Modules\Products\Models\Product;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends ProductStoreRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $product = $this->route('product');

        $rules['slug'] = [
            'required',
            'string',
            'max:160',
            Rule::unique('products', 'slug_current')->ignore($product instanceof Product ? $product->getKey() : null),
        ];

        return $rules;
    }
}
