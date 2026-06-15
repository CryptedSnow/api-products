<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProdutoUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome'          => 'sometimes|string',
            'valor'         => 'sometimes|numeric',
            'quantidade'    => 'sometimes|integer|min:0',
            'fora_validade' => 'sometimes|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Houve falha(s) na(s) validações.',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
