<?php

namespace App\Http\Requests;

use App\Rules\FederationMembershipPlanIsNotActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MembershipRequest extends FormRequest
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
     * @return array<string, mixed>
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function rules(): array
    {
        return [
            'name' => 'string|required|max:255',
            'federation_id' => 'integer|required',
            'plans' => 'nullable|array',
            'plan_ids.*' => ['integer', 'nullable', new FederationMembershipPlanIsNotActiveRule(request()->get('federation_id'))],
            'current_term_starts_at' => 'nullable|date',
            'current_term_ends_at' => 'nullable|date',
        ];
    }
}
