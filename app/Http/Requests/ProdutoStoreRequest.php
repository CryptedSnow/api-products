<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProdutoStoreRequest extends FormRequest
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
            'nome'          => 'required|string',
            'valor'         => 'required|numeric',
            'quantidade'    => 'required|integer|min:0',
            'fora_validade' => 'required|boolean',
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
